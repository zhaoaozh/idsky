<?php
/*
*idsky 视图类
*(c) muzizhao <muzizhao.cn>
*/
namespace  Idsky\Core;
class View
{
    protected $viewHome = '';
    protected $tplExt = ".html";
    protected $jsFiles = array();
    protected $cssFiles = array();
    protected $feDomain;
    protected $_controller;
    protected $_action;
    private static $_instance;

    public function __construct($viewHome){
        $idsky = \Idsky::getInstance();
        $this->_controller = $idsky->dispatchInfo['controller'];
        $this->_action = $idsky->dispatchInfo['action'];

        if(is_null($viewHome)){
            $this->viewHome=PRJDIR."/view";
        }else{
            $this->viewHome= $viewHome;
        }
        $this->staticDomain = \C::getByKey('common.static_domain');
    }

    public static function getInstance($viewHome){
        if(null===self::$_instance){
            self::$_instance = new self($viewHome);
        }
        return self::$_instance;
    }

    public function  display($tpl,$dir=''){
        $_content = $this->_display($tpl,$dir);
        ob_start();
        include $this->viewHome."/layout".$this->tplExt;
        ob_get_contents();
    }

    private function _display($tpl,$dir=''){
        ob_start();
        ob_implicit_flush(0);
        $this->fetch($tpl, $dir);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function fetch($tpl,$dir=''){
        if (empty($dir)){
            $dir = $this->viewHome;
        }
        if(empty($tpl)){
            $tpl = $this->defaultTemplate();
        }
        $tpl .=$this->tplExt;
        $dir = rtrim($dir,"/\\").DIRECTORY_SEPARATOR;
        include ($dir . $tpl);
    }

    public function defaultTemplate(){
        $controller = $this->_controller;
        $action = $this->_action;
        if(false !== ($lastNsPos = strripos($controller, '\\'))){
            $spaceArray = explode('\\',$controller);
            if(isset($spaceArray[1]) && $spaceArray[1]=="Controller"){
                unset($spaceArray[1]);
            }
            $path = implode("/",$spaceArray);
        }else{
            $path =  $controller;
        }
        $path = trim($path,"\\");
        $path = trim($path,"/");
        $default = strtolower($path.'/'.$action);
        return $default;
    }

    public function slice($tpl,array $data = array()){
        ob_start();
        $dir = $this->viewHome;
        $tpl .=$this->tplExt;
        $dir = rtrim($dir,"/\\").DIRECTORY_SEPARATOR;
        include ($dir . $tpl);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function css($cssFile,$pre=1){
        $domin= \C::getByKey('common.static_domain');
        $version = \C::getByKey('common.css_version');
        $url = "http://".$domin."/".$cssFile;
        if($version){
            $url .= "?v=".$version;
        }
        if($pre){
            $this->cssFiles[] = $url;
        }else{
            echo '<link href="'.$url.'" rel="stylesheet" type="text/css" />'."\r\n";
        }
    }

    public function js($jsFile,$pre=1){
        $domin= \C::getByKey('common.static_domain');
        $version = \C::getByKey('common.js_version');
        $url = "http://".$domin."/".$jsFile;
        if($version){
            $url .= "?v=".$version;
        }
        if($pre){
            $this->jsFiles[] = $url;
        }else{
            echo '<script type="text/javascript" src="'.$url.'"></script>'."\r\n";
        }
    }

    public function getCss(){
        foreach($this->cssFiles as $val){
            echo '<link href="'.$val.'" rel="stylesheet" type="text/css" />'."\r\n";
        }
    }

    public function getJs(){
        foreach($this->jsFiles as $val){
            echo '<script type="text/javascript" src="'.$val.'"></script>'."\r\n";
        }
    }
}

?>
