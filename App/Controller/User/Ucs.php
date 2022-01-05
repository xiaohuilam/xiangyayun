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
use App\Status\UcsActStatus;
use App\Status\UcsRunStatus;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Ucs extends UserLoginBase
{

    //安全组列表
    public function list()
    {

    }


    /**
     * @Param(name="instance_id",integer="")
     * 获取实例防火墙参数
     */
    public function detail()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $firewall_rules = UcsService::FindUcsFirewallByUcsInstanceId($instance_id);
            return $this->Success('获取防火墙规则成功', $firewall_rules);
        }
    }

    //获取安全组列表
    public function firewall_group_list()
    {
        $user_id = $this->GetUserId();
        $page = $this->GetParam('page');
        $size = $this->GetParam('size');
        $ucs_firewall_list = UcsService::SelectUcsFirewallGroupByUserIdPage($user_id, $page, $size);
        return $this->Success('获取安全组成功', $ucs_firewall_list);
    }

    public function firewall_rule_list()
    {
        $user_id = $this->GetUserId();
        $ucs_firewall_group_id = $this->GetParam('ucs_firewall_group_id');
        $ucs_firewall_group = UcsService::FindUcsFirewallGroupById($ucs_firewall_group_id);
        if (!$ucs_firewall_group) {
            return $this->Error('这个安全组不存在！');
        }
        if ($ucs_firewall_group->user_id != $user_id) {
            return $this->Error('这个安全组不是您的！');
        }
        $page = $this->GetParam('page');
        $size = $this->GetParam('size');
        $ucs_firewall_list = UcsService::SelectUcsFirewallRuleByGroupIdPage($ucs_firewall_group_id, $page, $size);
        return $this->Success('获取安全组规则成功', $ucs_firewall_list);
    }

    //修改或编辑安全组
    public function firewall_group_edit()
    {
        $id = $this->GetParam('id');
        $name = $this->GetParam('name');
        $remark = $this->GetParam('remark');
        $user_id = $this->GetUserId();
        if ($id) {
            //有ID则是编辑
            $ucs_firewall_group = UcsService::FindUcsFirewallGroupById($id);
            if (!$ucs_firewall_group) {
                return $this->Error('这个安全组不存在！');
            }
            if ($ucs_firewall_group->user_id != $user_id) {
                return $this->Error('这个安全组不是您的！');
            }
        }
        UcsService::EditUcsFirewallGroup($id, $name, $remark, $user_id);
        return $this->Success('修改安全组成功');
    }

    /**
     * @Param(name="id",integer="")
     * @Param(name="ucs_firewall_group_id",integer="")
     * @Param(name="priority",integer="")
     * @Param(name="action",inArray=["accept","drop","reject","continue","return"])
     * @Param(name="direction",inArray=["in","out","full"])
     * @Param(name="protocol",inArray=["tcp","udp","full"])
     * @Param(name="src_port_range",required="")
     * @Param(name="dst_port_range",required="")
     * @Param(name="src_ip",required="")
     * @Param(name="dst_ip",required="")
     * 编辑实例防火墙规则
     */
    public function edit_firewall_rule()
    {
        $params = [];
        $params['id'] = $this->GetParam('id');
        $params['ucs_firewall_group_id'] = $this->GetParam('ucs_firewall_group_id');
        $user_id = $this->GetUserId();
        $ucs_firewall_group = UcsService::FindUcsFirewallGroupById($params['ucs_firewall_group_id']);
        if (!$ucs_firewall_group) {
            return $this->Error('这个安全组不存在！');
        }
        if ($ucs_firewall_group->user_id != $user_id) {
            return $this->Error('这个安全组不是您的！');
        }
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
     * @Param(name="instance_id",integer="")
     */
    public function instance_info()
    {
        $instance_id = $this->GetParam('instance_id');
        $instance = $this->CheckIsMine($instance_id);
        if ($instance) {
            //获取实例详情
            $data = UcsService::FindInstanceInfoByInstanceId($instance_id);
            //获取实例操作日志
            return $this->Success('获取实例详情成功', $data);
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * @Param(name="page",integer="")
     * @Param(name="size",integer="")
     */
    public function instance_action_log()
    {
        $instance_id = $this->GetParam('instance_id');
        $page = $this->GetParam('page') ?? 1;
        $size = $this->GetParam('size') ?? 10;
        $instance = $this->CheckIsMine($instance_id);
        if ($instance) {
            $data = UcsService::SelectTaskListPage(['ucs_instance_id', $instance_id], $page, $size);
            return $this->Success('获取操作日志成功!', $data);
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
     * 计算价格
     */
    public function get_price()
    {
        $plan_id = $this->GetParam('plan_id');
        $harddisk = $this->GetParam('harddisk');
        if (!is_array($harddisk)) {
            return $this->Error('磁盘数据不能为空!');
        }

        foreach ($harddisk as $value) {
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

    //检查是否过期
    private function CheckExpire($instance_id)
    {
        $instance = $this->CheckIsMine($instance_id);
        if ($instance) {
            if ($instance->expire_time < date('Y-m-d H:i:s')) {
                return $this->Error('业务已到期,无法操作');
            }
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
        //检查是否是我自己的,以及是否过期
        $instance = $this->CheckExpire($instance_id);
        if ($instance) {
            if ($instance->run_status == UcsRunStatus::RUN) {
                UcsService::ShutdownAction($instance_id, 0);
                return $this->Success('发送关机指令成功!');
            } else {
                return $this->Error('当前实例状态不允许执行关机');
            }
        }
    }

    public function get_region()
    {
        $ucs_region_id = $this->GetParam('ucs_region_id');
        if ($ucs_region_id) {
            $data = UcsService::FindUcsRegionById($ucs_region_id);
            return $this->Success('成功!', $data);
        }
        $regions = UcsService::SelectRegion();
        $temp = [];
        foreach ($regions as $region) {
            $region = $region->toArray();
            if ($region['show_status'] == 1) {
                $temp[] = $region;
            }
        }
        return $this->Success('获取地域列表', $temp);
    }

    public function get_plan()
    {
        $ucs_region_id = $this->GetParam('ucs_region_id');
        $plans = UcsService::SelectPlanByUcsRegionId($ucs_region_id);
        $temp = [];
        foreach ($plans as $plan) {
            $temp[] = $plan->toArray();
        }
        $data = TreeService::GetUcsPlanTree($temp);
        return $this->Success('获取规格列表成功', $data);
    }

    public function get_storage()
    {
        $ucs_region_id = $this->GetParam('ucs_region_id');
        $storage = UcsService::SelectStorageByUcsRegionId($ucs_region_id);
        return $this->Success('获取磁盘类型成功', $storage);
    }

    /**
     *获取系统列表
     */
    public function get_system()
    {
        $instance_id = $this->GetParam('instance_id');
        $ucs_region_id = $this->GetParam('ucs_region_id');
        if ($ucs_region_id) {
            //如果是地域的话
            $data = UcsService::SelectSystemTree();
            $data = TreeService::GetSystemClassTree($data);
            return $this->Success('获取系统列表成功', $data);
        }
        if ($instance_id) {
            //检查是否过期
            if ($this->CheckExpire($instance_id)) {
                $ucs_instance = UcsService::FindUcsInstanceById($instance_id);
                $data = UcsService::SelectSystemTree();
                $data = TreeService::GetSystemClassTree($data);
                return $this->Success('获取系统列表成功', $data);
            }
        }
//        $instance_id = $this->GetParam('instance_id');
//        $data = UcsService::SelectSystemClass();
//        $system_list = [];
//        foreach ($data as $value) {
//            $systems = UcsService::SelectSystem($value->id);
//            $system = [];
//            foreach ($systems as $v) {
//                $temp['label'] = $v->system_version;
//                $temp['value'] = $v->id;
//                $system[] = $temp;
//            }
//            $item['label'] = $value->system_class;
//            $item['value'] = $value->id;
//            $item['children'] = $system;
//            $system_list[] = $item;
//        }
//        return $this->Success('', $system_list);
    }


    /**
     * @Param(name="instance_id",integer="")
     * 开机
     */
    public function start()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckExpire($instance_id)) {
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
        if ($this->CheckExpire($instance_id)) {
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
        //检查是否过期
        if ($this->CheckExpire($instance_id)) {
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
        //检查是否过期
        $instance = $this->CheckExpire($instance_id);
        if ($instance) {
            if ($instance->act_status != UcsActStatus::NORMAL) {
                return $this->Error('该服务器正在操作中');
            }
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
     * @Param(name="password",required="",lengthMin="6")
     * 重设密码
     */
    public function reset_password()
    {
        $instance_id = $this->GetParam('instance_id');
        //检查是否过期
        $instance = $this->CheckExpire($instance_id);
        if ($instance) {
            $password = $this->GetParam('password');
            $user = $this->GetUser();
            UcsService::ResetPasswordAction($instance, $password, 0, $user->nickname);
            return $this->Success('发送重设密码指令成功!');
        }
    }


    /**
     * @Param(name="instance_id",integer="")
     * 强制关机
     */
    public function force_shutdown()
    {
        $instance_id = $this->GetParam('instance_id');
        //检查是否过期
        if ($this->CheckExpire($instance_id)) {
            $user = $this->GetUser();
            UcsService::ForceShutdownAction($instance_id, 0, $user->nickname);
            return $this->Success('发送强制关机指令成功!');
        }
    }


    /**
     * @Param(name="instance_id",integer="")
     * @Param(name="time_type",inArray=["day","month","year"])
     * @Param(name="time_length",integer="")
     * 续费实例价格
     */
    public function renew_price()
    {

        $instance_id = $this->GetParam('instance_id');
        $time_type = $this->GetParam('time_type');
        $time_length = $this->GetParam('time_length');
        $instance = $this->CheckIsMine($instance_id);
        if ($instance) {
            $data['total'] = UcsService::GetReNewPrice($instance_id, $time_type, $time_length);
            $data['after_expire_time'] = UcsService::GetReNewExpireTime($instance->expire_time, $time_type, $time_length);
            return $this->Success('获取价格成功', $data);
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * @Param(name="time_type",inArray=["day","month","year"])
     * @Param(name="time_length",integer="")
     * 续费实例
     */
    public function renew()
    {
        //续费
        $instance_id = $this->GetParam('instance_id');
        $time_type = $this->GetParam('time_type');
        $time_length = $this->GetParam('time_length');
        //续费只需要检查是不是自己的,有没有被锁定
        if ($this->CheckIsMine($instance_id)) {
            $price = UcsService::GetReNewPrice($instance_id, $time_type, $time_length);
            if ($price > 0) {
                //
                $user_id = $this->GetUserId();
//                UserService::Consume($user_id, $price, '服务器续费');
            }
        }
        return $this->Success('成功');
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
     * @Param(name="password",required="")
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
        $password = $this->GetParam('password');


        $ip_count = UcsService::GetEnableIPCount($ucs_plan->ucs_region_id, $ip_number * $count);
        if ($ip_count < $ip_number * $count) {
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
        if (!$user_id || !$user) {
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
                $user_instance = UcsService::CreateInstance($master, $user_id, $system_id, $ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, 0, $user->nickname, $password);
                //更新订单中的实例ID
                $user_finance->instance_id = $user_instance->id;
                $user_finance->update();
                return $this->Success();
            } else {
                return $this->Error('消费异常');
            }
        }
    }
}