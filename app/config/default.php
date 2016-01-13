<?php
$config = [
    'basePath'=>APP_PATH,
    'timezone'=>'Asia/Shanghai',
    'runtimePath'=>APP_PATH."/app/runtime",
    'server' => [
        'class' => '\pinst\server\Server',
        'host' => '0,0,0,0',
        'port' => 3927,
        'pid_file'=>APP_PATH."/app/runtime/server.pid",
        'config' => [
            'worker_num' => 1,
            'daemonize'=>false,
            'log_file' => APP_PATH . "/app/runtime/server.log"
        ]
    ],
    'components' => [
        'cache' => [
            'class' => '\pinst\cache\FileCache',
            'directoryLevel' => 1,
            'keyPrefix' => '__cache'
        ],
        'dispatch'=>[
            'class'=>'\pinst\server\Dispatch',
            'handelMap'=>[
                "default"=>"\app\handel\MsgHandel",
            ]
        ],
        'logger'=>[
            'class'=>'\pinst\log\FileLogger',
        ],
        'db' => [
            'class' => '\pinst\db\schema\Mysqli',
            'host' => 'localhost',
            'port' => 3306,
            'username'=>'root',
            'password'=>'root',
            'database'=>'angel',
            'charset'=>'utf-8',
            'prefix'=>'tbl_'
        ]
    ]
];
return $config;