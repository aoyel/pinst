<?php

namespace pinst\handel;

use pinst\base\Exception;
use pinst\base\Object;
use pinst\model\BaseClient;
use pinst\model\Client;
use pinst\model\Connection;
use pinst\utils\Console;
use pinst\exception\InvalidCallException;

class BaseHandel extends Object
{
    protected $connections = [];

    /**
     * when work thread is start call this function
     * @param $server swoole_server object
     * @param $work_id work id
     */
    public function onStart($server,$work_id){
        if(method_exists($this,"afterStart")){
            call_user_func_array([$this,"afterStart"],[$server,$work_id]);
        }
    }

    public function beforeConnect($server, $client_id, $from_id){
        return true;
    }

    /**
     * when client is connection call this fucntion
     * @param $server swoole_server object
     * @param $client_id client id
     * @param $from_id form id
     */
    public function onConnect($server, $client_id, $from_id){
        if(!$this->beforeConnect($server,$client_id,$from_id)){
            $server->close($client_id);
            return;
        }
        if(APP_DEBUG){
            Console::info("have a new client[{$client_id}] connection");
        }
        $this->prepareConnection($client_id);
        return $this->afterConnect($server,$client_id,$from_id);
    }

    public function afterConnect($server, $client_id, $from_id){

    }

    /**
     *
     * @param $client_id
     */
    protected function prepareConnection($client_id){
        if(!isset($this->connections[$client_id])){
            $connection = new Connection();
            $clientInfo = $this->server->connection_info($client_id);
            foreach($clientInfo as $k=>$v){
                $connection->setProperty($k,$v);
            }
            $connection->setId($client_id);
            $this->connections[$client_id] = $connection;
        }
    }

    /**
     * remove connection from connection list
     * @param $client_id
     */
    protected function removeConnection($client_id){
        if(isset($this->connections[$client_id])){
            $this->connections[$client_id] = null;
            unset($this->connections[$client_id]);
        }
    }


    /**
     * get connection object by client id
     * @param $client_id client id
     * @return null| \pinst\model\Connection
     */
    public function getConnection($client_id){
        if(isset($this->connections[$client_id]))
            return $this->connections[$client_id];
        return null;
    }


    public function beforeReceive($server, $client_id, $from_id, $data){
        $connection = $this->getConnection($client_id);
        if(!$connection){
            return true;
        }
        $connection->pushMessage($data);
        return true;
    }

    /**
     * when client send new message this function is call
     * @param $server swoole_server object
     * @param $client_id client id
     * @param $from_id form id
     * @param $data receive message data
     */
    public function onReceive($server, $client_id, $from_id, $data){

        if(!$this->beforeReceive($server, $client_id, $from_id, $data)){
            return;
        }
        if(APP_DEBUG){
            Console::println("recv new message from client[{$client_id}],message data is:\n<<<\n{$data}\n>>>");
        }
        $connection = $this->getConnection($client_id);
        $connection->setServer($server);
        $this->onMessage($server,$connection,$data);
        return $this->afterReceive($server, $client_id, $from_id, $data);
    }

    public function afterReceive($server, $client_id, $from_id, $data){

    }




    /**
     * when client is close call this function
     * @param $server swoole_server object
     * @param $client_id client is
     * @param $from_id from id
     */
    public function onClose($server,$client_id,$from_id){
        if(method_exists($this,"beforeClose")){
            call_user_func_array([$this,"beforeClose"],[$server,$client_id,$from_id]);
        }
        if(APP_DEBUG){
            Console::println("client {$client_id} close,current have client count is:".$this->getConnectionCount());
        }
        $this->removeConnection($client_id);
        if(method_exists($this,"afterClose")){
            call_user_func_array([$this,"afterClose"],[$server,$client_id,$from_id]);
        }
    }

    public function beforeClose($server,$client_id,$from_id){
        $connection = $this->getConnection($client_id);
        if(!$connection){
            return true;
        }
        $connection->store();
    }

    /**
     * get client list count
     * @return int
     */
    public function getConnectionCount(){
        return count($this->server->connections);
    }

    /**
     * when work is stop
     * @param $server
     * @param $work_id
     */
    public function onStop($server,$work_id){
        if(APP_DEBUG){
            Console::println("work {$work_id} is stop");
        }
    }

    /**
     * send file to client
     * @param $client_id client id
     * @param $filename file name
     * @return mixed
     * @throws InvalidCallException
     */
    public function sendFile($client_id,$filename){
        if(!file_exists($filename)){
            throw new InvalidCallException("file is not exists!");
        }
        return $this->server->sendFile($client_id,$filename);
    }

    /**
     * send message to client
     * @param $client_id client id
     * @param $message message content
     * @return mixed
     */
    public function send($client_id, $message){
        return $this->server->send($client_id, $message);
    }

    /**
     * broadcast message
     * @param $message
     * @param null $client_id
     */
    public function broadcast($message,$client_id = null){
        $ids = $this->server->connections;
        foreach($ids as $id){
            if($id != $client_id)
                $this->server->send($id, $message);
        }
    }
    /**
     * @param $task task name
     * @param int $work_id dest work id
     */
    public function task($task, $work_id = -1){
        $this->server->task($task, $work_id);
    }

    /**
     * message event
     * @param $server \swoole_server
     * @param $connection \pinst\model\Connection client connection object
     * @param $data string message data
     */
    protected function onMessage($server, $connection, $data){

    }
}