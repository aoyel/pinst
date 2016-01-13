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
    protected $dispatch = null;


    public function init(){
        parent::init();
    }

    public function setPidFile($filename){
        $this->pid_file = $filename;
    }

    public function setDispatch($dispatch){
        $this->dispatch = $dispatch;
        return $this;
    }

    /**
     * @param $handel
     */
    public function run(){
        $this->server = new \swoole_server($this->host, $this->port,SWOOLE_BASE, SWOOLE_SOCK_TCP);
        $this->server->set($this->config);
        $this->dispatch->setServer($this->server);
        $this->server->on('Start', [$this, 'onMasterStart']);
        $this->server->on('Shutdown', [$this, 'onMasterStop']);
        $this->server->on('ManagerStop', [$this, 'onManagerStop']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Connect', [$this->dispatch, 'onConnect']);
        $this->server->on('Receive', [$this->dispatch, 'onReceive']);
        $this->server->on('Close', [$this->dispatch, 'onClose']);
        $this->server->on('WorkerStop', [$this->dispatch, 'onStop']);
        if(is_callable([$this->dispatch,"onTimer"])){
            $this->server->on("Timer",[$this->dispatch,"onTimer"]);
        }
        if(is_callable([$this->dispatch, 'onTask'])){
            $this->server->on("Timer",[$this->dispatch,"onTimer"]);
            $this->server->on("Finish",[$this->dispatch,"onFinish"]);
        }
        $this->server->start();
    }

    public function onMasterStart($server){
        //$server->master_pid
        //save pid
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
        if(method_exists($this->dispatch,"onStart")){
            $this->dispatch->onStart($server,$worker_id);
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

    public function close($client_id){
        return $this->server->close($client_id);
    }

    public function send($client_id,$content){
        return $this->server->send($client_id,$content);
    }

}