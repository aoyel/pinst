<?php

namespace pinst\connection;


interface ConnectionInterface
{
    public function getId();
    public function setId($id);
    public function setProperty($name,$value);
    public function getProperty($name,$defaultValue = null);
    public function hasProperty($name);
    public function close();
}