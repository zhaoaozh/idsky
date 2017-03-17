<?php
    /*
    *idsky 未使用idsky框架使用的调用API
    *(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
    **/
    defined('IDSKYDIR') || define('IDSKYDIR', dirname(__FILE__));
    defined('PRJDIR') || define('PRJDIR',dirname(dirname(__FILE__)));
    include_once (IDSKYDIR.'/core/Autoload.php');
    include_once (IDSKYDIR.'/vendor/autoload.php');
    include_once (IDSKYDIR.'/lib/Global.php');
    $GLOBALS['_PRJDIR'] = PRJDIR;
?>
