<?php

namespace App\Controller\Api;

use App\Controller\Common\Base;
use App\Service\HomeService;

class News extends Base
{

    /**
     * @Param(name="class_id",integer="")
     * @Param(name="page",integer="")
     * @Param(name="size",integer="")
     *  新闻列表
     */
    public function list()
    {
        $class_id = $this->GetParam('class_id');
        $page = $this->GetParam('page');
        $size = $this->GetParam('size');
        $news = HomeService::GetNewsList($class_id, $page, $size);
        return $this->Success('获取成功', $news);
    }


    /**
     * @Param(name="id",integer="",lengthMin="1")
     * 单条新闻详情
     */
    public function item()
    {
        $id = $this->GetParam('id');
        $news = HomeService::GetNewsItem($id);
        return $this->Success('获取成功', $news);
    }
}