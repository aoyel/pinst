<?php

namespace pinst\handel;

use pinst\utils\Console;
use pinst\utils\Parser;

class WebSocketHandel extends BaseHandel
{
    protected $max_frame_size = 2097152;

    const GUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

    public function onConnect($server, $client_id, $from_id)
    {
        parent::onConnect($server, $client_id, $from_id);
    }


    protected function parseHttpHeader($data){
        $parser = new Parser();
        return $parser->parseHttpHeader($data);
    }

    /**
     * @param \pinst\model\Connection $connection
     * @param string $data
     * @return mixed|null
     */
    protected function filiterHttpRequest($connection,$data){
        $header = $this->parseHttpHeader($data);
        $md = array_shift($header);
        if(!$connection->getProperty("isHandShake") && isset($header['Sec-WebSocket-Version']) && $header['Sec-WebSocket-Version'] == 13 && !empty($header['Sec-WebSocket-Key'])){
            return $this->handShake($connection,$data);
        }else{
            if(is_callable([$this,"onHttpRequest"])){
                call_user_func_array([$this,"onHttpRequest"],[$connection,$data]);
            }
        }
        return null;
    }

    public function onReceive($server, $client_id, $from_id, $data)
    {
        $connection = $this->getConnection($client_id);
        if($this->isHttpRequest($data)){
            return $this->filiterHttpRequest($connection,$data);
        }
        if($connection->getProperty("isHandShake"))
            $data = $this->decode($data,$client_id);
        if($data !== false){
            parent::onReceive($server, $client_id, $from_id, $data);
        }
    }

    /**
     * @param \pinst\model\Connection $connection
     * @param string $data
     * @return mixed
     */
    protected function handShake($connection,$data){
        $key = null;
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)) {
            $key = $match[1];
        }
        $acceptKey = base64_encode(sha1($key.self::GUID,true));
        $header = [
            "Upgrade"=>"websocket",
            "Connection"=>"Upgrade",
            "Sec-WebSocket-Accept"=>$acceptKey
        ];
        $headerString = "HTTP/1.1 101 pinst Protocols\r\n";
        foreach($header as $k=>$v){
            $headerString .= "{$k}:{$v}\r\n";
        }
        $headerString .= "\r\n";
        return $this->send($connection->getId(),$headerString);
    }

    public function send($client_id, $message,$messageType = 'text'){
        $connection = $this->getConnection($client_id);
        if($connection->getProperty("isHandShake")){
            $message = $this->encode($message,$messageType);
        }
        return parent::send($client_id, $message);
    }

    public function broadcast($message,$client_id = null){
        $message = $this->encode($message);
        return parent::broadcast($message,$client_id);
    }

    /**
     * decode receive message
     * @param $dataset
     * @return string
     */
    protected function decode($dataset,$client_id){
        $length = ord($dataset[1]) & 127;
        $firstbyte = ord($dataset[0]);
        $is_fin_frame = $firstbyte>>7;
        $opcode = $firstbyte & 0xf;
        switch($opcode)
        {
            case 0x0:
                break;
            // 文本数据帧
            case 0x1:
                break;
            // 二进制数据帧
            case 0x2:
                break;
            // 关闭的包
            case 0x8:
                $this->close($client_id);
                return false;
            // ping的包
            case 0x9:
                $this->send($client_id,pack('H*', '8a00'), true);
                break;
            // pong的包
            case 0xa:
                break;
            // 错误的opcode
            default :{
                \Pinst::error("get error data package from client[$client_id]");
                $this->close($client_id);
            }
                return false;
        }
        if($length > $this->max_frame_size){
            if(APP_DEBUG){
                Console::println("message to long , receive data length:{$length}");
            }
            \Pinst::error("get message to long");
            return false;
        }
        if($length == 126) {
            $masks = substr($dataset, 4, 4);
            $data = substr($dataset, 8);
        }
        elseif($length == 127) {
            $masks = substr($dataset, 10, 4);
            $data = substr($dataset, 14);
        }
        else {
            $masks = substr($dataset, 2, 4);
            $data = substr($dataset, 6);
        }
        $text = '';
        for($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i%4];
        }
        return $text;
    }

    /**
     * encode send message
     * @param $message message content
     * @param string $messageType
     * @return string
     */
    protected function encode($message,$messageType='text'){
        switch ($messageType) {
            case 'continuous':
                $b1 = 0;
                break;
            case 'text':
                $b1 = 1;
                break;
            case 'binary':
                $b1 = 2;
                break;
            case 'close':
                $b1 = 8;
                break;
            case 'ping':
                $b1 = 9;
                break;
            case 'pong':
                $b1 = 10;
                break;
        }
        $b1 += 128;
        $length = strlen($message);
        $lengthField = "";
        if($length < 126) {
            $b2 = $length;
        } elseif($length <= 65536) {
            $b2 = 126;
            $hexLength = dechex($length);
            if(strlen($hexLength)%2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;
            for($i = $n; $i >= 0; $i=$i-2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while(strlen($lengthField) < 2) {
                $lengthField = chr(0) . $lengthField;
            }
        } else {
            $b2 = 127;
            $hexLength = dechex($length);
            if(strlen($hexLength) % 2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;
            for($i = $n; $i >= 0; $i = $i - 2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while(strlen($lengthField) < 8) {
                $lengthField = chr(0) . $lengthField;
            }
        }
        return chr($b1) . chr($b2) . $lengthField . $message;
    }
}