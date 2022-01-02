<?php

namespace App\Service;

use App\Model\UserFinance;
use App\Model\UserRecharge;
use TencentCloud\Ump\V20200918\Models\Config;

class RechargeService
{
    public static function SelectRechargeLog($user_id, $page = 1, $size = 15)
    {
        return UserRecharge::create()
            ->where('user_id', $user_id)
            ->order('id')
            ->limit($size * ($page - 1), $size)
            ->withTotalCount();
    }

    public static function SelectFinanceLog($user_id, $page = 1, $size = 15)
    {
        return UserFinance::create()
            ->where('user_id', $user_id)
            ->order('id')
            ->limit($size * ($page - 1), $size)
            ->withTotalCount();
    }

    private static function WechatConfig()
    {
        $config = config('PAY.WECHAT');
        $wechatConfig = new \EasySwoole\Pay\WeChat\Config();
        $wechatConfig->setAppId($config['APP_ID']);      // 除了小程序以外使用该APPID
        $wechatConfig->setMiniAppId($config['MINI_APP_ID']);  // 小程序使用该APPID
        $wechatConfig->setMchId($config['MCH_ID']);
        $wechatConfig->setKey($config['KEY']);
        $wechatConfig->setNotifyUrl($config['NOTIFY_URL']);
        $wechatConfig->setApiClientCert($config['API_CLIENT_CERT']);//客户端证书
        $wechatConfig->setApiClientKey($config['API_CLIENT_KEY']); //客户端证书秘钥
        return $wechatConfig;
    }

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

    public static function Pay($type, $amount, $user_id, $ip)
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
        if (!$recharge_id) {
            return null;
        }
        switch ($type) {
            case "alipay_pc":
                return self:: AlipayPc($order_no, $amount, $user_id);
                break;
            case "alipay_h5":
                return self:: AlipayH5($order_no, $amount, $user_id);
                break;
            case "wechat":
                return self::Wechat($order_no, $amount, $user_id, $ip);
                break;
        }
    }

    //充值成功入账
    public static function EntryAmount($order_no, $order_out_no)
    {
        $user_recharge = UserRecharge::create()
            ->where('order_no', $order_no)
            ->get();
        info("入账订单:" . json_encode($user_recharge));
        if ($user_recharge && $user_recharge->status == 0) {
            info('找到订单');
            //存在订单，并且没有处理订单
            $user_recharge->order_out_no = $order_out_no;
            $user_recharge->status = 1;
            $user_recharge->finish_time = date('Y-m-d H:i:s');
            $user_recharge->update();
            if ($user_recharge->amount) {
                switch ($user_recharge->type) {
                    case "alipay_pc":
                        $type = '支付宝';
                        break;
                    case "alipay_pc":
                        $type = '支付宝';
                        break;
                    case "wechat":
                        $type = '微信';
                        break;
                    default:
                        $type = "";
                        break;
                }
                //充值并且记录流水
                $flag = UserService::Recharge($user_recharge->user_id, $user_recharge->amount, $type . '充值');
                //充值通知
                WechatService::SendRechargeSuccessNotify($type, $user_recharge->user_id, $user_recharge->amount, $order_no);
                if (!$flag) {
                    error("充值失败!订单号:" . $user_recharge->order_no . "\t金额:" . $user_recharge->amount);
                }
            }
        }
    }


    public static function Wechat($order_no, $amount, $user_id, $ip)
    {
        $config = config('PAY.WECHAT');
        $app_name = config('SYSTEM.APP_NAME');
        $wechatConfig = self::WechatConfig();
        $bean = new \EasySwoole\Pay\WeChat\RequestBean\Scan();
        $bean->setOutTradeNo($order_no);
        $bean->setProductId('1');
        $bean->setBody("[" . $app_name . "]充值" . $amount . "元,会员ID:" . $user_id); // 示例商品标题(仅供参考)
        $bean->setTotalFee($amount * 100);
        $bean->setSpbillCreateIp($ip);
        $bean->setNotifyUrl($config['NOTIFY_URL']);
        $pay = new \EasySwoole\Pay\Pay();
        $data = $pay->weChat($wechatConfig)->scan($bean);
        WechatService::SendPayNotify('微信支付', $user_id, $amount, $order_no);
        return $data->getCodeUrl();
    }

    public static function WechatNotify($param)
    {
        $pay = new \EasySwoole\Pay\Pay();
        return $pay->weChat(self::WechatConfig())->verify($param);
    }

    public static function AlipayNotify($param)
    {
        $pay = new \EasySwoole\Pay\Pay();
        $order = new \EasySwoole\Pay\AliPay\RequestBean\NotifyRequest($param, true);
        $aliPay = $pay->aliPay(self::AlipayConfig());
        return $aliPay->verify($order);
    }

    public static function AlipayH5($order_no, $amount, $user_id)
    {

        $app_name = config('SYSTEM.APP_NAME');
        $pay = new \EasySwoole\Pay\Pay();
        ## (面向对象风格)设置请求参数 biz_content，组件自动帮你组装成对应的格式
        $order = new \EasySwoole\Pay\AliPay\RequestBean\Wap();
        // (必须)设置 商户订单号(商户订单号。64 个字符以内的大小，仅支持字母、数字、下划线。需保证该参数在商户端不重复。)
        $order->setOutTradeNo($order_no); // 示例订单号(仅供参考)
        // (必须)设置 订单总金额
        $order->setTotalAmount($amount); // 示例订单总金额，单位：元(仅供参考)
        // (必须)设置 商品标题/交易标题/订单标题/订单关键字等。注意：不可使用特殊字符，如 /，=，& 等。
        $order->setSubject("[" . $app_name . "]充值" . $amount . "元,会员ID:" . $user_id); // 示例商品标题(仅供参考)
        $order->setBody("订单编号:" . $order_no);

        $res = $pay->aliPay(self::AlipayConfig())->wap($order);
        // 将所有请求参数转为数组
        WechatService::SendPayNotify('支付宝H5支付', $user_id, $amount, $order_no);
        return \EasySwoole\Pay\AliPay\GateWay::NORMAL . "?" . http_build_query($res->toArray());
    }

    public static function AlipayPc($order_no, $amount, $user_id)
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
        WechatService::SendPayNotify('支付宝PC端支付', $user_id, $amount, $order_no);
        return \EasySwoole\Pay\AliPay\GateWay::NORMAL . "?" . http_build_query($res->toArray());
    }

}