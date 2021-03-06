<?php
namespace pinst\server;

use pinst\utils\Console;

/**
 * application server
 * @package pinst\server
 */
class Server extends \pinst\base\Object
{
    /**
     * @var \swoole_server
     */
    protected $server = null;
    protected $handel = null;


    public function init(){
        parent::init();
    }

    public function setPidFile($filename){
        $this->pid_file = $filename;
    }

    public function setHandel($handel){
        $this->handel = $handel;
        return $this;
    }

    /**
     * @param $handel
     */
    public function run(){
        $this->server = new \swoole_server($this->host, $this->port,SWOOLE_BASE, SWOOLE_SOCK_TCP);
        $this->server->set($this->config);
        $this->server->on('Start', [$this, 'onMasterStart']);
        $this->server->on('Shutdown', [$this, 'onMasterStop']);
        $this->server->on('ManagerStop', [$this, 'onManagerStop']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Connect', [$this->handel, 'onConnect']);
        $this->server->on('Receive', [$this->handel, 'onReceive']);
        $this->server->on('Close', [$this->handel, 'onClose']);
        $this->server->on('WorkerStop', [$this->handel, 'onStop']);
        if(is_callable([$this->handel,"onTimer"])){
            $this->server->on("Timer",[$this->handel,"onTimer"]);
        }
        if(is_callable([$this->handel, 'onTask'])){
            $this->server->on("Timer",[$this->handel,"onTimer"]);
            $this->server->on("Finish",[$this->handel,"onFinish"]);
        }
        $this->server->start();
    }

    public function onMasterStart($server){
        file_put_contents($this->pid_file,$server->master_pid);
        if(APP_DEBUG){
            Console::println("Service[{$server->master_pid}] startup success !");
        }
        \Pinst::warning("server was start on {$this->port} !");
    }

    public function onMasterStop($server){
        @unlink($this->pid_file);
        if(APP_DEBUG){
            Console::println("server is stop");
        }
        \Pinst::$app->shutdown();
    }

    public function onManagerStop(){
        $this->releaseComponent();
    }

    public function onWorkerStart($server, $worker_id){
        $this->loadComponent();
        if(method_exists($this->handel,"onStart")){
            $this->handel->onStart($server,$worker_id);
        }
    }

    protected function loadComponent(){
        if(\Pinst::$app->hasComponent("db")){
            \Pinst::$app->db->connection();
        }
    }

    protected function releaseComponent(){
        if(\Pinst::$app->hasComponent("db")) {
            \Pinst::$app->db->close();
        }
    }

    public function shutdown(){
        return $this->server->shutdown();
    }

    /**
     * @param $client_id
     * @return bool
     */
    public function close($client_id){
        return $this->server->close($client_id);
    }

    /**
     * send message to client
     * @param $client_id
     * @param $content
     * @return bool
     */
    public function send($client_id,$content){
        if(is_callable([$this->handel,"beforeSend"])){
            if(!$this->handel->beforeSend($client_id,$content)){
                return;
            }
        }
        return $this->server->send($client_id,$content);
    }

    /**
     * send to all
     * @param $content send content
     */
    public function sendToAll($content){
        $connections = $this->server->connections;
        foreach($connections as $fd){
            $this->send($fd,$content);
        }
    }

    public function broadcast($content,$client_id){
        $connections = $this->server->connections;
        foreach($connections as $fd){
            if($fd != $client_id)
                $this->send($fd,$content);
        }
    }


}