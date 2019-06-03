<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/21/16
 * Time: 10:06 PM
 */

namespace common\traits;


use yii;
use yii\caching\DbDependency;

trait CacheTrait
{
    /**
     * 保存至缓存
     * @param $cacheKey
     * @param $cacheValue
     * @param null $dependencySql
     * @param int $duration
     * @param bool $withCache
     */
    public static function saveToCache($cacheKey, $cacheValue, $dependencySql = null, $duration = 3600, $withCache = true)
    {
        if ($withCache) {
            if (!empty($dependencySql)) {
                $dependency = new DbDependency();//Add a database dependency, in order to use a new key to replace the cache by manual
                $dependency->sql = $dependencySql;

                Yii::$app->cache->set($cacheKey, $cacheValue, $duration, $dependency);
            }
            else {
                Yii::$app->cache->set($cacheKey, $cacheValue, $duration);
//                $bool = Yii::$app->cache->exists($cacheKey);
            }
        }
    }

    /**
     * 从缓存清除数据
     * @param $cacheKey
     * @return bool
     */
    public static function removeFromCache($cacheKey) {
        return Yii::$app->cache->delete($cacheKey);
    }

    /**
     * 生成缓存Key
     * @param $model
     * @param $key
     * @return bool
     */
    public static function genCacheKey($kid) {
        $name = self::getName();
        $type = self::getCacheType();
        $cacheKey = $type . "-" . $name . "-" . $kid;
        return $cacheKey;
    }

    /**
     * 从缓存清除数据（通过Kid）
     * @param $cacheKey
     * @return bool
     */
    public static function removeFromCacheByKid($kid) {
        $cacheKey = self::genCacheKey($kid);
        return self::removeFromCache($cacheKey);
    }

    /**
     * 从缓存读取数据
     * @param $cacheKey
     * @param $withCache
     * @return mixed|null
     */
    public static function loadFromCache($cacheKey, $withCache = true, &$hasCache = false) {
        $result = null;
        $hasCache = false;
        if ($withCache) {
            if (Yii::$app->cache->exists($cacheKey)) {
                $hasCache = true;
                $result = Yii::$app->cache->get($cacheKey);
            }
        }

        return $result;
    }
}