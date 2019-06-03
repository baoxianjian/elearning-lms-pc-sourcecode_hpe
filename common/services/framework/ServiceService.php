<?php


namespace common\services\framework;

use common\models\framework\FwService;
use common\models\framework\FwServiceLog;
use Yii;

class ServiceService extends FwService{


    /**
     * 根据服务代码获取服务ID
     * @param $serviceCode
     * @param bool $withCache
     * @return null|string
     */
    public function getServiceIdByServiceCode($serviceCode, $withCache = true)
    {
        $cacheKey = "GetServiceId_ServiceCode_" . $serviceCode;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $result = FwService::findOne(['service_code'=>$serviceCode]);
            self::saveToCache($cacheKey, $result);
        }

        if (!empty($result)) {
            return $result->kid;
        }
        else {
            return null;
        }
    }


    /**
     * 根据服务数据获取服务ID
     * @param $serviceCode
     * @return FwService
     */
    public function getServiceInfoByServiceCode($serviceCode)
    {
        $serviceModel = FwService::findOne(['service_code'=>$serviceCode]);

        return $serviceModel;
    }

    /**
     * 判断服务是否正在运行中
     * @param $serviceId
     * @return bool
     */
    public function isServiceRunning($serviceId)
    {
        $isServiceRunning = false;

        if (!empty($serviceId)) {
            $model = FwService::findOne($serviceId);
            if (!empty($model)) {

                $isServiceRunning = $model->service_status == FwService::SERVICE_STATUS_RUNNING ? true : false;
            }
        }

        return $isServiceRunning;
    }

    /**
     * 判断服务是否需要记录日志
     * @param $serviceId
     * @return bool
     */
    public function isServiceLog($serviceId)
    {
        $isServiceLog = false;

        if (!empty($serviceId)) {
            $model = FwService::findOne($serviceId);
            if (!empty($model)) {

                $isServiceLog = $model->is_log == FwService::YES ? true : false;
            }
        }

        return $isServiceLog;
    }

    /**
     * 记录服务日志
     * @param $serviceId
     * @param $actionStatus
     * @param $serviceLog
     * @return bool
     */
    public function recordServiceLog($serviceId,$actionStatus,$serviceLog)
    {
        //只有服务运行时才记录
        if ($this->isServiceRunning($serviceId)) {
            if ($this->isServiceLog($serviceId)) {
                $model = new FwServiceLog();
                $model->service_id = $serviceId;
                $model->action_status = $actionStatus;
                $model->service_log = $serviceLog;
                $model->save();
            }
        }
    }
}