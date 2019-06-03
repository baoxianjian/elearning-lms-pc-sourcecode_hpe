<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\models\framework\FwPosition;
use common\services\framework\UserPositionService;
use common\helpers\TMessageHelper;
use api\services\PositionService;
use common\services\framework\ExternalSystemService;
use common\models\framework\FwServiceLog;
use common\services\framework\ServiceService;
use Exception;
use Yii;

class PositionController extends BaseOpenApiController{

    public $modelClass = 'api\services\PositionService';

    /**
     * 获取企业所有岗位记录数接口
     * 通过企业ID获取相关岗位记录数信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllPositionCount()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        if (!$this->systemKeyCheck) {
            $number = "002";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
        else {
            $commonServiceSerivce = new ServiceService();
            try {
                $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
                if ($isServiceRunning) {
                    if (Yii::$app->request->isGet) {
                        if (isset($queryParams['company_id']) && $queryParams['company_id'] != "") {
                            $companyId = TMessageHelper::decryptMsg($this->systemKey, $queryParams['company_id'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "company_id");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "company_id");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                    } else {
                        $rawBody = Yii::$app->request->getRawBody();
                        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                        if (!empty($errorCode)) {
                            $number = "004";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        } else {
                            $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
                            if (isset($bodyParams['company_id']) && $bodyParams['company_id'] != "") {
                                $companyId = $bodyParams['company_id'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "company_id");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }

                        }
                    }

                    $positionService = new PositionService();

                    $count = 0;
                    if (!empty($companyId))
                        $count = $positionService->getPositionListCountByCompanyId($companyId);

                    $jsonResult["count"] = $count;

                    $code = "OK";
                    $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);

                    return $result;
                } else {
                    $number = "005";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, null, null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
            } catch (Exception $e) {
                $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());
                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }

    /**
     * 获取企业所有岗位信息接口
     * 通过企业ID获取相关岗位基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllPositionInfo()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['position'] = null;
        $limit = "1";
        $offset = "0";

        if (!$this->systemKeyCheck) {
            $number = "002";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
        else {
            $commonServiceSerivce = new ServiceService();
            try {
                $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
                if ($isServiceRunning) {
                    if (Yii::$app->request->isGet) {
                        if (isset($queryParams['company_id']) && $queryParams['company_id'] != "") {
                            $companyId = TMessageHelper::decryptMsg($this->systemKey, $queryParams['company_id'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "company_id");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "company_id");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($queryParams['offset']) && $queryParams['offset'] != "" && is_int($queryParams['offset'])) {
                            $offset = TMessageHelper::decryptMsg($this->systemKey, $queryParams['offset'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "offset");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }

                        if (isset($queryParams['limit']) && $queryParams['limit'] != "" && is_int($queryParams['limit'])) {
                            $limit = TMessageHelper::decryptMsg($this->systemKey, $queryParams['limit'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "limit");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }
                    } else {
                        $rawBody = Yii::$app->request->getRawBody();
                        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                        if (!empty($errorCode)) {
                            $number = "004";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        } else {
                            $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
                            if (isset($bodyParams['company_id']) && $bodyParams['company_id'] != "") {
                                $companyId = $bodyParams['company_id'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "company_id");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }

                            if (isset($bodyParams['offset']) && $bodyParams['offset'] != "" && is_int($bodyParams['offset'])) {
                                $offset = $bodyParams['offset'];
                            }

                            if (isset($bodyParams['limit']) && $bodyParams['limit'] != "" && is_int($bodyParams['limit'])) {
                                $limit = $bodyParams['limit'];
                            }
                        }
                    }

                    $externalSystemService = new ExternalSystemService();

                    $positionService = new PositionService();

                    if (!empty($companyId))
                        $modelList = $positionService->getPositionListByCompanyId($companyId,$limit,$offset);

                    if (!empty($modelList) && count($modelList) > 0) {
                        $totalArray = [];
                        foreach ($modelList as $model) {
                            /* @var $model \common\models\framework\FwPosition */
                            $externalPositionKey = $externalSystemService->getPositionKeyByPositionId($this->systemKey, $model->kid);
                            $positionResult["position_key"] = $externalPositionKey;
                            $positionResult["position_id"] = $model->kid;
                            $positionResult["company_id"] = $model->company_id;

                            $positionResult["position_code"] = $model->position_code;
                            $positionResult["position_name"] = $model->position_name;
                            $positionResult["position_type"] = empty($model->position_type) ? null : $model->position_type;
                            $positionResult["position_level"] = empty($model->position_level) ? null : $model->position_level;
                            $positionResult["responsibilities"] = empty($model->responsibilities) ? null : $model->responsibilities;
                            $positionResult["capabilities"] = empty($model->capabilities) ? null : $model->capabilities;
                            $positionResult["description"] = empty($model->description) ? null : $model->description;
                            $positionResult["status"] = $model->status;
                            $positionResult["share_flag"] = $model->share_flag;

                            array_push($totalArray, $positionResult);
                        }

                        $jsonResult['position'] = $totalArray;

                        $code = "OK";
                        $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);

                        return $result;
                    } else {
                        $code = $codeName;
                        $number = "006";
                        $name = Yii::t('common', 'data_not_exist');
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }
                } else {
                    $number = "005";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, null, null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
            } catch (Exception $e) {
                $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());
                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }

    /**
     * 获取岗位信息接口
     * 通过岗位主键获取相关岗位基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetPositionInfo()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['position'] = null;

        if (!$this->systemKeyCheck) {
            $number = "002";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        } else {
            $commonServiceSerivce = new ServiceService();
            try {
                $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
                if ($isServiceRunning) {
                    if (Yii::$app->request->isGet) {
                        if (isset($queryParams['position_key']) && $queryParams['position_key'] != "") {
                            $positionKey = TMessageHelper::decryptMsg($this->systemKey, $queryParams['position_key'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "position_key");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "position_key");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($queryParams['key_type']) && $queryParams['key_type'] != "") {
                            $keyType = TMessageHelper::decryptMsg($this->systemKey, $queryParams['key_type'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "key_type");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }
                    } else {
                        $rawBody = Yii::$app->request->getRawBody();
                        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                        if (!empty($errorCode)) {
                            $number = "004";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        } else {
                            $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
                            if (isset($bodyParams['position_key']) && $bodyParams['position_key'] != "") {
                                $positionKey = $bodyParams['position_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "position_key");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }

                        if (isset($bodyParams['key_type']) && $bodyParams['key_type'] != "") {
                            $keyType = $bodyParams['key_type'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }
                    }

                    $externalSystemService = new ExternalSystemService();
                    if ($keyType == "1")
                        $positionId = $externalSystemService->getPositionIdByPositionKey($this->systemKey, $positionKey);
                    else if ($keyType == "2")
                        $positionId = $positionKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($positionId))
                        $model = FwPosition::findOne($positionId);

                    if (!empty($model)) {
                        /* @var $model \common\models\framework\FwPosition */
                        $externalPositionKey = null;
                        if ($keyType == "1")
                            $externalPositionKey = $positionKey;
                        else {
                            $externalPositionKey = $externalSystemService->getPositionKeyByPositionId($this->systemKey, $positionId);
                        }
                        $positionResult["position_key"] = $externalPositionKey;
                        $positionResult["position_id"] = $model->kid;
                        $positionResult["company_id"] = $model->company_id;

                        $positionResult["position_code"] = $model->position_code;
                        $positionResult["position_name"] = $model->position_name;
                        $positionResult["position_type"] = empty($model->position_type) ? null : $model->position_type;
                        $positionResult["position_level"] = empty($model->position_level) ? null : $model->position_level;
                        $positionResult["responsibilities"] = empty($model->responsibilities) ? null : $model->responsibilities;
                        $positionResult["capabilities"] = empty($model->capabilities) ? null : $model->capabilities;
                        $positionResult["description"] = empty($model->description) ? null : $model->description;


                        $positionResult["status"] = $model->status;
                        $positionResult["share_flag"] = $model->share_flag;

                        $jsonResult['position'] = $positionResult;

                        $code = "OK";
                        $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);

                        return $result;
                    } else {
                        $code = $codeName;
                        $number = "006";
                        $name = Yii::t('common', 'data_not_exist');
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }
                } else {
                    $number = "005";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, null, null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
            } catch (Exception $e) {
                $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());
                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }


    /**
     * 修改岗位信息接口
     * 此接口用来维护岗位基本信息。
     * 如果岗位主键已存在，则认为是更新对应岗位，否则增加新岗位。
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionModifyPositionInfo()
    {
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['position'] = null;

        if (Yii::$app->request->isPost) {
//            $queryParams = Yii::$app->request->getQueryParams();

            if (!$this->systemKeyCheck) {
                $number = "002";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            } else {
                $commonServiceSerivce = new ServiceService();
                try {
                    $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
                    if ($isServiceRunning) {
                        $rawBody = Yii::$app->request->getRawBody();
                        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                        if (!empty($errorCode)) {
                            $number = "004";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        } else {
                            $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

                            $positionList = $bodyParams['position'];

                            $totalArray = [];
                            if (!empty($positionList) && count($positionList) > 0) {
                                $positionService = new PositionService();
                                foreach ($positionList as $position) {
                                    $newInfo['position_key'] = null;
                                    $newInfo['position_id'] = null;
                                    $newInfo['op_result'] = "2";
                                    $newInfo['error_code'] = null;
                                    $newInfo['error_message'] = null;
                                    $add = true;

                                    $externalSystemService = new ExternalSystemService();

                                    $checkType = true;

                                    $companyId = null;
                                    $positionKey = null;
                                    $positionKeyType = null;
                                    $positionId = null;
                                    $postionCode = null;
                                    $postionName = null;

                                    if ($checkType) {
                                        if (isset($position['company_id']) && !empty($position['company_id'])) {
                                            $companyId = $position['company_id'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['company_id' => Yii::t('common', 'param_{value}_error', ['value' => 'company_id'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($position['position_code']) && !empty($position['position_code'])) {
                                            $postionCode = $position['position_code'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['position_code' => Yii::t('common', 'param_{value}_error', ['value' => 'position_code'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($position['position_name']) && !empty($position['position_name'])) {
                                            $postionName = $position['position_name'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['position_name' => Yii::t('common', 'param_{value}_error', ['value' => 'position_name'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($position['position_key_type']) && !empty($position['position_key_type'])) {
                                            $positionKeyType = $position['position_key_type'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['position_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'position_key_type'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($position['position_key']) && !empty($position['position_key'])) {
                                            $positionKey = $position['position_key'];
                                        } else {
                                            if ($positionKeyType != "2") {
                                                //如果是内部Id，允许为空；为空表示创建新记录
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['position_key' => Yii::t('common', 'param_{value}_error', ['value' => 'position_key'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        if ($positionKeyType == "1") {
                                            $positionId = $externalSystemService->getPositionIdByPositionKey($this->systemKey, $positionKey);
                                            $newInfo['position_key'] = $positionKey;
                                            $newInfo['position_id'] = $positionId;
                                        } else if ($positionKeyType == "2") {
                                            $positionId = $positionKey;
                                            $newInfo['position_key'] = null;
                                            $newInfo['position_id'] = $positionId;
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['position_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'position_key_type'])];
                                        }
                                    }

                                    $status = null;
                                    if ($checkType) {
                                        if (isset($position['status'])) {
                                            $status = $position['status'];

                                            if ($status == "" || $status == null || ($status !== FwPosition::STATUS_FLAG_NORMAL
                                                    && $status !== FwPosition::STATUS_FLAG_STOP)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['status' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'status'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        $model = null;
                                        if (!empty($positionId)) {
                                            $model = FwPosition::findOne($positionId);
                                        }

                                        if (isset($model) && !empty($model)) {
                                            $add = false;
                                        }
                                        else {

                                            $model = new FwPosition();

                                            if ($status === null) {
                                                $model->status = FwPosition::STATUS_FLAG_NORMAL;
                                            }

                                            $model->share_flag = FwPosition::SHARE_FLAG_EXCLUSIVE;
                                            $model->limitation = FwPosition::LIMITATION_NONE;
                                        }

                                        $model->company_id = $companyId;
                                        $model->position_code = $postionCode;
                                        $model->position_name = $postionName;

                                        $model->attributes = $position;
                                        $model->setScenario("api-manage");

                                        if ($model->validate()) {
                                            $systemKey = $this->systemKey;


                                            if ($positionService->isExistSamePositionCode($model->kid,  $companyId, $postionCode)) {
                                                $newInfo['error_code'] = $codeName . "-" . "001";
                                                $newInfo['error_message'] = ["position_code" => Yii::t('common', 'exist_same_code_{value}',
                                                    ['value' => Yii::t('common', 'position_code')]) . $postionCode];
                                                $checkType = false;
                                            }

                                            if ($checkType) {
                                                if ($add) {
                                                    $model->data_from = $systemKey;
                                                    $model->systemKey = $systemKey;

                                                    $model->needReturnKey = true;
                                                    if ($model->save()) {

                                                        $positionId = $model->kid;
                                                        $newInfo['position_id'] = $positionId;
                                                        $newInfo['op_result'] = "0";

                                                        if ($positionKeyType == "1")
                                                            $externalSystemService->addExternalSystemPositionKey($this->systemKey, $positionKey, $positionId);
                                                    }
                                                    else {
                                                        $newInfo['error_code'] = $codeName . "-" . "003";
                                                        $newInfo['error_message'] = "Data Insert Error";
                                                    }
                                                } else {
                                                    $model->systemKey = $systemKey;
                                                    if ($model->save()) {
                                                        $newInfo['position_id'] = $positionId;
                                                        $newInfo['op_result'] = "1";
                                                    }
                                                    else {
                                                        $newInfo['error_code'] = $codeName . "-" . "003";
                                                        $newInfo['error_message'] = "Data Update Error";
                                                    }
                                                }
                                            }
                                        } else {
                                            $newInfo['error_code'] = $codeName . "-" . "003";
                                            $newInfo['error_message'] = $model->getFirstErrors();
                                        }
                                    }

                                    array_push($totalArray, $newInfo);
                                }

                                $code = "OK";
                                $jsonResult['position'] = $totalArray;
                                $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);
                                return $result;

                            } else {
                                $code = $codeName;
                                $number = "006";
                                $name = Yii::t('common', 'data_not_exist');
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }

                    } else {
                        $number = "005";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, null, null);
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }
                } catch (Exception $e) {
                    $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());
                    $number = "001";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
            }
        } else {
            $number = "003";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
    }

    /**
     * 删除岗位信息接口
     * 通过岗位主键删除指定岗位基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDeletePositionInfo()
    {
//        $queryParams = Yii::$app->request->getQueryParams();
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['delete_result'] = false;

        if (!$this->systemKeyCheck) {
            $number = "002";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        } else {
            $commonServiceSerivce = new ServiceService();
            try {
                $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
                if ($isServiceRunning) {
                    if (Yii::$app->request->isGet) {
                        $number = "003";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    } else {
                        $rawBody = Yii::$app->request->getRawBody();
                        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                        if (!empty($errorCode)) {
                            $number = "004";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        } else {
                            $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
                            if (isset($bodyParams['position_key']) && $bodyParams['position_key'] != "") {
                                $positionKey = $bodyParams['position_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "position_key");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }

                            if (isset($bodyParams['key_type']) && $bodyParams['key_type'] != "") {
                                $keyType = $bodyParams['key_type'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }
                    }

                    $externalSystemService = new ExternalSystemService();
                    if ($keyType == "1")
                        $positionId = $externalSystemService->getPositionIdByPositionKey($this->systemKey, $positionKey);
                    else if ($keyType == "2")
                        $positionId = $positionKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($positionId)) {
                        $model = FwPosition::findOne($positionId);

                        $externalSystemService = new ExternalSystemService();
                        $externalSystemService->deletePositionInfoByPositionId($positionId);
                    }

                    if (!empty($model)) {
                        $model->systemKey = $this->systemKey;
                        if ($model->delete()) {
                            $code = "OK";
                            $jsonResult['delete_result'] = true;

                            $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);

                            return $result;
                        } else {
                            $code = $codeName;
                            $number = "007";
                            $name = Yii::t('common', 'operation_confirm_warning_failure');
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);

                            return $result;
                        }
                    } else {
                        $code = $codeName;
                        $number = "006";
                        $name = Yii::t('common', 'data_not_exist');
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }
                } else {
                    $number = "005";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, null, null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
            } catch (Exception $e) {
                $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());

                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }
}