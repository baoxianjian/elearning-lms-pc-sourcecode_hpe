<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/5/11
 * Time: 11:46
 */
namespace common\traits;

trait HelperTrait {
    private function _isset($object,$key,$default = null) {
        if(is_array($object)) return isset($object[$key]) ? $object[$key] : $default;
        if(is_object($object)) return isset($object->{$key}) ? $object->{$key} : $default;
        return $default;
    }

    public function arrayDeep($array) {
        if(!is_array($array)) return 0;
        $level = 0;
        foreach($array as $arr) {
            $tmp = $this->arrayDeep($arr);
            if($tmp > $level) $level = $tmp;
        }
        return $level + 1;
    }
}