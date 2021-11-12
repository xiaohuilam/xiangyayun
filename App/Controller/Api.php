<?php

namespace App\Controller;

use App\Controller\Common\Base;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class Api extends Base
{

    /**
     * @Param(name="mobile",required="")
     * @Param(name="password",required="")
     */
    public function login()
    {
        helloEasySwoole();
    }
}