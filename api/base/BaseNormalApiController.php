<?php

namespace api\base;

use common\services\framework\ExternalSystemService;
use common\models\framework\FwServiceLog;
use common\services\framework\ServiceService;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class BaseNormalApiController extends ActiveController
{
    public $systemKeyCheck = false;
    public $systemKey = null;
    public $serviceCode = "web-api";
    public $serviceId;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
        ];
        $behaviors['baseApiFilter'] = [
            'class' => BaseApiFilter::className(),
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete'], $actions['view']);

        $queryParams = Yii::$app->request->getQueryParams();
        Yii::$app->response->headers->add("Access-Control-Allow-Origin","*");
        if (isset($queryParams['system_key']) && trim($queryParams['system_key']) != "") {
            $systemKey = trim($queryParams['system_key']);

            $this->systemKeyCheck = $this->checkSystemKey($systemKey);

            if ($this->systemKeyCheck)
                $this->systemKey = $systemKey;
        }

        return $actions;
    }

    public function checkSystemKey($systemKey)
    {
        $success = true;

        $externalSystemService = new ExternalSystemService();
        $model = $externalSystemService->findBySystemKey($systemKey);

        if (empty($model))
        {
            $success = false;
        }

        return $success;
    }

    public function beforeAction($action)
    {
        $commonServiceSerivce = new ServiceService();
        if (empty($this->serviceId))
            $this->serviceId = $commonServiceSerivce->getServiceIdByServiceCode($this->serviceCode);

        $codeName = $this->action->id;
        $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_NORMAL, "开始调用：" . $codeName);

        return parent::beforeAction($action);
    }

    public function afterAction($action,$result)
    {
        $commonServiceSerivce = new ServiceService();
        if (empty($this->serviceId))
            $this->serviceId = $commonServiceSerivce->getServiceIdByServiceCode($this->serviceCode);

        $codeName = $this->action->id;
        $resultStr = json_encode($result);
        $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_NORMAL, "结束调用：" . $codeName . "；结果：". $resultStr);

        return parent::afterAction($action,$result);
    }
}