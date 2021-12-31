<?php

namespace App\Service;

class RedisService
{

    public static function SetVerifyCode($username, $code)
    {
        self::Set("VERIFY_CODE." . $username, $code, 300);
    }

    public static function GetVerifyCode($username)
    {
        return self::Get("VERIFY_CODE." . $username);
    }

    public static function SetImageCode($username, $code)
    {
        self::Set("IMAGE_CODE." . $username, $code, 300);
    }

    public static function GetImageCode($username)
    {
        return self::Get("IMAGE_CODE." . $username);
    }


    public static function GetUser($user_id)
    {
        return self::Get("USERINFO." . $user_id);
    }

    public static function SetUser($user_id, $user)
    {
        self::Set("USERINFO." . $user_id, $user, 60);
    }

    //登录用户的ticket
    public static function SetWxLoginUserTicket($Ticket, $user_id)
    {
        self::Set("WX_LOGIN_USER." . $Ticket, $user_id, 300);
    }

    public static function GetUcsResourceStatus($ucs_instance_id)
    {
        return self::Get("UCS_RESOURCE_STATUS." . $ucs_instance_id);
    }

    public static function SetUcsResourceStatus($ucs_instance_id, $data)
    {
        return self::Set("UCS_RESOURCE_STATUS." . $ucs_instance_id, $data);
    }

    //登录用户的ticket
    public static function GetWxLoginUserTicket($Ticket)
    {
        return self::Get("WX_LOGIN_USER." . $Ticket);
    }

    //绑定用户的ticket
    public static function SetWxBindUserTicket($Ticket, $user_id)
    {
        self::Set("WX_BIND_USER." . $Ticket, $user_id, 300);
    }

    //绑定用户的ticket
    public static function GetWxBindUserTicket($Ticket)
    {
        return self::Get("WX_BIND_USER." . $Ticket);
    }


    //管理员登录的ticket
    public static function SetWxLoginAdminTicket($Ticket, $user_id)
    {
        self::Set("WX_LOGIN_ADMIN." . $Ticket, $user_id, 300);
    }

    //管理员登录的ticket
    public static function GetWxLoginAdminTicket($Ticket)
    {
        return self::Get("WX_LOGIN_ADMIN." . $Ticket);
    }

    //管理员绑定的ticket
    public static function SetWxBindAdminTicket($Ticket, $user_id)
    {
        self::Set("WX_BIND_ADMIN." . $Ticket, $user_id, 300);
    }

    //管理员绑定的ticket
    public static function GetWxBindAdminTicket($Ticket)
    {
        return self::Get("WX_BIND_ADMIN." . $Ticket);
    }

    //管理员的权限列表
    public static function SetAdminAuthGroup($admin_id, $auth_ids)
    {
        return self::Set('AdminAuthGroup.' . $admin_id, $auth_ids, 300);
    }

    //管理员的权限列表
    public static function GetAdminAuthGroup($admin_id)
    {
        return self::Get('AdminAuthGroup.' . $admin_id);
    }

    //系统路由列表
    public static function GetAdminAuth()
    {
        return self::Get('AdminAuth');
    }

    //系统路由列表
    public static function SetAdminAuth($admin_auth)
    {
        return self::Set('AdminAuth', $admin_auth, 300);
    }

    public static function Get($key)
    {
        $redis = \EasySwoole\RedisPool\RedisPool::defer();
        return $redis->get($key);
    }

    public static function Set($key, $value, $timeout = 0)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($timeout, $key, $value) {
            $data = $redis->set($key, $value, $timeout);
            info("缓存数据." . json_encode($data));
        });
    }

    public static function Del($key)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($key) {
            $redis->del($key);
            info("删除数据." . $key);
        });
    }
}