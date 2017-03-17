<?php
/*
*idsky 核心类
*(c) muzizhao <muzizhao.cn>
**/
defined('IDSKYDIR') || define('IDSKYDIR', dirname(__FILE__));
include_once (IDSKYDIR.'/core/Autoload.php');
include_once (IDSKYDIR.'/vendor/autoload.php');
include_once (IDSKYDIR.'/lib/Global.php');
$GLOBALS['_PRJDIR'] = PRJDIR;
//log monolog
$logFile = \C::getByKey("common.error_log","/tmp/idsky_error.log");
$Logger = new \Monolog\Logger('system');
$Logger->pushHandler(new \Monolog\Handler\StreamHandler($logFile, \Monolog\Logger::WARNING));
$Logger->pushHandler(new \Monolog\Handler\StreamHandler($logFile, \Monolog\Logger::ERROR));
$Logger->pushHandler(new \Monolog\Handler\StreamHandler($logFile, \Monolog\Logger::CRITICAL));
$Logger->pushHandler(new \Monolog\Handler\StreamHandler($logFile, \Monolog\Logger::ALERT));

$debug = \C::getByKey("common.debug",false);

if($debug){
    // whoops: php errors for cool kids
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler($Logger));
    $whoops->register();
}

class Idsky
{
    public $dispatchInfo;
    private $_requestPath;
    private static $_instance;


    private function __construct(){

    }

    public static function getInstance(){
        if(null===self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    //初始化项目
    public function init(){
        global $IDSKYDATA,$_PRJDIR;
        if(!self::$_instance){
            self::getInstance();
        }
        $_PRJDIR = PRJDIR;
        $this->dispatchInfo();
        return $this;
    }

    public function run(){
        global $_PRJDIR;
        if(file_exists($_PRJDIR."/controller/Common.php")){
            $common = new \Controller\Common();
        }
        if($this->dispatchInfo['controller']){
            $className = $this->dispatchInfo['controller'];
            if(\Idsky\Core\Autoload::load($className)){
                $object = new $className();
            }
        }
        if($this->dispatchInfo['action']){
            $func = $this->dispatchInfo['action'];
        }
        if(is_a($object,"\Controller") && method_exists($object,$func)){
            call_user_func(array($object,$func));
            return true;
        }else{
            return false;
        }
    }

    public  function dispatchInfo(){
        $rules = \C::getByName('route');
        $router = new Idsky\Core\Router();
        $requestPath = $this->getRequestPath();
        $this->dispatchInfo = $router->match($requestPath,$rules);
        if(false == ($lastNsPos = strripos($this->dispatchInfo['controller'], "\\"))){
            $this->dispatchInfo['controller'] = "\Controller\\".$this->dispatchInfo['controller'];
        }
        return $this->dispatchInfo;
    }

    public function setRequestPath($requestPath = null){
        if (null === $requestPath) {
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        }
        if($uri){
            $uri = substr($uri, 1);
            $pos = strpos($uri,"?");
            $this->_requestPath = $pos === false ? substr($uri,0) : substr($uri,0,$pos);
        }
        return $this;
    }

    public function getRequestPath(){
        if (null === $this->_requestPath){
            $this->setRequestPath();
        }
        return $this->_requestPath;
    }
}

?>
