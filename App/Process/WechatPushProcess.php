<?php

namespace App\Process;

use App\Queue\WechatPushQueue;
use App\Service\WechatService;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;

class WechatPushProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            info('监听系统推送队列成功');
            WechatPushQueue::getInstance()->consumer()->listen(function (Job $job) {
                info('接到发送模板消息队列');
                $data = $job->getJobData();
                WechatService::SendTemplateMessage($data['user_id'], $data['open_id'], $data['params'], $data['action'], $data['url']);
            });
        });
    }
}