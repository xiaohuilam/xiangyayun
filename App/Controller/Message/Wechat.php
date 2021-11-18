<?php

namespace App\Controller\Message;

use App\Controller\Common\Base;
use App\Service\RedisService;
use App\Service\UserService;
use App\Service\WechatService;

class Wechat extends Base
{
    //微信公众号服务端地址
    public function server()
    {
        $server = WechatService::MessageServer();
        $this->message($server);
        $psr7Request = $this->request();
        $replyResponse = $server->forceValidate()->serve($psr7Request);

        $this->response()->withStatus($replyResponse->getStatusCode());
        foreach ($replyResponse->getHeaders() as $name => $values) {
            $this->response()->withHeader($name, implode(", ", $values));
        }
        // 将响应输出到客户端
        $this->response()->write($replyResponse->getBody()->__toString());
    }

    //处理事件消息
    private function Event($data)
    {
        switch ($data['EventKey']) {
            case "QRCODE_LOGIN":
                return $this->QRCODE_LOGIN($data);
            case "QRCODE_BIND":
                return $this->QRCODE_BIND($data);
        }
    }

    private function message($server)
    {
        $server->push(function (\EasySwoole\WeChat\Kernel\Contracts\MessageInterface $message) {
            $data = $message->transformForJsonRequest();
            switch ($message->getType()) {
                case 'event':
                    return $this->Event($data);
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

    //微信二维码绑定
    private function QRCODE_BIND($data)
    {
        $wx_openid = $data['FromUserName'];
        //把ticket保存的USERID，找到然后绑定!
        $user_id = RedisService::GetWxBindTicket($data['Ticket']);
        UserService::BindWxOpenId($user_id, $wx_openid);
        return new \EasySwoole\WeChat\Kernel\Messages\Text("扫码绑定成功!");
    }

    //微信二维码登录
    private function QRCODE_LOGIN($data)
    {
        $wx_openid = $data['FromUserName'];
        $user = UserService::FindByWxOpenId($wx_openid);
        if ($user) {
            RedisService::SetWxLoginTicket($data['Ticket'], $user->id);
            return new \EasySwoole\WeChat\Kernel\Messages\Text("扫码登录成功!");
        } else {
            return new \EasySwoole\WeChat\Kernel\Messages\Text("未注册绑定用户!请点击此处绑定!");
        }
    }

}