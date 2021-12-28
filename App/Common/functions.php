<?php

use App\Model\BaseModel;
use EasySwoole\ORM\DbManager;

//获取自定义配置项值
if (!function_exists('Desensitize')) {
    function Desensitize($string, $start = 0, $length = 0, $re = '*')
    {
        if (empty($string) || empty($length) || empty($re)) return $string;
        $end = $start + $length;
        $strlen = mb_strlen($string);
        $str_arr = array();
        for ($i = 0; $i < $strlen; $i++) {
            if ($i >= $start && $i < $end)
                $str_arr[] = $re;
            else
                $str_arr[] = mb_substr($string, $i, 1);
        }
        return implode('', $str_arr);
    }
}
//获取自定义配置项值
if (!function_exists('config')) {
    function config($key, $value = null)
    {
        $instance = \EasySwoole\EasySwoole\Config::getInstance();;
        if ($value) {
            $instance->setConf('CLOUD.' . $key, $value);
        } else {
            return $instance->getConf('CLOUD.' . $key);
        }
    }
}
//日志
if (!function_exists('info')) {
    function info($message)
    {
        \EasySwoole\EasySwoole\Logger::getInstance()->log($message, \EasySwoole\Log\LoggerInterface::LOG_LEVEL_INFO, 'info');
    }
}
if (!function_exists('error')) {
    function error($message)
    {
        \EasySwoole\EasySwoole\Logger::getInstance()->log($message, \EasySwoole\Log\LoggerInterface::LOG_LEVEL_ERROR, 'error');
    }
}

if (!function_exists('EmailJob')) {
    function EmailJob($data)
    {
        $job = new EasySwoole\Queue\Job();
        $job->setJobData($data);
        return App\Queue\EmailQueue::getInstance()->producer()->push($job);
    }
}

if (!function_exists('UcsJob')) {
    function UcsJob($data)
    {
        $job = new EasySwoole\Queue\Job();
        $job->setJobData($data);
        return App\Queue\UcsQueue::getInstance()->producer()->push($job);
    }
}
if (!function_exists('SmsJob')) {
    function SmsJob($data)
    {
        $job = new EasySwoole\Queue\Job();
        $job->setJobData($data);
        return App\Queue\SmsQueue::getInstance()->producer()->push($job);
    }
}
if (!function_exists('WechatPushJob')) {
    function WechatPushJob($data)
    {
        $job = new EasySwoole\Queue\Job();
        $job->setJobData($data);
        return App\Queue\WechatPushQueue::getInstance()->producer()->push($job);
    }
}
//function SendTemplateMessage($open_id, $params, $action, $url)
if (!function_exists('SystemJob')) {
    function SystemJob($data)
    {
        $job = new EasySwoole\Queue\Job();
        $job->setJobData($data);
        return App\Queue\SystemQueue::getInstance()->producer()->push($job);
    }
}
if (!function_exists('DBSave')) {
    function DBSave($table, $value = null)
    {
        return DbManager::getInstance()->invoke(function ($client) use ($value) {
            $baseModel = BaseModel::invoke($client, $value);
            $data = $baseModel->save();
            return $data;
        });
    }

}

if (!function_exists('DBUpdate')) {
    function DBUpdate($table, $value = null)
    {
        return DbManager::getInstance()->invoke(function ($client) use ($value) {
            $baseModel = BaseModel::invoke($client, $value);
            $data = $baseModel->save();
            return $data;
        });
    }
}
if (!function_exists('make_mac')) {
    function make_mac($id, $mac_prefix = [])
    {
        $data = dechex($id);
        $temp = str_split($data, 2);
        //前缀数组
        $mac_array = $mac_prefix;
        //总长度不够
        if (count($temp) < 6 - count($mac_array)) {
            for ($i = 0; $i < 7 - count($mac_array) - count($temp); $i++) {
                $mac_array[] = "00";
            }
        } else if (count($temp) > 6) {
            $mac_array = [];
            $length = count($temp);
            for ($i = 0; $i < $length - 6; $i++) {
                array_shift($temp);
            }
        } else if (count($temp) > 6 - count($mac_array)) {
            $length = count($mac_array);
            for ($i = 0; $i < count($temp) - $length; $i++) {
                array_shift($mac_array);
            }
        }
        foreach ($temp as $item) {
            if (strlen($item) != 2) {
                $mac_array[] = "0" . $item;
            } else {
                $mac_array[] = $item;
            }
        }
        return implode(':', $mac_array);
    }
}