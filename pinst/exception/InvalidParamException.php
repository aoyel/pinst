<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-6
 * Time: 下午3:19
 */

namespace pinst\exception;


use pinst\base\Exception;

class InvalidParamException extends Exception
{
    public function getName()
    {
        return 'Invalid Parameter';
    }
}