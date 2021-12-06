<?php

namespace App\Controller\Api;

use App\Controller\Common\Base;
use App\Service\HomeService;

class Help extends Base
{
    /**
     * @Param(name="class_id",integer="")
     * @Param(name="page",integer="")
     * @Param(name="size",integer="")
     * 帮助列表
     */
    public function list()
    {
        $class_id = $this->GetParam('class_id');
        $page = $this->GetParam('page');
        $size = $this->GetParam('size');
        $list = HomeService::GetHelpList($class_id, $page, $size);
        return $this->Success('获取成功', $list);
    }

    /**
     * @Param(name="instance_id",integer="")
     * 单条帮助文档
     */
    public function item()
    {
        $id = $this->GetParam('id');
        $help = HomeService::GetHelpItem($id);
        return $this->Success('获取成功', $help);
    }
}