<?php

namespace pinst\handel;

use pinst\base\Object;
use pinst\utils\Console;
use pinst\connection\Connection;

class BaseHandel extends Object implements HandelInterface
{
    protected $connections = [];

    /**
     * when worker process start calllback
     * @param \swoole_server $server
     * @param int $work_id
     */
    public function onStart(\swoole_server $server, $work_id){
        if(APP_DEBUG){
            Console::println("worker process[{$work_id}] started successfully !");
        }
    }

    /**
     * before client connection
     * @param \swoole_server $server
     * @param $client_id
     * @param $from_id
     * @return bool
     */
    public function beforeConnect(\swoole_server $server, $client_id, $from_id){
        return true;
    }

    /**
     * prepare connection info, if get connection info success push connection to connection list
     * @param $server \swoole_server object
     * @param $id connection id
     */
    protected function prepareConnection($server,$id){
        if(!isset($this->connections[$id])){
            $connection = new Connection();
            $clientInfo = $server->connection_info($id);
            foreach($clientInfo as $k=>$v){
                $connection->setProperty($k,$v);
            }
            $connection->setId($id);
            $this->connections[$id] = $connection;
        }
    }

    /**
     * when client connection callback
     * @param \swoole_server $server
     * @param int $client_id
     * @param int $from_id
     */
    public function onConnect(\swoole_server $server, $client_id, $from_id)
    {
        if(!$this->beforeConnect($server, $client_id, $from_id)){
            return ;
        }
        $this->prepareConnection($server,$client_id);
        if(APP_DEBUG){
            Console::println("have new client[$client_id] connection !");
        }
        $this->afterConnect($server, $client_id, $from_id);
    }

    /**
     * after client connection callback
     * @param \swoole_server $server
     * @param $client_id
     * @param $from_id
     * @return bool
     */
    public function afterConnect(\swoole_server $server, $client_id, $from_id){
        return true;
    }

    /**
     * before message process callback
     * @param \swoole_server $server
     * @param \pinst\connection\Connection $connection
     * @param $from_id
     * @param $data
     * @return bool
     */
    public function beforeReceive(\swoole_server $server, $connection, $from_id, &$data){
        return true;
    }

    /**
     * receive message callback
     * @param \swoole_server $server
     * @param client $client_id
     * @param from $from_id
     * @param $data
     */
    public function onReceive(\swoole_server $server, $client_id, $from_id, $data)
    {
        $connection = $this->getConnection($client_id);
        if(!$this->beforeReceive($server, $connection, $from_id, $data)){
            return;
        }
        if(APP_DEBUG){
            Console::println("receive client[$client_id] message,message data is:\n<<<\n{$data}\n>>>");
        }
        $this->onMessage($server,$connection,$data);
        $this->afterReceive($server, $connection, $from_id, $data);
    }

    /**
     * when message was processed callbck
     * @param \swoole_server $server
     * @param \pinst\connection\Connection $connection
     * @param $from_id
     * @param $data
     * @return bool
     */
    public function afterReceive(\swoole_server $server, $connection, $from_id, $data){
        return true;
    }

    /**
     * remove connection object from connection list
     * @param $id connection id
     */
    protected function removeConnection($id){
        if(isset($this->connections[$id])){
            $this->connections[$id] = null;
            unset($this->connections[$id]);
        }
    }

    /**
     * get connection object
     * @param $id connection id
     * @return null|\pinst\connection\Connection if has connection return connection otherwise return null
     */
    public function getConnection($id){
        if(isset($this->connections[$id])){
            return $this->connections[$id];
        }
        return null;
    }

    public function beforeClose($server, $client_id, $from_id){
        return true;
    }

    /**
     * when connection close callback
     * @param \swoole_server $server
     * @param $client_id
     * @param $from_id
     */
    public function onClose(\swoole_server $server, $client_id, $from_id)
    {
        if(!$this->beforeClose($server, $client_id, $from_id)){
            return ;
        }
        if(APP_DEBUG){
            Console::println("client[$client_id] close connection!");
        }
        $this->afterClose($server, $client_id, $from_id);
    }

    /**
     * after application close callback
     * @param \swoole_server $server
     * @param $client_id
     * @param $from_id
     */
    public function afterClose($server, $client_id, $from_id){
    }


    public function onStop(\swoole_server $server, $work_id){

    }

    public function onTimer(\swoole_server $server, $interval){
    }

    public function onTask(\swoole_server $server, $task_id, $from_id, $data){

    }

    public function onFinish(\swoole_server $server, $task_id, $data){

    }

    public function onPipeMessage(\swoole_server $server, $from_worker_id, $message){

    }

    /**
     * when have new message callback
     * @param \swoole_server $server
     * @param \pinst\connection\Connection $connection
     * @param string $data
     */
    public function onMessage($server,$connection,$data){

    }
}