<?php
namespace pinst;


use pinst\base\Logger;

class BasePinst
{
    /**
     * @var \pinst\base\Application
     */
    public static $app;

    /**
     *
     * @var array
     */
    public static $namespaceMap = [];

    public static function addNamespaceMap($namespace, $path) {
        self::$namespaceMap [$namespace] = $path;
    }

    /**
     * create object
     * @param $obj
     * @param array $args
     * @return bool|null|object
     */
    public static function createObject($obj, $args = []) {
        if (is_string ( $obj )) {
            return self::createInstance ( $obj, $args );
        } else if (is_array ( $obj ) && isset ( $obj ['class'] )) {
            $instance = self::createInstance ( $obj ['class'], $args );
            unset ( $obj ['class'] );
            foreach ( $obj as $k => $v )
                $instance->$k = $v;
            return $instance;
        }
        return false;
    }

    /**
     * create class instance
     * @param $class class name
     * @param array $args class args
     * @return null|object return object if create successful otherwise return null
     */
    protected static function createInstance($class, $args = []) {
        if (! class_exists ( $class ))
            return null;
        $re_args = [ ];
        if (method_exists ( $class, "__construct" )) {
            $refMethod = new \ReflectionMethod ( $class, '__construct' );
            $params = $refMethod->getParameters ();
            foreach ( $params as $key => $param ) {
                if ($param->isPassedByReference ()) {
                    $re_args [$key] = &$args [$key];
                } else {
                    $re_args [$key] = $args [$key];
                }
            }
        }
        $refClass = new \ReflectionClass ( $class );
        return $refClass->newInstanceArgs ( ( array ) $re_args );
    }


    public static function autoload($className) {
        $prefix = strstr ( $className, '\\', true );
        if (in_array ( $prefix, array_keys ( self::$namespaceMap ) )) {
            $className = str_replace ( $prefix, self::$namespaceMap [$prefix], $className );
            $fileName = str_replace ( "\\", DIRECTORY_SEPARATOR, $className ) . ".php";
            if (file_exists ( $fileName )) {
                require $fileName;
                return true;
            }
        }
        $className = ltrim ( $className, '\\' );
        $fileName = '';
        $namespace = '';
        if (($lastNsPos = strripos ( $className, '\\' )) !== false) {
            $namespace = substr ( $className, 0, $lastNsPos );
            $className = substr ( $className, $lastNsPos + 1 );
            $fileName = str_replace ( '\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
        }
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . $fileName . $className . '.php';
        if (file_exists ( $fileName )) {
            require $fileName;
            return true;
        }
    }

    static public function debug($message,$category='application'){
        return self::$app->logger->log($message,Logger::LEVEL_DEBUG,$category);
    }

    static public function warning($message,$category='application'){
        return self::$app->logger->log($message,Logger::LEVEL_WARNING,$category);
    }

    /**
     * logger error message
     * @param $message
     * @param string $category
     * @return mixed
     */
    static public function error($message,$category='application'){
        return self::$app->logger->log($message,Logger::LEVEL_ERROR,$category);
    }

    /**
     * set cache value
     * @param $key cache key
     * @param $value cache value
     * @param int $expire cache expire
     * @return mixed
     */
    static public function setCache($key,$value,$expire=0){
        return self::$app->cache->set($key,$value,$expire);
    }

    /**
     * get cache value
     * @param $key cache key
     * @param null $defaultValue
     * @return null
     */
    static public function getCache($key,$defaultValue = null){
        $data = self::$app->cache->get($key);
        return $data ? $data : $defaultValue;
    }

    static public function attachHandel($name,$handel){
        return self::$app->dispatch->attachHandel($name,$handel);
    }

    static public function detachHandel($name){
        return self::$app->dispatch->detachHandel($name);
    }
}