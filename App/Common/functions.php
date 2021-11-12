<?php

use App\Model\BaseModel;
use EasySwoole\ORM\DbManager;

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
