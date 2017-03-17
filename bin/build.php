<?php
/*
*idsky 创建项目脚本
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/

$projectName = $argv[1];
define('IDSKYDIR',realpath(dirname(__FILE__)."/../"));
$projectDir = IDSKYDIR.'/../'.$projectName;
define("ROOTDIR",IDSKYDIR.'/../');
define("PRJDIR",$projectDir);
if(file_exists(PRJDIR)){
    echo "项目".PRJDIR."已存在!\n";
    exit;
}

buildProjectDir();
buildConfig();
buildIndex($projectName);

_buildDefaultModel();
_buildDefaultController();
_buildDefaultView();

function buildProjectDir(){
    if(is_writeable(ROOTDIR)){
        $dirs = array(
            PRJDIR,
            PRJDIR.'/webroot',
            PRJDIR.'/config',
            PRJDIR.'/model',
            PRJDIR.'/controller',
            PRJDIR.'/view',
            PRJDIR.'/lib', //项目类包
            PRJDIR.'/vendor',//第三方包
        );
        foreach ($dirs as $dir){
            if(!is_dir($dir) && !file_exists($dir))  mkdir($dir,0755,true);
        }
    }else{
        echo "目录".ROOTDIR."不可写,无法自动生成,请手动创建项目目录～";
        exit;
    }
}

function buildConfig(){
    $dirs = array(
        PRJDIR.'/config/dev',
        PRJDIR.'/config/qa',
        PRJDIR.'/config/pro',
    );
    foreach ($dirs as $dir){
        if(!is_dir($dir) && !file_exists($dir))  mkdir($dir,0755,true);
    }
    _buildRouterConfig();
    _buildCommonConfig();
    _buildMysqlConfig();
    _buildRedisConfig();
    _buildMongoConfig();
}

function _buildRouterConfig(){
$content = '<?php
return array(
    "|^index\/index\/(\d+)|"=>array(
        "controller"=>"Index", //或者  "controller"=>"\Controller\Index",
        "action"=>"index",
        "maps"=>array("1"=>"type"),
    ),
);
';
    $routerFile = PRJDIR.'/config/route.conf.php';
    if(!file_exists($routerFile)){
        file_put_contents($routerFile,$content);
    }
}

function _buildCommonConfig(){
$devContents = '<?php
return array(
    "static_domain"=>"static.xx.com",
    "css_version"=>"201503311210",
    "js_version"=>"201503311210",
    "debug"=>true,
    "error_log"=>"/tmp/error.log",
);
';
$qaContents = '<?php
return array(
    "static_domain"=>"static.xx.com",
    "css_version"=>"201503311210",
    "js_version"=>"201503311210",
    "debug"=>true,
    "error_log"=>"/tmp/error.log",
);
';
$proContents = '<?php
return array(
    "static_domain"=>"static.xx.com",
    "css_version"=>"201503311210",
    "js_version"=>"201503311210",
    "debug"=>false,
    "error_log"=>"/tmp/error.log",
);
';
    $commonFiles = array(
        PRJDIR.'/config/dev/common.conf.php'=>$devContents,
        PRJDIR.'/config/qa/common.conf.php'=>$qaContents,
        PRJDIR.'/config/pro/common.conf.php'=>$proContents,
    );
    foreach ($commonFiles as $file => $content) {
        if(!file_exists($file)){
            file_put_contents($file,$content);
        }
    }
}

function _buildMysqlConfig(){
$content = '<?php
return array(
    //数据库 demo
    //单服务器
    "demo" => array(
        "host"=>"127.0.0.1",
        "username"=>"root",
        "password"=>"123456",
        "port"=>"3306",
        "charset"=>"utf8",
    ),

    //主从
    /*
    "demo" => array(
        "master"=>array(
            "host"=>"127.0.0.1",
            "username"=>"root",
            "password"=>"123456",
            "port"=>"3306",
            "charset"=>"utf8",
        ),
        "slave"=>array(
            "host"=>"127.0.0.1",
            "username"=>"root",
            "password"=>"123456",
            "port"=>"3306",
            "charset"=>"utf8",
        ),

    ),
    */
);';
    $mysqlFiles = array(
        PRJDIR.'/config/dev/mysql.conf.php'=>$content,
        PRJDIR.'/config/qa/mysql.conf.php'=>$content,
        PRJDIR.'/config/pro/mysql.conf.php'=>$content,
    );
    foreach ($mysqlFiles as $file => $content) {
        if(!file_exists($file)){
            file_put_contents($file,$content);
        }
    }
}

function _buildRedisConfig(){
$content = '<?php
return array(
    "cache"=>array(
        "host"=>"127.0.0.1",
        "port"=>"6379",
    ),
);
';
    $redisFiles = array(
        PRJDIR.'/config/dev/redis.conf.php'=>$content,
        PRJDIR.'/config/qa/redis.conf.php'=>$content,
        PRJDIR.'/config/pro/redis.conf.php'=>$content,
    );
    foreach ($redisFiles as $file => $content) {
        if(!file_exists($file)){
            file_put_contents($file,$content);
        }
    }
}

function _buildMongoConfig(){
$content = '<?php
return array(
    "log"=>array("server"=>"mongodb://192.168.1.115:27017"),
    );
';
    $mongoFiles = array(
        PRJDIR.'/config/dev/mongo.conf.php'=>$content,
        PRJDIR.'/config/qa/mongo.conf.php'=>$content,
        PRJDIR.'/config/pro/mongo.conf.php'=>$content,
    );
    foreach ($mongoFiles as $file => $content) {
        if(!file_exists($file)){
            file_put_contents($file,$content);
        }
    }
}

function buildIndex($projectName){
    $indexFile = PRJDIR.'/webroot/index.php';
$content = '<?php
//定义项目目录
define("PRJDIR",realpath(dirname(__FILE__)."/../../'.$projectName.'"));
include_once(PRJDIR."/../idsky/Idsky.php");
$instance = Idsky::getInstance();
$instance->init()->run();';
    if(!file_exists($indexFile)){
        file_put_contents($indexFile,$content);
    }
}

function _buildDefaultModel(){
    $modelFile = PRJDIR.'/model/Index.php';
$content = '<?php
namespace Model;
class Index extends \Model
{
    public function demo(){
        $result  = array("msg"=>"this is idsky demo");
        return $result;
    }
}';
    if(!file_exists($modelFile)){
        file_put_contents($modelFile,$content);
    }
}

function _buildDefaultController(){
    $controllerFile = PRJDIR.'/controller/Index.php';
$content = '<?php
namespace Controller;
class Index extends \Controller{
    public function index(){
        $data = array("titile"=>"idsky","msg"=>"this is a demo base on framework of idsky ");
        $this->view->data=$data;
        $this->title = "idsky demo";
        $this->keywords = "idsky demo";
        $this->description = "idsky demo";
        $this->display();
    }
}';
    if(!file_exists($controllerFile)){
        file_put_contents($controllerFile,$content);
    }
}

function _buildDefaultView(){
    $viewDir = PRJDIR.'/view/index';
    if(!is_dir($viewDir) && !file_exists($viewDir))  mkdir($viewDir,0755,true);
    $viewFile = $viewDir.'/index.html';
$content = '<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <title><?php echo $this->title;?></title>
        <meta name="keywords" content="<?php echo $this->keywords;?>">
        <meta name="description" content="<?php echo $this->description;?>">
    </head>
    <body>
        <h2><?php echo $this->data["msg"];?></h2>
    </body>
</html>';
    if(!file_exists($viewFile)){
        file_put_contents($viewFile,$content);
    }
}
