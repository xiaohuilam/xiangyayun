<?php

namespace App\Controller\Admin;

use App\Controller\Common\AdminAuthBase;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class UcsProduce extends AdminAuthBase
{
    /**
     * @Param(name="ucs_region_id",integer="")
     * @Param(name="ip_range",required="")
     * @Param(name="netmask",required="")
     * @Param(name="gateway",required="")
     * 添加IP地址
     */
    public function add_ip()
    {
        $ip_range = $this->GetParam('ip_range');

        $ips = explode('-', $ip_range);
        if (count($ips) != 2) {
            return $this->Error('IP范围参数错误');
        }
        $ip_start = $ips[0];
        $ip_stop = $ips[1];
        var_dump($ip_start);
        var_dump($ip_stop);
    }

}