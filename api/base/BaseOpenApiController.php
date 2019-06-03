<?php

namespace api\base;

use common\services\framework\ExternalSystemService;
use common\models\framework\FwServiceLog;
use common\models\framework\FwUser;
use common\services\framework\ServiceService;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;
use common\services\framework\PointRuleService;
use yii\base\Exception;

class BaseOpenApiController extends ActiveController
{
    public $systemKeyCheck = false;
    public $systemKey = null;
    public $user = null;
    public $serviceCode = "web-api";
    public $serviceId;
    public $accessToken = null;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
//                HttpBasicAuth::className(),
//                HttpBearerAuth::className(),
                BaseQueryParamAuth::className(),
            ],
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

        if (isset($queryParams['access_token']) && trim($queryParams['access_token']) != "") {
            $accessToken = trim($queryParams['access_token']);

            $userModel = FwUser::findIdentityByAccessToken($accessToken);
            if ($userModel !== null) {
                $this->user = $userModel;
                $this->accessToken = $accessToken;
            }
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
        $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_NORMAL, Yii::t('common','start_use_api') . ":" . $codeName);

        return parent::beforeAction($action);
    }

    public function afterAction($action,$result)
    {
        $commonServiceSerivce = new ServiceService();
        if (empty($this->serviceId))
            $this->serviceId = $commonServiceSerivce->getServiceIdByServiceCode($this->serviceCode);

        $codeName = $this->action->id;
        $resultStr = json_encode($result);
        $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_NORMAL, Yii::t('common','end_use_api') . ":" . $codeName . ";" . Yii::t('common','handle_result') . ":" . $resultStr);


        return parent::afterAction($action,$result);
    }
    
    /**
     * 添加积分
     * @param string $actionCode
     * @param string $systemKey
     * @param string $resourceId
     */
    public function curUserCheckActionForPoint($actionCode,$systemKey='',$resourceId='') {
        $cmpid = $this->user->company_id;
        $uid = $this->user->kid;
        try{
        	$pointRuleService = new PointRuleService();
        	return $pointRuleService->checkActionForPoint($cmpid,$uid,$actionCode,$systemKey,$resourceId);
        }catch (Exception $e){
        	
        }
        return null;
            
    }
}