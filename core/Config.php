<?php
/*
*idsky  配置文件管理类
*(c) muzizhao <muzizhao.cn>
**/
namespace Idsky\Core;
class Config
{
    public static function getRunEnv(){
        if(!empty($_SERVER['RUN_ENV']) && !defined('RUN_ENV')){
            define('RUN_ENV',$_SERVER['RUN_ENV']);
        }else{
            if(file_exists('/etc/env') && !defined('RUN_ENV')){
                $env = file_get_contents('/etc/env');
                define('RUN_ENV',trim($env));
            }
        }
        return defined('RUN_ENV') ? RUN_ENV : 'dev';
    }

    public static function setRunEnv($env){
        if(isset($env)){
            $_SERVER['RUN_ENV'] = $env;
        }
    }

    public static function getByName($name){
        global $_PRJDIR;
        $runEnv = self::getRunEnv();
        $configPath = $_PRJDIR."/config/".$name.".conf.php";
        if(!file_exists($configPath)){
            $configPath = $_PRJDIR."/config/".$runEnv.'/'.str_replace('.','/',$name).".conf.php";
        }
        if(file_exists($configPath)){
            $config = self::loadConfig($configPath);
        }
        return $config;
    }

    //$key  文件名.配置项  例  'common.mysql'
    public static function getByKey($key,$default=null){
        $config = array();
        $_array = explode('.',$key);
        $_key = array_pop($_array);
        $_name = implode('/',$_array);
        $allConfig = self::getByName($_name);
        if($allConfig && $allConfig[$_key]){
            $config = $allConfig[$_key];
        }
        return $config ? $config : $default;
    }

    public static function loadConfig($configPath){
        $config = include($configPath);
        return $config;
    }

}
