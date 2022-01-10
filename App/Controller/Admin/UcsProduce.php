<?php

namespace App\Controller\Admin;

use App\Controller\Common\AdminAuthBase;
use App\Controller\Common\Base;
use App\Service\UcsProduceService;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

class UcsProduce extends Base
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
        $netmask = $this->GetParam('netmask');
        $gateway = $this->GetParam('gateway');
        $ucs_region_id = $this->GetParam('ucs_region_id');

        $ips = explode('-', $ip_range);
        if (count($ips) != 2) {
            return $this->Error('IP范围参数错误');
        }
        $ip_start = $ips[0];
        $ip_stop = $ips[1];
        $long_ip_start = ip2long($ip_start);
        $long_ip_stop = ip2long($ip_stop);
        $long_netmask = ip2long($netmask);
        $long_gateway = ip2long($gateway);
        if (!$long_ip_start) {
            return $this->Error('起始IP错误');
        }
        if (!$long_ip_stop) {
            return $this->Error('结束IP错误');
        }
        if (!$long_netmask) {
            return $this->Error('子网掩码错误');
        }
        if (!$long_gateway) {
            return $this->Error('网关错误');
        }
        $flag = UcsProduceService::AddIPAddress($ip_start, $ip_stop, $netmask, $gateway, $ucs_region_id, '');
        if ($flag) {
            return $this->Success('添加成功');
        }
        return $this->Error('添加失败');
    }


}