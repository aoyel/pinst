#介绍
Pinst是一个基于swoole开发的PHP socket框架，支持websocket，具有很强大的扩展性！

#使用

##安装
```
git clone https://github.com/aoyel/pinst.git
./run
```

##教程

###应用程序目录结构

```
├── config
│   └── default.php                     #默认配置文件
├── handel
│   ├── DefaultHandel.php               #默认处理器
│   └── MsgHandel.php
└── runtime
    ├── cache                           #缓存文件目录
    ├── logs                            #日志文件目录
    ├── msg                             #消息记录文件目录
    ├── server.log                      #服务日志
    └── server.pid                      #服务进程PID
```

###程序配置

```
<?php

$config = [
    'basePath'=>APP_PATH, #application base path
    'runtimePath'=>APP_PATH."/app/runtime",  # runtime 目录配置
    'server' => [
        'class' => '\pinst\server\Server',  # default server
        'host' => '0,0,0,0',                # bind ip
        'port' => 3927,                     # bind port
        'pid_file'=>APP_PATH."/app/runtime/server.pid", # master server pid file path
        'config' => [
            'worker_num' => 1,                          # worker process number
            'daemonize'=>true,                          # is daemonize
            'log_file' => APP_PATH . "/app/runtime/server.log" # server pid file
        ]
    ],
    'components' => [
        'cache' => [                                   # cache component
            'class' => '\pinst\cache\FileCache',
            'directoryLevel' => 1,
            'keyPrefix' => '__cache'
        ],
        'logger'=>[                                   # logger component
            'class'=>'\pinst\log\FileLogger',
        ],
        'db' => [                                     # db component
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

?>

```
参考YII的组件配置模式，通过配置应用程序组件你可以轻松的实现组件重载,同时组件可以随意配置添加自己的组件，自需要继承`\pinst\base\Object`即可
使用组件可以通过
```
\Pinst::$app->component;
```
来进行使用，组件都是用到时候才会进行加载

###简单使用
```
<?php

namespace app\handel;
use pinst\handel\BaseHandel;
class DefaultHandel extends BaseHandel
{
    public function onMessage($server, $connection, $from_id, $data){
        $connection->send("HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: nginx\r\n\r\nhello\r\n\r\n");
        $connection->close($client_id);
    }
}
```



###composer安装其他PHP库
pinst默认启动会引入`vendor/aotoload.php`，需要其他依赖库只需要 `composer require 'some lib'` 即可


###压力测试

```
ab -n 1000 -c 100 http://127.0.0.1:8080/

Server Software:        nginx
Server Hostname:        127.0.0.1
Server Port:            8080

Document Path:          /
Document Length:        9 bytes

Concurrency Level:      100
Time taken for tests:   0.032 seconds
Complete requests:      1000
Failed requests:        0
Total transferred:      67000 bytes
HTML transferred:       9000 bytes
Requests per second:    31091.63 [#/sec] (mean)
Time per request:       3.216 [ms] (mean)
Time per request:       0.032 [ms] (mean, across all concurrent requests)
Transfer rate:          2034.32 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    1   0.5      1       3
Processing:     1    2   0.5      2       4
Waiting:        0    2   0.5      2       3
Total:          2    3   0.5      3       5

Percentage of the requests served within a certain time (ms)
  50%      3
  66%      3
  75%      3
  80%      3
  90%      4
  95%      4
  98%      4
  99%      4
 100%      5 (longest request)

```




