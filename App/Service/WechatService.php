<?php

namespace App\Service;

use App\Model\Admin;
use App\Model\WechatPushTemp;
use EasySwoole\WeChat\Factory;
use EasySwoole\WeChat\Kernel\Messages\News;
use EasySwoole\WeChat\Kernel\Messages\NewsItem;

class WechatService
{
    private static function MakeNewsItem($title, $description, $url, $image)
    {
        return new News([
            new NewsItem([
                'title' => '象牙云计算',
                'description' => '数据更安全',
                'url' => 'https://www.upy.cn',
                'image' => 'https://img2.baidu.com/it/u=1945464906,1635022113&fm=26&fmt=auto',
            ]),
            new NewsItem([
                'title' => '象牙云计算',
                'description' => '数据更安全',
                'url' => 'https://www.upy.cn',
                'image' => 'https://img2.baidu.com/it/u=1945464906,1635022113&fm=26&fmt=auto',
            ]),
        ]);
    }

    public static function SendPayNotify($user_id, $open_id, $amount, $order_no)
    {
        WechatPushJob([
            'user_id' => $user_id,
            'open_id' => $open_id,
            'params' => [
                'first' => '您有一笔订单需要支付',
                'keyword1' => 'PC端发起',
                'keyword2' => '支付宝',
                'keyword3' => ['10001', '#F00'],
                'keyword4' => [$amount . '元', '#F00'],
                'remark' => '您可以点击查看详情或直接支付该笔订单',
            ],
            'action' => 'pay_notify',
            'url' => 'https://upy.cn/user/order_no/' . $order_no,
        ]);

    }

    public static function SendCode($user_id, $open_id, $action, $code, $expire, $url)
    {
        WechatPushJob([
            'user_id' => $user_id,
            'open_id' => $open_id,
            'params' => [
                'keyword1' => $action,
                'keyword2' => $code,
                'keyword3' => $expire,
            ],
            'action' => 'code',
            'url' => $url,
        ]);
    }

    public static function SendTemplateMessage($user_id, $open_id, $params, $action, $url)
    {
        $temp = WechatPushTemp::create()->get(['action' => $action]);
        if ($temp) {
            $officialAccount = Factory::officialAccount(config('WECHAT'));
            $officialAccount->templateMessage->send([
                'touser' => $open_id,
                'template_id' => $temp->template_id,
                'url' => $url,
                'data' => $params,
            ]);
            LogService::WechatPushLogError($user_id, $open_id, $params);
            return true;
        }
        info('发送模板消息失败');
    }

    // 加载消息模板
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

    public static function send($title, $description, $url, $image)
    {
        $news = self::MakeNewsItem($title, $description, $url, $image);
        $officialAccount = Factory::officialAccount(config('WECHAT'));
        $temp_open_ids = Admin::create()->field('wechat_open_id')->all(['wechat_notify_status' => 1]);
        $open_ids = [];
        foreach ($temp_open_ids as $key => $value) {
            $open_ids[] = $value['wechat_open_id'];
        }
    }

    public static function aaa()
    {

    }
}