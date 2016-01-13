<?php

namespace pinst\server;

use pinst\base\Object;
use pinst\handel\BaseHandel;


class Dispatch extends Object
{
    protected $server;
    public  $handelMap = [];
    protected $handelInstance = [];

    /**
     * set current application swoole server
     * @param $server
     */
    public function setServer($server){
        $this->server = $server;
    }

    public function prepare(){
        foreach($this->handelMap as $k => $handel){
            $this->attachHandel($k,$handel);
        }
    }

    /**
     * attach message handel
     * @param $handel
     */
    public function attachHandel($name,$handel){
        if(isset($this->handelInstance[$name])){
            return true;
        }
        $instance = null;
        if(is_string($handel)){
            $instance = \Pinst::createObject($handel);
        }elseif($handel instanceof BaseHandel){
            $instance = $handel;
        }else{
            return false;
        }
        if($instance instanceof BaseHandel){
            $this->handelInstance[$name] = $instance;
            return true;
        }
        return false;
    }

    public function detachHandel($name){
        if(isset($this->handelInstance[$name])){
            unset($this->handelInstance[$name]);
        }
        return true;
    }

    public function onStart($server,$work_id){
        $this->toggle("onStart",[
            $server,$work_id
        ]);
    }

    public function onConnect($server, $client_id, $from_id){
        $this->toggle("onConnect",[
            $server,$client_id,$from_id
        ]);
    }

    public function onReceive($server, $client_id, $from_id, $data){

        $server->send($client_id,"HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: nginx\r\n\r\nhello\r\n\r\n");
        $server->close($client_id);
        return ;

        $this->toggle("onReceive",[
            $server,$client_id,$from_id,$data
        ]);
    }

    public function onClose($server,$client_id,$from_id){
        $this->toggle("onClose",[
            $server,$client_id,$from_id
        ]);
    }

    public function onStop($server,$work_id){
        $this->toggle("onStop",[
            $server,$work_id
        ]);
    }

    public function onTimer($server,$interval){
        $this->toggle("onTimer",[
            $server,$interval
        ]);
    }


    public function onTask($server,$task_id,$from_id,$data){
        $this->toggle("onTask",[
            $server,$task_id,$from_id,$data
        ]);
    }


    public function onFinish($server,$task_id,$data){
        $this->toggle("onFinish",[
            $server,$task_id,$data
        ]);
    }

    public function onPipeMessage($server,$from_worker_id,$message){
        $this->toggle("onPipeMessage",[
            $server,$from_worker_id,$message
        ]);
    }

    /**
     * toggle event
     * @param $event event name
     * @param array $params event param
     */
    protected function toggle($event,$params=[]){
        foreach($this->handelInstance as $name=>$instance){
            if(is_callable([$instance,$event]))
                call_user_func_array([$instance,$event],$params);
        }
    }
}