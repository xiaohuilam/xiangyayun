<?php

namespace App\Controller\User;


use App\Controller\Common\UserLoginBase;
use App\Service\QrcodeService;
use App\Service\RechargeService;

class Finance extends UserLoginBase
{

    /**
     * @Param(name="type",required="",inArray=["wechat","alipay_pc","alipay_h5"])
     * @Param(name="amount",money="")
     * @Param(name="qrcode",inArray=[1,0])
     */
    public function recharge()
    {
        //充值金额 充值方式
        $type = $this->GetParam('type');
        $amount = $this->GetParam('amount');
        $qrcode = $this->GetParam('qrcode');
        $user_id = $this->GetUserId();
        $ip = $this->GetClientIP();
        $url = RechargeService::Pay($type, $amount, $user_id, $ip);
        if (!$qrcode) {
            return $this->Success('获取充值链接成功!', $url);
        }
        $byte = QrcodeService::Qrcode($url);
        return $this->ImageWrite($byte);
    }
}