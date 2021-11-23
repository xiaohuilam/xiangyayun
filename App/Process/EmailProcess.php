<?php

namespace App\Process;

use App\Queue\EmailQueue;
use App\Service\EmailService;
use App\Service\SmsService;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;

class EmailProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            info('监听Email队列成功');
            EmailQueue::getInstance()->consumer()->listen(function (Job $job) {
                info('接到发送邮件队列');
                $data = $job->getJobData();
                EmailService::SendEmail($data['email'], $data['action'], $data['params']);
                info('EMAIL:' . json_encode($data));
            });
        });
    }
}