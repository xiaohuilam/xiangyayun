<?php

namespace App\Controller\Notify;

use App\Controller\Common\Base;
use App\Model\UcsTask;
use App\Service\RedisService;
use App\Service\UcsService;
use App\Status\UcsActStatus;
use App\Status\UcsRunStatus;

class Ucs extends Base
{
    public function notify()
    {
        $task_id = $this->GetParam('task_id');
        $progress = $this->GetParam('progress');
        $notify_message = $this->GetParam('notify_message');
        $task = UcsTask::create()->get(['id' => $task_id]);
        if ($task) {
            $status = $task->status;
            if ($progress == 100) {
                //完成操作开始修改状态
                $status = 1;
                //运行状态修改
                if ($task->action == "start") {
                    UcsService::ChangeRunStatus($task->ucs_instance_id, UcsRunStatus::RUN);
                } else if ($task->action == "shutdown") {
                    UcsService::ChangeRunStatus($task->ucs_instance_id, UcsRunStatus::POWEROFF);
                }
                //修改操作状态
                UcsService::ChangeActStatus($task->ucs_instance_id, UcsActStatus::NORMAL);
            }
            UcsTask::create()->update([
                'progress' => $progress,
                'notify_message' => $notify_message,
                'status' => $status,
                'notify_status' => 1,
                'notify_time' => date('Y-m-d H:i:s')
            ], [
                'id' => $task_id,
            ]);
        }
        return $this->Success();
    }

    public function flow()
    {

    }

    //资源监控接口
    public function monitor()
    {
        //验签

        //获取流量信息

        //计算按流量计费的机器

        //扣除流量后，恢复默认带宽

        //计算性能计费的实例

        //扣除相关性能积分后,恢复默认性能
        $data = [];
        $data['load'] = [
            'tips' => '运行流畅',
            'ratio' => 60
        ];
        $data['cpu'] = [
            'num' => 2,
            'ratio' => 60
        ];
        $data['memory'] = [
            'use' => 2048,
            'total' => 4096,
            'ratio' => 60
        ];

        $data['harddisk'] = [
            'use' => 10.6,
            'total' => 40.0,
            'ratio' => 60
        ];

        RedisService::SetUcsResourceStatus(1, $data);
    }

}