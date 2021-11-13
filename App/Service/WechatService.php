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

    public static function SendCode($open_id, $action, $code, $expire, $url)
    {
        WechatPushJob([
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

    public static function SendTemplateMessage($open_id, $params, $action, $url)
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