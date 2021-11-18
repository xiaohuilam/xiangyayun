<?php

namespace App\Service;

use App\Model\Admin;

class AdminService
{
    public static function FindByUserName($username)
    {
        return Admin::create()->get(['username' => $username]);
    }

}