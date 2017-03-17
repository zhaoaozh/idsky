<?php
/*
*idsky 全局函数
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/
class_alias("\Idsky\Core\Model","\Model");
class_alias("\Idsky\Core\View","\View");
class_alias("\Idsky\Core\Controller","\Controller");

class_alias("\Idsky\Core\Config","\C");
class_alias("\Idsky\Lib\Helper","\H");

class_alias("\Idsky\Lib\Response","\Response");
class_alias("\Idsky\Lib\Request","\Request");
class_alias("\Idsky\Lib\Validate","\Validate");
/*
*创建类的实例
*@$class 类名中包括命名空间
*@params  类构造函数参数
*/
function N($class,$params=array()){
    $_params="";
    if($params) $_params = implode(",",$params);
    try{
        return new $class($_params);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

/*
*调用类方法
*@$class  [类名]:[方法名]  类名中包括命名空间
*@params  调用方法的参数
*/
function F($class,$params=array()){
    $arr = explode(':',$class);
    $class = $arr[0];
    if(!$class) $class = 'base';
    if(!in_array($class,array('base'))){
        throw new Exception('不允许调用此项目中的接口');
    }
    $method = $arr[1];
    $obj = N($class);
    try{
        return call_user_func_array(array($obj,$method),$params);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

/*
*远程调用其他项目方法
*@$api 格式  [项目名]:[类名]:[方法名]  类名包含命名空间
*@$params 方法参数
*/
function API($api,$params=array()){
    global $_PRJDIR;
    if(strripos($api,':')){
        $arr = explode(':',$api);
        $project = $arr[0];
        $class = $arr[1];
        $method = $arr[2];
        $_PRJDIR  = realpath(PRJDIR."/../".$project);
        if(!file_exists($_PRJDIR)){
            $_PRJDIR = PRJDIR;
            throw new Exception('项目'.$project."不存在!");
        }

        $obj = new $class();
        try{
            $result  = call_user_func_array(array($obj,$method),$params);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        $_PRJDIR = PRJDIR;
        return $result;
    }else{
        return null;
    }
}

?>
