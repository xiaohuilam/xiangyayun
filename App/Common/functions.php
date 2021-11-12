<?php

use App\Model\BaseModel;
use EasySwoole\ORM\DbManager;

if (!function_exists('helloEasySwoole')) {
    function helloEasySwoole()
    {
        echo 'Hello EasySwoole!';
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
