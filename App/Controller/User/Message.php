<?php

namespace App\Controller\User;

use App\Controller\Common\UserLoginBase;
use App\Service\MessageService;

class Message extends UserLoginBase
{
    public function not_view()
    {
        $user_id = $this->GetUserId();
        $d['list'] = MessageService::SelectByUserId($user_id);
        $d['total'] = count($d['list']);
        return $this->Success('', $d);
    }

    public function new_message()
    {
        $user_id = $this->GetUserId();
        $data = MessageService::FindNewMessageByUserId($user_id);
        return $this->Success('', $data);
    }

    public function view()
    {
        $id = $this->GetParam('id');
        $message = MessageService::ViewById($id);
        return $this->Success('', $message);
    }

}