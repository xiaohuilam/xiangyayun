<?php

namespace App\Controller\User;

use App\Controller\Common\UserLoginBase;
use App\Model\UcsInstance;
use App\Model\UcsRegion;
use App\Model\User;
use App\Service\TreeService;
use App\Service\UcsService;
use App\Service\UserService;
use App\Service\WechatService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Ucs extends UserLoginBase
{
    /**
     * @Param(name="page",integer="")
     * @Param(name="size",integer="")
     */
    public function instance_list()
    {
        $page = $this->GetParam('page') ?? 1;
        $size = $this->GetParam('size') ?? 10;
        $user_id = $this->GetUserId();
        $where[] = ["a.user_id" => $user_id];
        $data = UcsService::SelectListPage($where, $page, $size);
        return $this->Success('获取列表成功', $data);
    }

    /**
     * @Param(name="plan_id",required="")
     * @Param(name="harddisk",required="")
     * @Param(name="bandwidth",integer="")
     * @Param(name="ip_number",integer="")
     * @Param(name="time_type",inArray=["day","month","year"])
     * @Param(name="time_length",integer="")
     * @Param(name="count",integer="")
     * 计算价格
     */
    public function get_price()
    {
        $plan_id = $this->GetParam('plan_id');
        $harddisk = $this->GetParam('harddisk');
        if (!is_array($harddisk)) {
            return $this->Error('磁盘数据不能为空!');
        }

        foreach ($harddisk as $key => $value) {
            var_dump($value);
            $value = json_decode($value, true);
            if (!array_key_exists('ucs_storage_plan_id', $value)) {
                return $this->Error('磁盘类型不能为空!');
            }
            if (!array_key_exists('size', $value)) {
                return $this->Error('磁盘大小不能为空!');
            }
        }
        $bandwidth = $this->GetParam('bandwidth');
        $ip_number = $this->GetParam('ip_number');
        $time_type = $this->GetParam('time_type');
        $time_length = $this->GetParam('time_length');
        $count = $this->GetParam('count');
        $ucs_plan = UcsService::FindUcsPlanById($plan_id);
        $price = UcsService::GetPrice($ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, $count);
        return $this->Success('计算价格成功!', $price);
    }


    private function CheckIsMine($instance_id)
    {
        $user_id = $this->GetUserId();
        $instance = UcsService::FindUcsInstanceById($instance_id);
        if (!$instance) {
            return $this->Error('实例不存在');
        }
        if ($instance->user_id != $user_id) {
            return $this->Error('该实例不是您的!');
        }
        if ($instance->lock_status == 1) {
            return $this->Error('实例已被锁定');
        }
        return $instance;
    }

    /**
     * @Param(name="instance_id",integer="")
     * 关机
     */
    public function shutdown()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            UcsService::ShutdownAction($instance_id, 0);
            return $this->Success('发送关机指令成功!');
        }
    }


    /**
     * @Param(name="instance_id",integer="")
     *获取系统列表
     */
    public function get_system()
    {
        $instance_id = $this->GetParam('instance_id');
        $data = UcsService::SelectSystemClass();
        $system_list = [];
        foreach ($data as $value) {
            $systems = UcsService::SelectSystem($value->id);
            $system = [];
            foreach ($systems as $v) {
                $temp['label'] = $v->system_version;
                $temp['value'] = $v->id;
                $system[] = $temp;
            }
            $item['label'] = $value->system_class;
            $item['value'] = $value->id;
            $item['children'] = $system;
            $system_list[] = $item;
        }
        return $this->Success('', $system_list);
    }


    /**
     * @Param(name="instance_id",integer="")
     * 开机
     */
    public function start()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $user = $this->GetUser();
            UcsService::StartAction($instance_id, 0, $user->nickname);
            return $this->Success('发送开机指令成功!');
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * 硬重新启动
     */
    public function force_restart()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $user = $this->GetUser();
            UcsService::ForceReStartAction($instance_id, 0, $user->nickname);
            return $this->Success('发送硬重启指令成功!');
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * 软重新启动
     */
    public function restart()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $user = $this->GetUser();
            UcsService::ReStartAction($instance_id, 0, $user->nickname);
            return $this->Success('发送软重启指令成功!');
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * @Param(name="system_id",integer="")
     * @Param(name="password",required="",lengthMin="6")
     * 重装系统
     */
    public function reset_system()
    {
        $instance_id = $this->GetParam('instance_id');
        $instance = $this->CheckIsMine($instance_id);
        if ($instance) {
            $system_id = $this->GetParam('system_id');
            $system = UcsService::FindUcsSystemById($system_id);
            if (!$system) {
                return $this->Error('系统版本不正确!');
            }
            $password = $this->GetParam('password');
            $user = $this->GetUser();
            UcsService::ResetSystemAction($instance, $system, $password, 0, $user->nickname);
            return $this->Success('发送重装系统指令成功!');
        }
    }


    /**
     * @Param(name="instance_id",integer="")
     * 强制关机
     */
    public function force_shutdown()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $user = $this->GetUser();
            UcsService::ForceShutdownAction($instance_id, 0, $user->nickname);
            return $this->Success('发送强制关机指令成功!');
        }
    }


    /**
     * @Param(name="instance_id",integer="")
     * 续费实例
     */
    public function renew()
    {
        //续费
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $price = UcsService::GetReNewPrice($instance_id);
            if ($price > 0) {
                //
                $user_id = $this->GetUserId();
                UserService::Consume($user_id, $price, '服务器续费');
            }
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * 获取实例防火墙参数
     */
    public function get_firewall()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $firewall_rules = UcsService::FindUcsFirewallByUcsInstanceId($instance_id);
            return $this->Success('获取防火墙规则成功', $firewall_rules);
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * @Param(name="priority",integer="")
     * @Param(name="action",inArray=["accept","drop","reject","continue","return"])
     * @Param(name="direction",inArray=["in","out","full"])
     * @Param(name="protocol",inArray=["tcp","udp","full"])
     * @Param(name="src_port_range",required="")
     * @Param(name="dst_port_range",required="")
     * @Param(name="src_ip",required="")
     * @Param(name="dst_ip",required="")
     * 编辑实例防火墙参数
     */
    public function edit_firewall()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $params = [];
            $params['id'] = $this->GetParam('id');
            $params['priority'] = $this->GetParam('priority');
            $params['action'] = $this->GetParam('action');
            $params['protocol'] = $this->GetParam('protocol');
            $params['src_port_range'] = $this->GetParam('src_port_range');
            $params['dst_port_range'] = $this->GetParam('dst_port_range');
            $params['src_ip'] = $this->GetParam('src_ip');
            $params['dst_ip'] = $this->GetParam('dst_ip');
            $flag = UcsService::EditUcsFirewall($params);
            if ($params['id']) {
                if ($flag) {
                    return $this->Success('修改防火墙规则成功');
                }
                return $this->Error('修改防火墙规则失败');
            }
            if ($flag) {
                return $this->Success('添加防火墙规则成功');
            }
            return $this->Error('添加防火墙规则失败');
        }
    }

    /**
     * @Param(name="plan_id",required="")
     * @Param(name="harddisk",required="")
     * @Param(name="bandwidth",integer="")
     * @Param(name="ip_number",integer="")
     * @Param(name="time_type",inArray=["day","month","year"])
     * @Param(name="time_length",integer="")
     * @Param(name="count",integer="")
     * @Param(name="system_id",required="")
     * 创建实例
     */
    public function buy()
    {
        //创建实例
        $system_id = $this->GetParam('system_id');
        $plan_id = $this->GetParam('plan_id');
        $ucs_plan = UcsService::FindUcsPlanById($plan_id);
        if (!$ucs_plan) {
            return $this->Error('套餐不存在!');
        }
        $ucs_region = UcsRegion::create()->get(['id' => $ucs_plan->ucs_region_id]);
        if (!$ucs_region) {
            return $this->Error('地域不存在');
        }
        $user_id = $this->GetUserId();
        $user = User::create()->get(['id' => $user_id]);
        if (!$user) {
            return $this->Error('用户不存在!');
        }
        if ($ucs_region->auth_status) {
            if (!$user->auth_status) {
                return $this->Error('该地域需实名认证,请先完成实名认证!');
            }
        }
        $harddisk = $this->GetParam('harddisk');
        if (!is_array($harddisk)) {
            return $this->Error('磁盘数据不能为空!');
        }
        foreach ($harddisk as $key => $value) {
            var_dump($value);
            $value = json_decode($value, true);
            if (!array_key_exists('ucs_storage_plan_id', $value)) {
                return $this->Error('磁盘类型不能为空!');
            }
            if (!array_key_exists('type', $value)) {
                return $this->Error('磁盘类别不能为空!');
            }
            if (!array_key_exists('size', $value)) {
                return $this->Error('磁盘大小不能为空!');
            }
        }
        $bandwidth = $this->GetParam('bandwidth');
        $ip_number = $this->GetParam('ip_number');
        $time_type = $this->GetParam('time_type');
        $time_length = $this->GetParam('time_length');
        $count = $this->GetParam('count');


        $ip_count = UcsService::GetEnableIPCount($ucs_plan->ucs_region_id, $ip_number * $count);
        if ($ip_count != $ip_number * $count) {
            WechatService::SendToManagerError('UCS_IP资源不足', 'UCS线路IP资源不足,请尽快添加资源!', '请尽快处理', '/admin/');
            return $this->Error('资源不足!');
        }

        $price = UcsService::GetPrice($ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, $count);
        //检查自己是否能够买得起
        $amount = $price['total'];
        if ($amount < 0) {
            return $this->Error('消费金额不能低于0元!');
        }
        $user = User::create()->get(['id' => $user_id]);
        if ($user_id && $user) {
            return $this->Error('数据异常,请重新登录!');
        }
        if ($user->balance < $amount) {
            return $this->Error('余额不足!');
        }

        for ($i = 0; $i < $count; $i++) {
            $master = UcsService::GetQueueMaster($ucs_plan);
            if (!$master) {
                //发送相关警告给管理员
                WechatService::SendToManagerError('UCS_MASTER资源不足', 'UCS宿主机资源不足,请尽快添加资源!', '请尽快处理', '/admin/');
                return $this->Error('资源不足!');
            }
            $user_finance = UserService::Consume($user_id, $price['instance_price'], '购买云服务器', 'ucs', 0);
            if ($user_finance) {
                //消费成功
                $user_instance = UcsService::CreateInstance($user_id, $system_id, $ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, 0, $user->nickname);
                //更新订单中的实例ID
                $user_finance->instance_id = $user_instance->id;
                $user_finance->update();
            } else {
                return $this->Error('消费异常');
            }
        }
    }

}