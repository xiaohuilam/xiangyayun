<?php

namespace App\Controller\Admin;

use \App\Controller\Common\AdminAuthBase;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Dashboard extends AdminAuthBase
{
    public function index()
    {
        return $this->Success();
    }
}