<?php

namespace App\Controller;

use App\Controller\Common\Base;
use App\Model\WechatPushTemp;
use App\Queue\UcsQueue;
use App\Service\LogService;
use App\Service\RechargeService;
use App\Service\SmsService;
use App\Service\UcsService;
use App\Service\UserService;
use App\Service\WechatService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Queue\Job;
use EasySwoole\VerifyCode\Conf;
use EasySwoole\WeChat\Factory;

class Api extends Base
{
    public function test()
    {
        WechatService:: SendToManagerError('服务器异常', "您的127.0.0.1服务器有问题!", "请及时处理!", "http://www.baidu.com");
//        $url = RechargeService::Alipay();
//        return $this->Success('1', $url);
//        if (UcsJob(['status' => true])) {
//            $this->Success();
//        }
    }

    public function start()
    {
        UcsService::Start(1);
    }


    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="verifycode",required="",lengthMin="6")
     */
    public function verifycode_login()
    {
        $username = $this->GetParam('username');
        $verifycode = $this->GetParam('verifycode');
        $sms_code = $this->Get('sms_code');
        if ($verifycode != $sms_code) {
            return $this->Error('验证码错误!');
        }
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if (LogService::FindLoginByIp($ip)) {
            return $this->Error('该IP错误次数过多,请稍后再试试!');
        }
        if (LogService::FindLoginByUserName($username)) {
            return $this->Error('该用户错误次数过多,请稍后再试试!');
        }
        $user = UserService::FindByUserName($username);
        if (!$user) {
            LogService:: LoginError(0, $username, $ip, $ua, '用户名或密码错误');
            return $this->Error('用户不存在');
        }
        if ($user->status != 1) {
            LogService:: LoginError($user->id, $username, $ip, $ua, '用户不可登录');
            return $this->Error('用户不可登录');
        }
        if ($user->lock_status == 1 && $user->lock_datetime > date('Y-m-d H:i:s')) {
            LogService:: LoginError($user->id, $username, $ip, $ua, '用户锁定至' . $user->lock_datetime);
            return $this->Error('用户锁定至' . $user->lock_datetime);
        }
        $this->SetUserId($user->id);
        LogService:: LoginSuccess($user->id, $username, $ip, $ua, '登录成功');
        return $this->Success();
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     */
    public function password_login()
    {
        $username = $this->GetParam('username');
        $password = $this->GetParam('password');
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if (LogService::FindLoginByIp($ip)) {
            return $this->Error('该IP错误次数过多,请稍后再试试!');
        }
        if (LogService::FindLoginByUserName($username)) {
            return $this->Error('该用户错误次数过多,请稍后再试试!');
        }
        $user = UserService::FindByUserName($username);
        if (!$user) {
            LogService:: LoginError(0, $username, $ip, $ua, '用户名或密码错误');
            return $this->Error('用户不存在');
        }
        if ($user->password != md5($password)) {
            LogService:: LoginError($user->id, $username, $ip, $ua, '用户名或密码错误');
            return $this->Error('用户名或密码错误');
        }
        if ($user->status != 1) {
            LogService:: LoginError($user->id, $username, $ip, $ua, '用户不可登录');
            return $this->Error('用户不可登录');
        }
        if ($user->lock_status == 1 && $user->lock_datetime > date('Y-m-d H:i:s')) {
            LogService:: LoginError($user->id, $username, $ip, $ua, '用户锁定至' . $user->lock_datetime);
            return $this->Error('用户锁定至' . $user->lock_datetime);
        }
        $this->SetUserId($user->id);
        LogService:: LoginSuccess($user->id, $username, $ip, $ua, '登录成功');
        return $this->Success();
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     * @Param(name="sms_code",integer="",lengthMin="6")
     */
    public function register()
    {
        // sms
        $username = $this->GetParam('username');
        $password = $this->GetParam('password');
        $sms_code = $this->GetParam('sms_code');
        $code = SmsService::FindCode($username, $sms_code);

        if ($code->data) {//Verify

        }
    }


    public function verifycode()
    {
        $config = new Conf();
        $config->setBackColor('#3A5FCD')
            ->setFontColor('#fff')
            ->setImageWidth(100)
            ->setImageHeight(40)
            ->setFontSize(14)
            ->setCharset('1234567890')
            ->setLength(4);

        $VCode = new \EasySwoole\VerifyCode\VerifyCode($config);
        $drawCode = $VCode->DrawCode();
        $this->Set('img_code', $drawCode->getImageCode());
        return $this->WriteImage($drawCode->getImageByte());
    }


    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="verifycode",required="",lengthMin="4")
     * @Param(name="action",required="")
     */
    public function send_code()
    {
        $code = $this->Get('img_code');
        $code = '1111';
        if (!$code) {
            return $this->Error('验证码错误!');
        }
        $username = $this->GetParam('username');
        $verifycode = $this->GetParam('verifycode');
        $action = $this->GetParam('action');
        if ($verifycode != $code) {
            return $this->Error('验证码错误!');
        }

        if ($action == 'register') {
            $user = UserService::FindByUserName($username);
            if ($user) {
                return $this->Error('用户已注册!请直接登录', null, '/login');
            }

            $sms_code = 100000;
            $this->Set('sms_code', $sms_code);
            //判断该IP或该用户今天收到了多少短信

            SmsJob([
                'mobile' => $username,
                'action' => 'action_code',
                'params' => [
                    '注册用户', $code
                ],
            ]);
            return $this->Success('发送成功');
        } else if ($action == 'login') {
            $user = UserService::FindByUserName($username);
            $sms_code = 100000;
            $this->Set('sms_code', $sms_code);
            if ($user && $user->wx_openid) {
                WechatService::SendCode($user->id, '登录会员中心', $sms_code, '5分钟', 'http://upy.cn/');
                return $this->Success('发送微信消息成功!');
            }

            SmsJob([
                'mobile' => $username,
                'action' => 'action_code',
                'params' => [
                    '注册用户', $code
                ],
            ]);
            return $this->Success('发送手机短信成功');
        }
        return $this->Error('请认真填写短信用途');
    }

    public function test_wechat()
    {
        WechatService::SendPayNotify(1, 'otbIy0R2VgjMxNwBntbVMYgCfwus', 1000, 111);
    }

    public function test_loadtemplate()
    {
        WechatService::LoadMessageTemplate();
    }

    public function test_sms()
    {
        WechatService::send('测试', '测试测试测试', 'https://upy.cn', 'https://img2.baidu.com/it/u=1945464906,1635022113&fm=26&fmt=auto');
        SmsJob([
            'mobile' => '18108018820',
            'action' => 'action_code',
            'params' => [
                '注册用户', 123456
            ],
        ]);
    }
}