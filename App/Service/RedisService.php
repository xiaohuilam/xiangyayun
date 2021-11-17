<?php

namespace App\Service;

class RedisService
{
    public static function Get($key)
    {
        $redis = \EasySwoole\RedisPool\RedisPool::defer();
        return $redis->get($key);
    }

    public static function Set($key, $value)
    {
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($key, $value) {
            $data = $redis->set($key, $value);
            info("缓存数据." . json_encode($data));
        });
    }

}