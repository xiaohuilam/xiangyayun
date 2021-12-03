<?php

namespace App\Status;

class UcsActStatus
{
    //创建中
    const Build = 0;
    //运行中
    const NORMAL = 1;
    //关机中
    const Poweroff = 2;
    //强制关机中
    const ForcePoweroff = 2;
    //开机中
    const Start = 3;
    //重启中
    const ReStart = 4;
    //强制重启中
    const ForceReStart = 5;
    //重装系统中
    const ReSystem = 6;

    public static function ConvertToString($ActStatus)
    {

        switch ($ActStatus) {
            case 0:
                return '创建中';
            case 1:
                return '正常';
            case 2:
                return '关机中';
            case 3:
                return '开机中';
            default:
                return '未知状态';
        }

    }
}