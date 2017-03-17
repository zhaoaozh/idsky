<?php
/*
*idsky mysql_pdo类
*(c) muzizhao <muzizhao.cn>
**/
namespace Idsky\Ext\Mysql;
class Pdo
{
    private static $_instance;
    private $connections;
    private $inTransaction = 0;
    private $pdo;
    private $result;

    private function __construct(){

    }

    public static function getInstance(){
        if(!self::$_instance){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function factory($database='',$masterOrSlave='master'){
        $config = \C::getByName('mysql');
        if(!$database){
            $database = current(array_keys($config));
        }
        $dbConfig = isset($config[$database]) ? $config[$database] : "";

        if(isset($dbConfig['master']) && isset($dbConfig['slave'])){
            $dbConfig = $dbConfig[$masterOrSlave];
            if(!empty($this->connections[$database][$masterOrSlave])){
                return $this->connections[$database][$masterOrSlave];
            }
            $dbConfig['database'] = isset($dbConfig['database']) ? $dbConfig['database'] : $database;
            return $this->connections[$database][$masterOrSlave] = $this->connect($dbConfig);
        }else{
            if(!empty($this->connections[$database]['single'])){
                return $this->connections[$database]['single'];
            }
            $dbConfig['database'] = isset($dbConfig['database']) ? $dbConfig['database'] : $database;
            return $this->connections[$database]['single'] =  $this->connect($dbConfig);
        }
    }

    private function connect($config){
        $config['port'] = isset($config['port']) ? $config['port'] : '3306';
        $config['charset'] = isset($config['charset']) ? $config['charset'] : 'utf8';
        $dsn = "mysql:host=".$config['host'].";port=".$config['port'].";dbname=".$config['database'];
        try{
            $this->pdo = new \PDO($dsn,$config['username'],$config['password'], array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$config['charset']
            ));
        } catch(PDOException $e){
            throw new Exception($e->getMessage());
        }
        return $this->pdo;
    }

    private function masterOrSlave($sql){
        $is = 'slave';
        if($this->inTransaction){
            $is = 'master';
        }else{
            $_mkws = array('insert','replace','update','delete');
            $_arr = explode(' ',trim($sql),2);
            if(in_array(strtolower($_arr[0]),$_mkws)){
                $is= 'master';
            }
        }
        return $is;
    }

    public function query($sql,array $data=array(),$database=''){
        $masterOrSlave = $this->masterOrSlave($sql);
        $pdo = $this->factory($database,$masterOrSlave);
        $dbt = debug_backtrace();
        $_sql=$sql;
        if($dbt){
            $dbt_count = count($dbt);
            $dbt1 = $dbt_count>=2 ? $dbt[1] : $dbt[0];
            if($dbt1 && $dbt1['file']!=__FILE__){
                $_sql='/*'.date("Y-m-d H:i:s").'--'.$dbt1['file'].':'.$dbt1['line'].'*/'.$sql;
            }
        }
        $this->result = $pdo->prepare($_sql);
        $res = $this->result->execute($data);
        if($res===FALSE){
            $this->error($_sql);
        }
        return $this;
    }

    private function error($sql){
        $error = '';
        $error .= $this->pdo->errorCode();
        $error .= ":".$this->pdo->errorInfo();
        $error .=" sql:".$sql;
        error_log($error."\r\n",3,"/tmp/sql_error.log");
        throw new Exception($error);
    }

    public function fetch(){
        return $this->result->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchAll($key=''){
        $data = $this->result->fetchAll(\PDO::FETCH_ASSOC);
        if($key){
            $_data = array();
            foreach($data as $v){
                $_data[$v[$key]] = $v;
            }
            $data = $_data;
        }
        return $data;
    }

    public function rowCount(){
        return $this->result->rowCount();
    }

    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }


    //常用操作
    public function insert($data,$table='',$database=''){
        $fields = $keys = $_data = array();
        foreach($data as $k=>$v){
            if(is_null($v)) continue;
            $_key = ":".$k;
            $fields[] = $k;
            $keys[] = $_key;
            $_data[$_key] = $v;
        }
        $sql = "insert into ".$table." (".implode(",",$fields).") values (".implode(",",$keys).")";
        return $this->query($sql,$_data,$database)->lastInsertId();
    }

    public function delete($where,$table='',$database=''){
        $whereStr = "";
        $_data = array();
        foreach($where as $k=>$v){
            if(is_null($v)) continue;
            $_key = ":".$k;
            $whereStr .= empty($whereStr) ? $k."=".$_key : " and ".$k."=".$_key;
            $_data[$_key] = $v;
        }
        $sql = "delete from ".$table;
        if($whereStr){
            $sql .= " where ".$whereStr;
        }
        return $this->query($sql,$_data,$database)->rowCount();
    }

    public function update($data,$where,$table='',$database=''){
        if(empty($data) || empty($where)){
            return false;
        }
        $setStr = $whereStr = "";
        $_data = array();
        foreach($data as $k=>$v){
            if(is_null($v)) continue;
            $_key = ":s_".$k;
            $setStr .= empty($setStr) ? $k."=".$_key : ",".$k."=".$_key;
            $_data[$_key] = $v;
        }
        foreach($where as $k=>$v){
            if(is_null($v)) continue;
            $_key = ":w_".$k;
            $whereStr .= empty($whereStr) ? $k."=".$_key : " and ".$k."=".$_key;
            $_data[$_key] = $v;
        }
        $sql = "update ".$table." set ".$setStr;
        if($whereStr){
            $sql .= " where ".$whereStr;
            return $this->query($sql,$_data,$database)->rowCount();
        }
        return false;
    }

    public function select($where,$table='',$database=''){
        $whereStr = "";
        $_data = array();
        foreach($where as $k=>$v){
            if(is_null($v)) continue;
            $_key = ":".$k;
            $whereStr .= empty($whereStr) ? $k."=".$_key : " and ".$k."=".$_key;
            $_data[$_key] = $v;
        }
        $sql = "select * from ".$table;
        if($whereStr){
            $sql .= " where ".$whereStr;
        }
        return $this->query($sql,$_data,$database)->fetchAll();
    }


    public function selectOne($where,$table='',$database=''){
        $whereStr = "";
        $_data = array();
        foreach($where as $k=>$v){
            if(is_null($v)) continue;
            $_key = ":".$k;
            $whereStr .= empty($whereStr) ? $k."=".$_key : " and ".$k."=".$_key;
            $_data[$_key] = $v;
        }
        $sql = "select * from ".$table;
        if($whereStr){
            $sql .= " where ".$whereStr;
        }
        $sql .= " limit 1";
        return $this->query($sql,$_data,$database)->fetch();
    }
}
