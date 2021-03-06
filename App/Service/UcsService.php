<?php

namespace App\Service;

use App\Model\UcsFirewall;
use App\Model\UcsFirewallGroup;
use App\Model\UcsInstance;
use App\Model\UcsIp;
use App\Model\UcsMaster;
use App\Model\UcsPlan;
use App\Model\UcsRegion;
use App\Model\UcsStoragePlan;
use App\Model\UcsStorageRalation;
use App\Model\UcsSystem;
use App\Model\UcsSystemClass;
use App\Model\UcsTask;
use App\Status\UcsActStatus;
use EasySwoole\Mysqli\QueryBuilder;

class UcsService
{
    //根据实例ID查找实例
    public static function FindUcsInstanceById($instance_id)
    {
        return UcsInstance::create()->get([
            'id' => $instance_id
        ]);
    }

    //查询所有宿主机
    public static function SelectMasterAll()
    {
        return UcsMaster::create()->all();
    }

    //根据宿主机查询实例
    public static function SelectUcsInstanceByMasterId($master_id)
    {
        return UcsInstance::create()->where('ucs_master_id', $master_id)->all();
    }

    //计算宿主机使用的内存
    public static function SumUcsMemoryByMasterId($master_id)
    {
        return UcsInstance::create()
            ->where('ucs_master_id', $master_id)
            ->where('order_status', 1)
            ->sum('memory');
    }


    //计算宿主机使用的硬盘
    public static function SumUcsHarddiskByMasterId($master_id)
    {
        return UcsInstance::create()
            ->where('ucs_master_id', $master_id)
            ->where('order_status', 1)
            ->sum('harddisk');
    }

    //计算宿主机使用的CPU
    public static function SumUcsCpuByMasterId($master_id)
    {
        return UcsInstance::create()
            ->where('ucs_master_id', $master_id)
            ->where('order_status', 1)
            ->sum('cpu');
    }

    //查询地域详情
    public static function FindUcsRegionById($id)
    {
        return UcsRegion::create()->get(['id' => $id]);
    }

    //获取地域列表
    public static function SelectRegion()
    {
        return UcsRegion::create()->all();
    }


    //获取系统详情
    public static function SelectSystem($ucs_system_class_id = 0)
    {
        if ($ucs_system_class_id) {
            return UcsSystem::create()->all([
                'ucs_system_class_id' => $ucs_system_class_id
            ]);
        }
        return UcsSystem::create()->all();
    }

    //获取系统类别
    public static function SelectSystemClass()
    {
        return UcsSystemClass::create()->all();
    }

    //获取系统树
    public static function SelectSystemTree($ucs_instance = null)
    {
        $data = UcsSystem::create()->alias('a')
            ->join('ucs_system_class b', 'b.id=a.ucs_system_class_id')
            ->field([
                'b.icon',
                'a.min_cpu',
                'a.min_memory',
                'a.ucs_system_class_id',
                'a.id',
                'a.system_version',
                'b.system_class'
            ])
            ->all();
        $d = [];
        foreach ($data as $value) {
            $item = $value->toArray(false);
            $item['system_class'] = $value->system_class;
            if ($ucs_instance) {
                //判断能否使用该系统
                if ($ucs_instance->cpu < $item['min_cpu'] && $ucs_instance->memory < $item['min_memory']) {
                    $item['disabled'] = false;
                }
            }
            $d[] = $item;
        }
        return $d;
    }

    //获取地域下套餐
    public static function SelectPlanByUcsRegionId($ucs_region_id)
    {
        return UcsPlan::create()->where('ucs_region_id', $ucs_region_id)->all();
    }

    //获取地域下存储
    public static function SelectStorageByUcsRegionId($ucs_region_id)
    {
        return UcsStoragePlan::create()
            ->field('a.*,b.config,b.status,b.suffix,b.type,b.ucs_region_id')
            ->alias('a')
            ->join('ucs_storage b', 'a.ucs_storage_id=b.id')
            ->where('b.ucs_region_id', $ucs_region_id)->all();
    }

    //获取UCS即将过期的实例
    public static function SelectUcsInstanceBySoonExpire()
    {
        return UcsInstance::create()
            ->where('expire_time', date('Y-m-d H:i:s', strtotime('-5 day')), '>')
            ->all();
    }

    //根据实例ID获取实例IP地址
    public static function SelectUcsIPByUcsInstanceId($instance_id)
    {
        return UcsIp::create()
            ->where('ucs_instance_id', $instance_id)
            ->all();
    }

    //续费
    public static function ReNew($instance_id, $time_type, $time_length)
    {
        $ucs_instance = self::FindUcsInstanceById($instance_id);
        $ucs_instance->expire_time = self::GetReNewExpireTime($ucs_instance->expire_time, $time_type, $time_length);
        $ucs_instance->update();
    }

    //根据实例ID查找实例磁盘
    public static function FindUcsStorageRalationByUcsInstanceId($instance_id)
    {
        //获取相关磁盘参数
        return UcsStorageRalation::create()
            ->where('ucs_instance_id', $instance_id)
            ->all();
    }

    //获取任务列表分页
    public static function SelectTaskListPage($where, $page, $size)
    {
        var_dump($where);
        $ucs_task = UcsTask::create()->limit($size * ($page - 1), $size)->where($where);
        $ucs_task = $ucs_task->withTotalCount();

        $data['list'] = $ucs_task->all();
        $result = $ucs_task->lastQueryResult();
        // 总条数
        $data['total'] = $result->getTotalCount();
        return $data;
    }

    public static function FindInstanceInfoByInstanceId($instance_id)
    {
        $ucs_instances = UcsInstance::create()->alias('a');
        $ucs_instances->field([
            'a.id',
            'a.cpu',
            'a.memory',
            'a.bandwidth',
            'a.user_id',
            'a.create_time',
            'a.expire_time',
            'a.run_status',
            'a.renew_status',
            'a.act_status',
            'a.name as instance_name',
            'b.name as region_name',
            'd.system_class',
            'c.system_version',
            'c.login_name',
            'b.defense',
            'a.ucs_region_id',
            'a.harddisk',
            'a.buy_price_type',
        ]);
        $ucs_instances = $ucs_instances
            ->join('ucs_region b', 'a.ucs_region_id=b.id')
            ->join('ucs_system c', 'c.id=a.ucs_system_id')
            ->join('ucs_system_class d', 'd.id=c.ucs_system_class_id');
        $ucs_instances->where('a.id', $instance_id);
        $value = $ucs_instances->get();
        $item = $value->toRawArray();
        if ($value->instance_name) {
            $item['instance_name'] = $value->instance_name;
        }
        if ($value->region_name) {
            $item['region_name'] = $value->region_name;
        }
        if ($value->login_name) {
            $item['login_name'] = $value->login_name;
        }
        if ($value->defense) {
            $item['defense'] = $value->defense;
        }
        //获取资源状态
        $item['resource_status'] = self::GetResourceStatus($value);
        $item['system_name'] = $value->system_class . " " . $value->system_version;
        $item['ip_address'] = UcsService::SelectUcsIPByUcsInstanceId($value->id);
        $item['act_tips'] = UcsActStatus::ConvertToString($item['act_status']);
        return $item;
    }

    //获取UCS列表
    public static function SelectListPage($where, $page, $size)
    {
        $ucs_instances = UcsInstance::create()->alias('a');
        $ucs_instances->field([
            'a.id',
            'a.cpu',
            'a.memory',
            'a.bandwidth',
            'a.user_id',
            'a.create_time',
            'a.expire_time',
            'a.run_status',
            'a.renew_status',
            'a.act_status',
            'a.name as instance_name',
            'b.name as region_name',
            'd.system_class',
            'c.system_version',
            'c.login_name',
            'b.defense',
            'a.ucs_region_id',
            'a.harddisk',
            'a.buy_price_type',
        ]);
        $ucs_instances = $ucs_instances
            ->join('ucs_region b', 'a.ucs_region_id=b.id')
            ->join('ucs_system c', 'c.id=a.ucs_system_id')
            ->join('ucs_system_class d', 'd.id=c.ucs_system_class_id');
        foreach ($where as $value) {
            $ucs_instances = $ucs_instances->where($value);
        }
        $ucs_instances->where('a.expire_time', date('Y-m-d H:i:s'), '>');
        $model = $ucs_instances
            ->limit($size * ($page - 1), $size)->withTotalCount();

        // 列表数据

        $list = $model->all();
        $temp = [];
        foreach ($list as $key => $value) {
            $item = $value->toRawArray();
            if ($value->instance_name) {
                $item['instance_name'] = $value->instance_name;
            }
            if ($value->region_name) {
                $item['region_name'] = $value->region_name;
            }
            if ($value->login_name) {
                $item['login_name'] = $value->login_name;
            }
            if ($value->defense) {
                $item['defense'] = $value->defense;
            }
            //获取资源状态
            $item['resource_status'] = self::GetResourceStatus($value);
            $item['system_name'] = $value->system_class . " " . $value->system_version;
            $item['ip_address'] = UcsService::SelectUcsIPByUcsInstanceId($value->id);
            $item['act_tips'] = UcsActStatus::ConvertToString($item['act_status']);
            $temp[] = $item;
        }
        $d = [];
        $d['list'] = $temp;
        $result = $model->lastQueryResult();

        // 总条数
        $d['total'] = $result->getTotalCount();
        return $d;
    }

    private static function randomFloat($min = 0, $max = 1)
    {
        $num = $min + mt_rand() / getrandmax() * ($max - $min);
        return sprintf("%.2f", $num);
    }

    private static function GetResourceStatus($value)
    {
        $resource_status = RedisService::GetUcsResourceStatus($value->id);
        if (!$resource_status) {
            $data = [];
            $data['load'] = [
                'tips' => '运行流畅',
                'ratio' => self::randomFloat(1, 10)
            ];
            $data['cpu'] = [
                'num' => $value->cpu,
                'ratio' => self::randomFloat(1, 10)
            ];
            $memory = rand(128, 512);
            $data['memory'] = [
                'use' => $memory,
                'total' => $value->memory,
                'ratio' => sprintf("%.2f", ($memory / $value->memory) * 100)
            ];

            $data['harddisk'] = [
                'use' => sprintf("%.2f", $value->harddisk - 40.7),
                'total' => $value->harddisk,
                'ratio' => sprintf("%.2f", (9.3 / $value->harddisk) * 100)
            ];
            $resource_status = $data;
        }
        return $resource_status;
    }

    //根据系统ID查找系统
    public static function FindUcsSystemById($system_id)
    {
        return UcsSystem::create()->get(['id' => $system_id]);
    }

    //根据系统ID查找系统详情
    public static function FindUcsSystemDetailById($system_id)
    {
        return UcsSystem::create()->alias('a')
            ->field([
                'a.system_version',
                'b.system_class',
            ])
            ->join('ucs_system_class b', 'b.id=a.ucs_system_class_id')
            ->where('a.id', $system_id)
            ->get();
    }

    //根据套餐ID查找套餐
    public static function FindUcsPlanById($plan_id)
    {
        return UcsPlan::create()->get([
            "id" => $plan_id
        ]);
    }

    //获取用户安全组 By 用户ID
    public static function SelectUcsFirewallGroupByUserIdPage($user_id, $page, $size)
    {
        $ucs_firewall_group = UcsFirewallGroup::create()->limit($size * ($page - 1), $size)
            ->where('user_id', $user_id);
        $ucs_firewall_group = $ucs_firewall_group->withTotalCount();

        $data['list'] = $ucs_firewall_group->all();
        foreach ($data['list'] as $item) {
            $item->count = UcsInstance::create()->where('ucs_firewall_group_id', $item->id)->count();
        }
        $result = $ucs_firewall_group->lastQueryResult();
        // 总条数
        $data['total'] = $result->getTotalCount();
        return $data;
    }

    //获取安全组规则 By 安全组ID
    public static function SelectUcsFirewallRuleByGroupIdPage($ucs_firewall_group_id, $page, $size)
    {
        UcsFirewall::create()->where();
        $ucs_firewall_rule = UcsFirewall::create()->limit($size * ($page - 1), $size)
            ->where('ucs_firewall_group_id', $ucs_firewall_group_id);
        $ucs_firewall_rule = $ucs_firewall_rule->withTotalCount();

        $data['list'] = $ucs_firewall_rule->all();
        $result = $ucs_firewall_rule->lastQueryResult();
        // 总条数
        $data['total'] = $result->getTotalCount();
        return $data;
    }

    //查询安全组 By 安全组ID
    public static function FindUcsFirewallGroupById($ucs_firewall_group_id)
    {
        return UcsFirewallGroup::create()
            ->where('id', $ucs_firewall_group_id)
            ->get();
    }

    public static function EditUcsFirewallGroup($id, $name, $remark, $user_id)
    {
        $params['update_time'] = date('Y-m-d H:i:s');
        $params['name'] = $name;
        $params['remark'] = $remark;
        if ($id) {
            UcsFirewallGroup::create()->update($params, ['id' => $id]);
        } else {
            $params['create_time'] = date('Y-m-d H:i:s');
            $params['user_id'] = $user_id;
            UcsFirewallGroup::create($params)->save();
        }
    }

    //编辑实例安全组规则
    public static function EditUcsFirewall($params)
    {
        var_dump($params);
        $ucs_firewall = null;
        if ($params['id']) {
            $ucs_firewall = UcsFirewall::create()->get(['id' => $params['id']]);
            //存在即修改
            if ($ucs_firewall) {
                return UcsFirewall::create()->update($params, ['id' => $params['id']]);
            }
            return false;
        }
        var_dump($params);
        return UcsFirewall::create($params)->save();
    }

    //查找实例安全组规则
    public static function FindUcsFirewallByUcsInstanceId($ucs_instance_id)
    {
        return UcsFirewall::create()->get([
            "ucs_instance_id" => $ucs_instance_id
        ]);
    }

    //根据实例查找套餐
    public static function FindUcsPlanByUcsInstance($ucs_instance)
    {

        return UcsPlan::create()->get([
            'cpu' => $ucs_instance->cpu,
            'memory' => $ucs_instance->memory,
            'ucs_region_id' => $ucs_instance->ucs_region_id,
        ]);
    }

    public static function GetReNewExpireTime($expire_time, $time_type, $time_length)
    {
        return date('Y-m-d H:i:s', strtotime("+" . $time_length . " " . $time_type, strtotime($expire_time)));
    }

    //获取续费价格
    public static function GetReNewPrice($instance_id, $time_type, $time_length)
    {
        //找到实例
        $ucs_instance = self::FindUcsInstanceById($instance_id);
        //判断是否为固定价格续费
        if ($ucs_instance->renew_type == 1) {
            return $ucs_instance->renew_price;
        }
        //非固定价格续费,开始计算价格

        $harddisk = [];
        $harddisk = self::FindUcsStorageRalationByUcsInstanceId($instance_id);
        $ucs_plan = self::FindUcsPlanByUcsInstance($ucs_instance);
        if (!$ucs_plan) {
            //如果套餐已经不存在了,则返回0
            return 0;
        }
        $ips = self::SelectUcsIPByUcsInstanceId($instance_id);
        if (!$ips) {
            return 0;
        }
        $ip_number = count($ips);
        $price = self::GetPrice($ucs_plan, $harddisk, $ucs_instance->bandwidth, $ip_number, $time_type, $time_length, 1);
        return $price['total'];
    }

    //获取价格
    public static function GetPrice($ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, $count)
    {
        //bandwidth基础带宽
        $price = [];
        $plan_price = 0;
        switch ($time_type) {
            case "day":
                $plan_price = $ucs_plan->price_day * $time_length;
                break;
            case "month":
                $plan_price = $ucs_plan->price_month * $time_length;
                break;
            case "year":
                $plan_price = $ucs_plan->price_year * $time_length;
                break;
            default:
                $plan_price = 0;
                break;
        }
        //套餐价格
        $price['plan_price'] = $plan_price;
        //带宽价格
        $ucs_region = UcsRegion::create()->get(['id' => $ucs_plan->ucs_region_id]);

        if ($bandwidth <= $ucs_plan->free_bandwidth) {
            //如果购买的小于等于送的，以送的为准,免费
            $bandwidth_price = 0;
        } else {
            $bandwidth = $bandwidth - $ucs_plan->free_bandwidth;
            switch ($time_type) {
                case "day":
                    $bandwidth_price = $ucs_region->bandwidth_price_day * $bandwidth * $time_length;
                    break;
                case "month":
                    $bandwidth_price = $ucs_region->bandwidth_price_month * $bandwidth * $time_length;
                    break;
                case "year":
                    $bandwidth_price = $ucs_region->bandwidth_price_year * $bandwidth * $time_length;
                    break;
                default:
                    $bandwidth_price = 0;
                    break;
            }
        }

        $price['bandwidth_price'] = $bandwidth_price;

        switch ($time_type) {
            case "day":
                $ip_price = $ucs_region->ip_price_day * $ip_number * $time_length;
                break;
            case "month":
                $ip_price = $ucs_region->ip_price_month * $ip_number * $time_length;
                break;
            case "year":
                $ip_price = $ucs_region->ip_price_year * $ip_number * $time_length;
                break;
            default:
                $ip_price = 0;
                break;
        }
        $price['ip_price'] = $ip_price;

        //硬盘价格
        $harddisk_total_price = 0;
        $harddisk_prices = [];
        foreach ($harddisk as $key => $value) {
            $ucs_storage_plan_id = $value['ucs_storage_plan_id'];
            $ucs_storage_plan = UcsStoragePlan::create()->get(['id' => $ucs_storage_plan_id]);
            if ($key == 0) {
                $value['size'] = $value['size'] - 40;
            }
            switch ($time_type) {
                case "day":
                    $temp_harddisk_price = $ucs_storage_plan->price_day * $value['size'];
                    break;
                case "month":
                    $temp_harddisk_price = $ucs_storage_plan->price_month * $value['size'];
                    break;
                case "year":
                    $temp_harddisk_price = $ucs_storage_plan->price_year * $value['size'];
                    break;
                default:
                    $temp_harddisk_price = 0;
                    break;
            }

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
            //如果可用内存小于虚拟机内存就不给安排
            if ($v->virtual_memory - $v->use_memory < $ucs_plan->memory) {
                continue;
            }
            //如果可用CPU小于虚拟化CPU就不给安排
            if ($v->virtual_cpu - $v->use_cpu < $ucs_plan->cpu) {
                continue;
            }

            $temp[] = $v;

            if ($v->queue == 0) {
                $queue[] = $v;
            }
        }
        //
        if (count($queue) > 0) {
            //没在队列的随机拿
            info('找到空闲的UCS宿主机');
            return $queue[rand(0, count($queue) - 1)];
        }
        if (count($temp) > 0) {
            info('实在是没有空闲的UCS宿主机了');
            return $temp[rand(0, count($temp) - 1)];
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

    //使用宿主机资源
    public static function UseMasterResource($master_id, $cpu, $memory, $harddisk)
    {
        UcsMaster::create()->update([
            'use_cpu' => QueryBuilder::inc($cpu), // 自增3
            'use_memory' => QueryBuilder::inc($memory), // 自降4
            'use_harddisk' => QueryBuilder::inc($harddisk), // 自降4
        ], [
            'id' => $master_id
        ]);
    }

    //释放宿主机资源
    public static function DestroyMasterResource($master_id, $cpu, $memory, $harddisk)
    {
        UcsMaster::create()->update([
            'use_cpu' => QueryBuilder::dec($cpu), // 自增3
            'use_memory' => QueryBuilder::dec($memory), // 自降4
            'use_harddisk' => QueryBuilder::dec($harddisk), // 自降4
        ], [
            'id' => $master_id
        ]);
    }

    //$harddisk ['ucs_storage_plan_id':'1',"size":'20']
    //创建实例
    public static function CreateInstance($master, $user_id, $system_id, $ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, $resolved_type, $resolved_name, $password)
    {
        //宿主机,队列+1
        $master->queue = 1;

        $master->update();

        //创建UCS实例数据
        $instance = UcsInstance::create([
            'name' => 'ucs_' . time(),
            'user_id' => $user_id,
            'ucs_region_id' => $ucs_plan->ucs_region_id,
            'ucs_master_id' => $master->id,
            'ucs_system_id' => $system_id,
            'cpu' => $ucs_plan->cpu,
            'memory' => $ucs_plan->memory,
            'cpu_ratio' => $ucs_plan->cpu_ratio,
            'buy_price_type' => $time_type,
            'bandwidth' => $bandwidth,
            'harddisk' => '0',
            'create_time' => date('Y-m-d H:i:s'),
            'expire_time' => date('Y-m-d H:i:s', strtotime('+' . $time_length . $time_type)),
            'run_status' => 0,
            'act_status' => 0,
            'renew_status' => 1,
            'lock_status' => 0,
            'vnc_port' => '59000',
            'public_mac' => '',
            'private_mac' => '',
            'password' => $password,
            'vnc_password' => $password
        ]);
        $id = $instance->save();
        $ucs_region = self::FindUcsRegionById($instance->ucs_region_id);
        //生成并修改MAC地址
        $instance->update([
            'public_mac' => make_mac($id, json_decode($ucs_region->public_mac_prefix, true)),
            'private_mac' => make_mac($id, json_decode($ucs_region->private_mac_prefix, true)),
        ], [
            'id' => $id
        ]);
        //修改IP地址状态为已占用,并且给实例
        $ip_address = self::GetEnableIP($ucs_plan->ucs_region_id, $ip_number);
        foreach ($ip_address as $key => $value) {
            $value->occ_status = 1;
            $value->ucs_instance_id = $instance->id;
            $value->update();
        }

        $harddisk_size = 0;
        //创建数据盘数据到数据库表
        foreach ($harddisk as $k => $v) {
            //循环创建数据盘数据
            $ucs_storage_plan = UcsStoragePlan::create()
                ->alias('a')
                ->field('a.iops_write,a.iops_read,a.path,b.suffix,b.type,b.config')
                ->join('ucs_storage b', 'a.ucs_storage_id=b.id')
                ->where('a.id', $v['ucs_storage_plan_id'])
                ->get();
            switch ($ucs_storage_plan->type) {
                case "windows_local":
                    $path = $ucs_storage_plan->path . "\\" . $id . "_" . $k . $ucs_storage_plan->suffix;
                    break;
                case "ceph":
                case "linux_local":
                    $path = $ucs_storage_plan->path . "/" . $id . "_" . $k . $ucs_storage_plan->suffix;
                    break;
                default:
                    $path = "";
                    break;
            }
            UcsStorageRalation::create([
                'ucs_instance_id' => $instance->id,
                'ucs_storage_plan_id' => $v['ucs_storage_plan_id'],
                'type' => $v['type'],
                'iops_read' => $ucs_storage_plan->iops_read,
                'iops_write' => $ucs_storage_plan->iops_write,
                'path' => $path,
                'size' => $v['size'],
                'storage_type' => $ucs_storage_plan->type,
                'storage_config' => $ucs_storage_plan->config,
            ])->save();
            $harddisk_size += $v['size'];
        }
        //修改当前硬盘
        $instance->harddisk = $harddisk_size;
        $instance->update();
        //使用资源
        self::UseMasterResource($master->id, $instance->cpu, $instance->memory, $harddisk_size);

        self::CreateAction($instance->id, 'create', $resolved_type, $resolved_name);
        return $instance;
    }

    //发送操作至服务器
    public static function SendAction($task_id, $instance_id, $params)
    {
        self::ActionUcsTask($task_id, ['action_time' => date('Y-m-d H:i:s')]);
        $params['instance_id'] = $instance_id;
        $params['task_id'] = $task_id;
        $instance = UcsInstance::create()->get(['id' => $instance_id]);
        $ucs_master = UcsMaster::create()->where('id', $instance->ucs_master_id)->get();
        if ($ucs_master) {
            $client = new \EasySwoole\HttpClient\HttpClient($ucs_master->api);
            $aes = new AesService($ucs_master['token']);
            $string = $aes->encrypt(json_encode($params['action']));
            $params['sign'] = $string;
            $response = $client->postJson(json_encode($params));
            $return = $response->json(true);

            self::ActionUcsTask($task_id, [
                'api_status' => $response->getStatusCode(),
                'api_message' => json_encode($return)
            ]);
            info('发送请求给宿主机返回:');
            info(json_encode($return));
        }
    }

    //修改操作状态
    public static function ChangeActStatus($instance_id, $act_status)
    {
        UcsInstance::create()->update([
            'act_status' => $act_status
        ], [
            'id' => $instance_id
        ]);
    }

    //修改运行状态
    public static function ChangeRunStatus($instance_id, $run_status)
    {
        UcsInstance::create()->update([
            'run_status' => $run_status
        ], [
            'id' => $instance_id
        ]);
    }


    public static function SendActionJob($instance_id, $params, $resolved_type, $resolved_name)
    {
        $ucs_task = self::CreateUcsTask($instance_id, $resolved_type, $resolved_name, $params);
        UcsJob([
            'task_id' => $ucs_task->id,
            'instance_id' => $instance_id,
            'params' => $params
        ]);
    }


    //创建实例
    public static function CreateAction($instance_id, $action = 'create', $resolved_type = 0, $resolved_name = '客户自己')
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

        $ucs_region = self::FindUcsRegionById($ucs_instance->ucs_region_id);

        $params['dns'] = json_decode($ucs_region->dns, true);

        //获取磁盘相关参数
        $harddisk = self::FindUcsStorageRalationByUcsInstanceId($instance_id);

        $params['harddisk'] = $harddisk;

        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }

    //
    public static function CreateUcsTask($instance_id, $resolved_type, $resolved_name, $params)
    {
        $data['ucs_instance_id'] = $instance_id;
        $instance = UcsInstance::create()->get(['id' => $instance_id]);
        $data['ucs_master_id'] = $instance->ucs_master_id;
        $data['user_id'] = $instance->user_id;
        $data['resolved_type'] = $resolved_type;
        $data['resolved_name'] = $resolved_name;
        $data['resolved_time'] = date('Y-m-d H:i:s');
        $data['action'] = $params['action'];
        $data['params'] = json_encode($params);
        $ucs_task = UcsTask::create($data);
        $ucs_task->save();
        return $ucs_task;
    }

    public static function ActionUcsTask($task_id, $data = [])
    {
        UcsTask::create()->update($data, ['id' => $task_id]);
    }

    //重新创建实例
    public static function ReCreateAction($instance_id, $resolved_type = 0, $resolved_name = '客户自己')
    {
        return self::Create($instance_id, 're_create');
    }

    //开机实例
    public static function StartAction($instance_id, $resolved_type, $resolved_name)
    {
        self::ChangeActStatus($instance_id, UcsActStatus::Start);
        $params['action'] = 'start';
        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }


    //重启
    public static function ReStartAction($instance_id, $resolved_type = 0, $resolved_name = '客户自己')
    {
        self::ChangeActStatus($instance_id, UcsActStatus::ReStart);
        $params['action'] = 'restart';
        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }

    //关机
    public static function ShutdownAction($instance_id, $resolved_type = 0, $resolved_name = '客户自己')
    {
        self::ChangeActStatus($instance_id, UcsActStatus::Poweroff);
        $params['action'] = 'shutdown';
        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }

    //重设服务器密码
    public static function ResetPasswordAction($instance_id, $password, $resolved_type = 0, $resolved_name = '客户自己')
    {
        self::ChangeActStatus($instance_id, UcsActStatus::RePwd);
        $params['action'] = 'reset_password';
        $params['password'] = $password;
        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }

    //重装系统
    public static function ResetSystemAction($ucs_instance, $system, $password, $resolved_type = 0, $resolved_name = '客户自己')
    {

        self::ChangeActStatus($ucs_instance->id, UcsActStatus::ReSystem);
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
        self::SendActionJob($ucs_instance->id, $params, $resolved_type, $resolved_name);
    }

    //重设服务器IP地址
    public static function ResetIPAddressAction($instance_id, $ip_address, $resolved_type = 0, $resolved_name = '客户自己')
    {
        $params['action'] = 'reset_ip_address';
        $params['ip_address'] = $ip_address;
        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }

    //强制重启
    public static function ForceReStartAction($instance_id, $resolved_type = 0, $resolved_name = '客户自己')
    {

        self::ChangeActStatus($instance_id, UcsActStatus::Poweroff);
        $params['action'] = 'force_restart';
        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }

    //强制关机
    public static function ForceShutdownAction($instance_id, $resolved_type = 0, $resolved_name = '客户自己')
    {
        self::ChangeActStatus($instance_id, UcsActStatus::Poweroff);
        $params['action'] = 'force_shutdown';
        self::SendActionJob($instance_id, $params, $resolved_type, $resolved_name);
    }
}
