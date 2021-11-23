<?php

namespace App\Queue;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class WechatPushQueue extends Queue
{
    use Singleton;
}