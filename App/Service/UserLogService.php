<?php

namespace App\Service;

use App\Model\UserLog;
use App\Model\WechatPushLog;

class UserLogService
{

    public static function Save($params)
    {
        $params['create_time'] = date('Y-m-d H:i:s');
        return UserLog::create($params)->save();
    }

    public static function RegisterLog($username, $user_id, $ip, $ua, $new_params)
    {
        return self::Save([
            'username' => $username,
            'user_id' => $user_id,
            'ip' => $ip,
            'ua' => $ua,
            'status' => 0,
            'message' => '创建用户成功',
            'action' => 'update_info',
            'new_params' => json_encode($new_params),
        ]);
    }

    public static function UpdateInfoLog($user_id, $username, $ip, $ua, $old_params, $new_params)
    {
        return self::Save([
            'username' => $username,
            'user_id' => $user_id,
            'ip' => $ip,
            'ua' => $ua,
            'status' => 0,
            'message' => '修改资料成功',
            'action' => 'update_info',
            'old_params' => json_encode($old_params),
            'new_params' => json_encode($new_params),
        ]);
    }

    public static function LoginError($user_id, $username, $ip, $ua, $msg)
    {
        return self::Save([
            'username' => $username,
            'user_id' => $user_id,
            'ip' => $ip,
            'ua' => $ua,
            'status' => 0,
            'message' => $msg,
            'action' => 'login',
        ]);
    }


    public static function FindLoginByUserName($username)
    {
        $user_log = UserLog::create();
        $user_counts = $user_log
            ->where('status', 0)
            ->where('action', 'login')
            ->where('create_time', date('Y-m-d H:i:s', strtotime('- 10 minutes')), '>')
            ->where('username', $username, '=')
            ->count();
        if ($user_counts >= 5) {
            return true;
        }
        return false;
    }

    public static function FindLoginByIp($ip)
    {
        $count = UserLog::create();
        $ip_counts = $count->where('status', 0)
            ->where('action', 'login')
            ->where('create_time', date('Y-m-d H:i:s', strtotime('- 10 minutes')), '>')
            ->where('ip', $ip, '=')->count();
        if ($ip_counts >= 5) {
            return true;
        }
        return false;
    }

    public static function LoginSuccess($user_id, $username, $ip, $ua, $msg)
    {
        return UserLog::create([
            'username' => $username,
            'user_id' => $user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'ip' => $ip,
            'ua' => $ua,
            'status' => 1,
            'message' => $msg,
            'action' => 'login'
        ])->save();
    }

    public static function WechatPushLogSuccess($user_id, $open_id, $params)
    {
        return WechatPushLog::create([
            'create_time' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'open_id' => $open_id,
            'status' => 1,
            'params' => json_encode($params),
        ])->save();
    }

    public static function WechatPushLogError($user_id, $open_id, $params)
    {
        return WechatPushLog::create([
            'create_time' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'open_id' => $open_id,
            'status' => 0,
            'params' => json_encode($params),
        ])->save();
    }
}