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
        try {
            $data = RechargeService::WechatNotify($params);
            $order_out_no = $data->get('transaction_id');
            $order_no = $data->get('out_trade_no');
            RechargeService::EntryAmount($order_no, $order_out_no);
        } catch (\EasySwoole\Pay\Exceptions\InvalidArgumentException $e) {
            error('微信支付验签失败!');
        }
        $this->TextWrite(\EasySwoole\Pay\WeChat\WeChat::success());
    }
}