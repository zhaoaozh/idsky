<?php
/*
*idsky 小工具助手
*(c) muzizhao <muzizhao.cn>
**/
namespace Idsky\Lib;
use Zhuzhichao\IpLocationZh\Ip;
use DynamicCrypto\DynamicCryptoFactory;
use Endroid\QrCode\QrCode;

class Helper{

    //对象转数组
    public static function objToArr($obj){
        if(is_object($obj)) {
            $obj = (array)$obj;
            $obj = self::objToArr($obj);
        } elseif(is_array($obj)) {
            foreach($obj as $key => $value) {
                $obj[$key] = self::objToArr($value);
            }
        }
        return $obj;
    }

    //数组转对象
    public static function arrToObj($arr){
        if(is_array($arr)){
            $arr = (object) $arr;
            $arr = self::arrToObj($arr);
        }elseif(is_object($arr)){
            foreach($arr as $key=>$value){
                $arr->$key = self::arrToObj($value);
            }
        }
        return $arr;
    }

    //xml
    public static function parseXml($xmlStr,$attribute=false){
        $xmlObj = simplexml_load_string($xmlStr,'SimpleXMLElement',LIBXML_NOCDATA);
        $xmlArr = self::objToArr($xmlObj);
        foreach($xmlArr as $k=>$v){
            if(empty($v)){
                unset($xmlArr[$k]);
            }
        }
        $_attributes = array();
        if($attribute){
            foreach($xmlObj as $key=>$child){
                $attributeData = $child->attributes();
                $attributeArr = self::objToArr($attributeData);
                $_attributes[$key] = $attributeArr['@attributes'];
            }
            $xmlArr['_attributes'] = $_attributes;
        }
        return $xmlArr;
    }

    //加密
    public static function encrypt($str,$key='idcool'){
        if(!$str) return false;
        $dynamicEncrypt = DynamicCryptoFactory::buildDynamicEncrypter($key);
        return $dynamicEncrypt->encrypt($str);
    }

    //解密
    public static function decrypt($str,$key='idcool'){
        if(!$str) return false;
        $dynamicDecrypt = DynamicCryptoFactory::buildDynamicDecrypter($key);
        return $dynamicDecrypt->decrypt($str);
    }

    //curl
    public static function curl($url,$data=array(),$method='get',$userAgent='',$header=array(),$cookies=array()){
        $curl = new \Curl\Curl();
        if($cookies){
            foreach($cookies as $k=>$v){
                $curl->setCookie($k,$v);
            }
        }

        if($header){
            foreach($header as $k=>$v){
                $curl->setHeader($k,$v);
            }
        }
        if($userAgent){
            $curl->setUserAgent($userAgent);
        }
        $error = $curl->$method($url,$data);
        if($error){
            throw new \Exception($error.":".$curl->error_message);
        }else{
            $header = $curl ->response_headers;
            $body = $curl->response;
            return array('header'=>$header,'body'=>$body);
        }
    }

    //ip2city
    public static function ip2city($ip){
        return Ip::find($ip);
    }

    public static function safeShow($content){
        if (! is_scalar($content)){
            return null;
        }
        echo htmlspecialchars($content, ENT_QUOTES);
    }


    public static function strCut($string, $maxLength,$terminator='...',$encoding='utf-8'){
        if(mb_strlen($string, $encoding) > $maxLength){
            if($terminator){
                return mb_substr($string,0, $maxLength-1,$encoding).$terminator;
            }else{
                return mb_substr($string,0, $maxLength, $encoding).$terminator;
            }
        }
        return $string;
    }

    public static function isMobile(){
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = array(
                    'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo',
                    'iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini',
                    'operamobi','openwave','nexusone','cldc','midp','wap','mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])){
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            }
        }
        return false;
    }

    //生成二维码
    public static function qrcode($text,$size=100,$padding=2){
        $qrCode = new QrCode();
        $qrCode
            ->setText($text)
            ->setSize($size)
            ->setPadding($padding)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel("")
            ->setLabelFontSize(16)
            ->render();
    }

    //获取客户端IP
    public static function ip(){
        return \Idsky\Lib\Request::clientIp();
    }

    public static function isWeixin(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, 'MicroMessenger') === false){
            // 非微信浏览器禁止浏览
            return false;
        }else{
            // 微信浏览器，允许访问
            return true;
        }
    }

    //生成md5签名
    public static function md5Sign($str,$key=''){
        return md5(md5($str.$key).$key);
    }

    public static function xssClean($data){
        $xss = new \Idsky\Ext\Xss();
        return $xss->cleanInput($data);
    }
}
?>
