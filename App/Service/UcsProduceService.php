<?php

namespace App\Service;

use App\Model\UcsIp;
use EasySwoole\Mysqli\QueryBuilder;

class UcsProduceService
{
    public static function AddIPAddress($ip_start, $ip_stop, $netmask, $gateway, $ucs_region_id, $remark)
    {

        $array = [];
        $ip_start_array = explode('.', $ip_start);
        $ip_stop_array = explode('.', $ip_stop);
        $range = ip2long($ip_stop) - ip2long($ip_start);
        for ($i = 0; $i <= $range; $i++) {
            if ($ip_start_array[3] > 255) {
                $ip_start_array[3] = 0;
                $ip_start_array[2]++;
                UcsIP::create()->func(function (QueryBuilder $builder) use ($array) {
                    $builder->insertAll('ucs_ip', $array);
                });
                $array = [];
            }
            if ($ip_start_array[2] > 255) {
                $ip_start_array[2] = 0;
                $ip_start_array[1]++;
            }
            if ($ip_start_array[1] > 255) {
                $ip_start_array[1] = 0;
                $ip_start_array[0]++;
            }
            $ip_address = [];
            $ip_address['ip'] = implode('.', $ip_start_array);
            $ip_address['netmask'] = $netmask;
            $ip_address['gateway'] = $gateway;
            $ip_address['ucs_region_id'] = $ucs_region_id;
            $ip_address['remark'] = $remark;
            $array[] = $ip_address;
            $ip_start_array[3]++;
        }

        UcsIP::create()->func(function (QueryBuilder $builder) use ($array) {
            $builder->insertAll('ucs_ip', $array);
        });
        $array = [];
        return true;
    }

}