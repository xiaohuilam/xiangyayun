<?php

namespace App\Controller\Common;

use EasySwoole\EasySwoole\Logger;
use EasySwoole\Jwt\Jwt;

class LoginBase extends Base
{
    private function GetUserId()
    {
        return $this->Get('user_id');
    }

    protected function onRequest(?string $action): ?bool
    {
        $flag = parent::onRequest($action); // TODO: Change the autogenerated stub
        if ($flag) {
            $this->GetData();
            if ($this->GetUserId()) {
                return true;
            }
        }
        return false;
    }

    private function Guester()
    {
        $server_params = $this->request()->getServerParams();
        $user_agent = $this->request()->getHeaderLine('user-agent');
        \EasySwoole\RedisPool\RedisPool::invoke(function (\EasySwoole\Redis\Redis $redis) use ($user_agent, $server_params) {
            //$data=$this->request()->getUri();
            $data['remote_addr'] = $server_params['remote_addr'];
            $data['request_uri'] = $server_params['request_uri'];
            $data['request_time'] = date('Y-m-d H:i:s', $server_params['master_time']);
            $data['user-agent'] = $user_agent;
            $redis->lPush('Guester', json_encode($data, true));
        });
        if ($user_agent) {//拦截user-agent

        }
        return true;
    }
}