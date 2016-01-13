<?php
namespace pinst\base;

use pinst\exception\InvalidCallException;
use pinst\exception\UnknownPropertyException;

class Object
{
    private $_propertys;

    function __construct(){
        $this->init();
    }

    /**
     * get current class name
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * init function
     */
    public function init(){

    }

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }elseif ($this->hasProperty($name)){
            return $this->getProperty($name);
        }elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }else{
            return $this->_propertys[$name] = $value;
        }
        if (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __unset($name){
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        }elseif ($this->hasProperty($name)){
            if(property_exists($this,$name)){
                $this->$name = null;
            }else{
                unset($this->_propertys[$name]);
            }
        }else{
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function getProperty($name){
        if(property_exists($this,$name)){
            return $this->$name;
        }elseif(isset($this->_propertys[$name])){
            return $this->_propertys[$name];
        }else{
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function hasProperty($name){
        return property_exists($this,$name) || isset($this->_propertys[$name]);
    }

    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}