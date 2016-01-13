<?php
/**
 * User: smile
 * Date: 16-1-11
 * Time: 下午7:31
 * Summary:
 */

namespace app\handel;

use pinst\handel\Handel;

class MsgHandel extends Handel
{

    public function onMessage($server, $connection, $from_id, $data)
    {
        $this->sendHttpRespone($connection);
    }

    public function sendHttpRespone($connection){
        $this->send($connection->getId(),"HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: nginx\r\n\r\nhello\r\n\r\n");
        $connection->close();
    }
}