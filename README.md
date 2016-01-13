#介绍
Pinst是一个基于swoole开发的PHP socket框架，支持websocket，高扩张性！

#使用

##安装
>git clone https://github.com/aoyel/pinst.git

>./run

##教程
进入app目录
config 应用程序的配置目录
handel 消息处理器目录
runtime 程序运行缓存数据目录

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
        'dispatch'=>[                                  # dispatch component
            'class'=>'\pinst\server\Dispatch',
            'handelMap'=>[
                "default"=>"\app\handel\DefaultHandel",
            ]
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

参考YII的组件配置模式，通过配置应用程序组件你可以轻松的实现组件重载,同时组件可以随意配置添加自己的组件，自需要继承`\pinst\base\Object`即可
使用组件可以通过
>\Pinst::$app->component;
来进行使用，组件都是用到时候才会进行加载


```

##简单使用
```
<?php

namespace app\handel;
use pinst\handel\BaseHandel;
class DefaultHandel extends BaseHandel
{
    public function onMessage($server, $client_id, $from_id, $data){
        $this->send($client_id,"HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: nginx\r\n\r\nhello\r\n\r\n");
        $this->close($client_id);
    }
}



```
####高级使用
绑定多个Handel
>\Pinst::attachHandel($name,$handel_class);
取消Handel的绑定
>\Pinst::detachHandel($name);

####composer安装其他PHP库
pinst默认启动会引入vendor/aotoload.php，需要其他依赖库只需要 composer require 'some lib' 即可




