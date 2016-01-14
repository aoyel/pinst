<?php

namespace pinst\connection;


/**
 * Base connection object
 * @package pinst\connection
 * @author smile <smile@aoyel.com>
 */
class BaseConnection implements ConnectionInterface
{
    /**
     * connection id
     * @var
     */
    protected $id;

    /**
     * connection object property
     * @var array
     */
    protected $property = [];

    /**
     * get connection id
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set connection id
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * set connection property
     * @param $name
     * @param $value
     */
    public function setProperty($name,$value){
        $this->property[$name] = $value;
    }

    /**
     * get connection property
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
     * check has property
     * @param $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return isset($this->property[$name]);
    }

    /**
     * close connection
     * close client
     * @return mixed
     */
    public function close(){
        return \Pinst::$app->server->close($this->getId());
    }

}