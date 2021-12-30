<?php

namespace App\Controller\Api;

use App\Controller\Common\Base;
use App\Service\EmailService;
use App\Service\UcsService;
use App\Service\WechatService;

class Test extends Base
{
    public function test()
    {
        WechatService:: SendToManagerError('服务器异常', "您的127.0.0.1服务器有问题!", "请及时处理!", "http://www.baidu.com");
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