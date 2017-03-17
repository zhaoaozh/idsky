<?php
/*
*idsky mongo类
*(c) muzizhao <muzizhao.cn>
**/
namespace Idsky\Ext;
class Mongo
{
    private static $_instance;
    private $collections;
    private $mongo;

    private function __construct(){

    }

    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function connect($database=''){
        $allConfig = \C::getByName('mongo');
        if(empty($database)){
            $database = current(array_kes($allConfig));
        }

        if(empty($this->collections[$database])){
            $config = $allConfig[$database];
            $_class = '\MongoClient';
            if(!class_exists($_class)){
                $_class = '\Mongo';
            }
            $this->collections[$database] = new $_class($config['server']);
            $this->collections[$database]->selectDB($database);
        }
        return $this->mongo = $this->collections[$database];
    }

    public function selectCollection ($database,$collection){
        return $this->mongo->selectCollection($database,$collection);
    }
}
