<?php

namespace App\Service;

use App\Model\User;
use App\Model\UserConsume;

class UserService
{

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