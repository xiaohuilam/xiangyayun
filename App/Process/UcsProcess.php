<?php

namespace App\Process;

use App\Queue\UcsQueue;
use App\Service\UcsService;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Queue\Job;
use EasySwoole\Queue\Queue;

class UcsProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            info('监听UCS队列成功');
            UcsQueue::getInstance()->consumer()->listen(function (Job $job) {
                info('接到UCS队列');
                $data = $job->getJobData();
                UcsService::SendAction($data['task_id'], $data['instance_id'], $data['params']);
            });
        });
    }
}