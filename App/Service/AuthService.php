<?php

namespace App\Service;


use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Member\Identification\Models\IdentityParam;
use Alipay\EasySDK\Member\Identification\Models\MerchantConfig;
use App\Model\UserAuth;

class AuthService
{

    public static function GetUserAuth()
    {

    }

    //检查身份证号码是否正确
    public static function CheckCertNumber($vStr)
    {
        $vCity = array('11', '12', '13', '14', '15', '21', '22', '23',
            '31', '32', '33', '34', '35', '36', '37', '41', '42', '43',
            '44', '45', '46', '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91');

        if (!preg_match('/^(\d{17}[xX\d]|\d{15})$/', $vStr)) return false;
        if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);
        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' .
                substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' .
                substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }
        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18) {
            $vSum = 0;
            for ($i = 17; $i >= 0; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
            }
            if ($vSum % 11 != 1) return false;
        }
        return true;
    }

    //用户获取认证状态
    public static function GetStatus($order_no)
    {
        $user_auth = UserAuth::create()->get(['order_no' => $order_no]);
        if ($user_auth && $user_auth->finish_status == 1) {
            return true;
        }
        return false;
    }

    //生成订单号
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
        return 'AUTH' . date('YmdHis') . $randstr;
    }

    //保存认证信息
    public static function SaveUserAuth($user_id, $cert_mobile, $cert_name, $cert_number, $cert_type, $ip, $ua, $order_out_no)
    {
        $order_no = self::GetOrderNo();
        //开始认证
        $user_auth = UserAuth::create([
            'user_id' => $user_id,
            'cert_name' => $cert_name,
            'cert_number' => $cert_number,
            'cert_mobile' => $cert_mobile,
            'order_no' => $order_no,
            'order_out_no' => $order_out_no,
            'cert_type' => $cert_type,
            'create_time' => date('Y-m-d H:i:s'),
            'create_ip' => $ip,
            'create_ua' => $ua,
        ]);
        $user_auth->save();

        return $user_auth;
    }

    //获取认证所使用的URL
    public static function AlipayCertify($order_no)
    {
        //1. 设置参数（全局只需设置一次）
        $user_auth = UserAuth::create()->get(['order_no' => $order_no]);
        if ($user_auth) {
            Factory::setOptions(self::GetAlipayOptions());
            return Factory::member()->identification()->certify($user_auth->order_out_no)->body;
        }
        return null;
    }


    //初始化支付宝认证
    public static function AlipayInit($cert_name, $cert_number)
    {
        //1. 设置参数（全局只需设置一次）
        Factory::setOptions(self::GetAlipayOptions());
        try {
            //2. 发起API调用（以支付能力下的统一收单交易创建接口为例）
            $identityParam = new IdentityParam();
            $identityParam->identityType = "CERT_INFO";
            $identityParam->certType = "IDENTITY_CARD";
            $identityParam->certName = $cert_name;
            $identityParam->certNo = $cert_number;

            $merchantConfig = new MerchantConfig();
            //认证成功回调地址
            $merchantConfig->returnUrl = "www.taobao.com";
            $result = Factory::member()->identification()->init(microtime(), 'FACE', $identityParam, $merchantConfig);
            $responseChecker = new ResponseChecker();
            //3. 处理响应或异常
            if ($responseChecker->success($result)) {
                return $result->certifyId;
            } else {
                info("调用失败，原因：" . $result->msg . "，" . $result->subMsg);
            }
        } catch (Exception $e) {
            info("调用失败：" . $e->getMessage());
        }
        return false;
    }

    //获取15分钟未认证成功的订单
    public static function SelectAllNotAuth()
    {
        return UserAuth::create()
            ->where('create_time', date('Y-m-d H:i:s', strtotime('-15 minutes')), '>')
            ->where('finish_status', 0)
            ->all();
    }

    public static function AlipayQueryForTimer($user_auth, $certify_id)
    {
        //1. 设置参数（全局只需设置一次）
        Factory::setOptions(self::GetAlipayOptions());
        $result = Factory::member()->identification()->query($certify_id);
        if ($result->passed == "T") {
            //认证成功则修改用户认证状态
            UserService::SuccessUserAuth($user_auth);
            $user_auth->finish_status = 1;
            $user_auth->finish_time = date('Y-m-d H:i:s');
            $user_auth->update();
        }
    }

    //获取支付宝配置项
    private static function GetAlipayOptions()
    {
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';


        $config = config('PAY.ALIPAY');
        ### 配置支付公共请求参数
        // (必须)设置 支付宝分配给开发者的应用ID
        $options->appId = $config['APP_ID'];

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = $config['PRIVATE_KEY'];

        $options->alipayCertPath = __DIR__ . "/../../" . $config['ALIPAY_CERT_PUBLIC_KEY_PATH'];
        $options->alipayRootCertPath = __DIR__ . "/../../" . $config['ALIPAY_ROOT_CERT_PATH'];
        $options->merchantCertPath = __DIR__ . "/../../" . $config['MERCHANT_CERT_PATH'];
        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        $options->alipayPublicKey = $config['PUBLIC_KEY'];
        return $options;
    }
}