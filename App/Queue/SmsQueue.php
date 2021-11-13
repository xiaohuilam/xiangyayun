<?php

namespace App\Queue;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class SmsQueue extends Queue
{
    use Singleton;
}