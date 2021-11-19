<?php

namespace App\Controller\User;

use App\Controller\Common\UserLoginBase;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\UserService;
use App\Service\WechatService;

class Profile extends UserLoginBase
{

    public function wx_qrcode_bind()
    {
        $data = WechatService::GetQrcode("QRCODE_BIND");
        $byte = QrcodeService::Qrcode($data['url']);
        //服务端获取EventKey
        $ticket = $data['ticket'];
        $this->Set('ticket', $ticket);
        $user_id = $this->GetUserId();
        RedisService::SetWxBindTicket($ticket, $user_id);
        return $this->ImageWrite($byte);
    }

    public function info()
    {
        $user_id = $this->GetUserId();
        if ($user_id) {
            $user = UserService::FindById($user_id);
            if ($user) {
                $data['nickname'] = $user->nickname;
                $data['username'] = $user->username;
                return $this->Success('获取用户信息成功', $data);
            }
        }
        return $this->Error('未登录');
    }
}