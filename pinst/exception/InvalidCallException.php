<?php
namespace pinst\exception;
use pinst\base\Exception;

/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-6
 * Time: 下午3:12
 */
class InvalidCallException extends Exception
{
    public function getName()
    {
        return 'Invalid Call';
    }
}