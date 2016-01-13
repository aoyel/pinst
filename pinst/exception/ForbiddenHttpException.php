<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-6
 * Time: 下午3:21
 */

namespace pinst\exception;


class ForbiddenHttpException extends HttpException
{
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}