<?php

namespace App\Controller\Common;

class AdminLoginBase extends Base
{
    protected function GetAdminId()
    {
        return $this->Get('admin_id');
    }

    protected function onRequest(?string $action): ?bool
    {
        $flag = parent::onRequest($action); // TODO: Change the autogenerated stub
        if ($flag) {
            if ($this->GetAdminId()) {
                return true;
            }
        }
        $this->Error('请登录管理员账号', null, '/admin/login');
        return false;
    }
}