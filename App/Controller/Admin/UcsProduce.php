<?php

namespace App\Controller\Admin;

use App\Controller\Common\AdminAuthBase;
use App\Controller\Common\Base;
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
        if ((ip2long($ip_stop) - ip2long($ip_start)) > 255) {
            return $this->Error('范围不能高于255');
        }
    }

    public function ip_range_to_array($ip_start, $ip_stop)
    {
        $array = [];
        $ip_start_array = explode('.', $ip_start);
        $ip_stop_array = explode('.', $ip_stop);
        $range = ip2long($ip_stop) - ip2long($ip_start);
        for ($i = 0; $i < $range; $i++) {
            if ($ip_start_array[3] > 255) {
                $ip_start_array[3] = 0;
                $ip_start_array[2]++;
            }
            if ($ip_start_array[2] > 255) {
                $ip_start_array[2] = 0;
                $ip_start_array[1]++;
            }
            if ($ip_start_array[1] > 255) {
                $ip_start_array[1] = 0;
                $ip_start_array[0]++;
            }
            var_dump(implode($ip_start_array, '.'));
        }
    }

}