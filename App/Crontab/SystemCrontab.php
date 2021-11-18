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

            //获取ip地址网卡缓冲信息
            $data = LinuxDash::arpCache();
            var_dump($data);
            //获取当前带宽数据
            $data = LinuxDash::bandWidth();
            var_dump($data);
            //获取cpu进程占用排行信息
            $data = LinuxDash::cpuIntensiveProcesses();
            var_dump($data);
            //获取磁盘分区信息
            $data = LinuxDash::diskPartitions();
            var_dump($data);
            //获取当前内存使用信息
            $data = LinuxDash::currentRam();
            var_dump($data);
            //获取cpu信息
            $data = LinuxDash::cpuInfo();
            var_dump($data);
            //获取当前系统信息
            $data = LinuxDash::generalInfo();
            var_dump($data);
            //获取当前磁盘io统计
            $data = LinuxDash::ioStats();
            var_dump($data);
            //获取ip地址
            $data = LinuxDash::ipAddresses();
            var_dump($data);
            //CPU负载信息
            $data = LinuxDash::loadAvg();
            var_dump($data);
            //获取内存详细信息
            $data = LinuxDash::memoryInfo();
            var_dump($data);
            //获取进程占用内存排行信息
            $data = LinuxDash::ramIntensiveProcesses();
            var_dump($data);
            //获取swap交换空间信息
            $data = LinuxDash::swap();
            var_dump($data);
            //获取当前用户名信息
            $data = LinuxDash::userAccounts();
            var_dump($data);

        });
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        error($throwable->getMessage());
        // 捕获run方法内所抛出的异常
    }
}