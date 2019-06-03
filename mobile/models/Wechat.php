<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/5
 * Time: 11:26
 */

namespace  mobile\models;

class Wechat {
    public static function exists($openid) {
        //todo 数据库检查及缓存
        return true;
    }
    
    public static function createOrUpdate(array $info) {
        return true;
    }
}