<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\models\framework\FwServiceLog;
use common\services\api\CompanyService;
use common\services\framework\ServiceService;
use Exception;
use Yii;

class CompanyController extends BaseOpenApiController{

    public $modelClass = 'common\services\api\CompanyService';

    /**
     * 获取企业记录数接口
     * 获取相关企业记录数信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCompanyCount()
    {
        $companyService = new CompanyService($this->systemKey);
        $commonServiceSerivce = new ServiceService();

        $code = "common";
        $codeName = $this->action->id;

        if(!$this->systemKeyCheck) {
            return $companyService->exception(['code' => $code]);
        }
        $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
        if(!$isServiceRunning) {
            return $companyService->exception(['number' => '005']);
        }

        try{
            return $companyService->getCompanyCount();
        } catch(Exception $e) {
            $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());
            return $companyService->exception(['number' => '001','message' => $e->getMessage()]);
        }
    }

    /**
     * 获取企业信息接口
     * 获取相关企业基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetCompanyInfo()
    {
        $companyService = new CompanyService($this->systemKey);
        $commonServiceSerivce = new ServiceService();

        if(!$this->systemKeyCheck) {
            return $companyService->exception(['number' => '002']);
        }
        $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
        if(!$isServiceRunning) {
            return $companyService->exception(['number' => '005']);
        }
        $codeName = $this->action->id;
        return $companyService->getCompanyInfo($codeName);
    }
}