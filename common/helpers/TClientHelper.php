<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/15/15
 * Time: 5:00 PM
 */

namespace common\helpers;


class TClientHelper {

    /**
     * 根据php的$_SERVER['HTTP_USER_AGENT'] 中各种浏览器访问时所包含各个浏览器特定的字符串来判断是属于PC还是移动端
     * @param $browserName
     * @return bool
     */
    public function isMobile(&$browserName) {

        //各个触控浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
        $touchBrowserList = ['iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
            'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
            'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
            'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
            'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
            'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
            'benq', 'haier', '^lct', '320x320', '240x320', '176x220'];

        //window手机浏览器数组【猜的】
        static $mobileBrowserList = ['windows phone'];

        //wap浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
        static $wmlBrowserList = ['cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
            'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
            'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte'];

        $padList = ['pad', 'gt-p1000'];
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if($v = $this->dstrpos($userAgent, $padList, true)) {
            $browserName = $v;
            return true;
        }

        if(($v = $this->dstrpos($userAgent, $mobileBrowserList, true))){
            $browserName = $v;
            return true;
        }

        if(($v = $this->dstrpos($userAgent, $touchBrowserList, true))){
            $browserName = $v;
            return true;
        }

        if(($v = $this->dstrpos($userAgent, $wmlBrowserList, true))) {
            $browserName = $v;
            return true; //wml版
        }

        $brower = ['mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop'];
        if($v =$this->dstrpos($userAgent, $brower, true)) {
            $browserName = $v;
            return false;
        }
        else {
            $browserName = 'unknown';
            return false;
        }
    }


    /**
     * 判断$arr中元素字符串是否有出现在$string中
     * @param $string $_SERVER['HTTP_USER_AGENT']
     * @param $arr $_SERVER['HTTP_USER_AGENT']中必定会包含的字符串
     * @param bool|false $returnValue 返回浏览器名称还是返回布尔值，true为返回浏览器名称，false为返回布尔值【默认】
     * @return bool
     */
    private function dstrpos($string, $arr, $returnValue = false) {
        if(empty($string)) return false;
        foreach((array)$arr as $v) {
            if(strpos($string, $v) !== false) {
                $return = $returnValue ? $v : true;
                return $return;
            }
        }
        return false;
    }

    /**
     * 根据php的$_SERVER['HTTP_USER_AGENT'] 中各种浏览器访问时所包含各个浏览器特定的字符串来判断程序是否支持
     * @return bool
     */
    public function isSupported()
    {
        $notSupportedBrowserList = ['msie 9.0', 'msie 8.0', 'msie 7.0', 'msie 6.0', 'msie 5.0'];

        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if ($this->dstrpos($userAgent, $notSupportedBrowserList, false)) {
            return false;
        }
        return true;
    }
}