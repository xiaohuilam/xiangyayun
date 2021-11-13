<?php

namespace App\Controller;

use App\Controller\Common\Base;
use App\Queue\UcsQueue;
use App\Service\LogService;
use App\Service\SmsService;
use App\Service\UserService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Queue\Job;
use EasySwoole\VerifyCode\Conf;

class Api extends Base
{
    public function test()
    {
        if (UcsJob(['status' => true])) {
            $this->Success();
        }
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     */
    public function login()
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
        $this->Set('code', $drawCode->getImageCode());
        return $this->JsonImage($drawCode->getImageByte());
    }


    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="verifycode",required="",lengthMin="4")
     */
    public function send_code()
    {
        $code = $this->Get('code');
        if (!$code) {
            return $this->Error('验证码错误!');
        }
        $username = $this->GetParam('username');
        $verifycode = $this->GetParam('verifycode');
        if ($verifycode != $code) {
            return $this->Error('验证码错误!');
        }
        $user = UserService::FindByUserName($username);
        if ($user) {
            return $this->Error('用户已注册!请直接登录', null, '/login');
        }
        $sms_code = 100000;
        //判断该IP或该用户今天收到了多少短信
        if ($sms_code) {

        }
        SmsJob([
            'mobile' => $username,
            'action' => 'action_code',
            'params' => [
                '注册用户', $code
            ],
        ]);
        return $this->Success('发送成功', null, '/user/dashboard');
    }
}