<?php
namespace app\handel;
use pinst\handel\WebSocketHandel;

class DefaultHandel extends WebSocketHandel
{
    public function onHttpRequest($connection,$data){
        $content = file_get_contents(APP_PATH."/assets/index.html");
        $this->sendHttpRespone($connection->getId(),$content);
    }

    /**
     * @param \pinst\handel\swoole $server
     * @param \pinst\model\Connection $client
     * @param \pinst\handel\from $from_id
     * @param \pinst\handel\recv $data
     */
    public function onMessage($server, $connection , $data){
        $this->process($server,$connection,$data);
    }

    /**
     * @param $server
     * @param $client \pinst\model\Client $client
     * @param $data
     */
    protected function process($server,$connection,$data){
        $dataset = json_decode($data,true);
        if($dataset == false){
            $this->broadcast(json_encode([
                "client_id"=>$connection->getId(),
                "name"=>$connection->getProperty("name"),
                "msg"=>$data
            ]),$connection->getId());
        }
    }

    /**
     * broadcast
     * @param \pinst\handel\swoole_server $server
     * @param \pinst\model\Client $client_id
     * @param \pinst\handel\from $from_id
     */
    public function onClose($server, $client_id, $from_id)
    {
        parent::onClose($server, $client_id, $from_id);
    }

    protected function sendHttpRespone($client_id,$data){
        $this->send($client_id,"HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: nginx\r\n\r\n{$data}\r\n\r\n");
        $this->close($client_id);
    }
}