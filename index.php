<?php

define("APP_DEBUG",true);
define("APP_PATH",__DIR__);

require "./pinst/Pinst.php";
$autoload = APP_PATH."/vendor/autoload.php";
if(file_exists($autoload)){
    require $autoload;
}
$config = require APP_PATH."/app/config/default.php";

(new \pinst\base\Application($config))->run();
