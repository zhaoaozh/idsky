<?php
/*
*idsky  自动加载类
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/
namespace Idsky\Core;
class Autoload
{
    public static function load($className,$classFile=''){
        global $_PRJDIR;
        if (class_exists($className, false) || interface_exists($className, false)){
            return true;
        }
        //autoload class file
        if(isset($classFile) && file_exists($classFile)){
            include_once($classFile);
            unset($classFile);
            return true;
        }
        if(false !== ($lastNsPos = strripos($className, '\\'))) {
            $nameSpace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $spaceArray = explode('\\',$nameSpace);
            $topSpace = current($spaceArray);
            if($topSpace=='Idsky'){
                $classPath = IDSKYDIR.strtolower(str_replace(array("\\",$topSpace),array("/",""),$nameSpace));
            }else{
                $classPath = $_PRJDIR."/".strtolower(str_replace("\\","/",$nameSpace));
            }
            $classFile = $classPath."/".$className.".php";
        }
        if(file_exists($classFile)){
            include_once($classFile);
            return true;
        }
        return false;
    }

    public static function register($func = 'self::load', $enable = true){
        return $enable ? spl_autoload_register($func) : spl_autoload_unregister($func);
    }
}
Autoload::register();
?>
