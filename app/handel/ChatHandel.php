<?php

namespace app\handel;
use pinst\handel\WebSocketHandel;
use pinst\utils\Console;

class ChatHandel extends WebSocketHandel
{
    public function afterConnect(\swoole_server $server, $client_id, $from_id){
    }

    public function onMessage($server, $connection, $data){
        if(APP_DEBUG){
            Console::println("receive client[$connection->getId()] message,message data is:\n<<<\n{$data}\n>>>");
        }
        $data = json_decode($data,true);
        if(empty($data)){
            return false;
        }
        $action = $data['action'];
        switch($action){
            case "login":{
                $this->doLogin($connection,$data);
            }
            break;
            case "message":{
                \Pinst::$app->server->broadcast(
                    $this->buildMessage($data['data'],"message",$connection->getProperty("name")),
                    $connection->getId()
                );
            }
            break;
            case "":{

            }
            break;
            default:
                break;
        }
    }

    protected function doLogin($connection,$data){
        $connection->setProperty("name",$data['data']);
        $connection->send($this->buildMessage("ok","login"));
        \Pinst::$app->server->broadcast(
            $this->buildMessage("{$data['data']}登陆系统","notify"),
            $connection->getId()
        );
    }

    public function afterClose($server, $client_id, $from_id)
    {
        $connection = $this->getConnection($client_id);
        $name = $connection->getProperty("name");
        \Pinst::$app->server->broadcast($this->buildMessage("{$name}退出了聊天室","notify"),$client_id);
    }

    public function buildMessage($data,$action=null,$target=null){
        return json_encode([
            'action'=>$action,
            'data'=>$data,
            'target'=>$target
        ]);
    }
}