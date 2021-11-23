<?php

namespace App\Controller\User;

use App\Controller\Common\Base;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\UserLogService;
use App\Service\UserService;
use App\Service\WechatService;
use EasySwoole\VerifyCode\Conf;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Api extends Base
{

    //二维码登录
    public function wx_qrcode_login()
    {
        $data = WechatService::GetQrcode("QRCODE_USER_LOGIN");

        $byte = QrcodeService::Qrcode($data['url']);
        //服务端获取EventKey
        $ticket = $data['ticket'];
        $this->Set('user.ticket', $ticket);
        RedisService::SetWxLoginUserTicket($ticket, 0);
        return $this->ImageWrite($byte);
    }

    public function wx_qrcode_status()
    {
        //状态
        $ticket = $this->Get('user.ticket');
        var_dump($ticket);
        $user_id = RedisService::GetWxLoginUserTicket($ticket);
        var_dump($user_id);
        if ($user_id) {
            return $this->Success('微信登录成功!');
        }
        return $this->Error('等待扫码中');
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="verifycode",required="",lengthMin="6")
     * 验证码登录
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
        if (UserLogService::FindLoginByIp($ip)) {
            return $this->Error('该IP错误次数过多,请稍后再试试!');
        }
        if (UserLogService::FindLoginByUserName($username)) {
            return $this->Error('该用户错误次数过多,请稍后再试试!');
        }
        $user = UserService::FindByUserName($username);
        if (!$user) {
            UserLogService:: LoginError(0, $username, $ip, $ua, '用户名或密码错误');
            return $this->Error('用户不存在');
        }
        if ($user->status != 1) {
            UserLogService:: LoginError($user->id, $username, $ip, $ua, '用户不可登录');
            return $this->Error('用户不可登录');
        }
        if ($user->lock_status == 1 && $user->lock_datetime > date('Y-m-d H:i:s')) {
            UserLogService:: LoginError($user->id, $username, $ip, $ua, '用户锁定至' . $user->lock_datetime);
            return $this->Error('用户锁定至' . $user->lock_datetime);
        }
        $this->SetUserId($user->id);
        UserLogService:: LoginSuccess($user->id, $username, $ip, $ua, '登录成功');
        return $this->Success();
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     * 密码登录
     */
    public function password_login()
    {
        $username = $this->GetParam('username');
        $password = $this->GetParam('password');
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if (UserLogService::FindLoginByIp($ip)) {
            return $this->Error('该IP错误次数过多,请稍后再试试!');
        }
        if (UserLogService::FindLoginByUserName($username)) {
            return $this->Error('该用户错误次数过多,请稍后再试试!');
        }
        $user = UserService::FindByUserName($username);
        if (!$user) {
            UserLogService:: LoginError(0, $username, $ip, $ua, '用户名或密码错误');
            return $this->Error('用户不存在');
        }
        if ($user->password != md5($password)) {
            UserLogService:: LoginError($user->id, $username, $ip, $ua, '用户名或密码错误');
            return $this->Error('用户名或密码错误');
        }
        if ($user->status != 1) {
            UserLogService:: LoginError($user->id, $username, $ip, $ua, '用户不可登录');
            return $this->Error('用户不可登录');
        }
        if ($user->lock_status == 1 && $user->lock_datetime > date('Y-m-d H:i:s')) {
            UserLogService:: LoginError($user->id, $username, $ip, $ua, '用户锁定至' . $user->lock_datetime);
            return $this->Error('用户锁定至' . $user->lock_datetime);
        }
        $this->SetUserId($user->id);
        UserLogService:: LoginSuccess($user->id, $username, $ip, $ua, '登录成功');
        return $this->Success();
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     * @Param(name="sms_code",integer="",lengthMin="6")
     * @Param(name="qq",required="",lengthMin="5")
     * 注册
     */
    public function register()
    {
        // sms
        $username = $this->GetParam('username');
        $password = $this->GetParam('password');
        $sms_code = $this->GetParam('sms_code');
        $qq = $this->GetParam('qq');
        $email = $this->GetParam('email');

        $code = $this->Get('sms_code');
        if (!$code) {
            //没有获取图形验证码就开始发短信,多半是有人搞事情
            return $this->Error('验证码错误!');
        }
        if ($code && $sms_code && $sms_code == $code) {//Verify
            //不为空且验证成功
            $ip = $this->GetClientIP();
            $ua = $this->GetUserAgent();
            $user = UserService::CreateUser($username, $password, $qq, $email, $ip, $ua);
            return $this->Success('注册成功!', $user, '/user/auth');
        }
        return $this->Error('验证码错误!');
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
        return $this->ImageWrite($drawCode->getImageByte());
    }


    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="verifycode",required="",lengthMin="4")
     * @Param(name="action",required="")
     */
    public function send_code()
    {
        $code = $this->Get('img_code');
        if (!$code) {
            //没有获取图形验证码就开始发短信,多半是有人搞事情
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
                    '短信登录', $code
                ],
            ]);
            return $this->Success('发送手机短信成功');
        }
        return $this->Error('请认真填写短信用途');
    }


}