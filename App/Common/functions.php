<?php

use App\Model\BaseModel;
use EasySwoole\ORM\DbManager;

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
