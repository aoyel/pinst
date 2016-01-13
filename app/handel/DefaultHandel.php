<?php
namespace app\handel;
use pinst\handel\WebSocketHandel;

class DefaultHandel extends WebSocketHandel
{

    public function onMessage($server, $connection, $data)
    {
        if($data == "hello"){
            \Pinst::$app->server->sendToAll("hello");
        }elseif($data == 'y') {
            \Pinst::$app->server->broadcast("y",$connection->getId());
        }else{
            $connection->send("you message is:{$data}");
        }
    }

}