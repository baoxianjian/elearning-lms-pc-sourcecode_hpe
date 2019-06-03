<?php


namespace common\services\framework;


use common\models\framework\FwActionLogFilter;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class ActionLogFilterService extends FwActionLogFilter{

    /**
     * 获取日志过滤器
     * @param $filterCode
     * @return array|null|FwActionLogFilter
     */
    public function getActionLogFilterByCode($filterCode, $withCache = true)
    {
        $cacheKey = "ActionLogFilter_Code_" . $filterCode;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = FwActionLogFilter::find(false);

            $result = $model
                ->andFilterWhere(['=','filter_code',$filterCode])
                ->one();

//            $dependencySql = "SELECT * FROM " . self::calculateTableName(FwActionLogFilter::tableName()) . " WHERE filter_code = '" . $filterCode . "' and is_deleted ='0'";
            
            if ($withCache) {
                self::saveToCache($cacheKey, $result);
            }
        }

        return $result;
    }
}