<?php
/*
*idsky  控制器基类
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/
namespace Idsky\Core;
class Controller
{
    protected $title;
    protected $keywords;
    protected $description;

    protected $_controller;
    protected $_action;
    private $redis;

    public function __construct(){
        $idsky = \Idsky::getInstance();
        $this->_controller = $idsky->dispatchInfo['controller'];
        $this->_action = $idsky->dispatchInfo['action'];
    }

    protected function get($key='',$default=''){
        return \Idsky\Lib\Request::get($key,$default);
    }

    protected function post($key='',$default=''){
        return \Idsky\Lib\Request::post($key,$default);
    }

    protected function input($parse=false){
        return \Idsky\Lib\Request::input($parse);
    }

    protected function redirect($url, $code = 302){
        \Idsky\Lib\Response::redirect($url,$code);
    }

    protected function forward($controller,$action='index'){
        $className = $controller;
        $controller = new $className();
        if($controller && $action){
            $func = array($controller,$action);
            call_user_func($func);
        }
    }

    protected function view($viewsHome=null){
        return $this->view = \View::getInstance($viewsHome);
    }

    protected function display($tpl=null,$dir=null){
        $this->view->title = $this->title;
        $this->view->keywords = $this->keywords;
        $this->view->description = $this->description;
        return $this->view->display($tpl,$dir);
    }

    protected function slice($tpl,array $data=array()){
        return $this->view->slice($tpl,$data);
    }

    protected function json($status=1,$info='',$data='',$header=true){
        return \Idsky\Lib\Response::json($status,$info,$data,$header);
    }

    protected function xml($xml,$header=true){
        return \Idsky\Lib\Response::xml($xml,$header);
    }

    protected function redis($key=''){
        $instance = \Idsky\Ext\Redis::getInstance();
        return $this->redis = $instance->connect($key);
    }

    protected function isAjax(){
        return \Idsky\Lib\Request::isAjax();
    }

    public function __set($key, $value = null){
        $this->$key = $value;
    }

    public function __get($key){
        switch ($key) {
            case 'view':
                return $this->view ?  $this->view : $this->view = $this->view();
                break;
            case 'config':
                return $this->config ? $this->config : $this->config = new \C();
                break;
            case 'response':
                return $this->response ? $this->response : $this->response = new \Idsky\Lib\Response();
                break;
            case 'request':
                return $this->request ? $this->request : $this->request = new \Idsky\Lib\Request();
                break;
            case 'validator':
                return $this->validator ? $this->validator : $this->validator = new \Idsky\Lib\Validate();
                break;
            case 'redis':
                return $this->redis ? $this->redis : $this->redis();
                break;
            default:
                break;
        }
    }
}

?>
