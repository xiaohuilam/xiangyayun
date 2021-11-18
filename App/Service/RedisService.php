<?php

namespace App\Service;

class RedisService
{
    public static function SetWxLoginTicket($Ticket, $user_id)
    {
        self::Set("LOGIN" . $Ticket, $user_id);
    }

    public static function GetWxLoginTicket($Ticket)
    {
        return self::Get("LOGIN" . $Ticket);
    }

    public static function SetWxBindTicket($Ticket, $user_id)
    {
        self::Set("WX_BIND" . $Ticket, $user_id);
    }

    public static function GetWxBindTicket($Ticket)
    {
        return self::Get("WX_BIND" . $Ticket);
    }


    public static function SetWxLoginAdminTicket($Ticket, $user_id)
    {
        self::Set("LOGIN_ADMIN" . $Ticket, $user_id);
    }

    public static function GetWxLoginAdminTicket($Ticket)
    {
        return self::Get("LOGIN_ADMIN" . $Ticket);
    }

    public static function SetWxBindAdminTicket($Ticket, $user_id)
    {
        self::Set("WX_BIND_ADMIN" . $Ticket, $user_id);
    }

    public static function GetWxBindAdminTicket($Ticket)
    {
        return self::Get("WX_BIND_ADMIN" . $Ticket);
    }

    public static function SetAdminAuthGroup($admin_id, $auth_ids)
    {
        return self::Set('AdminAuthGroup.' . $admin_id, $auth_ids);
    }

    public static function GetAdminAuthGroup($admin_id)
    {
        return self::Get('AdminAuthGroup.' . $admin_id);
    }

    public static function GetAdminAuth()
    {
        return self::Get('AdminAuth');
    }

    public static function SetAdminAuth($admin_auth)
    {
        return self::Set('AdminAuth', $admin_auth);
    }

    public static function Get($key)
    {
        $redis = \EasySwoole\RedisPool\RedisPool::defer();
        return $redis->get($key);
    }

    public static function Set($key, $value)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($key, $value) {
            $data = $redis->set($key, $value);
            info("缓存数据." . json_encode($data));
        });
    }

}