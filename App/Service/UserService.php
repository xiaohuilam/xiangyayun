<?php

namespace App\Service;

use App\Model\User;
use App\Model\UserConsume;
use EasySwoole\Mysqli\QueryBuilder;
use mysql_xdevapi\SqlStatement;

class UserService
{
    public static function SuccessUserAuth($user_id)
    {
        $user = User::create()->get(['id' => $user_id]);
        if (!$user) {
            return false;
        }
        $user->auth_status = 1;
        return $user->update();
    }

    public static function CreateUser($username, $password, $qq, $email)
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
        return $user;
    }

    //通过ID查询用户
    public static function FindById($user_id)
    {
        return User::create()->get([
            'id' => $user_id
        ]);
    }

    //通过用户名查询用户
    public static function FindByUserName($username)
    {
        return User::create()->get([
            'username' => $username
        ]);
    }

    //用户充值
    public static function Recharge($user_id, $amount, $action)
    {
        $user = User::create()->get(['id' => $user_id]);
        if ((!$user) || $amount < 0) {
            return false;
        }

        $user_consume = UserConsume::create([
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
    public static function Consume($user_id, $amount, $action)
    {
        $user = User::create()->get(['id' => $user_id]);
        if ((!$user) || $amount < 1 || $amount > $user->balance) {
            return false;
        }
        $user_consume = UserConsume::create([
            'user_id' => $user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'action' => $action,
            'amount' => $amount,
            'balance' => $user->balance,
        ]);
        $user_consume->save();
        if ($user_consume->id) {
            if ($user->update([
                'balance' => QueryBuilder::dec($amount)
            ])) {
                //消费成功
                return true;
            }
        }
        return false;
    }
}