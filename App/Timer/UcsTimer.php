<?php

namespace App\Timer;

use App\Service\AuthService;
use App\Service\UcsService;
use App\Status\UcsRunStatus;

class UcsTimer
{
    public static function run()
    {
        //发现UCS过期的,及时停机断网
        $ucs_instance_expire = UcsService::SelectUcsInstanceBySoonExpire();
        foreach ($ucs_instance_expire as $key => $value) {
            if ($value->expire_time < date('Y-m-d H:i:s')) {
                //修改订单状态为已过期
                $value->order_status = 0;
                $value->update();
                //已经过期,强制关机
                if ($value->run_status == UcsRunStatus::RUN) {
                    //正常的给他关机
                    $value->run_status = UcsRunStatus::POWEROFF;
                    $value->update();
                    UcsService::ForceShutdownAction($value->id);
                    info('实例过期关机:' . $value->id);
                }
            }
        }
        //计算已经使用了多少内存 CPU 以及 磁盘
        $masters = UcsService::SelectMasterAll();
        foreach ($masters as $master) {
            $master->use_memory = UcsService::SumUcsMemoryByMasterId($master->id);
            $master->use_harddisk = UcsService::SumUcsHarddiskByMasterId($master->id);
            $master->use_cpu = UcsService::SumUcsCpuByMasterId($master->id);
            $master->update();
        }
    }

}