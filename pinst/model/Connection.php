<?php

namespace pinst\model;


class Connection
{
    /**
     * @var client id
     */
    protected $id;
    protected $server;
    protected $property = [];
    protected $message = [];
    protected $callback = [];

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * set server
     * @param $server
     */
    public function setServer($server){
        $this->server = $server;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setProperty($name,$value){
        $this->property[$name] = $value;
    }

    /**
     * @param $name
     * @param null $defaultValue
     * @return null
     */
    public function getProperty($name,$defaultValue = null){
        if($this->hasProperty($name))
            return $this->property[$name];
        return $defaultValue;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return isset($this->property[$name]);
    }

    /**
     * push chat log message
     * @param $content
     */
    public function pushMessage($content){
        $this->message[] = [
            'time'=>time(),
            'content'=>$content
        ];
    }

    /**
     * store cht log
     * @param null $filename filename
     * @return bool
     * @throws \pinst\exception\ExitException
     */
    public function store($filename = null){
        if(empty($filename)){
            $filename = \Pinst::$app->runtimePath.DIRECTORY_SEPARATOR."msg";
            if(!is_dir($filename)){
                FileHelper::createDirectory($filename);
            }
        }

        $filename .= DIRECTORY_SEPARATOR.$this->getProperty("client_id").'_'.time();

        $fp = fopen($filename,"a+");

        if(!$fp){
            return false;
        }

        foreach($this->message as $msg){
            fwrite($fp,'['.date("Y/m/d H:i:s",$msg['time']).']'.$msg['content']);
        }
        fclose($fp);
    }

    public function on($event,$callback){
        $list = null;
        if(!isset($this->callback[$event])){
            $list = [];
        }else{
            $list = $this->callback[$event];
        }
        $list[] = $callback;
        $this->callback[$event] = $list;
    }



    /**
     * send message
     * @param $message
     * @return mixed
     */
    public function send($message){
        return $this->server->send($this->getId(),$message);
    }

    /**
     * close client
     * @return mixed
     */
    public function close(){
        return $this->server->close($this->getId());
    }
}