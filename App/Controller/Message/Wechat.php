<?php

namespace App\Controller\Message;

use App\Controller\Common\Base;
use App\Service\AdminService;
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
            case "QRCODE_USER_LOGIN":
                return $this->QRCODE_USER_LOGIN($data);
            case "QRCODE_USER_BIND":
                return $this->QRCODE_USER_BIND($data);
            case "QRCODE_ADMIN_LOGIN":
                return $this->QRCODE_ADMIN_LOGIN($data);
            case "QRCODE_ADMIN_BIND":
                return $this->QRCODE_ADMIN_BIND($data);
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
    private function QRCODE_USER_BIND($data)
    {
        $wx_openid = $data['FromUserName'];
        //把ticket保存的USERID，找到然后绑定!
        $user_id = RedisService::GetWxBindUserTicket($data['Ticket']);
        UserService::BindWxOpenId($user_id, $wx_openid);
        $user = UserService::FindById($user_id);
        //微信绑定成功
        //绑定用户：EASON
        //绑定说明：你已成功绑定Easy系统
        //欢迎使用Easy系统，我们竭诚为您服务。
        WechatPushJob([
            'user_id' => $user_id,
            'params' => [
                'first' => '微信绑定成功',
                'keyword1' =>$user->username,
                'keyword2' => '你已成功绑定'.config('SYSTEM.APP_NAME').'系统',
                'remark' => '欢迎使用'.config('SYSTEM.APP_NAME').'系统，我们竭诚为您服务。',
            ],
            'action' => 'user_bind',
            'url' => config('SYSTEM.APP_URL') . '/user/info',
        ]);
        return new \EasySwoole\WeChat\Kernel\Messages\Text("扫码绑定成功!");
    }

    //微信二维码登录
    private function QRCODE_USER_LOGIN($data)
    {
        $wx_openid = $data['FromUserName'];
        $user = UserService::FindByWxOpenId($wx_openid);
        if ($user) {
            RedisService::SetWxLoginUserTicket($data['Ticket'], $user->id);
            return new \EasySwoole\WeChat\Kernel\Messages\Text("扫码登录成功!");
        } else {
            return new \EasySwoole\WeChat\Kernel\Messages\Text("未注册绑定用户!请点击此处绑定!");
        }
    }

    //微信二维码绑定
    private function QRCODE_ADMIN_BIND($data)
    {
        $wx_openid = $data['FromUserName'];
        //把ticket保存的USERID，找到然后绑定!
        $admin_id = RedisService::GetWxBindAdminTicket($data['Ticket']);
        WechatPushJob([
            'admin_id' => $admin_id,
            'params' => [
                'content' => date('Y-m-d H:i:s'),
            ],
            'action' => 'message',
            'url' => config('SYSTEM.APP_URL') . '/user/info',
        ]);
        AdminService::BindWxOpenId($admin_id, $wx_openid);
        return new \EasySwoole\WeChat\Kernel\Messages\Text("扫码绑定管理员成功!");
    }

    //微信二维码登录
    private function QRCODE_ADMIN_LOGIN($data)
    {
        $wx_openid = $data['FromUserName'];
        $admin = AdminService::FindByWxOpenId($wx_openid);
        WechatPushJob([
            'admin_id' => $admin->id,
            'params' => [
                'time' => date('Y-m-d H:i:s'),
                'ip' => '127.0.0.1',
            ],
            'action' => 'user_login',
            'url' => config('SYSTEM.APP_URL') . '/admin/info',
        ]);
        if ($admin) {
            RedisService::SetWxLoginAdminTicket($data['Ticket'], $admin->id);
            return new \EasySwoole\WeChat\Kernel\Messages\Text("扫码登录成功!");
        } else {
            return new \EasySwoole\WeChat\Kernel\Messages\Text("未注册绑定用户!请点击此处绑定!");
        }
    }

}