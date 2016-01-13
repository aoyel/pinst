<?php
define("PINST_PATH", __DIR__);

require PINST_PATH.'/BasePinst.php';


class Pinst  extends \pinst\BasePinst{
}

if(!defined("APP_DEBUG")){
    define("APP_DEBUG",true);
}

Pinst::addNamespaceMap("pinst", PINST_PATH);
spl_autoload_register(['Pinst', 'autoload'], true, true);