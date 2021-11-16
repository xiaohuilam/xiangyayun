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
                //已经过期,强制关机
                if ($value->run_status == UcsRunStatus::RUN) {
                    //正常的给他关机
                    UcsService::ForceShutdownAction($value->id);
                    info('实例过期关机:' . $value->id);
                }
            }
        }
    }

}