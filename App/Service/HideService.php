<?php

namespace App\Service;

class HideService
{
    public static function Mobile($mobile)
    {
        if (!$mobile) {
            return null;
        }
        return substr($mobile, 0, 3) . '****' . substr($mobile, 7);
    }

    public static function Email($email)
    {
        if (!$email) {
            return null;
        }
        return Desensitize($email, 2, 2);
    }

    public static function RealName($name)
    {
        if (!$name) {
            return null;
        }
        return Desensitize($name, 1, 1);
    }

    public static function IdCard($id_card)
    {
        if (!$id_card) {
            return null;
        }
        return substr($id_card, 0, 6) . '********' . substr($id_card, 14);
    }

}