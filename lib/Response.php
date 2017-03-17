<?php
/*
*Response 类
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/
namespace Idsky\Lib;
class Response{

    public static function redirect($url, $code = 302){
        header("Location:$url", true, $code);
        exit();
    }

    public static function json($status=1,$info='',$data='',$header=true){
        $jsonData = array();
        $jsonData['status'] = $status;
        $jsonData['info'] = $info;
        $jsonData['data'] = $data;
        if($header){
           header("Content-Type:application/json;charset=UTF-8");
        }
        ob_clean();//clear output:Notice and others
        echo json_encode($jsonData);
        exit();
    }
    public static function xml($xml,$header=true){
        if($header){
            header("Content-Type:application/xml;charset=UTF-8");
        }
        ob_clean();
        echo $xml;
        exit();
    }
}
?>
