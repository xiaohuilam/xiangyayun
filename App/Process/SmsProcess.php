<?php

namespace App\Process;

use App\Queue\SmsQueue;
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
                sleep(2);
                info('SMS:' . json_encode($job->getJobData()));
            });
        });
    }
}