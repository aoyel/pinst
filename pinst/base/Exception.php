<?php

namespace pinst\base;

class Exception extends \Exception
{
    public function getName(){
        return "Exception";
    }
}