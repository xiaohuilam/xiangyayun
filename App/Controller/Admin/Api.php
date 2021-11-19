<?php

namespace App\Controller\Admin;

use App\Model\Admin;
use App\Service\AdminService;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\WechatService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use App\Controller\Common\Base;

class Api extends Base
{
    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     * 密码登录
     */
    public function index()
    {
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
        $admin = AdminService::FindByUserName($username);
        if (!$admin) {
            return $this->Error('该管理员不存在');
        }
        if (!$admin->status) {
            return $this->Error('该管理员被禁用');
        }
        if ($admin->password != $password) {
            return $this->Error('账号或密码错误');
        }
        $this->SetAdminId($admin->id);
        return $this->Success('登录成功!');
    }

    /**
     * @Param(name="username",required="",lengthMin="11")
     * @Param(name="password",required="",lengthMin="6")
     *
     */
    public function verifycode_login()
    {


    }

    //二维码登录
    public function wx_qrcode_login()
    {
        $data = WechatService::GetQrcode("QRCODE_LOGIN_ADMIN");
        $byte = QrcodeService::Qrcode($data['url']);
        //服务端获取EventKey
        $ticket = $data['ticket'];
        $this->Set('admin.ticket', $ticket);
        RedisService::SetWxLoginAdminTicket($ticket, 0);
        return $this->ImageWrite($byte);
    }

    public function wx_qrcode_status()
    {
        //状态
        $ticket = $this->Get('admin.ticket');
        var_dump($ticket);
        $user_id = RedisService::GetWxLoginAdminTicket($ticket);
        var_dump($user_id);
        if ($user_id) {
            return $this->Success('微信登录成功!');
        }
        return $this->Error('等待扫码中');
    }

}