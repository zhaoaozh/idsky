<?php
/*
*idsky 验证类
*(c) zhaoaozh <zhaoaozh@gmail.com> <zhaozhao.name>
*/
namespace Idsky\Lib;
class Validate
{
    protected $_error = array();

    protected static $_message = array(
        'email'    => 'invalid_email',
        'required' => 'empty',
        'max'      => 'above_max',
        'min'      => 'below_min',
        'range'    => 'not_in_rang',
        'ip'       => 'invalid_ip',
        'number'   => 'not_all_numbers',
        'int'      => 'not_int',
        'digit'    => 'not_digit',
        'string'   => 'not_string'
    );

    /**
     * Check if is not empty
     */
    public static function notEmpty($str, $trim = true){
        if (is_array($str)){
            return 0 < count($str);
        }

        return strlen($trim ? trim($str) : $str) ? true : false;
    }

    /*
    *检查多个字段不能同时为空
    **/
    public static function notFullEmpty($data,$list){
        $res=false;
        foreach($list as $k=>$v){
            if(!empty($data[$v])){
                $res=true;
                break;
            }
        }
        return $res;
    }

    /**
     * Match regex
     */
    public static function match($value, $regex){
        return preg_match($regex, $value) ? true : false;
    }

    /**
     * Max
     */
    public static function max($value, $max){
        //一个英文字母 0.5个字符
        if (is_string($value)) $value = (strlen($value)+mb_strlen($value,'UTF8'))/4;
        return $value <= $max;
    }

    /**
     * Min
     */
    public static function min($value, $min){
        //一个英文字母 0.5个字符
        if (is_string($value)) $value = (strlen($value)+mb_strlen($value,'UTF8'))/4;
        return $value >= $min;
    }

    /**
     * Range
     */
    public static function range($value, $range){
        if (is_string($value)) $value = strlen($value);
        return $value >= $range[0] && $value <= $range[1];
    }

    /**
     * Check if in array
     */
    public static function in($value, $list){
        return in_array($value, $list);
    }

    /**
     * Check if is numbers
     */
    public static function number($value){
        return is_numeric($value);
    }

    /**
     * Check if is int
     */
    public static function int($value){
        return is_int($value);
    }

    /**
     * Check if is digit
     */
    public static function digit($value){
        return is_int($value) || ctype_digit($value);
    }

    /**
     * Check if is string
     */
    public static function string($value){
        return is_string($value);
    }

    /*
    *检查个数
    *$data 逗号隔开的字符串 或 一维数组
    **/
    public static function length($data,$length=5){
        if(is_string($data)) $data = explode(',',trim($data,','));
        if(is_array($data)){
            return count($data)<=$length;
        }else{
            return true;
        }
    }

    /**
     * Check if is email
     */
    public static function email($email){
        return preg_match('/[a-zA-Z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+/is', $email) ? true : false;
    }

    /**
    *check if is mobile
    */
    public static function mobile($mobile){
        if (!$mobile) {
            return false;
        }
        return preg_match('/^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^17[0-9]\d{8}$|^18[0-9]\d{8}$/', $mobile) ? true : false;
    }

    /**
     * Check if is url
     */
    public static function url($url){
        if(empty($url)) return false;
        $url = htmlspecialchars_decode($url);
        return preg_match('/^(http|https):\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $url) ? true : false;
    }

    /**
     * Check if is ip
     */
    public static function ip($ip){
        return ((false === ip2long($ip)) || (long2ip(ip2long($ip)) !== $ip)) ? false : true;
    }

    /**
     * Check if is date
     */
    public static function date($date){
        return preg_match('/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/', $date) ? true : false;
    }

    /*
    *检查颜色值是否合法
    **/
    public static function color($color){
        return preg_match('/^[0-9a-fA-F]{6}$/',$color) ? true : false;
    }

    /**
     * Check
     *
     * $rules = array(
     *     'required' => true if required , false for not
     *     'type'     => var type, should be in ('email', 'url', 'ip', 'date', 'number', 'int', 'string')
     *     'regex'    => regex code to match
     *     'func'     => validate function, use the var as arg
     *     'max'      => max number or max length
     *     'min'      => min number or min length
     *     'range'    => range number or range length
     *     'msg'      => error message,can be as an array
     * )
     */
    public function check($data, $rules, $ignorNotExists = false){
        foreach ($rules as $key => $rule) {
            $rule += array('required' => false, 'msg' => self::$_message);
            if(isset($rule['fullcheck'])){
                $res=self::_fullCheck($data,$rule);
                if (0 !== $res['code']) $this->_error[$key] = $res['msg'];
            }
            // deal with not existed
            if (empty($data[$key])){
                if (!$rule['required']) continue;
                if ($ignorNotExists) continue;
                $this->_error[$key] = $this->_msg($rule, 'required');
                continue;
            }

            $value = $data[$key];
            $result = self::_check($value, $rule);
            if (0 !== $result['code']) $this->_error[$key] = $result['msg'];

            if (isset($rule['rules'])) {
                $this->check($value, $rule['rules'], $ignorNotExists);
            }
        }

        return $this->_error;
    }

    /**
     * Check value
     */
    protected function _check($value, $rule){
        if ($rule['required'] && !self::notEmpty($value)) {
            return array('code' => -1, 'msg' => $this->_msg($rule, 'required'));
        }

        if (isset($rule['func']) && !call_user_func($rule['func'], $value)) {
            return array('code' => -1, 'msg' => $this->_msg($rule, 'func'));
        }

        if (isset($rule['regex']) && !self::match($value, $rule['regex'])) {
            return array('code' => -1, 'msg' => $this->_msg($rule, 'regex'));
        }

        $type = $rule['type'];
        if (isset($rule['type']) && !self::$type($value)) {
            return array('code' => -1, 'msg' => $this->_msg($rule, $rule['type']));
        }

        $acts = array('max', 'min', 'range', 'in','length');
        foreach ($acts as $act) {
            if (isset($rule[$act]) && !self::$act($value, $rule[$act])) {
                return array('code' => -1, 'msg' => $this->_msg($rule, $act));
            }
        }

        if (isset($rule['each'])) {
            $rule['each'] += array('required' => false, 'msg' => self::$_message);
            if (isset($rule['msg'])) {
                $rule['each'] += array('msg' => $rule['msg']);
            }
            foreach ($value as $item) {
                $result = $this->_check($item, $rule['each']);
                if (0 !== $result['code']) {
                    return $result;
                }
            }
        }

        return array('code' => 0);
    }

    /*
    *一次验证多个字段
    **/
    private function _fullCheck($data,$rule){
        $acts = array('notFullEmpty');
        foreach ($acts as $act) {
            if (isset($rule[$act]) && !self::$act($data, $rule[$act])) {
                return array('code' => -1, 'msg' => $this->_msg($rule, $act));
            }
        }
        return array('code' => 0);
    }

    /**
     * Get error message
     */
    protected function _msg($rule, $name){
        if (empty($rule['msg'])) return 'INVALID';

        if (is_string($rule['msg'])) return $rule['msg'];

        return isset($rule['msg'][$name]) ? $rule['msg'][$name] : 'INVALID';
    }

    /**
     * Get error
     */
    public function error(){
        return $this->_error;
    }
}
