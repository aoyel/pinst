<?php

namespace app\handel;

use pinst\handel\WebSocketHandel;



class MsgHandel extends WebSocketHandel
{
    public function onMessage($server, $connection, $data)
    {
        var_dump($connection);
        $connection->send("You message is:{$data}");
    }
}