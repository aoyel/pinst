<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-12
 * Time: 下午4:30
 */

namespace pinst\utils;


use pinst\base\Object;

class Http extends Object
{
    static public function  isHttpRequest($data){
        if(preg_match("/(GET|POST) (.*) HTTP\//mUi",$data)){
            return true;
        }
        return false;
    }
}