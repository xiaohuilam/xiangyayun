<?php

namespace App\Service;

use App\Model\UserRecharge;

class RechargeService
{
    private static function AlipayConfig()
    {
        $config = config('PAY.ALIPAY');
        $aliConfig = new \EasySwoole\Pay\AliPay\Config();
        ### 配置支付公共请求参数
        // (必须)设置 支付宝分配给开发者的应用ID
        $aliConfig->setAppId($config['APP_ID']);
        $aliConfig->setSignType('RSA2');
        $aliConfig->setPrivateKey($config['PRIVATE_KEY']);
        if ($config['CERT_MODE']) {
            $aliConfig->setCertMode(true);
            // (必须)设置 支付宝公钥文件路径
            $aliConfig->setCertPath(__DIR__ . "/../../" . $config['ALIPAY_CERT_PUBLIC_KEY_PATH']); // 示例支付宝公钥文件路径
            // (必须)设置 支付宝根证书文件路径
            $aliConfig->setRootCertPath(__DIR__ . "/../../" . $config['ALIPAY_ROOT_CERT_PATH']); // 示例支付宝公钥根证书文件路径
            // (必须)设置 阿里应用公钥证书文件路径
            $aliConfig->setMerchantCertPath(__DIR__ . "/../../" . $config['MERCHANT_CERT_PATH']);
            // (必须)设置 阿里应用私钥(支持 .pem 结尾的格式，默认为 PKCS1 格式)，用于生成签名
        } else {
            $aliConfig->setPublicKey($config['PUBLIC_KEY']); // 示例应用公钥字符串
        }
        $aliConfig->setReturnUrl($config['RETURN_URL']);
        $aliConfig->setNotifyUrl($config['NOTIFY_URL']);
        // (必须)设置 请求网关(默认为 沙箱模式)
        $aliConfig->setGateWay(\EasySwoole\Pay\AliPay\GateWay::NORMAL);
        return $aliConfig;
    }

    private static function GetOrderNo($length = 6)
    {
        //字符组合
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($str) - 1;
        $randstr = '';
        for ($i = 0; $i < $length; $i++) {
            $num = mt_rand(0, $len);
            $randstr .= $str[$num];
        }
        return 'PAY' . date('YmdHis') . $randstr;
    }

    public static function Pay($type, $amount, $user_id)
    {
        $order_no = self::GetOrderNo(6);
        $recharge_id = UserRecharge::create([
            'user_id' => $user_id,
            'type' => $type,
            'amount' => $amount,
            'order_no' => $order_no,
            'create_time' => date('Y-m-d H:i:s'),
            'status' => 0
        ])->save();
        switch ($type) {
            case "alipay":
                return self:: Alipay($order_no, $amount, $user_id);
                break;
        }
    }

    public static function AlipayNotify($param)
    {
        $pay = new \EasySwoole\Pay\Pay();
        $order = new \EasySwoole\Pay\AliPay\RequestBean\NotifyRequest($param, true);
        $aliPay = $pay->aliPay(self::AlipayConfig());
        $result = $aliPay->verify($order);
        var_dump($result);
    }

    public static function Alipay($order_no, $amount, $user_id)
    {
        $app_name = config('SYSTEM.APP_NAME');
        $pay = new \EasySwoole\Pay\Pay();
        ## (面向对象风格)设置请求参数 biz_content，组件自动帮你组装成对应的格式
        $order = new \EasySwoole\Pay\AliPay\RequestBean\Web();
        // (必须)设置 商户订单号(商户订单号。64 个字符以内的大小，仅支持字母、数字、下划线。需保证该参数在商户端不重复。)
        $order->setOutTradeNo($order_no); // 示例订单号(仅供参考)
        // (必须)设置 订单总金额
        $order->setTotalAmount($amount); // 示例订单总金额，单位：元(仅供参考)
        // (必须)设置 商品标题/交易标题/订单标题/订单关键字等。注意：不可使用特殊字符，如 /，=，& 等。
        $order->setSubject("[" . $app_name . "]充值" . $amount . "元,会员ID:" . $user_id); // 示例商品标题(仅供参考)
        $order->setBody("订单编号:" . $order_no);

        $res = $pay->aliPay(self::AlipayConfig())->web($order);
        // 将所有请求参数转为数组
        WechatService::SendPayNotify($user_id);
        return \EasySwoole\Pay\AliPay\GateWay::NORMAL . "?" . http_build_query($res->toArray());
    }

}