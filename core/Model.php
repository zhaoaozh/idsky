<?php
/*
*idsky 模型基类
*(c) muzizhao <muzizhao.cn>
*/
namespace  Idsky\Core;
class Model
{
    private $mysql;
    private $redis;
    private $mongo;

    public function mysql(){
        return $this->mysql = \Idsky\Ext\Mysql\Pdo::getInstance();
    }

    public function redis($key=''){
        $instance = \Idsky\Ext\Redis::getInstance();
        return $this->redis = $instance->connect($key);
    }

    public function mongo($database,$collection){
        $instance = \Idsky\Ext\Mongo::getInstance();
        $mongo = $instance->connect($database);
        return $this->mongo = $mongo ->selectCollection($database,$collection);
    }

    public function __set($key, $value = null){
        $this->$key = $value;
    }

    public function __get($key){
        switch ($key) {
            case 'mysql':
                return $this->mysql ? $this->mysql : $this->mysql();
                break;
            case 'redis':
                return $this->redis ? $this->redis : $this->redis();
                break;
            case 'mongo':
                return $this->mongo ? $this->mongo : $this->mongo();
                break;
            default:
                break;
        }
    }
}

?>
