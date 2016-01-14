<?php

namespace pinst\query;

interface QueryInterface
{
    /**
     * push data to query
     * @param $key query key
     * @param $value push value
     * @return mixed
     */
    static public function push($key,$value);

    /**
     * pop data from query
     * @param $key query key
     * @return mixed
     */
    static public function pop($key);

    /**
     * get query length
     * @param $key query key
     * @return mixed
     */
    static public function length($key);
}