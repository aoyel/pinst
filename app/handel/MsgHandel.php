<?php
/**
 * User: smile
 * Date: 16-1-11
 * Time: 下午7:31
 * Summary:
 */

namespace app\handel;

use pinst\handel\Handel;

class MsgHandel extends Handel
{

    public function onMessage($server, $client_id, $from_id, $data)
    {
        $this->send($client_id,"You message id:{$data}");
        $this->close($client_id);
    }
}