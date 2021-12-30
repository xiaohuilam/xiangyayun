<?php

namespace App\Controller\Api;

use App\Controller\Common\Base;
use App\Controller\Message\Wechat;
use App\Service\EmailService;
use App\Service\UcsService;
use App\Service\WechatService;

class Test extends Base
{
    public function test()
    {
        WechatService:: SendToManagerError('服务器异常', "您的127.0.0.1服务器有问题!", "请及时处理!", "/admin");
//        $url = RechargeService::Alipay();
//        return $this->Success('1', $url);
//        if (UcsJob(['status' => true])) {
//            $this->Success();
//        }
    }

    public function start()
    {
        UcsService::Start(1);
    }

    public function test_wechat()
    {
        WechatService::SendPayNotify(1, 'otbIy0R2VgjMxNwBntbVMYgCfwus', 1000, 111);
    }

    public function test_tag_list()
    {
        $data = WechatService::LoadUserTagList();
        return $this->Success('', $data);
    }

    public function test_tag_del()
    {
        $data = WechatService::DeleteUserTag(2);
        return $this->Success('', $data);
    }

    public function test_create()
    {
        WechatService::SendCreateSuccessNotify('UCS实例', '购买产品', '1', '1', '2022-12-12', '如果在使用过程中遇到问题，请尽快联系客服处理哦!');
    }

    public function test_loadtemplate()
    {
        WechatService::LoadMessageTemplate();
    }

    public function test_email()
    {
        EmailService::SendEmailJob("1015653737@qq.com", "test", ["aa" => "111"]);
    }

    public function test_sms()
    {
        WechatService::send('测试', '测试测试测试', 'https://upy.cn', 'https://img2.baidu.com/it/u=1945464906,1635022113&fm=26&fmt=auto');
        SmsJob([
            'mobile' => '18108018820',
            'action' => 'action_code',
            'params' => [
                '注册用户', 123456
            ],
        ]);
    }

}