<?php

namespace App\Controller\User;

use App\Controller\Common\UserLoginBase;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\UserService;
use App\Service\WechatService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Profile extends UserLoginBase
{
    //绑定微信二维码
    public function wx_qrcode_bind()
    {
        $data = WechatService::GetQrcode("QRCODE_USER_BIND");
        $byte = QrcodeService::Qrcode($data['url']);
        //服务端获取EventKey
        $ticket = $data['ticket'];
        $this->Set('ticket', $ticket);
        $user_id = $this->GetUserId();
        RedisService::SetWxBindUserTicket($ticket, $user_id);
        return $this->ImageWrite($byte);
    }

    //更新用户信息

    /**
     * @Param(name="nickname",required="",lengthMin="1",lengthMax="10")
     * @Param(name="email",required="",lengthMin="4")
     * @Param(name="qq",integer="",lengthMin="6",lengthMax="11")
     */
    public function update()
    {
        $nickname = $this->GetParam('nickname');
        $email = $this->GetParam('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->Error('请输入正确的邮件地址');
        }
        $qq = $this->GetParam('qq');
        $user_id = $this->GetUserId();
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        UserService::UpdateUserInfo($user_id, $nickname, $email, $qq, $ip, $ua);
    }

    //获取用户信息成功
    public function info()
    {
        $user_id = $this->GetUserId();
        $data = [];
        $user = UserService::FindById($user_id);
        //##过滤掉不需要的字段
        $data['user'] = $user;
        $auth = UserService::FindUserAuth($user->auth_id);;
        //##过滤掉不需要的字段
        $data['auth'] = $auth;
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
}