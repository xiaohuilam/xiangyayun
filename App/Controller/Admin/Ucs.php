<?php

namespace App\Controller\Admin;

use App\Controller\Common\AdminAuthBase;
use App\Service\UcsService;

class Ucs extends AdminAuthBase
{
    //返回列表
    /**
     * @Param(name="user_id",integer="")
     * @Param(name="page",integer="")
     * @Param(name="size",integer="")
     * 返回实例详情
     */
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
        $data = UcsService:: SelectListPage($where, $page, $size);
        return $this->Success('获取实例列表成功!', $data);
    }

    /**
     * @Param(name="instance_id",integer="")
     * 返回实例详情
     */
    public function instance()
    {
        $data = [];
        $instance_id = $this->GetParam('instance_id');
        //获取实例详情
        $data['instance'] = UcsService::FindUcsInstanceById($instance_id);
        //获取实例防火墙
        $data['firewall'] = UcsService::FindUcsFirewallByUcsInstanceId($instance_id);
        return $this->Success('获取实例详情成功', $data);
    }

    public function index()
    {
        return $this->Success();
    }
}