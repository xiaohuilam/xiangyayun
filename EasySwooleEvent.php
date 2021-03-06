<?php


namespace EasySwoole\EasySwoole;


use App\Process\EmailProcess;
use App\Process\SmsProcess;
use App\Process\UcsProcess;
use App\Process\WechatPushProcess;
use App\Queue\EmailQueue;
use App\Queue\SmsQueue;
use App\Queue\UcsQueue;
use App\Queue\WechatPushQueue;
use App\Timer\AuthTimer;
use App\Timer\UcsTimer;
use App\Tools\Session;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FileWatcher\FileWatcher;
use EasySwoole\FileWatcher\WatchRule;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
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


        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response) {
            // 获取 header 中 language 参数
            $response->withHeader('Access-Control-Allow-Origin', '*');
            $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->withHeader('Access-Control-Allow-Credentials', 'true');
            $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With ,Token');
            if ($request->getMethod() === 'OPTIONS') {
                $response->withStatus(\EasySwoole\Http\Message\Status::CODE_OK);
                return false;
            }
            return true;
        });
        $redisData = config('REDIS');
        $redisConfig = new RedisConfig($redisData);

        $redisPoolConfig = \EasySwoole\RedisPool\RedisPool::getInstance()->register($redisConfig);
        // 配置连接池连接数
        $redisPoolConfig->setMinObjectNum(5);
        $redisPoolConfig->setMaxObjectNum(50);
        $mysqlConfig = config('MYSQL');

        $config = new Config();
        $config->setDatabase($mysqlConfig['database']);
        $config->setUser($mysqlConfig['user']);
        $config->setPassword($mysqlConfig['password']);
        $config->setHost($mysqlConfig['host']);
        $config->setTimeout($mysqlConfig['timeout']); // 超时时间
        //连接池配置
        $config->setGetObjectTimeout($mysqlConfig['get_object_timeout']); //设置获取连接池对象超时时间
        $config->setIntervalCheckTime($mysqlConfig['interval_check_time']); //设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime($mysqlConfig['max_idle_time']); //连接池对象最大闲置时间(秒)
        $config->setMinObjectNum($mysqlConfig['min_object_num']); //设置最小连接池存在连接对象数量
        $config->setMaxObjectNum($mysqlConfig['max_object_num']); //设置最大连接池存在连接对象数量
        $config->setAutoPing($mysqlConfig['auto_ping']); //设置自动ping客户端链接的间隔

        DbManager::getInstance()->addConnection(new Connection($config));
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $redisData = config('REDIS');
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


        $driver = new \EasySwoole\Queue\Driver\RedisQueue($redisConfig, 'email_queue');
        EmailQueue::getInstance($driver);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess(new EmailProcess());


        //系统定时任务
        Crontab::getInstance()->addTask(\App\Crontab\SystemCrontab::class);

        Crontab::getInstance()->addTask(\App\Crontab\UcsCrontab::class);


        $watcher = new FileWatcher();
        $rule = new WatchRule(EASYSWOOLE_ROOT . "/App"); // 设置监控规则和监控目录
        $watcher->addRule($rule);
        $watcher->setOnChange(function () {
            Logger::getInstance()->info('file change ,reload!!!');
            ServerManager::getInstance()->getSwooleServer()->reload();
        });
        $watcher->attachServer(ServerManager::getInstance()->getSwooleServer());
        //定时器
        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) {
            //如何避免定时器因为进程重启而丢失
            //例如在第一个进程 添加一个10秒的定时器
            if ($workerId == 0) {
                \EasySwoole\Component\Timer::getInstance()->loop(5 * 1000, function () {
                    // 从数据库，或者是redis中，去获取下个就近10秒内需要执行的任务
                    AuthTimer::run();
                });
                \EasySwoole\Component\Timer::getInstance()->loop(5 * 1000, function () {
                    // 从数据库，或者是redis中，去获取下个就近10秒内需要执行的任务
                    UcsTimer::run();
                });
            }
        });
    }
}