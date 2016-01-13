<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-6
 * Time: 下午3:18
 */

namespace pinst\exception;


use pinst\base\Exception;

class InvalidConfigException extends Exception
{
    public function getName()
    {
        return 'Invalid Configuration';
    }
}