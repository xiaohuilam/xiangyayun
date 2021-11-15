<?php

namespace App\Service;

use App\Model\UcsInstance;
use App\Model\UcsMaster;

class UcsService
{
    public static function SendAction($instance_id, $params)
    {
        $params['instance_id'] = $instance_id;
        $instance = UcsInstance::create()->get(['id' => $instance_id]);
        $ucs_master = UcsMaster::create()->where('id', $instance->ucs_master_id)->get();
        if ($ucs_master) {
            $client = new \EasySwoole\HttpClient\HttpClient($ucs_master->api);
            $aes = new AesService($ucs_master['token']);
            $string = $aes->encrypt(json_encode($params));
            $d['encrypt'] = $string;
            $response = $client->postJson(json_encode($d));
            $return = $response->json(true);
            var_dump($return);
        }
    }

    //开机
    public static function Start($instance_id)
    {
        $params['action'] = 'start';
        self::SendAction($instance_id, $params);
    }

    //重启
    public static function ReStart($instance_id)
    {
        $params['action'] = 'restart';
        self::SendAction($instance_id, $params);
    }

    //关机
    public static function Shutdown($instance_id)
    {
        $params['action'] = 'shutdown';
        self::SendAction($instance_id, $params);
    }

    //重设服务器密码
    public static function ResetPassword($instance_id, $password)
    {
        $params['action'] = 'reset_password';
        $params['password'] = $password;
        self::SendAction($instance_id, $params);
    }

    //重设服务器密码
    public static function ResetIPAddress($instance_id, $ip_address)
    {
        $params['action'] = 'reset_password';
        $params['ipaddr'] = $ip_address;
        self::SendAction($instance_id, $params);
    }

    //强制重启
    public static function ForceReStart($instance_id)
    {
        $params['action'] = 'force_restart';
        self::SendAction($instance_id, $params);
    }

    //强制关机
    public static function ForceShutdown($instance_id)
    {
        $params['action'] = 'force_shutdown';
        self::SendAction($instance_id, $params);
    }
}