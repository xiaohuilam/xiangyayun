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
     * @Param(name="wechat",required="",lengthMin="3")
     */
    public function update()
    {
        $nickname = $this->GetParam('nickname');
        $email = $this->GetParam('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->Error('请输入正确的邮件地址');
        }
        $qq = $this->GetParam('qq');
        $wechat = $this->GetParam('wechat');
        $user_id = $this->GetUserId();
        $ip = $this->GetClientIP();
        $ua = $this->GetUserAgent();
        UserService::UpdateUserInfo($user_id, $nickname, $email, $qq,$wechat, $ip, $ua);
        return $this->Success('修改资料成功!');
    }

    //获取待办事项
    public function todo_list()
    {
        $data = [
            'renew' => 1,
            'order' => 1,
            'work_order' => 1
        ];
        return $this->Success('获取信息成功', $data);
    }

    //获取实名认证信息
    public function auth_info()
    {
        $user_id = $this->GetUserId();
        $data = UserService::FindUserAuthByUserId($user_id);
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
        //##过滤掉不需要的字段
//        $auth = UserService::FindUserAuth($user->auth_id);;
//        //##过滤掉不需要的字段
//        $data['auth'] = $auth;
        $user = $user->toArray();
        return $this->Success('获取用户信息成功', $user);
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