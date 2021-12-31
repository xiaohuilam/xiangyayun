<?php

use EasySwoole\Log\LoggerInterface;

return [
    'SERVER_NAME' => "HiCloud",
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
        "EMAIL" => [
            "HOST" => "smtp.qq.com",
            "PORT" => "25",
            "SSL" => false,
            "USERNAME" => "1015653737@qq.com",
            "PASSWORD" => "pdjmwfyquvcjbcfh",
            "FROM" => "1015653737@qq.com"
        ],
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
                "APP_ID" => "wxa94cec11d95af0e9",
                "MINI_APP_ID" => "",
                "MCH_ID" => "1565047471",
                "KEY" => "JKHJKAHKJfhkhjskafhjahdsjkufhadj",
                "NOTIFY_URL" => "http://api.hiy.cn/notify/pay/wechat",
                "API_CLIENT_CERT" => "-----BEGIN CERTIFICATE-----
MIID7DCCAtSgAwIBAgIUbyRvBLMQiNJsk1OV0b34ptTMyAowDQYJKoZIhvcNAQEL
BQAwXjELMAkGA1UEBhMCQ04xEzARBgNVBAoTClRlbnBheS5jb20xHTAbBgNVBAsT
FFRlbnBheS5jb20gQ0EgQ2VudGVyMRswGQYDVQQDExJUZW5wYXkuY29tIFJvb3Qg
Q0EwHhcNMjExMTE3MDQzMzU2WhcNMjYxMTE2MDQzMzU2WjB+MRMwEQYDVQQDDAox
NTY1MDQ3NDcxMRswGQYDVQQKDBLlvq7kv6HllYbmiLfns7vnu58xKjAoBgNVBAsM
IeWbm+W3neixoeeJmeS6keiuoeeul+aciemZkOWFrOWPuDELMAkGA1UEBgwCQ04x
ETAPBgNVBAcMCFNoZW5aaGVuMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKC
AQEA4Y5uY1OCrt2J2ihUS22sYwxgxyPMLuChun8jb9N1rOtsUizYMAy0GxYpaq8O
0ZpYECAWMGNs1OevxP4Ye7L8InzGHE0ew4WnZ4dpMqQ+ljLR9BtTvfj8HLE4X1bD
Gs/fmdzVz7NPJfln5cdZ/TkU16wCvAf3CDMNPydH3zm66QzC7v7psAHn9AL4k7ee
48+1W3WNHM3cQ4InEiJeTftSHaYP4os7Jg0fUmljbqdfUjPH8/oDt0iLD1j3/sCr
1JLNuuVKjudUAibWGhregEKe0CiwoZMrzIb2LRBDg96od/UcsjgidjWnIyXXXreb
VbTsoJZKOE9yNx+iAdupvEBDLwIDAQABo4GBMH8wCQYDVR0TBAIwADALBgNVHQ8E
BAMCBPAwZQYDVR0fBF4wXDBaoFigVoZUaHR0cDovL2V2Y2EuaXRydXMuY29tLmNu
L3B1YmxpYy9pdHJ1c2NybD9DQT0xQkQ0MjIwRTUwREJDMDRCMDZBRDM5NzU0OTg0
NkMwMUMzRThFQkQyMA0GCSqGSIb3DQEBCwUAA4IBAQCfA3CA9/Yk0onBlYC1tQx9
dwWEdmljXslqxzOprESq/L0TTc3GLjZpQP5H8kzuBmRzbvYim9EHyrtRBWu/aqIB
tliYylbJFs47ISm9EapaPra6p6xkThq8Q71PQKFrGczUFUiibg5zvrmXk8sIsrZb
FV+Mdty0ltmVBIZGMH0GuzeBK/HvOCab46GyrzWpZw/4KZHQ0OheF45HwxIxcPpE
lYg1qA1uk/b/7fksxjHa7Y4Ao2l4mzJr7e3lHHSaeQFtabY1tnkrOQzvgocHtTfc
dgQwcoF8qHpJnuoxTUnMW+PgHRZkBsJyBYpK4T/cUY1OQVvi72JXMHQlxIBhtTUN
-----END CERTIFICATE-----",
                "API_CLIENT_KEY" => "-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDhjm5jU4Ku3Yna
KFRLbaxjDGDHI8wu4KG6fyNv03Ws62xSLNgwDLQbFilqrw7RmlgQIBYwY2zU56/E
/hh7svwifMYcTR7Dhadnh2kypD6WMtH0G1O9+PwcsThfVsMaz9+Z3NXPs08l+Wfl
x1n9ORTXrAK8B/cIMw0/J0ffObrpDMLu/umwAef0AviTt57jz7VbdY0czdxDgicS
Il5N+1Idpg/iizsmDR9SaWNup19SM8fz+gO3SIsPWPf+wKvUks265UqO51QCJtYa
Gt6AQp7QKLChkyvMhvYtEEOD3qh39RyyOCJ2NacjJddet5tVtOyglko4T3I3H6IB
26m8QEMvAgMBAAECggEBANmxMfFDZFmJD8cyLUkvbLWb+Lu6XRLRUsZAdt8y49wk
y8Lz7SNS33FVDlwc4NyDboMBtOi2zQ8fHZGu+8pHkPloG3ytIgfrMwrEsk9iHdWw
7tn+lfBAInM+x/2cK2nxPwmtDd8MXFN2R0SKBtxS/z64kVsRBOcw/pP5QXC8sxzA
l6Yy29p2PiL4czt2z3+bmEvMlBCJLBvolccjBDjc82z4seycKevvKj1hUzbTKiKK
VqevzMRpgHuIGbvhMq1Njg5E1pHXUAjZpsjPaJ0DWm3wmdPm/BsZxzpPl9scvwBv
zTxn3dosGVwIZtrj9VI2dG6R+cgL2qrWuKCTSBls2sECgYEA/yBkeWyMDq6wYHP3
uV2AKz0rcMfMLsUK3JBUi9FT/5i7xPXAO0h+Dg20Z4Spstlejh8WeorXi85Oam3x
el1IfPIDYh4RbMnP0E/nOjUUNaEcFyyBPa9Q5gM3nh1+cVvFsG6wPCoptNmeTDQL
r0Bz3eujDCSMDMEqAZJTpyDG7GkCgYEA4lQfKmKycUhW7I0VUKeyT29UQwk0Iwoc
fsAxqeasCEBiwtVLQaAqO3SMP+RUzNfxTzFy47PrcttnKpW8NYkY9Q6tugw+jxG0
pXQ+jCuIEajggD+olj8vSWAyvxjwvnabWX/pgutffBK0prk9EuVeCqfHUdor7243
JYevmzQ3H9cCgYAeQF4Jk9hrqUUausWGAlM/TUqIXfiVFyI5kxejWJfoN9kT+rvn
i6n+yps4px9kKKgwm/kTYME8P6NGtXCrvMHqptvF16DjUG7G2aKmYULbNRZanRla
Eh66l2kR45dpo6MmT4mwKKO3YRKHIKi+CBt1FgrIVtEQdhsgApTOvXE4+QKBgE3o
51GP6B1W6ZVEe7HoCFmP2VG6OVhWgrifMHlsDoxInbaz9dQBbohI9n6H+ykIrOi1
/PalWMeQ/1KJeOB08UJqayNAU8isL6NFAML/uTdbCu0a/M27smtv0gg2baki6xdE
EP4gSB5N0iISYhO/IUBJwTNMBxPEvPcOXkTvFIrBAoGAMpOaWFkA1IpwjT+FasHd
HnuDTkhYSZg8oNTxM81qEWll/4v43yjTPGwXcDfqmMnaiYmBmGmaIHCEy/fMS0wM
U9jfj53+kJ/dtcNYCnTrkDRGb4Gtysg/hfLLh+swagywenxpJkBGGZSlIEY+RPja
rodnjf927gnA85Fp2eigUn8=
-----END PRIVATE KEY-----",
            ],
            "ALIPAY" => [
                "APP_ID" => "2021001165608852",
                "PUBLIC_KEY" => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAk+gOwn8lZtUcxFRymxKI9frXnuUWHiqtO1hP/a2gl89K3nDWrqjLnTR54D+6CFGWIaOHABsBCwj4c5y5Renwynjk7/1YBMJjJNW7cuiMbPM4aZYGPdI5DyCtsZBIBZrwejMlEwYfhwKVTYZlGmZ/2R8eA5PoRkV9DQpaeo08N0estR7FXeF5qlSQq7cqehYTwewoG12U9/GizhovT83zj2jgVd/xLLl9h2Spgk4Vr4lJgv7UhZ5pAAKRcl0gJr3pCwKFiB7wTs8odDlJJERM1KIjF1m8+0f7ccFuTrMuJdx3KXfxVa4ts9+Cj8bUNGTUfUT0N+aQXe+YOzlyDiYJSQIDAQAB",
                "PRIVATE_KEY" => "MIIEowIBAAKCAQEAk+gOwn8lZtUcxFRymxKI9frXnuUWHiqtO1hP/a2gl89K3nDWrqjLnTR54D+6CFGWIaOHABsBCwj4c5y5Renwynjk7/1YBMJjJNW7cuiMbPM4aZYGPdI5DyCtsZBIBZrwejMlEwYfhwKVTYZlGmZ/2R8eA5PoRkV9DQpaeo08N0estR7FXeF5qlSQq7cqehYTwewoG12U9/GizhovT83zj2jgVd/xLLl9h2Spgk4Vr4lJgv7UhZ5pAAKRcl0gJr3pCwKFiB7wTs8odDlJJERM1KIjF1m8+0f7ccFuTrMuJdx3KXfxVa4ts9+Cj8bUNGTUfUT0N+aQXe+YOzlyDiYJSQIDAQABAoIBAAkxLeGml0N50SzedrTuhPaMnWPxpts/Gb8LcQU6CjYFGwkSCDWYpfbiMQTf0Qb9UxBKInS+OOVfrFk7D0SqEl3y/39uxk6dah367ohorXmD8CiXu1GSRBuNk13qsp8uju15Sj+RbNouLetAg/4NCrsKtQnR4mijnu71isP9DRX+VQ6wGKwMyYbzn45n8ug3cPMoIeZFlr82w3xqfsJGRhlMbfhMYQdWeOB12xK7VyH7LNOHXmmrEe19ulCC6o8Oqn/oAASdD05qUhDdbnJTApEL5RtfbD5hgXNg0AKfymxA8eDTQE/W24dRJ410UiId4CFKBqc9YSuDI5u/Wm3D3JECgYEA1rJFBfom5gu80ewUIY3yRaI7XjIe/cefKUbfcFWUvJqdA3NyQJaHaKZ+N0GdRqioRMo4V7jZSShkUoCsGA7/LoqG9B28e0QhmTX8xziEc2ANio2PqdfoLL8YAXVgFuf7119rGmpB2JyM+vwjlwsbj40UD7z4ekRBGDTidIbVPW0CgYEAsFxoBiccFo7+6u6ulIIl3m2PaQeCSDHsm+XC7/RQMgSKCfeWhg2VsKHgIKH2BK7dktnS34odlXe56dOB80pDwNBmdEat4sDJT2+LbmOUzVI71n6Rd+ddjie9g4LqD4IMvRfanIYH3sp5ISaom3nc9UsRB5kpBEHDK88YMG6Ync0CgYEAoFailcQsKMG7Ukss/aI0vSxbig2Ed6MNipYTaKGBYxlVCa6+NN42YkF1IW8vNgXfJQCg82JpY+l2gub1n0IT8X4jK0zJ5oULUpCvJ1leEYw6kK8IC5/jFQfhtbUi/fhibIYmRqP0aQEBiK+lJLh27M4/nUFTkHxI4Psd5EiOGEECgYBDbcE0AQYDNf8PEdzb2PrjlR4MuRs9wSDG5kzm85Ep3oRslSYO8Oi3lNydfW8TkUwUFoZCg4sWF6WUWhLIUy+ea8+QS2m2VaFiZrJ3rUk6S2OnedLidipV7KnPu2EENuPp9FHYIIKn0uAHJcG3WBt8CYxkTFHbV81oVZDZC5/8TQKBgBe6LW9gIu9xshfhUlwP/r244dC5qTE0dtTIw0+wOgy9hrioEYSdXhS5tobLxrPWwJHnR18vqfehpz9YLfP0CmSGfOtsywa48QbnkreYZfU/gprdBXRKhANiNs8KOcAgxH2hGRb2xeI6U063XEnXTZX8xrki8f9SmJmup28tHHgG",
                "CERT_MODE" => true,//是否使用证书签名
                "ALIPAY_CERT_PUBLIC_KEY_PATH" => "/cert/alipayCertPublicKey_RSA2.crt",//支付宝公钥证书路径
                "ALIPAY_ROOT_CERT_PATH" => "/cert/alipayRootCert.crt",//支付宝根证书路径
                "MERCHANT_CERT_PATH" => "/cert/appCertPublicKey_2021001165608852.crt",//商户应用公钥证书
                "RETURN_URL" => "http://www.hiy.cn/#/finance/recharge/log",
                "NOTIFY_URL" => "http://api.hiy.cn/notify/pay/alipay",
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
        ],
        "WORK_WECHAT" => [
            // 企业微信后台的 企业 ID
            'corpId' => 'ww5850165be15617b4',
            // 企业微信后台的 secret
            'corpSecret' => 'CbnVEqHPeJvL0284RNyYNM3TYbCTwAHcXPvumDNKWaM',
            // 企业微信后台的 agentid
            'agentId' => 1000002,
            // server config
            'token' => '0wCyJVwU',
            'aesKey' => 'L9zK61RVjlS0uAV8FDG6Sd5qankRUXpmMXfbyuawipi',
        ],
        "REDIS" => [
            'host' => '127.0.0.1', // 服务端地址 默认为 '127.0.0.1'
            'port' => 6379, // 端口 默认为 6379
            'auth' => 'Chanmir1314', // 密码 默认为 不设置
            'db' => 0, // 默认为 0 号库
        ],
    ],
    'TEMP_DIR' => null
];
