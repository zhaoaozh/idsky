<?php
/*
*idsky  路由类
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/
namespace  Idsky\Core;
class Router
{
    public function __construct(){

    }

    public  $defaultAutoRouter = array(
        'controller'=>'Index',
        'action'=>'index',
    );

    public  $enableAutoMatch = true;

    public  function autoMatch($requestPath){
        $dispatchInfo = $this->defaultAutoRouter;
        $patchArr = explode('/', $requestPath);
        if($controller = current($patchArr)){
            $dispatchInfo['controller'] = ucfirst($controller);
        }
        if($action = next($patchArr)){
            $dispatchInfo['action'] = $action;
        }

        $params = array();
        while (false!==($next = next($patchArr))) {
            $params[$next] = urldecode(next($patchArr));
        }
        $this->setParams($params);
        return $dispatchInfo;
    }

    public function match($requestPath,$rules=null){
        if($rules){
            foreach ($rules as $regex => $rule){
                if(!preg_match($regex, $requestPath,$matches)){
                    continue;
                }
                if(isset($rule['maps']) && is_array($rule['maps'])){
                    $params = array();
                    foreach ($rule['maps'] as $key => $val) {
                        if(isset($matches[$key]) && '' !=$matches[$key]){
                            $params[$val] = urldecode($matches[$key]);
                        }
                        if(isset($rule['defaults'])){
                            $params += $rule['defaults'];
                        }
                    }
                    $this->setParams($params);
                }

                if(isset($rule['controller'])){
                    $dispatchInfo['controller'] = $rule['controller'];
                }
                if(isset($rule['action'])) {
                    $dispatchInfo['action'] = $rule['action'];
                }
                return $dispatchInfo;
            }
        }

        if($this->enableAutoMatch){
            return $this->autoMatch($requestPath);
        }
    }

    //设置请求参数
    private  function setParams($params){
        if($params && is_array($params)){
            foreach ($params as $key => $value){
                if($key && $value){
                    $_GET[$key] = $value;
                }
            }
        }
        return;
    }
}

?>
