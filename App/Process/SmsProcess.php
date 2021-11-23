<?php

namespace App\Process;

use App\Queue\SmsQueue;
use App\Service\SmsService;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Log\Logger;
use EasySwoole\Queue\Job;

class SmsProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            info('监听SMS队列成功');
            SmsQueue::getInstance()->consumer()->listen(function (Job $job) {
                info('接到发送短信队列');
                $data = $job->getJobData();
                SmsService::JobSend($data['mobile'], $data['action'], $data['params']);
                info('SMS:' . json_encode($data));
            });
        });
    }
}