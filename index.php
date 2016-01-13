<?php



define("APP_DEBUG",true);
define("APP_PATH",__DIR__);

if(php_sapi_name() !== "cli"){
    $content = file_get_contents(APP_PATH."/assets/index.html");
    $server_ip = getenv('SERVER_ADDR');
    $content = str_replace("#SERVER_IP#","127.0.0.1",$content);
    echo $content;
}

require "./pinst/Pinst.php";
$autoload = APP_PATH."/vendor/autoload.php";
if(file_exists($autoload)){
    require $autoload;
}
$config = require APP_PATH."/app/config/default.php";
(new \pinst\base\Application($config))->run();
