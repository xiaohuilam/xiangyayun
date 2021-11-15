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
        "SYSTEM" => [
            "APP_NAME" => "象牙云",
            "APP_URL" => "https://hiy.cn",
        ],
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
        "PAY" => [
            "WECHAT" => [
                "APP_ID" => "",
                "MINI_APP_ID" => "",
                "MCH_ID" => "",
                "KEY" => "",
                "NOTIFY_URL" => "",
                "API_CLIENT_CERT" => "",
                "API_CLIENT_KEY" => "",
            ],
            "ALIPAY" => [
                "APP_ID" => "2021001165608852",
                "PUBLIC_KEY" => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAk+gOwn8lZtUcxFRymxKI9frXnuUWHiqtO1hP/a2gl89K3nDWrqjLnTR54D+6CFGWIaOHABsBCwj4c5y5Renwynjk7/1YBMJjJNW7cuiMbPM4aZYGPdI5DyCtsZBIBZrwejMlEwYfhwKVTYZlGmZ/2R8eA5PoRkV9DQpaeo08N0estR7FXeF5qlSQq7cqehYTwewoG12U9/GizhovT83zj2jgVd/xLLl9h2Spgk4Vr4lJgv7UhZ5pAAKRcl0gJr3pCwKFiB7wTs8odDlJJERM1KIjF1m8+0f7ccFuTrMuJdx3KXfxVa4ts9+Cj8bUNGTUfUT0N+aQXe+YOzlyDiYJSQIDAQAB",
                "PRIVATE_KEY" => "MIIEowIBAAKCAQEAk+gOwn8lZtUcxFRymxKI9frXnuUWHiqtO1hP/a2gl89K3nDWrqjLnTR54D+6CFGWIaOHABsBCwj4c5y5Renwynjk7/1YBMJjJNW7cuiMbPM4aZYGPdI5DyCtsZBIBZrwejMlEwYfhwKVTYZlGmZ/2R8eA5PoRkV9DQpaeo08N0estR7FXeF5qlSQq7cqehYTwewoG12U9/GizhovT83zj2jgVd/xLLl9h2Spgk4Vr4lJgv7UhZ5pAAKRcl0gJr3pCwKFiB7wTs8odDlJJERM1KIjF1m8+0f7ccFuTrMuJdx3KXfxVa4ts9+Cj8bUNGTUfUT0N+aQXe+YOzlyDiYJSQIDAQABAoIBAAkxLeGml0N50SzedrTuhPaMnWPxpts/Gb8LcQU6CjYFGwkSCDWYpfbiMQTf0Qb9UxBKInS+OOVfrFk7D0SqEl3y/39uxk6dah367ohorXmD8CiXu1GSRBuNk13qsp8uju15Sj+RbNouLetAg/4NCrsKtQnR4mijnu71isP9DRX+VQ6wGKwMyYbzn45n8ug3cPMoIeZFlr82w3xqfsJGRhlMbfhMYQdWeOB12xK7VyH7LNOHXmmrEe19ulCC6o8Oqn/oAASdD05qUhDdbnJTApEL5RtfbD5hgXNg0AKfymxA8eDTQE/W24dRJ410UiId4CFKBqc9YSuDI5u/Wm3D3JECgYEA1rJFBfom5gu80ewUIY3yRaI7XjIe/cefKUbfcFWUvJqdA3NyQJaHaKZ+N0GdRqioRMo4V7jZSShkUoCsGA7/LoqG9B28e0QhmTX8xziEc2ANio2PqdfoLL8YAXVgFuf7119rGmpB2JyM+vwjlwsbj40UD7z4ekRBGDTidIbVPW0CgYEAsFxoBiccFo7+6u6ulIIl3m2PaQeCSDHsm+XC7/RQMgSKCfeWhg2VsKHgIKH2BK7dktnS34odlXe56dOB80pDwNBmdEat4sDJT2+LbmOUzVI71n6Rd+ddjie9g4LqD4IMvRfanIYH3sp5ISaom3nc9UsRB5kpBEHDK88YMG6Ync0CgYEAoFailcQsKMG7Ukss/aI0vSxbig2Ed6MNipYTaKGBYxlVCa6+NN42YkF1IW8vNgXfJQCg82JpY+l2gub1n0IT8X4jK0zJ5oULUpCvJ1leEYw6kK8IC5/jFQfhtbUi/fhibIYmRqP0aQEBiK+lJLh27M4/nUFTkHxI4Psd5EiOGEECgYBDbcE0AQYDNf8PEdzb2PrjlR4MuRs9wSDG5kzm85Ep3oRslSYO8Oi3lNydfW8TkUwUFoZCg4sWF6WUWhLIUy+ea8+QS2m2VaFiZrJ3rUk6S2OnedLidipV7KnPu2EENuPp9FHYIIKn0uAHJcG3WBt8CYxkTFHbV81oVZDZC5/8TQKBgBe6LW9gIu9xshfhUlwP/r244dC5qTE0dtTIw0+wOgy9hrioEYSdXhS5tobLxrPWwJHnR18vqfehpz9YLfP0CmSGfOtsywa48QbnkreYZfU/gprdBXRKhANiNs8KOcAgxH2hGRb2xeI6U063XEnXTZX8xrki8f9SmJmup28tHHgG",
                "CERT_MODE" => true,//是否使用证书签名
                "ALIPAY_CERT_PUBLIC_KEY_PATH" => "/cert/alipayCertPublicKey_RSA2.crt",//支付宝公钥证书路径
                "ALIPAY_ROOT_CERT_PATH" => "/cert/alipayRootCert.crt",//支付宝根证书路径
                "MERCHANT_CERT_PATH" => "/cert/appCertPublicKey_2021001165608852.crt",//商户应用公钥证书
                "RETURN_URL" => "https://notify.hiy.cn/notify/alipay",
                "NOTIFY_URL" => "https://www.hiy.cn/user/recharge",
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
