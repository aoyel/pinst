<?php

namespace pinst\handel;


interface HandelInterface
{


    /**
     * work process start callback
     * @param \swoole_server $server
     * @param int $work_id work process id
     * @return mixed
     */
    public function onStart(\swoole_server $server,$work_id);

    /**
     * client connection callback
     * @param \swoole_server $server
     * @param int $client_id client id
     * @param int $from_id
     * @return mixed
     */
    public function onConnect(\swoole_server $server, $client_id, $from_id);

    /**
     * client send message callback
     * @param \swoole_server $server
     * @param $client_id client id
     * @param $from_id from id
     * @param $data
     * @return mixed
     */
    public function onReceive(\swoole_server $server, $client_id, $from_id, $data);

    /**
     * client close callback
     * @param \swoole_server $server
     * @param $client_id
     * @param $from_id
     * @return mixed
     */
    public function onClose(\swoole_server $server,$client_id,$from_id);

    /**
     * work process stop callback
     * @param \swoole_server $server
     * @param $work_id
     * @return mixed
     */
    public function onStop(\swoole_server $server,$work_id);

    /**
     * time callback
     * @param \swoole_server $server
     * @param $interval
     * @return mixed
     */
    public function onTimer(\swoole_server $server,$interval);

    /**
     * task running callback
     * @param \swoole_server $server
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return mixed
     */
    public function onTask(\swoole_server $server,$task_id,$from_id,$data);

    /**
     * task finish callback
     * @param \swoole_server $server
     * @param $task_id task id means whatever task was running
     * @param $data addition data
     * @return mixed
     */
    public function onFinish(\swoole_server $server,$task_id,$data);

    /**
     * pipe message callback
     * @param \swoole_server $server
     * @param $from_worker_id
     * @param $message
     * @return mixed
     */
    public function onPipeMessage(\swoole_server$server,$from_worker_id,$message);
}