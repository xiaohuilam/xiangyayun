<?php

namespace App\Crontab;

use App\Service\WechatService;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\LinuxDash\LinuxDash;

class SystemCrontab extends AbstractCronTask
{
    public static function getRule(): string
    {
        // 定义执行规则 根据Crontab来定义
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        // 定时任务的名称
        return 'SystemCrontab';
    }

    public function run(int $taskId, int $workerIndex)
    {
        // 开发者可投递给task异步处理
        TaskManager::getInstance()->async(function () {
            // todo some thing
            info('开始执行定时任务xxx');
            //  WechatService::SendToManager('', '');

        });
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        error($throwable->getMessage());
        // 捕获run方法内所抛出的异常
    }
}