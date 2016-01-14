<?php

namespace pinst\handel;


use pinst\utils\Console;

class WebSocketHandel extends BaseHandel
{

    const TYPE_CONTINUATION = 0;
    const TYPE_TEXT         = 1;
    const TYPE_BINARY       = 2;
    const TYPE_RESERVED_3   = 3;
    const TYPE_RESERVED_4   = 4;
    const TYPE_RESERVED_5   = 5;
    const TYPE_RESERVED_6   = 6;
    const TYPE_RESERVED_7   = 7;
    const TYPE_CLOSE        = 8;
    const TYPE_PING         = 9;
    const TYPE_PONG         = 10;
    const TYPE_RESERVED_11  = 11;
    const TYPE_RESERVED_12  = 12;
    const TYPE_RESERVED_13  = 13;
    const TYPE_RESERVED_14  = 14;
    const TYPE_RESERVED_15  = 15;


    const CLOSE_NORMAL            = 1000;
    const CLOSE_GOING_AWAY        = 1001;
    const CLOSE_PROTOCOL_ERROR    = 1002;
    const CLOSE_DATA_INVALID      = 1003;
    const CLOSE_RESERVED          = 1004;
    const CLOSE_RESERVED_NONE     = 1005;
    const CLOSE_RESERVED_ABNORM   = 1006;
    const CLOSE_DATA_INCONSISTENT = 1007;
    const CLOSE_POLICY_VIOLATION  = 1008;
    const CLOSE_MESSAGE_TOO_BIG   = 1009;
    const CLOSE_EXTENSION_NEEDED  = 1010;
    const CLOSE_UNEXPECTED        = 1011;
    const CLOSE_RESERVED_TLS      = 1015;

    protected $max_frame_size = 2097152;
    const MAGIC_GUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";


    public function beforeReceive(\swoole_server $server, $connection, $from_id, &$data)
    {
        if(empty($data)){
            $this->close($connection,self::CLOSE_RESERVED_NONE);
        }
        if($connection->getProperty("isHandShake") == false){
            $this->handShake($connection,$data);
            return false;
        }

        $this->processFrame($server,$connection,$data);

        $this->afterReceive($server,$connection,$from_id,$data);
        return false;
    }

    protected function handShake($connection,$data){
        $key = null;
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)) {
            $key = $match[1];
        }else{
            $this->close($connection,self::CLOSE_PROTOCOL_ERROR);
        }
        $acceptKey = base64_encode(sha1($key.self::MAGIC_GUID, true));
        $headerString = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept:{$acceptKey}\r\n\r\n";
        $connection->setProperty("isHandShake",true);
        return $connection->send($headerString);
    }

    protected function close($connection,$status){
        $connection->send(pack('n',$status));
    }

    /**
     * process receive frame
     * @param \pinst\connection\Connection $connection
     * @param $buffer
     * @return bool|int
     */
    protected function processFrame($server,$connection,$buffer){
        $bufferLength = strlen($buffer);
        Console::info("buffer len:{$bufferLength}");

        $dataLength = ord($buffer[1]) & 127;
        $firstByte = ord($buffer[0]);
        $fin = $firstByte >>7 & 0x1;
        $mask = ($firstByte >> 7) & 0x1;
        $rsv1 = ($firstByte >> 6) & 0x1;
        $rsv2 = ($firstByte >> 5) & 0x1;
        $rsv3 = ($firstByte >> 4) & 0x1;

        $opcode = $firstByte & 0xf;
        switch($opcode){
            case self::TYPE_CONTINUATION:
                break;
            case self::TYPE_TEXT:
                break;
            case self::TYPE_BINARY:
                break;
            case self::TYPE_CLOSE:
                $this->close($connection,self::CLOSE_NORMAL);
                $connection->close();
                return false;
            case self::TYPE_PING:
                $connection->send(pack('H*', '8a00'));
                break;
            case self::TYPE_PONG:
                break;
            default :
                $this->close($connection,self::CLOSE_UNEXPECTED);
                return false;
        }
        $headerLength = 6;

        if($dataLength == 126){
            $headerLength = 8;
            if($headerLength > $bufferLength){
                return false;
            }
            $pack = unpack('ntotal_len', substr($buffer, 2, 2));
            $dataLength = $pack['total_len'];
        }else if($dataLength == 127){
            $headerLength = 14;
            if($headerLength > $bufferLength){
                return false;
            }
            $pack = unpack('N2', substr($buffer, 2, 8));
            $dataLength = $pack[1]*4294967296 + $pack[2];
        }

        $frameLength = $headerLength + $dataLength;

        if($frameLength == $bufferLength){
            $data = $this->decode($connection,$buffer);
            $this->onMessage($server,$connection,$data);
        }elseif($frameLength < $bufferLength){
            /**
             * get package to long
             */
            $data = substr($buffer,0,$frameLength);
            $data = $this->decode($connection,$buffer);
            $this->onMessage($server,$connection,$data);
            return $this->processFrame($server,$connection,substr($buffer, $frameLength));
        }else{
            /**
             * get package to short
             */
            Console::warning("get package to short");
        }
    }

    protected function decode($connection,$buffer){
        $id = $connection->getId();
        $length = ord($buffer[1]) & 127;
        $firstByte = ord($buffer[0]);
        $is_fin_frame = $firstByte>>7;
        $flag = $firstByte & 0xf;

        switch($flag)
        {
            case self::TYPE_CONTINUATION:
                break;
            case self::TYPE_TEXT: // text data
                break;
            case self::TYPE_BINARY:  // binary data
                break;
            case self::TYPE_CLOSE: // close package
                $this->close($connection,self::CLOSE_NORMAL);
                $connection->close();
                return false;
            case self::TYPE_PING: // ping package
                $connection->send(pack('H*', '8a00'));
                break;
            case self::TYPE_PONG:  // pong package
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