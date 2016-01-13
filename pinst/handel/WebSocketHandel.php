<?php

namespace pinst\handel;


use pinst\utils\Console;

class WebSocketHandel extends BaseHandel
{

    protected $max_frame_size = 2097152;
    const GUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";


    public function beforeReceive(\swoole_server $server, $connection, $from_id, &$data)
    {
        if($connection->getProperty("isHandShake") == false){
            $this->handShake($connection,$data);
            return false;
        }
        $data = $this->decode($connection,$data);
        if($data == false){
            return false;
        }
        return true;
    }

    protected function handShake($connection,$data){
        $key = null;
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)) {
            $key = $match[1];
        }
        $acceptKey = base64_encode(sha1($key.self::GUID,true));
        $headerString = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept:{$acceptKey}\r\n\r\n";
        $connection->setProperty("isHandShake",true);
        return $connection->send($headerString);
    }

    protected function decode($connection,$buffer){
        $id = $connection->getId();
        $length = ord($buffer[1]) & 127;
        $firstByte = ord($buffer[0]);
        $is_fin_frame = $firstByte>>7;
        $flag = $firstByte & 0xf;

        switch($flag)
        {
            case 0x0:
                break;
            case 0x1: // text data
                break;
            case 0x2:  // binary data
                break;
            case 0x8: // close package
                $connection->close();
                return false;
            case 0x9: // ping package
                $connection->send(pack('H*', '8a00'));
                break;
            case 0xa:  // pong package
                break;
            default : // error flag
                \Pinst::error("get error data package from client[$id]");
                $connection->close();
                return false;
        }

        if($length == 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        }
        elseif($length == 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        }
        else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        $text = '';
        for($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i%4];
        }
        return $text;
    }

    /**
     *
     * @param $client_id client id
     * @param $content message content
     * @return bool must be return true
     */
    public function beforeSend($client_id,&$content)
    {
        $connection = $this->getConnection($client_id);
        if($connection->getProperty("isHandShake")){
            $content = $this->encode($content);
        }
        return true;
    }

    /**
     * encode frame data
     * @param $message message data
     * @param string $messageType message data type
     * @return string return encode data
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