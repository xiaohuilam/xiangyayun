<?php

use EasySwoole\Log\LoggerInterface;

return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9502,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time' => 3
        ],
        'TASK' => [
            'workerNum' => 4,
            'maxRunningNum' => 128,
            'timeout' => 15
        ]
    ],
    "LOG" => [
        'dir' => null,
        'level' => LoggerInterface::LOG_LEVEL_DEBUG,
        'handler' => null,
        'logConsole' => true,
        'displayConsole' => true,
        'ignoreCategory' => []
    ],
    "CLOUD" => [
        'is_cdn' => false,
        'real_ip_header' => '',
        'SMS' => [
            'TENCENTCLOUD' => [
                'APP_ID' => '1400595436',
                'SECRET_ID' => 'AKIDEH5csNgiTzEF1Xprd9RTFm46sOfhU0FE',
                'SECRET_KEY' => 'kESQ8ZjgPr3yd190cx3uWt0tWu2uWt1u',
                'SIGN_NAME' => '象牙云'
            ],
        ],
        "WECHAT" => [
            'appId' => 'wxa94cec11d95af0e9',
            // 微信公众平台后台配置的 Token
            'token' => 'ARSLMBTKYB5MVYKUBTJY7KTYJXHHGXO4',
            // 微信公众平台后台配置的 EncodingAESKey
            'aesKey' => 'PT2Jougys5BTH9rzSRbPuk6BC1cBO6nxeL8FzRuyG1C',
            // 微信公众平台后台配置的 AppSecret
            'appSecret' => 'ccf45ae6fd2738b0f6c26fff7f21516b',
        ]
    ],
    "REDIS" => [
        'host' => '127.0.0.1', // 服务端地址 默认为 '127.0.0.1'
        'port' => 6379, // 端口 默认为 6379
        'auth' => '', // 密码 默认为 不设置
        'db' => 0, // 默认为 0 号库
    ],
    'TEMP_DIR' => null
];
