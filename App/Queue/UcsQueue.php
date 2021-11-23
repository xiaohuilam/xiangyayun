<?php

namespace App\Queue;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class UcsQueue extends Queue
{
    use Singleton;
}