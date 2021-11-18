<?php

namespace App\Service;

use App\Model\Admin;
use App\Model\User;
use App\Model\WechatPushTemp;
use EasySwoole\WeChat\Factory;
use EasySwoole\WeChat\Kernel\Messages\News;
use EasySwoole\WeChat\Kernel\Messages\NewsItem;

class WechatService
{

    //发送待支付提醒给微信
    public static function SendPayNotify($app_name, $type, $user_id, $amount, $order_no)
    {
        WechatPushJob([
            'user_id' => $user_id,
            'params' => [
                'first' => '您有一笔订单需要支付',
                'keyword1' => $app_name,
                'keyword2' => $type,
                'keyword3' => [$user_id, '#F00'],
                'keyword4' => [$amount . '元', '#F00'],
                'remark' => '您可以点击查看详情或直接支付该笔订单',
            ],
            'action' => 'pay_notify',
            'url' => config('SYSTEM.APP_URL') . "/pay/" . $order_no,
        ]);
    }

    //发送验证码至微信
    public static function SendCode($user_id, $action, $code, $expire, $url)
    {
        WechatPushJob([
            'user_id' => $user_id,
            'params' => [
                'first' => "您好，本次需要进行验证码验证，为保障帐户安全，请勿向任何人提供此验证码。",
                'keyword1' => $action,
                'keyword2' => $code,
                'keyword3' => $expire,
                'remark' => "5分钟内有效,请及时使用!",
            ],
            'action' => 'code',
            'url' => $url,
        ]);
    }

    public static function SendTemplateMessageThread($open_id, $template_id, $url, $params)
    {
        $officialAccount = Factory::officialAccount(config('WECHAT'));
        $officialAccount->templateMessage->send([
            'touser' => $open_id,
            'template_id' => $template_id,
            'url' => $url,
            'data' => $params,
        ]);
        return true;
    }

    public static function SendTemplateMessage($admin_id, $user_id, $params, $action, $url)
    {
        $temp = WechatPushTemp::create()->get(['action' => $action]);
        if (!$temp) {
            return info('没有找到相关模板');
        }
        if ($admin_id) {
            $admin = Admin::create()->field('wechat_open_id')->get([
                'wechat_notify_status' => 1,
                'id' => $admin_id
            ]);
            self::SendTemplateMessageThread($admin['wechat_open_id'], $temp->template_id, $url, $params);
        } else if ($user_id) {
            $user = User::create()->get(['id' => $user_id]);
            if ($user && $user->wx_openid) {
                self::SendTemplateMessageThread($user->wx_openid, $temp->template_id, $url, $params);
                LogService::WechatPushLogError($user_id, $user->wx_openid, $params);
                return true;
            }
            return false;
        }
    }

    // 加载消息模板至数据库
    public static function LoadMessageTemplate()
    {
        $officialAccount = Factory::officialAccount(config('WECHAT'));
        $data = $officialAccount->templateMessage->getPrivateTemplates();
        foreach ($data['template_list'] as $key => $value) {
            $temp = WechatPushTemp::create()->get(['template_id' => $value['template_id']]);
            if (!$temp) {
                WechatPushTemp::create([
                    'template_id' => $value['template_id'],
                    'title' => $value['title'],
                    'content' => $value['content'],
                ])->save();
            }
        }
    }

    public static function MessageServer()
    {
        $officialAccount = Factory::officialAccount(config('WECHAT'));
        $server = $officialAccount->server;
        return $server;
    }

    public static function GetQrcode($token)
    {
        $officialAccount = Factory::officialAccount(config('WECHAT'));
        //生成一个随机字符串
        return $officialAccount->qrcode->temporary($token, 600);
    }

    //微信推送异常消息给管理员
    public static function SendToManagerError($title, $describe, $remark, $url)
    {
        $officialAccount = Factory::officialAccount(config('WECHAT'));
        $temp_open_ids = Admin::create()->field('id,wechat_open_id')->all(['wechat_notify_status' => 1]);
        foreach ($temp_open_ids as $key => $value) {
            WechatPushJob([
                'admin_id' => $value->id,
                'params' => [
                    'first' => $title,
                    'keyword1' => $describe,
                    'keyword2' => date('Y-m-d H:i:s'),
                    'remark' => $remark,
                ],
                'action' => 'error',
                'url' => config('SYSTEM.APP_URL') . $url,
            ]);
        }
    }

    public static function aaa()
    {

    }
}