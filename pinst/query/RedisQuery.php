<?php
/**
 *
 * User: smile
 * Date: 16-1-14
 * Time: 上午11:15
 *
 */

namespace pinst\query;


class RedisQuery implements QueryInterface
{
    /**
     * @var \Redis
     */
    static protected $redis = null;

    static protected function instance(){
        if(self::$redis == null){
            self::$redis = new \Redis();
        }
        return self::$redis;
    }

    static public function push($key, $value)
    {
        $redis = self::instance();
        if($redis)
            self::$redis->lPush($key,$value);
    }

    static public function pop($key)
    {
        $redis = self::instance();
        if($redis)
            self::$redis->lPop($key);
    }

    static public function length($key)
    {
        $redis = self::instance();
        if($redis)
            self::$redis->lLen($key);
    }
}