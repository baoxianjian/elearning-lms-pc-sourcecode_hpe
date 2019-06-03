<?php

namespace api\base;

use common\services\framework\ExternalSystemService;
use common\models\framework\FwUser;
use common\services\framework\PointRuleService;
use common\services\framework\UserService;
use Exception;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

/**
 * BaseController
 */
class BaseController extends Controller
{
    public $defaultPageSize;

    const DELETE_FLAG_NO = "0";
    const DELETE_FLAG_YES = "1";

    const STATUS_FLAG_TEMP = "0";
    const STATUS_FLAG_NORMAL = "1";
    const STATUS_FLAG_STOP = "2";

    public $systemKeyCheck = false;
    public $systemKey = null;
    public $user = null;


    public function init()
    {
        if ($this->defaultPageSize == null)
        {
            if (isset(Yii::$app->params['defaultPageSize'])) {
                $this->defaultPageSize = Yii::$app->params['defaultPageSize'];
            }
            else {
                $this->defaultPageSize = 10;
            }
        }

    }
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


        $queryParams = Yii::$app->request->getQueryParams();

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
