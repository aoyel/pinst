<?php
namespace app\handel;
use pinst\handel\Handel;

class DefaultHandel extends Handel
{

    public function onMessage($server, $connection, $data)
    {
        $connection->send("HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: nginx\r\n\r\n1\r\n\r\n");
        $connection->close();
    }

}