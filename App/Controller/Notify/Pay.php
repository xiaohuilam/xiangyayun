<?php

namespace App\Controller\Notify;

use App\Controller\Common\Base;
use App\Service\RechargeService;

class Pay extends Base
{

    public function alipay()
    {
        $params = $this->GetParam();
        if (RechargeService::AlipayNotify($params)) {
            //验证成功
            $order_no = $params['out_trade_no'];
            $order_out_no = $params['trade_no'];
            RechargeService::EntryAmount($order_no, $order_out_no);
        }
        $this->TextWrite(\EasySwoole\Pay\AliPay\AliPay::success());
    }

    public function wechat()
    {
        $params = $this->request()->getBody()->__toString();
        var_dump($params);
        $data = RechargeService::WechatNotify($params);
        var_dump($data);
        if ($data) {
            //验证成功
            $order_no = $params['out_trade_no'];
            $order_out_no = $params['trade_no'];
            RechargeService::EntryAmount($order_no, $order_out_no);
        }
        $this->TextWrite(\EasySwoole\Pay\AliPay\AliPay::success());
    }
}