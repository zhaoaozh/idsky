<?php
/*
*Request 类
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/
namespace Idsky\Lib;
class Request
{
    //获取get参数
    public static function get($key='',$default=''){
        if(!$key){
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    //获取post参数
    public static function post($key='',$default=''){
        if(!$key){
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    //获取input数据
    public static function input($parse=false){
        $input =  file_get_contents('php://input','r');
        if($parse){
            parse_str($input, $inputArr);
            return $inputArr;
        }
        return $input;
    }

    //获取所有请求参数
    public static function params(){
        $params = array();
        if($_GET) $params += $_GET;
        if($_POst) $params +=$_POST;
        return $params;
    }

    //获取IP
    public static function clientIp($default = '0.0.0.0'){
        $keys = array('HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR','HTTP_CLIENT_IP');
        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) continue;
            $ips = explode(',', $_SERVER[$key]);
            $ip = $ips[0];
            return $ip;
        }
        return $default;
    }

    public static function header($header){
        if (empty($header)) {
            return null;
        }
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    public static function isAjax(){
        return ('XMLHttpRequest' == self::header('X_REQUESTED_WITH'));
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public static function isFlashRequest(){
        return ('Shockwave Flash' == self::header('USER_AGENT'));
    }
}
?>
