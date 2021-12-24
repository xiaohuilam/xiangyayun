<?php

namespace App\Service;

use App\Model\User;
use App\Model\UserAuth;
use App\Model\UserFinance;
use App\Model\UserLog;
use EasySwoole\Mysqli\QueryBuilder;
use mysql_xdevapi\SqlStatement;

class UserService
{
    public static function SuccessUserAuth($user_auth)
    {
        $user = User::create()->get(['id' => $user_auth->user_id]);
        if (!$user) {
            return false;
        }
        $old_params = UserAuth::create()->get(['id' => $user->auth_id]);
        $user->auth_id = $user_auth->auth_id;
        $user->auth_status = 1;
        UserLogService::UpdateAuthLog($user_auth->user_id, $user->username, $user_auth->create_ip, $user_auth->create_ua, $old_params, $user_auth);
        return $user->update();
    }

    //创建用户并保存日志
    public static function CreateUser($username, $password, $qq, $email, $ip, $ua)
    {
        $user = User::create([
            'username' => $username,
            'password' => md5($password),
            'create_time' => date('Y-m-d H:i:s'),
            'qq' => $qq,
            'email' => $email ?? $qq . "@qq.com",
            'status' => 1,
            'nickname' => '手机用户' . substr($username, -4)
        ]);
        $user->save();
        $new_params = [
            'nickname' => $user->nickname,
            'email' => $email,
            'qq' => $qq,
        ];
        UserLogService::RegisterLog($username, $user->id, $ip, $ua, $new_params);
        return $user;
    }

    public static function FindUserAuthByAuthId($user_auth_id)
    {
        return UserAuth::create()->get([
            'id' => $user_auth_id
        ]);
    }

    public static function FindUserAuthByUserId($user_id)
    {
        return UserAuth::create()->get([
            'user_id' => $user_id,
            'finish_status' => 1
        ]);
    }

    //通过ID查询用户
    public static function FindById($user_id)
    {
        return User::create()->get([
            'id' => $user_id
        ]);
    }

    //更新用户资料
    public static function UpdateUserInfo($user_id, $nickname, $email, $qq, $wechat, $ip, $ua)
    {
        $user = self::FindById($user_id);

        $old_params = [
            'email' => $user->email,
            'qq' => $user->qq,
            'nickname' => $user->nickname,
            'wechat' => $user->wechat,
        ];
        $new_params = [
            'nickname' => $nickname,
            'email' => $email,
            'qq' => $qq,
            'wechat' => $wechat
        ];
        //记录日志
        UserLogService::UpdateInfoLog($user_id, $user->username, $ip, $ua, $old_params, $new_params);
        //记录日志,然后修改
        return User::create()->update($new_params, ['id' => $user_id]);
    }

    //通过用户名查询用户
    public static function FindByUserName($username)
    {
        return User::create()->get([
            'username' => $username
        ]);
    }

    public static function FindByWxOpenId($wx_openid)
    {
        return User::create()->get([
            'wx_openid' => $wx_openid
        ]);
    }

    //微信绑定用户
    public static function BindWxOpenId($user_id, $wx_openid)
    {
        $user = self::FindById($user_id);
        $user->wx_openid = $wx_openid;
        $user->update();
        return $user;
    }

    //用户充值
    public static function Recharge($user_id, $amount, $action)
    {
        $user = User::create()->get(['id' => $user_id]);
        if ((!$user) || $amount < 0) {
            return false;
        }

        $user_consume = UserFinance::create([
            'user_id' => $user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'action' => $action,
            'amount' => $amount,
            'balance' => $user->balance,
        ]);
        $user_consume->save();
        if ($user_consume->id) {
            if ($user->update([
                'balance' => QueryBuilder::inc($amount)
            ])) {
                //消费成功
                return true;
            }
        }
    }

    //消费金额
    public static function Consume($user_id, $amount, $action, $type, $instance_id)
    {
        $user = User::create()->get(['id' => $user_id]);
        if ((!$user) || $amount < 1 || $amount > $user->balance) {
            return false;
        }
        $user_consume = UserFinance::create([
            'user_id' => $user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'action' => $action,
            'amount' => $amount,
            'type' => $type,
            'instance_id' => $instance_id,
            'balance' => $user->balance,
        ]);
        $user_consume->save();
        if ($user_consume->id) {
            if ($user->update([
                'balance' => QueryBuilder::dec($amount)
            ])) {
                //消费成功
            }
        }
        return $user_consume;
    }
}