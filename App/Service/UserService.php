<?php

namespace App\Service;

use App\Model\User;

class UserService
{

    public static function FindById($user_id)
    {
        return User::create()->get([
            'id' => $user_id
        ]);
    }

    public static function FindByUserName($username)
    {
        return User::create()->get([
            'username' => $username
        ]);
    }
}