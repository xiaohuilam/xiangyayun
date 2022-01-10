<?php

namespace App\Crontab;

use App\Service\UcsService;
use App\Service\WechatService;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Task\TaskManager;

class UcsCrontab extends AbstractCronTask
{
    public static function getRule(): string
    {
        // 定义执行规则 根据Crontab来定义
        return '01 12 * * *';
    }

    public static function getTaskName(): string
    {
        // 定时任务的名称
        return 'UcsCrontab';
    }

    public function run(int $taskId, int $workerIndex)
    {
        // 开发者可投递给task异步处理
        TaskManager::getInstance()->async(function () {
            // todo some thing
            info('每天早上的UCS过期提醒');
            //  WechatService::SendToManager('', '');
            $list = UcsService::SelectUcsInstanceBySoonExpire();
            foreach ($list as $item) {
                WechatService::SendExpireNotify('UCS云服务器', $item->expire_time, $item->user_id);
            }
        });
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        error($throwable->getMessage());
        // 捕获run方法内所抛出的异常
    }

}