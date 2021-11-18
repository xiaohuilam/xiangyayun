<?php

namespace App\Service;

use App\Model\UcsFirewall;
use App\Model\UcsInstance;
use App\Model\UcsIp;
use App\Model\UcsMaster;
use App\Model\UcsPlan;
use App\Model\UcsRegion;
use App\Model\UcsStoragePlan;
use App\Model\UcsStorageRalation;
use App\Model\UcsSystem;

class UcsService
{
    public static function FindUcsInstanceById($instance_id)
    {
        return UcsInstance::create()->get([
            'id' => $instance_id
        ]);
    }

    //获取UCS即将过期的实例
    public static function SelectUcsInstanceBySoonExpire()
    {
        return UcsInstance::create()
            ->where('expire_time', date('Y-m-d H:i:s', strtotime('-5 day')), '>')
            ->all();
    }

    public static function SelectUcsIPByUcsInstanceId($instance_id)
    {
        return UcsIp::create()
            ->where('ucs_instance_id', $instance_id)
            ->all();
    }

    //续费
    public static function ReNew($instance_id)
    {


    }

    //$instance_id
    public static function FindUcsStorageRalationByUcsInstanceId($instance_id)
    {
        //获取相关磁盘参数
        return UcsStorageRalation::create()
            ->where('ucs_instance_id', $instance_id)
            ->all();
    }

    //获取UCS列表
    public static function SelectListPage($where, $page, $size)
    {
        $ucs_instances = UcsInstance::create();
        foreach ($where as $key => $value) {
            $ucs_instances->where($value);
        }
        $ucs_instances
            ->limit($size * ($page - 1), $size)
            ->all();
    }

    public static function FindUcsSystemById($system_id)
    {
        return UcsSystem::create()->get(['id' => $system_id]);
    }

    public static function FindUcsPlanById($plan_id)
    {
        return UcsPlan::create()->get([
            "id" => $plan_id
        ]);
    }

    public static function EditUcsFirewall($params)
    {
        $ucs_firewall = null;
        if (array_key_exists('id', $params)) {
            $ucs_firewall = UcsFirewall::create()->get(['id' => $params['id']]);
            //存在即修改
            if ($ucs_firewall) {
                return UcsFirewall::create()->update($params, ['id' => $params['id']]);
            }
            return false;
        }
        return UcsFirewall::create($params)->save();
    }

    public static function FindUcsFirewallByUcsInstanceId($ucs_instance_id)
    {
        return UcsFirewall::create()->get([
            "ucs_instance_id" => $ucs_instance_id
        ]);
    }

    public static function FindUcsPlanByUcsInstance($ucs_instance)
    {

        return UcsPlan::create()->get([
            'cpu' => $ucs_instance->cpu,
            'memory' => $ucs_instance->memory,
            'ucs_region_id' => $ucs_instance->ucs_region_id,
        ]);
    }

    public static function GetReNewPrice($instance_id, $time_type, $time_length,)
    {
        //找到实例
        $ucs_instance = self::FindUcsInstanceById($instance_id);
        //判断是否为固定价格续费
        if ($ucs_instance->renew_type == 1) {
            return $ucs_instance->renew_price;
        }
        //非固定价格续费,开始计算价格

        $harddisk = [];
        self::FindUcsStorageRalationByUcsInstanceId($instance_id)->toArray();
        $ucs_plan = self::FindUcsPlanByUcsInstance($ucs_instance);
        $ip_number = 1;
        $price = self::GetPrice($ucs_plan, $harddisk, $ucs_instance->bandwidth, $ip_number, $time_type, $time_length, 1);
        return $price['total'];
    }

    public static function GetPrice($ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, $count)
    {
        //bandwidth基础带宽
        $price = [];
        $plan_price = match ($time_type) {
            "day" => $ucs_plan->price_day * $time_length,
            "month" => $ucs_plan->price_month * $time_length,
            "year" => $ucs_plan->price_year * $time_length,
            default => 0,
        };
        //套餐价格
        $price['plan_price'] = $plan_price;
        //带宽价格
        $ucs_region = UcsRegion::create()->get(['id' => $ucs_plan->ucs_region_id]);


        $bandwidth_price = match ($time_type) {
            "day" => $ucs_region->bandwidth_price_day * $bandwidth * $time_length,
            "month" => $ucs_region->bandwidth_price_month * $bandwidth * $time_length,
            "year" => $ucs_region->bandwidth_price_year * $bandwidth * $time_length,
            default => 0,
        };
        $price['bandwidth_price'] = $bandwidth_price;

        $ip_price = match ($time_type) {
            "day" => $ucs_region->ip_price_day * $ip_number * $time_length,
            "month" => $ucs_region->ip_price_month * $ip_number * $time_length,
            "year" => $ucs_region->ip_price_year * $ip_number * $time_length,
            default => 0,
        };
        $price['ip_price'] = $ip_price;

        //硬盘价格
        $harddisk_total_price = 0;
        $harddisk_prices = [];
        foreach ($harddisk as $key => $value) {
            $value = json_decode($value, true);
            var_dump($value);
            $ucs_storage_plan_id = $value['ucs_storage_plan_id'];
            $ucs_storage_plan = UcsStoragePlan::create()->get(['id' => $ucs_storage_plan_id]);

            var_dump($time_type);
            $temp_harddisk_price = match ($time_type) {
                "day" => $ucs_storage_plan->price_day * $value['size'],
                "month" => $ucs_storage_plan->price_month * $value['size'],
                "year" => $ucs_storage_plan->price_year * $value['size'],
                default => 0,
            };

            $harddisk_prices[] = $temp_harddisk_price * $time_length;
            $harddisk_total_price += $temp_harddisk_price;
        }
        $price['harddisk_price'] = $harddisk_prices;
        $price['total'] = ($plan_price + $bandwidth_price + $harddisk_total_price + $ip_price) * $count;
        $price['instance_price'] = ($plan_price + $bandwidth_price + $harddisk_total_price + $ip_price);
        return $price;
    }

    public static function GetQueueMaster($ucs_plan)
    {

        $masters = UcsMaster::create()->all([
            'ucs_region_id' => $ucs_plan->ucs_region_id,
        ]);
        $temp = [];
        $queue = [];
        foreach ($masters as $k => $v) {
            //如果使用内存小于虚拟化内存就不给安排
            if ($v->use_memory < $v->virtual_memory + $ucs_plan->memory) {
                continue;
            }
            $temp[] = $v;

            if ($v->queue == 0) {
                $queue[] = $v;
            }
        }
        //
        if (count($queue) > 1) {
            return $queue[rand(0, count($queue))];
        }
        if (count($temp) > 1) {
            return $temp[rand(0, count($temp))];
        }
        return null;
    }


    public static function GetEnableIP($ucs_region_id, $ip_number)
    {
        return UcsIp::create()
            ->where('ucs_region_id', $ucs_region_id)
            ->where('occ_status', 0)
            ->where('disable_status', 0)
            ->limit($ip_number)
            ->all();
    }

    public static function GetEnableIPCount($ucs_region_id, $ip_number)
    {
        return UcsIp::create()
            ->where('ucs_region_id', $ucs_region_id)
            ->where('occ_status', 0)
            ->where('disable_status', 0)
            ->limit($ip_number)
            ->count();
    }

    //$harddisk ['ucs_storage_plan_id':'1',"size":'20']
    public static function CreateInstance($user_id, $system_id, $ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length)
    {
        //宿主机
        $master = self::GetQueueMaster($ucs_plan);
        $master->queue = 1;
        $master->update();

        //创建UCS实例数据
        $instance = UcsInstance::create([
            'user_id' => $user_id,
            'ucs_region_id' => $ucs_plan->ucs_region_id,
            'ucs_master_id' => $master->id,
            'ucs_system_id' => $system_id,
            'cpu' => $ucs_plan->cpu,
            'memory' => $ucs_plan->memory,
            'cpu_ratio' => $ucs_plan->cpu_ratio,
            'bandwidth' => $bandwidth,
            'create_time' => date('Y-m-d H:i:s'),
            'expire_time' => date('Y-m-d H:i:s', strtotime('+' . $time_length . $time_type)),
            'run_status' => 0,
            'act_status' => 0,
            'renew_status' => 1,
            'lock_status' => 0,
            'vnc_port' => '59000',
            'public_mac' => 'public_mac',
            'private_mac' => 'private_mac',
        ]);
        $instance->save();


        //修改IP地址状态为已占用,并且给实例
        $ip_address = self::GetEnableIP($ucs_plan->ucs_region_id, $ip_number);
        foreach ($ip_address as $key => $value) {
            $value->occ_status = 1;
            $value->ucs_instance_id = $instance->id;
            $value->update();
        }

        //创建数据盘数据到数据库表
        foreach ($harddisk as $k => $v) {
            //循环创建数据盘数据
            $ucs_storage_plan = UcsStoragePlan::create()
                ->alias('a')
                ->field('a.iops,a.path,b.suffix,b.type')
                ->join('ucs_storage b', 'a.ucs_storage_id=b.id')
                ->where('id', $v['ucs_storage_plan_id'])
                ->get();
            $path = match ($ucs_storage_plan->type) {
                "windows_local" => $ucs_storage_plan->path . "\\" . "_" . $k . $ucs_storage_plan->suffix,
                "ceph", "linux_local" => $ucs_storage_plan->path . "/" . "_" . $k . $ucs_storage_plan->suffix,
                default => "",
            };
            UcsStorageRalation::create([
                'ucs_instance_id' => $instance->id,
                'ucs_storage_plan_id' => $v['ucs_storage_plan_id'],
                'type' => $v['type'],
                'iops' => $ucs_storage_plan->iops,
                'path' => $path,
            ])->save();
        }
        self::CreateAction($instance->id);
        return $instance;
    }

    //发送操作至服务器
    public static function SendAction($instance_id, $params)
    {
        $params['instance_id'] = $instance_id;
        $instance = UcsInstance::create()->get(['id' => $instance_id]);
        $ucs_master = UcsMaster::create()->where('id', $instance->ucs_master_id)->get();
        if ($ucs_master) {
            $client = new \EasySwoole\HttpClient\HttpClient($ucs_master->api);
            $aes = new AesService($ucs_master['token']);
            $string = $aes->encrypt(json_encode($params));
            $d['encrypt'] = $string;
            $response = $client->postJson(json_encode($d));
            $return = $response->json(true);
            info('发送请求给宿主机返回:' . $return);
        }
    }

    public static function SendActionJob($instance_id, $params)
    {
        UcsJob([
            'instance_id' => $instance_id,
            'params' => $params
        ]);
    }


    //创建实例
    public static function CreateAction($instance_id, $action = 'create')
    {
        $ucs_instance = self::FindUcsInstanceById($instance_id);
        $params['action'] = $action;
        //把数据库参数拿出来 给咱们的宿主机发过去
        $params['vnc_port'] = $ucs_instance->vnc_port;
        $params['public_mac'] = $ucs_instance->public_mac;
        $params['private_mac'] = $ucs_instance->private_mac;
        $params['vnc_password'] = $ucs_instance->vnc_password;
        $params['password'] = $ucs_instance->password;
        //获取操作系统参数
        $system = self::FindUcsSystemById($ucs_instance->ucs_system_id);
        $params['mirror_name'] = $system->mirror_name;
        $params['cpu'] = $ucs_instance->cpu;
        $params['cpu_ratio'] = $ucs_instance->cpu_ratio;
        $params['memory'] = $ucs_instance->memory;
        $params['bandwidth'] = $ucs_instance->bandwidth;

        //获取IP地址参数
        $ucs_ip = self::SelectUcsIPByUcsInstanceId($instance_id);
        $params['ip_address'] = $ucs_ip;

        //获取磁盘相关参数
        $harddisk = self::FindUcsStorageRalationByUcsInstanceId($instance_id);

        $params['harddisk'] = $harddisk;

        self::SendActionJob($instance_id, $params);
    }

    //重新创建实例
    public static function ReCreateAction($instance_id)
    {
        return self::Create($instance_id, 're_create');
    }

    //开机实例
    public static function StartAction($instance_id)
    {
        $params['action'] = 'start';
        self::SendActionJob($instance_id, $params);
    }

    //重启
    public static function ReStartAction($instance_id)
    {
        $params['action'] = 'restart';
        self::SendActionJob($instance_id, $params);
    }

    //关机
    public static function ShutdownAction($instance_id)
    {
        $params['action'] = 'shutdown';
        self::SendActionJob($instance_id, $params);
    }

    //重设服务器密码
    public static function ResetPasswordAction($instance_id, $password)
    {
        $params['action'] = 'reset_password';
        $params['password'] = $password;
        self::SendActionJob($instance_id, $params);
    }

    public static function ResetSystemAction($ucs_instance, $system, $password)
    {
        //修改数据库相关操作
        $params['action'] = 'reset_system';
        $params['password'] = $password;


        //把数据库参数拿出来 给咱们的宿主机发过去
        $params['vnc_port'] = $ucs_instance->vnc_port;

        $params['public_mac'] = $ucs_instance->public_mac;
        $params['private_mac'] = $ucs_instance->private_mac;
        $params['vnc_password'] = $ucs_instance->vnc_password;
        $params['password'] = $ucs_instance->password;
        //获取操作系统参数
        $params['mirror_name'] = $system->mirror_name;
        $params['cpu'] = $ucs_instance->cpu;
        $params['cpu_ratio'] = $ucs_instance->cpu_ratio;
        $params['memory'] = $ucs_instance->memory;
        $params['bandwidth'] = $ucs_instance->bandwidth;

        //获取IP地址参数
        $ucs_ip = self::SelectUcsIPByUcsInstanceId($ucs_instance->id);
        $params['ip_address'] = $ucs_ip;

        //获取磁盘相关参数
        $harddisk = self::FindUcsStorageRalationByUcsInstanceId($ucs_instance->id);

        $params['harddisk'] = $harddisk;
        self::SendActionJob($ucs_instance->id, $params);
    }

    //重设服务器IP地址
    public static function ResetIPAddressAction($instance_id, $ip_address)
    {
        $params['action'] = 'reset_ip_address';
        $params['ip_address'] = $ip_address;
        self::SendActionJob($instance_id, $params);
    }

    //强制重启
    public static function ForceReStartAction($instance_id)
    {
        $params['action'] = 'force_restart';
        self::SendActionJob($instance_id, $params);
    }

    //强制关机
    public static function ForceShutdownAction($instance_id)
    {
        $params['action'] = 'force_shutdown';
        self::SendActionJob($instance_id, $params);
    }
}