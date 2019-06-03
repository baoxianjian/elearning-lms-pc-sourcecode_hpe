<?php


namespace common\services\framework;

use common\models\framework\FwCompany;
use common\models\framework\FwSystemInfo;
use common\models\framework\FwTag;
use common\models\framework\FwTagCategory;
use common\services\framework\RbacService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class SystemInfoService extends FwSystemInfo{

    /**
     * 获取系统信息
     * @return array|null|FwSystemInfo
     */
    public function getSystemInfo($systemCode = "PC", $withSession = true)
    {
        $cacheKey = "System_Info";

        $systemInfo = BaseActiveRecord::loadFromCache($cacheKey, $withSession, $hasCache);

        if (empty($systemInfo) && !$hasCache) {
            $model = new FwSystemInfo();
            $systemInfo = $model->find(false)
                ->andFilterWhere(['=', 'system_code', $systemCode])
                ->one();

            BaseActiveRecord::saveToCache($cacheKey, $systemInfo);
        }

        return $systemInfo;
    }
}