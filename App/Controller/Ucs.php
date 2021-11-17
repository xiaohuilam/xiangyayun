<?php

namespace App\Controller;

use App\Controller\Common\LoginBase;
use App\Model\UcsInstance;
use App\Model\UcsRegion;
use App\Model\User;
use App\Service\UcsService;
use App\Service\UserService;
use App\Service\WechatService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Ucs extends LoginBase
{
    /**
     * @Param(name="plan_id",required="")
     * @Param(name="harddisk",required="")
     * @Param(name="bandwidth",integer="")
     * @Param(name="ip_number",integer="")
     * @Param(name="time_type",inArray=["day","month","year"])
     * @Param(name="time_length",integer="")
     * @Param(name="count",integer="")
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
        $price = UcsService::GetPrice($plan_id, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, $count);
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
            return $this->Error('实例并非是您的!');
        }
        if ($instance->lock_status == 1) {
            return $this->Error('实例已被锁定');
        }
        return true;
    }

    /**
     * @Param(name="instance_id",integer="")
     */
    public function shutdown()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            UcsService::ShutdownAction($instance_id);
            return $this->Success('成功!');
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     */
    public function force_shutdown()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            UcsService::ForceShutdownAction($instance_id);
            return $this->Success('成功!');
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
     */
    public function buy()
    {

        //创建实例
        $system_id = $this->GetParam('system_id');
        $plan_id = $this->GetParam('plan_id');
        $ucs_plan = UcsService::GetUcsPlan($plan_id);
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
                return $this->Error('本地域需实名认证,请先完成实名认证!');
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
        $price = UcsService::GetPrice($plan_id, $harddisk, $bandwidth, $ip_number, $time_type, $time_length, $count);
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
                WechatService::SendToManagerError('UCS_MASTER资源不足', 'UCS宿主机资源不足,请尽快添加资源!', '请尽快处理', '/admin/');
                return $this->Error('资源不足!');
            }
            $flag = UserService::Consume($user_id, $price['instance_price'], '购买云服务器');
            if ($flag) {
                //消费成功
                UcsService::CreateInstance($user_id, $system_id, $ucs_plan, $harddisk, $bandwidth, $ip_number, $time_type, $time_length);
            } else {
                return $this->Error('消费异常');
            }
        }
    }

}