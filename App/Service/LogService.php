<?php

namespace App\Service;

use App\Model\UserLog;

class LogService
{

    public static function LoginError($user_id, $username, $ip, $ua, $msg)
    {
        UserLog::create([
            'username' => $username,
            'user_id' => $user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'ip' => $ip,
            'ua' => $ua,
            'status' => 0,
            'message' => $msg,
        ])->save(0);
    }


    public static function FindLoginByUserName($username)
    {
        $count = UserLog::create();
        $user_counts = $count->where('status', 0)
            ->where('create_time', date('Y-m-d H:i:s', strtotime('- 10 minutes')), '>')
            ->where('username', $username, '=')->count();
        if ($user_counts >= 5) {
            return true;
        }
        return false;
    }

    public static function FindLoginByIp($ip)
    {
        $count = UserLog::create();
        $ip_counts = $count->where('status', 0)
            ->where('create_time', date('Y-m-d H:i:s', strtotime('- 10 minutes')), '>')
            ->where('ip', $ip, '=')->count();
        if ($ip_counts >= 5) {
            return true;
        }
        return false;
    }

    public static function LoginSuccess($user_id, $username, $ip, $ua, $msg)
    {
        UserLog::create([
            'username' => $username,
            'user_id' => $user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'ip' => $ip,
            'ua' => $ua,
            'status' => 1,
            'message' => $msg
        ])->save(0);
    }
}