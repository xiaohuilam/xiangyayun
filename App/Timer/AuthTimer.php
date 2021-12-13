<?php

namespace App\Timer;

use App\Service\AuthService;

class AuthTimer
{
    public static function run()
    {
        $user_auths = AuthService::SelectAllNotAuth();
        foreach ($user_auths as $key => $value) {
            if ($value->cert_type == "alipay") {
                info('扫描支付宝认证订单' . $value->order_no);
                AuthService::AlipayQueryForTimer($value, $value->order_out_no);
            }
        }
    }

}