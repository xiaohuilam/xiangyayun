<?php

namespace App\Controller\User;

use App\Controller\Common\Base;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\UserLogService;
use App\Service\UserService;
use App\Service\WechatService;
use EasySwoole\Redis\Redis;
use EasySwoole\VerifyCode\Conf;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Api extends Base
{
    public function alipay_login()
    {
        $config = new \EasySwoole\OAuth\AliPay\Config();
        $config->setState('easyswoole');
        $config->setAppId('appid');
        $config->setRedirectUri('redirect_uri');

        // 使用沙箱环境测试开发的时候 把OAuth的源码文件里面的 API_DOMAIN 和 AUTH_DOMAIN 进行修改
        $oauth = new \EasySwoole\OAuth\AliPay\OAuth($config);
        $url = $oauth->getAuthUrl();
        return $this->response()->redirect($url);
    }

    public function alipay_callback()
    {
        $params = $this->request()->getQueryParams();

        $config = new \EasySwoole\OAuth\AliPay\Config();
        $config->setAppId('appid');
//        $config->setAppPrivateKey('私钥');
        $config->setAppPrivateKeyFile('私钥文件'); // 私钥文件(非远程) 此方法与上个方法二选一

        $oauth = new \EasySwoole\OAuth\AliPay\OAuth($config);
        $accessToken = $oauth->getAccessToken('easyswoole', $params['state'], $params['auth_code']);
        $refreshToken = $oauth->getAccessTokenResult()['alipay_system_oauth_token_response']['refresh_token'];

        $userInfo = $oauth->getUserInfo($accessToken);

        if (!$oauth->validateAccessToken($accessToken)) echo 'access_token 验证失败！' . PHP_EOL;

        if (!$oauth->refreshToken($refreshToken)) echo 'access_token 续期失败！' . PHP_EOL;
    }

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
        $user_id = RedisService::GetWxLoginUserTicket($ticket);
        if ($user_id == 0) {
            return $this->Error('等待扫码中');
        } else if ($user_id > 0) {
            //登录成功后需要销毁掉
            RedisService::DelWxLoginUserTicket($ticket);
            $this->SetUserId($user_id);
            return $this->Success('微信登录成功!');
        } else if ($user_id == -1) {
            //登录失败后需要销毁掉
            RedisService::DelWxLoginUserTicket($ticket);
            return $this->Error('您的微信未绑定账号!请选择其他方式登录!');
        }
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
        //从Redis里面拿出来相关的短信验证码
        $sms_code = RedisService::GetVerifyCode($username);
        if ($verifycode != $sms_code) {
            return $this->Error('验证码错误!');
        }
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if (UserLogService::FindLoginByIp($ip) > 5) {
            return $this->Error('该IP错误次数过多,请稍后再试试!');
        }
        if (UserLogService::FindLoginByUserName($username) > 5) {
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
        UserLogService::LoginSuccess($user->id, $username, $ip, $ua, '登录成功');
        return $this->Success();
    }

    //判断是否需要验证码

    /**
     * @Param(name="username",required="",lengthMin="11",lengthMax="11")
     * 判断是否需要验证码
     */
    public function need_verifycode()
    {
        $ip = $this->GetClientIP();
        $username = $this->GetParam('username');
        $data['status'] = false;
        if (UserLogService::FindLoginByIp($ip) > 2) {
            $data['status'] = true;
            return $this->Success('错误超出2次,需要验证码!', $data);
        }
        if (UserLogService::FindLoginByUserName($username) > 2) {
            $data['status'] = true;
            return $this->Success('错误超出2次,需要验证码!', $data);
        }
        return $this->Success('不需要验证码!', $data);
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
        if (UserLogService::FindLoginByIp($ip) > 2 || UserLogService::FindLoginByUserName($username) > 2) {
            //要求验证码
            $code = $this->GetParam('code');
            $img_code = RedisService::GetImageCode($username);
            if ($code != $img_code) {
                return $this->Error('图片验证码错误！');
            }
        }
        //已经使用过的验证码就得删除掉
        RedisService::DelImageCode($username);

        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if (UserLogService::FindLoginByIp($ip) > 5) {
            return $this->Error('该IP错误次数过多,请稍后再试试!');
        }
        if (UserLogService::FindLoginByUserName($username) > 5) {
            return $this->Error('该用户错误次数过多,请稍后再试试!');
        }
        $user = UserService::FindByUserName($username);
        if (!$user) {
            UserLogService:: LoginError(0, $username, $ip, $ua, '用户不存在');
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
        UserLogService::LoginSuccess($user->id, $username, $ip, $ua, '登录成功');
        $this->SetData();
        $data['token'] = $this->token;
        return $this->Success('登录成功', $data);
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     * @Param(name="verifycode",integer="",lengthMin="6")
     * 注册
     */
    public function register()
    {
        // sms
        $username = $this->GetParam('username');
        $password = $this->GetParam('password');
        $verifycode = $this->GetParam('verifycode');
        $qq = $this->GetParam('qq');

        $code = RedisService::GetVerifyCode($username);
        if (!$code) {
            //没有获取图形验证码就开始发短信,多半是有人搞事情
            return $this->Error('验证码错误!');
        }

        if ($code && $verifycode && $verifycode == $code) {//Verify
            //销毁验证码
            RedisService::DelVerifyCode($username);
            //不为空且验证成功
            $ip = $this->GetClientIP();
            $ua = $this->GetUserAgent();
            $user = UserService::CreateUser($username, $password, $ip, $ua);
            return $this->Success('注册成功!', $user, '/user/auth');
        }
        return $this->Error('验证码错误!');
    }


    /**
     * @Param(name="username",required="",lengthMin="11",lengthMax="11")
     *获取验证码需要传入用户名
     */
    public function verifycode()
    {
        $username = $this->GetParam('username');
        $config = new Conf();
        $config
            ->setFontColor('#fff')
            ->setImageWidth(100)
            ->setImageHeight(40)
            ->setFontSize(14)
            ->setCharset('1234567890')
            ->setLength(4);

        $VCode = new \EasySwoole\VerifyCode\VerifyCode($config);
        $drawCode = $VCode->DrawCode();
        RedisService::SetImageCode($username, $drawCode->getImageCode());
        $data['image'] = $drawCode->getImageBase64();
        return $this->Success('获取验证码成功!', $data);
    }

    public function logout()
    {
        return $this->Success('退出登录成功!');
    }


    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="verifycode",required="",lengthMin="4")
     * @Param(name="action",required="")
     */
    public function send_code()
    {
        $username = $this->GetParam('username');
        //获取这个手机号的图片验证码
        $code = RedisService::GetImageCode($username);
        $verifycode = $this->GetParam('verifycode');
        $action = $this->GetParam('action');
        if (!$code || $verifycode != $code) {
            return $this->Error('验证码错误!');
        }

        $sms_code = rand(100000, 999999);
        if ($action == 'register') {
            $user = UserService::FindByUserName($username);
            if ($user) {
                RedisService::DelImageCode($username);
                return $this->Error('用户已注册!请直接登录', null, '/login');
            }

            RedisService::SetVerifyCode($username, $sms_code);
            //判断该IP或该用户今天收到了多少短信


            SmsJob([
                'mobile' => $username,
                'action' => 'action_code',
                'params' => [
                    '注册用户', $sms_code
                ],
            ]);
            return $this->Success('发送成功');
        } else if ($action == 'login') {
            $user = UserService::FindByUserName($username);

            RedisService::SetVerifyCode($username, $sms_code);

            //发送微信
            if ($user && $user->wx_openid) {
                WechatService::SendCode($user->id, '登录会员中心', $sms_code, '5分钟', 'http://upy.cn/');
                return $this->Success('发送微信消息成功!');
            }
            //发送短信
            SmsJob([
                'mobile' => $username,
                'action' => 'action_code',
                'params' => [
                    '短信登录', $sms_code
                ],
            ]);
            return $this->Success('发送手机短信成功');
        }
        return $this->Error('请认真填写短信用途');
    }


}