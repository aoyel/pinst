<?php

namespace app\handel;

use pinst\handel\WebSocketHandel;



class MsgHandel extends WebSocketHandel
{
    public function onMessage($server, $connection, $data)
    {

        $connection->send("You message is:{$data}");
    }
}