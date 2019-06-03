<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/17/15
 * Time: 12:48 PM
 */

namespace api\base;


use common\services\framework\ActionLogService;
use common\services\framework\ExternalSystemService;
use common\models\framework\FwActionLog;
use common\models\framework\FwActionLogFilter;
use common\models\framework\FwUser;
use common\base\BaseFilter;
use common\helpers\TNetworkHelper;
use Yii;

class BaseApiFilter extends BaseFilter{

    private $systemFlag = 'eln_api';
    private $startTime;

    public function beforeAction($action)
    {
        $this->startTime = microtime(true);

        return parent::beforeAction($action);
    }


    public function afterAction($action,$result)
    {
        $endTime = microtime(true);
        $durationTime = $endTime - $this->startTime;

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userId = $identity->getId();
        } else {
            $userId = '00000000-0000-0000-0000-000000000000';
        }

        $controllerId = $action->controller->id;
        $actionId = $action->id;

        $actionFilterModel = new FwActionLogFilter();
        $actionFilterResult = $actionFilterModel->getRelateActionFilter($controllerId,$actionId,$this->systemFlag);

        if ($actionFilterResult != null) {
            $requestInfo = Yii::$app->getRequest();
            $paramterQuery = $requestInfo->getQueryString();
            $paramterBody = $requestInfo->getRawBody();
            $actionUrl = $requestInfo->url;
            $actionIp = TNetworkHelper::getClientRealIP();
            $systemId = null;
            $encryptMode = FwActionLog::ENCRYPT_MODE_NONE;

            $queryParams = Yii::$app->request->getQueryParams();
            if (isset($queryParams['system_key']) && trim($queryParams['system_key']) != "") {
                $systemKey = trim($queryParams['system_key']);

                $externalSystemService = new ExternalSystemService();
                $model = $externalSystemService->findBySystemKey($systemKey);

                if (!empty($model)) {
                    $systemId = $model->kid;
                    $encryptMode = $model->encrypt_mode;
                }
            }
            else {
                $systemKey = "OTHER";
            }

            $httpMode = FwActionLog::HTTP_MODE_GET;
            if ($requestInfo->isPost)
                $httpMode = FwActionLog::HTTP_MODE_POST;

            $machineLabel = null;
            if (isset(Yii::$app->params['machine_label']))
                $machineLabel = Yii::$app->params['machine_label'];

            $actionLogService = new ActionLogService();
            $actionLogService->insertActionLog($systemId,$actionFilterResult->kid,$userId,$controllerId,$actionId,$paramterQuery,$paramterBody,$encryptMode,$httpMode,
                $actionUrl,$this->systemFlag,$actionIp,$this->startTime,$endTime,$durationTime,$machineLabel,$systemKey);
        }

        return parent::afterAction($action,$result);
    }
}