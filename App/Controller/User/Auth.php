<?php

namespace App\Controller\User;

use App\Controller\Common\UserLoginBase;
use App\Service\AuthService;
use App\Service\QrcodeService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Auth extends UserLoginBase
{

    //支付宝认证初始化
    /**
     * @Param(name="cert_name",required="")
     * @Param(name="cert_number",required="",lengthMin="18",lengthMax="18")
     * 支付宝初始化信息
     */
    public function alipay_auth_init()
    {
        $cert_name = $this->GetParam('cert_name');
        $cert_number = $this->GetParam('cert_number');
        $cert_mobile = $this->GetParam('cert_mobile');
        $user_id = $this->GetUserId();
        if (AuthService::CheckCertNumber($cert_number)) {
            $ip = $this->GetClientIP();
            $ua = $this->GetUserAgent();
            $certify_id = AuthService::AlipayInit($cert_name, $cert_number);
            if ($certify_id) {
                $user_auth = AuthService::SaveUserAuth($user_id, $cert_mobile, $cert_name, $cert_number, 'alipay', $ip, $ua, $certify_id);
                $d['order_no'] = $user_auth->order_no;
                return $this->Success('生成认证订单成功!', $d);
            }
            return $this->Error('初始化接口失败!');
        }
        return $this->Error('身份证号码不正确!');
    }

    /**
     * @Param(name="order_no",required="")
     * 获取支付宝认证二维码
     */
    public function alipay_auth_qrcode()
    {
        $order_no = $this->GetParam('order_no');
        $auth = AuthService::AlipayCertify($order_no);
        $byte = QrcodeService::Qrcode($auth);
        $data['image'] = $byte;
        return $this->Success('获取二维码成功', $data);
    }

    //认证状态查询

    /**
     * @Param(name="order_no",required="")
     * 查询认证状态
     */
    public function auth_query()
    {
        $order_no = $this->GetParam('order_no');
        $auth = AuthService::GetStatus($order_no);
        $data['status'] = $auth ? true : false;
        $data['auth'] = $auth;
        if ($auth) {
            return $this->Success('认证成功!', $data);
        }
        return $this->Success('等待认证', $data);
    }
}