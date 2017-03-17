<?php
/*
*idsky  redis类
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
**/
namespace Idsky\Ext;

class Redis
{
    private static $_instance;
    private $connects;

    private function __construct(){

    }

    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function connect($key=''){
        $allConfig = \C::getByName('redis');
        if(empty($key)){
            $config = current($allConfig);
        }else{
            $config = $allConfig[$key];
        }
        if($this->connects[$key]){
            return $this->connects[$key];
        }
        try {
            $redis = new \redis();
            $redis->connect($config['host'],$config['port']);
            $this->connects[$key] = $redis;
        } catch (Exception $e){
            throw new \Exception($e->getMessage());
        }

        return $this->connects[$key];
    }
}
