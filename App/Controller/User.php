<?php

namespace App\Controller;

use App\Controller\Common\LoginBase;
use App\Service\AuthService;
use App\Service\QrcodeService;
use App\Service\RechargeService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Redis\CommandHandle\Auth;

class User extends LoginBase
{
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

    //支付宝认证初始化

    /**
     * @Param(name="cert_name",required="")
     * @Param(name="cert_number",required="",lengthMin="18",lengthMax="18")
     */
    public function alipay_auth_init()
    {
        $cert_name = $this->GetParam('cert_name');
        $cert_number = $this->GetParam('cert_number');
        if (AuthService::CheckCertNumber($cert_number)) {
            $ip = $this->GetClientIP();
            $ua = $this->GetUserAgent();
            $certify_id = AuthService::AlipayInit($cert_name, $cert_number);
            if ($certify_id) {
                $user_auth = AuthService:: SaveUserAuth($cert_name, $cert_number, 'alipay', $ip, $ua, $certify_id);
                $d['order_no'] = $user_auth->order_no;
                return $this->Success('生成认证订单成功!', $d);
            }
            return $this->Error('初始化接口失败!');
        }
        return $this->Error('身份证号码不正确!');
    }

    /**
     * @Param(name="order_no",required="")
     */
    public function alipay_auth_qrcode()
    {
        $order_no = $this->GetParam('order_no');
        $auth = AuthService::AlipayCertify($order_no);
        $byte = QrcodeService::Qrcode($auth);
        $this->ImageWrite($byte);
    }

    //认证状态查询

    /**
     * @Param(name="order_no",required="")
     */
    public function auth_query()
    {
        $order_no = $this->GetParam('order_no');
        $flag = AuthService::GetStatus($order_no);
        if ($flag) {
            return $this->Success('认证成功!', null, '/user/auth');
        }
        return $this->Error('等待认证');
    }
}