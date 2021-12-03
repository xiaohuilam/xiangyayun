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

}