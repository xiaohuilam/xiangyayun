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
    private function aa(){

        $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
            var_dump($message);
            $data = $message->transformForJsonRequest();
            var_dump($data);
            switch ($message->getType()) {
                case 'event':
                    if ($data['EventKey'] == "QRCODE_LOGIN") {
                        return self::QRCODE_LOGIN($data);
                    }
                    if ($data['EventKey'] == "QRCODE_LOGIN") {
                        return self::QRCODE_BIND($data);
                    }
                    break;
                case 'text':
                    $text = '收到文字消息';
                    break;
                case 'image':
                    $text = '收到图片消息';
                    break;
                case 'voice':
                    $text = '收到语音消息';
                    break;
                case 'video':
                    $text = '收到视频消息';
                    break;
                case 'location':
                    $text = '收到坐标消息';
                    break;
                case 'link':
                    $text = '收到链接消息';
                    break;
                case 'file':
                    $text = '收到文件消息';
                    break;
                // ... 其它消息
                default:
                    $text = '收到其它消息';
                    break;
            }

            return new \EasySwoole\WeChat\Kernel\Messages\Text($text);
        });
    }

}