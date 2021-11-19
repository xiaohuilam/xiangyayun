<?php

namespace App\Controller\Admin;

use App\Controller\Common\AdminLoginBase;
use App\Service\QrcodeService;
use App\Service\RedisService;
use App\Service\WechatService;

class Profile extends AdminLoginBase
{
    //更新自己的资料
    public function update()
    {

    }

    //绑定微信二维码
    public function wx_qrcode_bind()
    {
        $data = WechatService::GetQrcode("QRCODE_ADMIN_BIND");
        $byte = QrcodeService::Qrcode($data['url']);
        //服务端获取EventKey
        $ticket = $data['ticket'];
        $this->Set('admin.ticket', $ticket);
        $user_id = $this->GetUserId();
        RedisService::SetWxBindAdminTicket($ticket, $user_id);
        return $this->ImageWrite($byte);
    }
}