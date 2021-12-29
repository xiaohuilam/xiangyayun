<?php

namespace App\Service;

use App\Model\Message;

class MessageService
{
    public static function SelectByUserId($user_id)
    {
        return Message::create()->where('user_id', $user_id)->all();
    }

    public static function FindNewMessageByUserId($user_id)
    {
        return Message::create()->where('user_id', $user_id)
            ->where('status', 0)->order('id')
            ->get();
    }

    public static function FindById($id)
    {
        return Message::create()->where('id', $id)->get();
    }

    public static function ViewById($id)
    {
        $message = Message::create()->where('id', $id)->get();
        $message->update([
            'view_time' => date('Y-m-d H:i:s'),
            'status' => 1
        ], ['id' => $id]);
        return $message;
    }

}