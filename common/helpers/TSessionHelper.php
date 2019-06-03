<?php
namespace common\helpers;

use yii;

class TSessionHelper
{
    private $_obj;
    public function __construct ()
    {
        if ($this->_obj == null)
            $this->_obj = Yii::$app->session;


    }

    /**
     * 设置session
     * @param data 数据,可以是数组
     */
    public function set ($name, $value = '', $expire = 0, $path = '')
    {
        //$session = new CHttpSession();
        $this->_obj->open();
//        $expire && $this->_obj->expire = $expire;
//        $expire && $this->_obj->path = $path;
        $this->_obj[$name] = $value;
    }

    /**
     * 获取session
     */
    public function get ($name, $once = false)
    {
        //$session = new CHttpSession();
        $this->_obj->open();
        $data = $this->_obj[$name];
        if ($once)
            $this->_obj->remove($name);
        return $data;
    }

    /**
     * 清除session
     * @param  $name
     */
    public function clear ($name)
    {
        $this->_obj->remove($name);
    }
}

?>