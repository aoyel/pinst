<?php

namespace app\handel;
use pinst\handel\Handel;
use pinst\utils\Console;


class MsgHandel extends Handel
{
    public function onMessage($server, $connection, $data)
    {

        $this->sendHttpRespone($connection);

    }

    public function sendHttpRespone($connection){
        $connection->send("HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: nginx\r\n\r\nhello\r\n\r\n");
        $connection->close();
    }
}