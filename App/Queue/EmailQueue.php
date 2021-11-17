<?php

namespace App\Queue;

use EasySwoole\Component\Singleton;
use EasySwoole\Queue\Queue;

class EmailQueue extends Queue
{
    use Singleton;
}