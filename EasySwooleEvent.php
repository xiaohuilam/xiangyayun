<?php


namespace EasySwoole\EasySwoole;


use App\Process\SmsProcess;
use App\Process\UcsProcess;
use App\Process\WechatPushProcess;
use App\Queue\SmsQueue;
use App\Queue\UcsQueue;
use App\Queue\WechatPushQueue;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config;
use EasySwoole\Queue\Queue;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_CONTROLLER_NAMESPACE, 'App\\Controller\\');
        date_default_timezone_set('Asia/Shanghai');
        $redisPoolConfig = \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig());
        // 配置连接池连接数
        $redisPoolConfig->setMinObjectNum(5);
        $redisPoolConfig->setMaxObjectNum(20);


        $config = new Config();
        $config->setDatabase('hiy');
        $config->setUser('hiy');
        $config->setPassword('4iDzkRhTX44GZz3z');
        $config->setHost('119.23.58.76');
        $config->setTimeout(15); // 超时时间
        //连接池配置
        $config->setGetObjectTimeout(3.0); //设置获取连接池对象超时时间
        $config->setIntervalCheckTime(30 * 1000); //设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime(15); //连接池对象最大闲置时间(秒)
        $config->setMinObjectNum(5); //设置最小连接池存在连接对象数量
        $config->setMaxObjectNum(20); //设置最大连接池存在连接对象数量
        $config->setAutoPing(5); //设置自动ping客户端链接的间隔

        DbManager::getInstance()->addConnection(new Connection($config));


    }

    public static function mainServerCreate(EventRegister $register)
    {
        $redisData = \EasySwoole\EasySwoole\Config::getInstance()->getConf('REDIS');
        $redisConfig = new RedisConfig($redisData);
        $driver = new \EasySwoole\Queue\Driver\RedisQueue($redisConfig, 'ucs_queue');
        UcsQueue::getInstance($driver);
        //注册消费进程
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new UcsProcess());

        $driver = new \EasySwoole\Queue\Driver\RedisQueue($redisConfig, 'sms_queue');
        SmsQueue::getInstance($driver);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new SmsProcess());

        $driver = new \EasySwoole\Queue\Driver\RedisQueue($redisConfig, 'wechat_push_queue');
        WechatPushQueue::getInstance($driver);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new WechatPushProcess());

    }
}