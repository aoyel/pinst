<?php

namespace pinst\utils;


use pinst\base\Object;

class Parser extends Object
{

    public function parseHttpHeader($string){
        $header = array();
        $header[0] = array();
        $meta = &$header[0];
        $parts = explode("\r\n\r\n", $string, 2);
        $headerLines = explode("\r\n", $parts[0]);
        list($meta['method'], $meta['uri'], $meta['protocol']) = explode(' ', $headerLines[0], 3);
        if (empty($meta['method']) or empty($meta['uri']) or empty($meta['protocol']))
        {
            return false;
        }
        unset($headerLines[0]);
        $header = array_merge($header,$this->parseHttpHeaderLine($headerLines));
        return $header;
    }

    public function parseHttpHeaderLine($line){
        if (is_string($line)){
            $headerLines = explode("\r\n", $line);
        }
        $header = array();
        foreach ($line as $_h){
            $_h = trim($_h);
            if (empty($_h)) continue;
            $_r = explode(':', $_h, 2);
            $key = $_r[0];
            $value = isset($_r[1])?$_r[1]:'';
            $header[trim($key)] = trim($value);
        }
        return $header;
    }


}