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
    //重设密码中
    const RePwd = 7;

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
            case 4:
                return '软重启中';
            case 5:
                return '硬重启中';
            case 6:
                return '重装中';
            case 7:
                return '重设密码中';
            default:
                return '未知状态';
        }
    }
}