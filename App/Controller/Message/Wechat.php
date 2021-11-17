<?php

namespace App\Controller\Message;

use App\Controller\Common\Base;
use App\Service\WechatService;

class Wechat extends Base
{
    public function server()
    {
        $server = WechatService::MessageServer();
        $psr7Request = $this->request();
        $replyResponse = $server->forceValidate()->serve($psr7Request);

        $this->response()->withStatus($replyResponse->getStatusCode());
        foreach ($replyResponse->getHeaders() as $name => $values) {
            $this->response()->withHeader($name, implode(", ", $values));
        }

        // 将响应输出到客户端
        $this->response()->write($replyResponse->getBody()->__toString());
    }

}