<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/17/15
 * Time: 12:48 PM
 */

namespace backend\base;


use common\models\framework\FwActionLog;
use common\models\framework\FwActionLogFilter;
use common\base\BaseFilter;
use common\helpers\TNetworkHelper;
use common\helpers\TURLHelper;
use common\services\framework\ActionLogService;
use Yii;

class BaseBackFilter extends BaseFilter{

    private $systemFlag = 'eln_backend';
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

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

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

                $httpMode = FwActionLog::HTTP_MODE_GET;
                if ($requestInfo->isPost)
                    $httpMode = FwActionLog::HTTP_MODE_POST;

                $machineLabel = null;
                if (isset(Yii::$app->params['machine_label']))
                    $machineLabel = Yii::$app->params['machine_label'];

                $actionLogService = new ActionLogService();
                $actionLogService->insertActionLog($systemId,$actionFilterResult->kid,$userId,$controllerId,$actionId,$paramterQuery,$paramterBody,$encryptMode,$httpMode,
                    $actionUrl,$this->systemFlag,$actionIp,$this->startTime,$endTime,$durationTime,$machineLabel);
            }
        }

        return parent::afterAction($action,$result);
    }
}