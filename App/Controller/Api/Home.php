<?php

namespace App\Controller\Api;

use App\Controller\Common\Base;
use App\Model\Banner;
use App\Service\HomeService;

use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Home extends Base
{

    public function banner()
    {
        $banner = HomeService::GetBanner();
        return $this->Success('获取成功', $banner);
    }

    /**
     * @Param(name="class_id",integer="")
     * @Param(name="page",integer="")
     * @Param(name="size",integer="")
     * 帮助列表
     */
    public function help_list()
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
    public function help_item()
    {
        $id = $this->GetParam('id');
        $help = HomeService::GetHelpItem($id);
        return $this->Success('获取成功', $help);
    }

    /**
     * @Param(name="class_id",integer="")
     * @Param(name="page",integer="")
     * @Param(name="size",integer="")
     *  新闻列表
     */
    public function news_list()
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
    public function news_item()
    {
        $id = $this->GetParam('id');
        $news = HomeService::GetNewsItem($id);
        return $this->Success('获取成功', $news);
    }
}