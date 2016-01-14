<?php

namespace pinst\connection;

class Connection extends BaseConnection
{
   protected $message = [];
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

    /**
     * send message to client
     * @param $message
     * @return mixed
     */
    public function send($message){
        return \Pinst::$app->server->send($this->getId(),$message);
    }
}