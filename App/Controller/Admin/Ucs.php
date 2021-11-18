<?php

namespace App\Controller\Admin;

use App\Controller\Common\AdminAuthBase;
use App\Service\UcsService;

class Ucs extends AdminAuthBase
{
    //返回列表
    public function list()
    {
        $where = [];
        $page = $this->GetParam('page') ?? 1;
        $size = $this->GetParam('size') ?? 10;
        $user_id = $this->GetParam('user_id') ?? 0;
        if ($user_id) {
            $where[] = ["user_id" => $user_id];
        }
        $ucs_region_id = $this->GetParam('ucs_region_id');
        if ($user_id) {
            $where[] = ["ucs_region_id" => $ucs_region_id];
        }
        UcsService:: SelectListPage($where, $page, $size);
    }

    //返回实例详情
    public function instance()
    {

    }

    public function index()
    {
        return $this->Success();
    }
}