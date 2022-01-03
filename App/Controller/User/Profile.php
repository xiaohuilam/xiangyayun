<?php

namespace App\Controller\User;

use App\Controller\Common\UserLoginBase;
use App\Model\UserLog;
use App\Service\EmailService;
use App\Service\HideService;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\SmsService;
use App\Service\UserLogService;
use App\Service\UserService;
use App\Service\WechatService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Profile extends UserLoginBase
{
    //绑定微信二维码
    public function wx_qrcode_bind()
    {
        $data = WechatService::GetQrcode("QRCODE_USER_BIND");
        $d['image'] = QrcodeService::Qrcode($data['url']);
        //服务端获取EventKey
        $ticket = $data['ticket'];
        $this->Set('ticket', $ticket);
        $user_id = $this->GetUserId();
        RedisService::SetWxBindUserTicket($ticket, $user_id);
        return $this->Success('获取绑定二维码成功', $d);
    }

    //绑定微信状态
    public function wx_qrcode_bind_status()
    {
        //状态
        $ticket = $this->Get('user.ticket');
        $user_id = RedisService::GetWxBindUserTicket($ticket);
        if ($user_id) {
            return $this->Success('微信登录成功!');
        }
        return $this->Error('等待扫码中');
    }

    //更新用户信息

    /**
     * @Param(name="nickname",required="",lengthMin="1",lengthMax="10")
     * @Param(name="qq",integer="",lengthMin="6",lengthMax="11")
     * @Param(name="wechat",required="",lengthMin="3")
     */
    public function update()
    {
        $nickname = $this->GetParam('nickname');
        $qq = $this->GetParam('qq');
        $wechat = $this->GetParam('wechat');
        $user_id = $this->GetUserId();
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        UserService::UpdateUserInfo($user_id, $nickname, $qq, $wechat, $ip, $ua);
        return $this->Success('修改资料成功!');
    }

    /**
     * @Param(name="password",required="")
     * 屏幕解锁
     */
    public function unlock()
    {
        $password = $this->GetParam('password');
        $user = $this->GetUser();
        if ($user->password != md5($password)) {
            return $this->Error('密码错误');
        }
        return $this->Success('解锁成功!');
    }

    //获取待办事项
    public function todo_list()
    {
        $data = [
            'renew' => 0,
            'order' => 0,
            'work_order' => 0
        ];
        return $this->Success('获取信息成功', $data);
    }

    //消费趋势
    public function trend()
    {
        //
        //UserServic
        $user_id = $this->GetUserId();
        $data = UserService::ConsumeTrend($user_id);
        return $this->Success('', $data);
    }

    //获取实名认证信息
    public function auth_info()
    {
        $user_id = $this->GetUserId();
        $auth_info = UserService::FindUserAuthByUserId($user_id);
        $auth_info = $auth_info->toArray();
        $data['cert_bankcard'] = $auth_info['cert_bankcard'];
        $data['cert_mobile'] = HideService::Mobile($auth_info['cert_mobile']);
        $data['cert_name'] = $auth_info['cert_name'];
        $data['cert_number'] = HideService::IdCard($auth_info['cert_number']);
        $data['finish_time'] = $auth_info['finish_time'];
        $data['finish_status'] = $auth_info['finish_status'];
        return $this->Success('', $data);
    }

    //获取第三方账号绑定列表
    public function bind_list()
    {
        $data = [
            'wechat' => 1,
            'qq' => 1,
            'alipay' => 1,
            'weibo' => 1,
            'github' => 1,
            'paypal' => 1
        ];
        return $this->Success('获取信息成功', $data);
    }

    //获取用户信息成功
    public function user_info()
    {
        $user_id = $this->GetUserId();
        $data = [];
        $user = UserService::FindById($user_id);
        $user = $user->toArray();
        $data['auth_status'] = $user['auth_status'];
        $data['status'] = $user['status'];
        $data['avatar'] = $user['avatar'];
        $data['username'] = HideService::Mobile($user['username']);
        $data['nickname'] = $user['nickname'];
        $data['balance'] = $user['balance'];
        $data['create_time'] = $user['create_time'];
        $data['lock_datetime'] = $user['lock_datetime'];
        $data['lock_status'] = $user['lock_status'];
        $data['email'] = HideService::Mobile($user['email']);
        $data['qq'] = $user['qq'];
        $data['user_id'] = $user['id'];
        $data['wechat'] = $user['wechat'];
        $data['wechat_bind_status'] = $user['wx_openid'] ? 1 : 0;
        $data['alipay_bind_status'] = $user['ali_openid'] ? 1 : 0;
        $data['qq_bind_status'] = $user['qq_openid'] ? 1 : 0;
        $data['email_bind_status'] = $user['email'] ? 1 : 0;
        ##过滤掉不需要的字段
        if ($user['auth_status'] && $user['auth_id']) {
            $authTemp = UserService::FindUserAuthByAuthId($user['auth_id']);
            $authTemp = $authTemp->toArray();
            $auth['cert_type'] = $authTemp['cert_type'];
            $auth['finish_time'] = $authTemp['finish_time'];
            $auth['cert_mobile'] = HideService::Mobile($authTemp['cert_mobile']);
            $auth['cert_number'] = HideService::IdCard($authTemp['cert_number']);
            $auth['cert_name'] = HideService::RealName($authTemp['cert_name']);
            //##过滤掉不需要的字段
            $data['auth'] = $auth;
        } else {
            $data['auth'] = null;
        }
        return $this->Success('获取用户信息成功', $data);
    }

    //获取用户状态
    public function status()
    {
        $user_id = $this->GetUserId();
        $user = UserService::FindById($user_id);
        if ($user && $user->status) {
            $data['nickname'] = $user->nickname;
            $data['username'] = $user->username;
            $data['status'] = $user->status;
            return $this->Success('获取用户信息成功', $data);
        }
        //直接注销当前登录
        $this->SetUserId(0);
        return $this->Error('用户被禁用');
    }


    /**
     * @Param(name="email",required="")
     * @Param(name="type",required="",inArray=["password","sms","email","wechat"])
     * 发送验证码
     */
    public function send_change_email_code()
    {

        $user_id = $this->GetUserId();
        $user = UserService::FindById($user_id);
        $email = $this->GetParam('email');
        $type = $this->GetParam('type');
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if ($type == 'password') {
            $password = $this->GetParam('password');
            if ($user->password != md5($password)) {
                //日志BUG
                UserLogService::ChangeEmailError($user->id, $user->username, $ip, $ua, '发送修改邮箱验证码失败,原密码错误');
                return $this->Error('原密码错误');
            }
        } else {
            $code = $this->GetParam('code');
            $save_code = RedisService::GetVerifyCode($user->username);
            if (!$save_code || $save_code != $code) {
                //日志BUG
                UserLogService::ChangeEmailError($user->id, $user->username, $ip, $ua, '发送修改邮箱验证码失败,验证码错误');
                return $this->Error('验证码错误');
            }
        }
        $newuser = UserService::FindByEmail($email);
        if ($newuser) {
            //如果手机号不为空，而且用户也不存在就可以发送验证码!
            return $this->Error('该邮箱已存在,不可绑定!');
        }
        //开始发送验证码
        $verify_code = rand(100000, 999999);
        RedisService::SetVerifyCode($user->username, $verify_code);
        EmailService::SendCode($email, $verify_code);
        return $this->Success('发送验证码成功');
    }

    /**
     * @Param(name="mobile",required="")
     * @Param(name="type",required="",inArray=["password","sms","email","wechat"])
     * 发送验证码
     */
    public function send_change_mobile_code()
    {
        $user_id = $this->GetUserId();
        $user = UserService::FindById($user_id);
        $mobile = $this->GetParam('mobile');
        $type = $this->GetParam('type');
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if ($type == 'password') {
            $password = $this->GetParam('password');
            if ($user->password != md5($password)) {
                //日志BUG
                UserLogService::ChangeMobileError($user->id, $user->username, $ip, $ua, '发送修改手机验证码失败,原密码错误');
                return $this->Error('原密码错误');
            }
        } else {
            $code = $this->GetParam('code');
            $save_code = RedisService::GetVerifyCode($user->username);
            if (!$save_code || $save_code != $code) {
                //日志BUG
                UserLogService::ChangeMobileError($user->id, $user->username, $ip, $ua, '发送修改手机验证码失败,验证码错误');
                return $this->Error('验证码错误');
            }
        }
        $newuser = UserService::FindByUserName($mobile);
        if ($newuser) {
            //如果手机号不为空，而且用户也不存在就可以发送验证码!
            return $this->Error('该用户已存在,不可绑定!');
        }
        //开始发送验证码
        $verify_code = rand(100000, 999999);
        RedisService::SetVerifyCode($mobile, $verify_code);
        SmsService::SendCode($mobile, $verify_code);
        return $this->Success('发送验证码成功');
    }

    /**
     * @Param(name="action",required="")
     * @Param(name="type",required="",inArray=["sms","email","wechat"])
     * 发送验证码
     */
    public function sendcode()
    {
        $user_id = $this->GetUserId();
        $user = UserService::FindById($user_id);
        $type = $this->GetParam('type');
        $action = $this->GetParam('action');
        $actionString = [
            'change_password' => '修改密码'
        ];

        $verify_code = rand(100000, 999999);
        RedisService::SetVerifyCode($user->username, $verify_code);
        switch ($type) {
            case 'wechat':
                WechatService::SendCode($user->id, $actionString[$action], $verify_code, '5分钟', 'http://upy.cn/');
                return $this->Success('发送微信验证码成功!');
            case 'sms':
                SmsService::SendCode($user->username, $verify_code);
                return $this->Success('发送短信验证码成功!');
            case 'email':
                EmailService::SendCode($user->email, $verify_code);
                return $this->Success('发送邮件验证码成功!');
            default:
                return $this->Error('请选择正确的发送方式');
        }
    }

    /**
     * @Param(name="type",required="",inArray=["password","sms","email","wechat"])
     * @Param(name="email",required="",lengthMin="6")
     * @Param(name="email_code",required="",lengthMin="6")
     * 修改邮箱地址
     */
    public function change_email()
    {

        $user_id = $this->GetUserId();
        $user = UserService::FindById($user_id);
        $type = $this->GetParam('type');
        $email = $this->GetParam('email');
        $email_code = $this->GetParam('email_code');
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if ($type == 'password') {
            $password = $this->GetParam('password');
            if ($user->password != md5($password)) {
                UserLogService::ChangeEmailError($user->id, $user->username, $ip, $ua, '改绑邮箱失败,校验密码失败');
                return $this->Error('原密码错误');
            }
        } else {
            $code = $this->GetParam('code');
            $save_code = RedisService::GetVerifyCode($user->username);
            if (!$save_code || $save_code != $code) {
                UserLogService::ChangeEmailError($user->id, $user->username, $ip, $ua, '改绑邮箱失败,验证码错误');
                return $this->Error('验证码错误');
            }
        }
        $save_smscode = RedisService::GetVerifyCode($user->username);
        if (!$save_smscode || $save_smscode != $email_code) {
            UserLogService::ChangeEmailError($user->id, $user->username, $ip, $ua, '改绑邮箱失败,新邮箱短验证码错误');
            return $this->Error('新邮箱验证码错误');
        }
        $user->email = $email;
        $user->update();
        //校验通过后，开始操作
        UserLogService::ChangeEmailSuccess($user->id, $user->username, $ip, $ua, '改绑邮箱成功');
        return $this->Success('改绑邮箱成功');
    }

    /**
     * @Param(name="type",required="",inArray=["password","sms","email","wechat"])
     * @Param(name="mobile",required="",lengthMin="6")
     * @Param(name="smscode",required="",lengthMin="6")
     * 修改绑定手机号
     */
    public function change_mobile()
    {
        $user_id = $this->GetUserId();
        $user = UserService::FindById($user_id);
        $type = $this->GetParam('type');
        $mobile = $this->GetParam('mobile');
        $smscode = $this->GetParam('smscode');
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if ($type == 'password') {
            $password = $this->GetParam('password');
            if ($user->password != md5($password)) {
                UserLogService::ChangeMobileError($user->id, $user->username, $ip, $ua, '改绑手机失败,原密码错误');
                return $this->Error('原密码错误');
            }
        } else {
            $code = $this->GetParam('code');
            $save_code = RedisService::GetVerifyCode($user->username);
            if (!$save_code || $save_code != $code) {
                UserLogService::ChangeMobileError($user->id, $user->username, $ip, $ua, '改绑手机失败,验证码错误');
                return $this->Error('验证码错误');
            }
        }
        $save_smscode = RedisService::GetVerifyCode($mobile);
        if (!$save_smscode || $save_smscode != $smscode) {
            UserLogService::ChangeMobileError($user->id, $user->username, $ip, $ua, '改绑手机失败,新手机短信验证码错误');
            return $this->Error('新手机短信验证码错误');
        }
        $user->username = $mobile;
        $user->update();
        //校验通过后，开始操作
        UserLogService::ChangeMobileSuccess($user->id, $user->username, $ip, $ua, '改绑手机成功');
        return $this->Success('改绑手机成功');
    }

    /**
     * @Param(name="type",required="",inArray=["password","sms","email","wechat"])
     * @Param(name="newpassword",required="",lengthMin="6")
     * 修改密码
     */
    public function change_password()
    {
        $user_id = $this->GetUserId();
        $user = UserService::FindById($user_id);
        $type = $this->GetParam('type');
        $newpassword = $this->GetParam('newpassword');
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        if ($type == 'password') {
            $password = $this->GetParam('password');
            if ($user->password != md5($password)) {
                UserLogService::ChangePasswordError($user->id, $user->username, $ip, $ua, '修改密码失败,原密码错误');
                return $this->Error('原密码错误');
            }
        } else {
            $code = $this->GetParam('code');
            $save_code = RedisService::GetVerifyCode($user->username);
            if (!$save_code || $save_code != $code) {
                UserLogService::ChangePasswordError($user->id, $user->username, $ip, $ua, '修改密码失败,验证码错误');
                return $this->Error('验证码错误');
            }
        }
        //校验通过后，开始操作
        $user->password = md5($newpassword);
        $user->update();
        UserLogService::ChangePasswordSuccess($user->id, $user->username, $ip, $ua, '修改密码成功');
        return $this->Success('修改密码成功');
    }

    public function userlog()
    {
        $user_id = $this->GetUserId();
        $page = $this->GetParam('page') ?? 1;
        $size = $this->GetParam('size') ?? 15;
        $model = UserLogService::SelectUserLog($user_id, $page, $size);

        // 列表数据
        $data['list'] = $model->all(null);
        $result = $model->lastQueryResult();
        // 总条数
        $data['total'] = $result->getTotalCount();
        return $this->Success('获取用户日志成功', $data);
    }
}