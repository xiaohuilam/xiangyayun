<?php

namespace App\Service;

use App\Model\Admin;

class AdminService
{
    public static function FindById($admin_id)
    {
        return Admin::create()->get(['id' => $admin_id]);
    }

    public static function FindByUserName($username)
    {
        return Admin::create()->get(['username' => $username]);
    }

    public static function FindByWxOpenId($wx_openid)
    {
        return Admin::create()->get(['wechat_open_id' => $wx_openid]);
    }

    public static function BindWxOpenId($admin_id, $wx_openid)
    {
        return Admin::create()->update([
            'wechat_open_id' => $wx_openid
        ], ['id' => $admin_id]);
    }

}