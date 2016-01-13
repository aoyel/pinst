<?php
namespace pinst\base;

use pinst\utils\Console;
use pinst\exception\InvalidCallException;
use pinst\exception\UnknownPropertyException;

/**
 *
 * @package pinst\base
 *
 * @property string $runtimePath The directory that stores runtime files. Defaults to the "runtime"
 *
 * @property string $basePath the Application base path
 *
 * @property \pinst\base\Cache $cache Application cache component
 *
 * @property \pinst\base\Logger $logger Application logger component
 *
 * @property \pinst\handel\Handel $handel Application handel component
 *
 * @property \pinst\base\Db $db Application database component
 *
 * @property \pinst\server\Server $server Application server component
 *
 * @property \pinst\base\ErrorHandel $errorHandel Application default error handel
 *
 */
class Application extends Object
{
    protected $callback = [];
    /**
     * Application constructor.
     * @param array $config
     */
    function __construct($config = []){
        \Pinst::$app = $this;
        $this->prepare($config);
        parent::__construct();
    }

    /**
     *
     */
    protected function prepareNameSpace(){
        \Pinst::addNamespaceMap("app",APP_PATH."/app");
    }

    protected function prepareComponent(){
        $this->components = array_merge($this->getKernelComponent(),$this->components);
        $this->errorHandel->register();
    }

    /**
     * prepare application
     * @param $config
     */
    protected function prepare($config){
        foreach ($config as $k=>$v)
            $this->$k = $v;
        if(isset($this->timezone) && !empty($this->timezone)){
            date_default_timezone_set('Asia/Shanghai');
        }else{
            date_default_timezone_set($this->timezone);
        }
        $this->prepareNameSpace();
        $this->prepareComponent();
    }

    /**
     * get vars or property or component
     * @param $name name of you wan't get
     * @return mixed
     * @throws InvalidCallException
     * @throws UnknownPropertyException
     */
    function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }elseif ($this->hasProperty($name)){
            return $this->getProperty($name);
        }elseif($this->hasComponent($name)){
            return $this->getComponent($name);
        }elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    protected function getKernelComponent(){
        return [
            'cache' => [
                'class' => '\pinst\cache\FileCache',
                'directoryLevel' => 1,
                'keyPrefix' => '__cache'
            ],
            'handel'=>[
                'class'=>'\pinst\handel\Basehandel'
            ],
            'logger'=>[
                'class'=>'\pinst\log\FileLogger',
            ],
            'errorHandel'=>[
                'class'=>'\pinst\base\ErrorHandel'
            ]
        ];
    }

    /**
     * check component is exists
     * @param $name
     * @return bool
     */
    public function hasComponent($name){
        return isset($this->components[$name]);
    }

    /**
     * get component
     * @param $name component name
     * @return mixed return component object
     * @throws UnknownPropertyException
     */
    protected function getComponent($name){
        $component = $this->components[$name];
        $this->$name = \Pinst::createObject($component);
        return $this->getProperty($name);
    }

    /**
     * run application
     */
    public function run(){
        global $argv;
        $server_pid = null;
        $pid_file = $this->server->pid_file;
        if(file_exists($pid_file)){
            $server_pid = intval(@file_get_contents($pid_file));
        }
        if(empty($argv[1]) || $argv[1] == 'start'){
            if($server_pid && !empty($pid_file) && $this->isRun($server_pid)){
                Console::println("server is running , please stop first!");
                return true;
            }
            Console::println("start....");
            $this->start();
        }elseif($argv[1] == 'stop'){
            if (empty($server_pid)){
                Console::println("Server is not running\n");
            }
            Console::println("stop...");
            posix_kill($server_pid,SIGTERM);
            while($this->isRun($server_pid)){
                sleep(1);
            }
            Console::println("server stoped!");
            exit;
        }elseif($argv[1] == 'restart' || $argv[1] == 'reload'){
            if (empty($server_pid)){
                Console::println("Server is not running\n");
            }
            Console::println("restart...");
            posix_kill($server_pid, SIGUSR1);
        }elseif($argv[1] == 'state' || $argv[1] == 'status'){
            if($server_pid && $this->isRun($server_pid)){
                Console::println("Server is running");
            }else{
                Console::println("Server is stop");
            }
        }else{
            exit("Usage: php {$argv[0]} \n*start start server\n*stop stop server\n*reload|restart restart server \n*state|status get current server status\n");
        }
    }

    protected function start(){
        $this->server->setHandel($this->handel)->run();
    }

    /**
     * if get process group is false means process is stop
     * @param $pid process id
     * @return bool is true means process is running otherwise process is stop
     */
    protected function isRun($pid){
        return posix_getpgid($pid) !== false;
    }

    /**
     * register shutdown callback
     * @param $callback
     */
    public function registerShutdownFunction($callback){
        if($callback instanceof \Closure){
            $this->callback['shutdown'][] = $callback;
        }
    }

    /**
     * when server stop called
     */
    public function shutdown(){
        $callback = $this->callback['shutdown'];
        if(empty($callback))
            return ;
        foreach($callback as $cb){
            call_user_func($cb,$this);
        }
    }

}