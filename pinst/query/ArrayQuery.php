<?php


namespace pinst\query;


class ArrayQuery implements QueryInterface
{
    static protected $data = [];

    static public function push($key,$value)
    {
        if(!isset(self::$data[$key])){
            self::$data[$key] = [];
        }
        array_push(self::$data[$key],$value);
    }

    static public function pop($key)
    {
        if(isset(self::$data[$key]) && !empty(self::$data[$key])){
            return array_pop(self::$data[$key]);
        }
        return null;
    }

    static public function length($key)
    {
        if(isset(self::$data[$key])){
            return count(self::$data[$key]);
        }
        return 0;
    }

}