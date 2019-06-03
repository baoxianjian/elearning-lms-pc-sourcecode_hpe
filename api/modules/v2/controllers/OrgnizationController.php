<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\helpers\TMessageHelper;
use common\services\framework\ExternalSystemService;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwServiceLog;
use api\services\OrgnizationService;
use common\services\framework\ServiceService;
use common\services\framework\TreeNodeService;
use Exception;
use Yii;

class OrgnizationController extends BaseOpenApiController{

    public $modelClass = 'api\services\OrgnizationService';

    /**
     * 获取企业所有组织部门记录数接口
     * 通过企业ID获取相关组织部门记录数信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllOrgnizationCount()
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
        } else {
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

                    $orgnizationService = new OrgnizationService();

                    $count = 0;
                    if (!empty($companyId))
                        $count = $orgnizationService->getOrgnizationListCountByCompanyId($companyId);


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
     * 获取企业所有组织部门信息接口
     * 通过企业ID获取相关组织部门基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllOrgnizationInfo()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['orgnization'] = null;
        $limit = "1";
        $offset = "0";

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

                            if (isset($bodyParams['offset']) && $bodyParams['offset'] != "") {
                                $offset = $bodyParams['offset'];
                            }

                            if (isset($bodyParams['limit']) && $bodyParams['limit'] != "") {
                                $limit = $bodyParams['limit'];
                            }
                        }
                    }

                    $externalSystemService = new ExternalSystemService();

                    $orgnizationService = new OrgnizationService();

                    if (!empty($companyId))
                        $modelList = $orgnizationService->getOrgnizationListByCompanyId($companyId,$limit,$offset);

                    if (!empty($modelList) && count($modelList) > 0) {
                        $totalArray = [];
                        foreach ($modelList as $model) {
                            /* @var $model \common\models\framework\FwOrgnization */
                            $externalOrgnizationKey = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $model->kid);
                            $orgnizationResult["orgnization_key"] = $externalOrgnizationKey;
                            $orgnizationResult["orgnization_id"] = $model->kid;
                            $orgnizationResult["company_id"] = $model->company_id;

                            $orgnizationResult["orgnization_code"] = $model->orgnization_code;
                            $orgnizationResult["orgnization_name"] = $model->orgnization_name;
                            $orgnizationResult["orgnization_level"] = $model->orgnization_level;
                            $orgnizationResult["orgnization_manager_id"] = $model->orgnization_manager_id;
                            $orgnizationResult["is_make_org"] = $model->is_make_org;
                            $orgnizationResult["is_service_site"] = $model->is_service_site;
                            $orgnizationResult["domain_id"] = $model->domain_id;

                            if (!empty($model->domain_id))
                                $externalDomainKey = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $model->domain_id);
                            else
                                $externalDomainKey = null;

                            $orgnizationResult["domain_key"] = $externalDomainKey;

                            if (!empty($model->domain_id))
                                $domainModel = FwDomain::findOne($model->domain_id);

                            if (!empty($domainModel)) {
                                /* @var $domainModel \common\models\framework\FwDomain */
                                $orgnizationResult["domain_name"] = $domainModel->domain_name;
                            }
                            else
                                $orgnizationResult["domain_name"] = null;

                            if (!empty($model->parent_orgnization_id)) {
                                $orgnizationResult["parent_orgnization_id"] = $model->parent_orgnization_id;
                            }
                            else {
                                $orgnizationResult["parent_orgnization_id"] = null;
                            }

                            if (!empty($model->parent_orgnization_id))
                                $internalParentOrgnizationKey = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $model->parent_orgnization_id);
                            else
                                $internalParentOrgnizationKey = null;

                            $orgnizationResult["parent_orgnization_key"] = $internalParentOrgnizationKey;

                            if (!empty($model->parent_orgnization_id))
                                $parentOrgnizationModel = FwOrgnization::findOne($model->parent_orgnization_id);

                            if (!empty($parentOrgnizationModel))
                                $orgnizationResult["parent_orgnization_name"] = $parentOrgnizationModel->orgnization_name;
                            else
                                $orgnizationResult["parent_orgnization_name"] = null;

                            $orgnizationResult["status"] = $model->status;

                            if (!empty($model->description)) {
                                $orgnizationResult["description"] = $model->description;
                            }
                            else {
                                $orgnizationResult["description"] = null;
                            }

                            array_push($totalArray, $orgnizationResult);
                        }

                        $jsonResult['orgnization'] = $totalArray;

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
     * 获取组织部门信息接口
     * 通过组织主键获取相关组织基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetOrgnizationInfo()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['orgnization'] = null;

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
                        if (isset($queryParams['orgnization_key']) && $queryParams['orgnization_key'] != "") {
                            $orgnizationKey = TMessageHelper::decryptMsg($this->systemKey, $queryParams['orgnization_key'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "orgnization_key");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "orgnization_key");
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
                            if (isset($bodyParams['orgnization_key']) && $bodyParams['orgnization_key'] != "") {
                                $orgnizationKey = $bodyParams['orgnization_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "orgnization_key");
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
                        $orgnizationId = $externalSystemService->getOrgnizationIdByOrgnizationKey($this->systemKey, $orgnizationKey);
                    else if ($keyType == "2")
                        $orgnizationId = $orgnizationKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($orgnizationId))
                        $model = FwOrgnization::findOne($orgnizationId);
                    /* @var $model \common\models\framework\FwOrgnization */
                    if (!empty($model)) {
                        $externalOrgnizationKey = null;
                        if ($keyType == "1")
                            $externalOrgnizationKey = $orgnizationKey;
                        else {
                            $externalOrgnizationKey = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $orgnizationId);
                        }
                        $orgnizationResult["orgnization_key"] = $externalOrgnizationKey;
                        $orgnizationResult["orgnization_id"] = $model->kid;
                        $orgnizationResult["company_id"] = $model->company_id;

                        $orgnizationResult["orgnization_code"] = $model->orgnization_code;
                        $orgnizationResult["orgnization_name"] = $model->orgnization_name;
                        $orgnizationResult["orgnization_level"] = $model->orgnization_level;
                        $orgnizationResult["orgnization_manager_id"] = $model->orgnization_manager_id;
                        $orgnizationResult["is_make_org"] = $model->is_make_org;
                        $orgnizationResult["is_service_site"] = $model->is_service_site;
                        $orgnizationResult["domain_id"] = $model->domain_id;

                        if (!empty($model->domain_id))
                            $externalDomainKey = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $model->domain_id);
                        else
                            $externalDomainKey = null;

                        $orgnizationResult["domain_key"] = $externalDomainKey;

                        if (!empty($model->domain_id))
                            $domainModel = FwDomain::findOne($model->domain_id);

                        if (!empty($domainModel)) {
                            /* @var $domainModel \common\models\framework\FwDomain */
                            $orgnizationResult["domain_name"] = $domainModel->domain_name;
                        }
                        else
                            $orgnizationResult["domain_name"] = null;

                        $orgnizationResult["parent_orgnization_id"] = $model->parent_orgnization_id;

                        if (!empty($model->parent_orgnization_id))
                            $externalParentOrgnizationKey = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $model->parent_orgnization_id);
                        else
                            $externalParentOrgnizationKey = null;

                        $orgnizationResult["parent_orgnization_key"] = $externalParentOrgnizationKey;

                        if (!empty($model->parent_orgnization_id))
                            $parentOrgnizationModel = FwOrgnization::findOne($model->parent_orgnization_id);

                        if (!empty($parentOrgnizationModel)) {
                            /* @var $parentOrgnizationModel \common\models\framework\FwOrgnization */
                            $orgnizationResult["parent_orgnization_name"] = $parentOrgnizationModel->orgnization_name;
                        }
                        else
                            $orgnizationResult["parent_orgnization_name"] = null;

                        $orgnizationResult["status"] = $model->status;

                        if (!empty($model->description)) {
                            $orgnizationResult["description"] = $model->description;
                        }
                        else {
                            $orgnizationResult["description"] = null;
                        }

                        $jsonResult['orgnization'] = $orgnizationResult;

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
     * 修改组织部门信息接口
     * 此接口用来维护组织部门基本信息。
     * 如果组织主键已存在，则认为是更新对应组织，否则增加新组织。
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionModifyOrgnizationInfo()
    {
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['orgnization'] = null;

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

                            $orgnizationList = $bodyParams['orgnization'];
                            $totalArray = [];

                            $externalSystemService = new ExternalSystemService();

                            if (!empty($orgnizationList) && count($orgnizationList) > 0) {

                                $orgnizationService = new OrgnizationService();
                                foreach ($orgnizationList as $orgnization) {
                                    $newInfo['orgnization_key'] = null;
                                    $newInfo['orgnization_id'] = null;
                                    $newInfo['op_result'] = "2";
                                    $newInfo['error_code'] = null;
                                    $newInfo['error_message'] = null;
                                    $add = true;

                                    $checkType = true;

                                    $orgnizationKeyType = null;
                                    $orgnizationKey = null;
                                    $orgnizationName = null;
                                    $orgnizationCode = null;
                                    $companyId = null;
                                    $orgnizationId = null;

                                    if ($checkType) {
                                        if (isset($orgnization['orgnization_name']) && !empty($orgnization['orgnization_name'])) {
                                            $orgnizationName = $orgnization['orgnization_name'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_name' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_name'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($orgnization['orgnization_code']) && !empty($orgnization['orgnization_code'])) {
                                            $orgnizationCode = $orgnization['orgnization_code'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_code' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_code'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($orgnization['company_id']) && !empty($orgnization['company_id'])) {
                                            $companyId = $orgnization['company_id'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['company_id' => Yii::t('common', 'param_{value}_error', ['value' => 'company_id'])];
                                        }
                                    }

                                    $isMakeOrg = null;
                                    if ($checkType) {
                                        if (isset($orgnization['is_make_org'])) {
                                            $isMakeOrg = $orgnization['is_make_org'];

                                            if ($isMakeOrg == "" || $isMakeOrg == null || ($isMakeOrg !== FwOrgnization::NO && $isMakeOrg !== FwOrgnization::YES)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['is_make_org' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'is_make_org'])];
                                            }
                                        }
                                    }

                                    $isServiceSite = null;
                                    if ($checkType) {
                                        if (isset($orgnization['is_service_site'])) {
                                            $isServiceSite = $orgnization['is_service_site'];

                                            if ($isServiceSite == "" || $isServiceSite == null || ($isServiceSite !== FwOrgnization::NO && $isServiceSite !== FwOrgnization::YES)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['is_service_site' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'is_service_site'])];
                                            }
                                        }
                                    }

                                    $status = null;
                                    if ($checkType) {
                                        if (isset($orgnization['status'])) {
                                            $status = $orgnization['status'];

                                            if ($status == "" || $status == null || ($status !== FwDomain::STATUS_FLAG_NORMAL && $status !== FwDomain::STATUS_FLAG_STOP)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['status' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'status'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($orgnization['orgnization_key_type']) && !empty($orgnization['orgnization_key_type'])) {
                                            $orgnizationKeyType = $orgnization['orgnization_key_type'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key_type'])];
                                        }
                                    }

                                    $orgnizationManagerId = null;

                                    if ($checkType) {
                                        if (isset($orgnization['orgnization_manager_id']) && !empty($orgnization['orgnization_manager_id'])) {
                                            $orgnizationManagerId = $orgnization['orgnization_manager_id'];
                                        } 
                                    }

                                    if ($checkType) {
                                        if (isset($orgnization['orgnization_key']) && !empty($orgnization['orgnization_key'])) {
                                            $orgnizationKey = $orgnization['orgnization_key'];
                                        } else {
                                            if ($orgnizationKeyType != "2") {
                                                //如果是内部Id，允许为空；为空表示创建新记录
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['orgnization_key' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        if ($orgnizationKeyType == "1") {
                                            $orgnizationId = $externalSystemService->getOrgnizationIdByOrgnizationKey($this->systemKey, $orgnizationKey);
                                            $newInfo['orgnization_key'] = $orgnizationKey;
                                            $newInfo['orgnization_id'] = $orgnizationId;
                                        } else if ($orgnizationKeyType == "2") {
                                            $orgnizationId = $orgnizationKey;
                                            $newInfo['orgnization_key'] = null;
                                            $newInfo['orgnization_id'] = $orgnizationId;
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key_type'])];
                                        }

//                                        if (empty($orgnizationId)) {
//                                            $checkType = false;
//                                            $newInfo['error_code'] = $codeName . "-" . "004";
//                                            $newInfo['error_message'] = ['orgnization_key' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key'])];
//                                        }
                                    }

                                    $parentOrgnizationKeyType = null;
                                    $parentOrgnizationKey = null;
                                    $parentOrgnizationId = null;

                                    if ($checkType) {
                                        if (isset($orgnization['parent_orgnization_key_type']) && !empty($orgnization['parent_orgnization_key_type'])) {
                                            $parentOrgnizationKeyType = $orgnization['parent_orgnization_key_type'];
                                        }
                                    }

                                    if ($checkType) {
                                        if (!empty($parentOrgnizationKeyType)) {
                                            if (isset($orgnization['parent_orgnization_key']) && !empty($orgnization['parent_orgnization_key'])) {
                                                $parentOrgnizationKey = $orgnization['parent_orgnization_key'];
                                            }

                                            if ($parentOrgnizationKeyType == "1") {
                                                $parentOrgnizationId = $externalSystemService->getOrgnizationIdByOrgnizationKey($this->systemKey, $parentOrgnizationKey);
                                            } else if ($parentOrgnizationKeyType == "2") {
                                                $parentOrgnizationId = $parentOrgnizationKey;
                                            } else {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['parent_orgnization_key_type' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'parent_orgnization_key_type'])];
                                            }
                                        }
                                    }


                                    $domainKeyType = null;
                                    $domianKey = null;
                                    $domianId = null;

                                    if ($checkType) {
                                        if (isset($orgnization['domain_key_type']) && !empty($orgnization['domain_key_type'])) {
                                            $domainKeyType = $orgnization['domain_key_type'];
                                        }
                                    }

                                    if ($checkType) {
                                        if (!empty($domainKeyType)) {
                                            if (isset($orgnization['domain_key']) && !empty($orgnization['domain_key'])) {
                                                $domianKey = $orgnization['domain_key'];
                                            }

                                            if ($domainKeyType == "1") {
                                                $domianId = $externalSystemService->getDomainIdByDomainKey($this->systemKey, $domianKey);
                                            } else if ($domainKeyType == "2") {
                                                $domianId = $domianKey;
                                            }
                                            else {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['domain_key_type' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'domain_key_type'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        $model = null;
                                        if (!empty($orgnizationId)) {
                                            $model = FwOrgnization::findOne($orgnizationId);
                                        }

                                        if (isset($model) && !empty($model)) {
                                            $add = false;
                                        } else {
                                            $model = new FwOrgnization();

                                            if ($status === null) {
                                                $model->status = FwOrgnization::STATUS_FLAG_NORMAL;
                                            }

                                            if ($isMakeOrg === null) {
                                                $model->is_make_org = FwOrgnization::NO;
                                            }

                                            if ($isServiceSite === null) {
                                                $model->is_service_site = FwOrgnization::NO;
                                            }
                                        }

                                        $model->attributes = $orgnization;

                                        $model->orgnization_name = $orgnizationName;
                                        $model->orgnization_code = $orgnizationCode;
                                        $model->company_id = $companyId;


                                        if (!empty($parentOrgnizationKeyType)) {
                                            //如果传了keyType，说明是要修改id的（哪怕是变成null）
                                            $model->parent_orgnization_id = $parentOrgnizationId;
                                        }

                                        if (!empty($domainKeyType)) {
                                            $model->domain_id = $domianId;
                                        }
                                        
                                        if (!empty($orgnizationManagerId)) {
                                            $model->orgnization_manager_id = $orgnizationManagerId;
                                        }

                                        $model->setScenario("api-manage");

                                        $treeType = "orgnization";

                                        $treeNodeService = new TreeNodeService();

                                        if (!empty($model->parent_orgnization_id)) {
                                            $parentNodeId = FwOrgnization::findOne($model->parent_orgnization_id)->tree_node_id;
                                        } else {
                                            $parentNodeId = null;
                                        }

                                        if ($add) {
                                            $model->setScenario("api-manage-add");
                                        }
                                        else {
                                            $model->setScenario("api-manage-update");
                                        }
                                        
                                        if ($model->validate()) {
                                            $systemKey = $this->systemKey;
                                            $model->data_from = $systemKey;
                                            $model->systemKey = $systemKey;

                                            $orgnizationName = $model->orgnization_name;
                                            $orgnizationCode = $model->orgnization_code;

                                             if ($orgnizationService->isExistSameOrgnizationCode($model->kid, $companyId, $orgnizationCode)) {
                                                 $newInfo['error_code'] = $codeName . "-" . "001";
                                                 $newInfo['error_message'] = ["orgnization_code" => Yii::t('common', 'exist_same_code_{value}',
                                                     ['value' => Yii::t('common', 'orgnization_code')]) . $orgnizationCode];
                                                 $checkType = false;
                                             }

                                            if ($checkType) {
                                                if ($add) {
                                                    $treeNodeId = $treeNodeService->addTreeNode($treeType, $orgnizationName, $parentNodeId, $orgnizationCode);

                                                    $model->tree_node_id = $treeNodeId;
                                                    $model->needReturnKey = true;
                                                    if ($model->save()) {

                                                        $orgnizationId = $model->kid;
                                                        $newInfo['orgnization_id'] = $orgnizationId;
                                                        $newInfo['op_result'] = "0";

                                                        if ($orgnizationKeyType == "1")
                                                            $externalSystemService->addExternalSystemOrgnizationKey($this->systemKey, $orgnizationKey, $orgnizationId);
                                                    }
                                                    else {
                                                        $newInfo['error_code'] = $codeName . "-" . "003";
                                                        $newInfo['error_message'] = "Data Insert Error";
                                                    }
                                                } else {
                                                    $treeNodeId = $model->tree_node_id;
                                                    $treeNodeService->updateTreeNode($treeNodeId, $orgnizationName, $parentNodeId, $orgnizationCode);
                                                    if ($model->save()) {
                                                        $newInfo['orgnization_id'] = $orgnizationId;
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
                                $jsonResult['orgnization'] = $totalArray;
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
     * 删除组织部门信息接口
     * 通过组织主键删除指定组织部门基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDeleteOrgnizationInfo()
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
                            if (isset($bodyParams['orgnization_key']) && $bodyParams['orgnization_key'] != "") {
                                $orgnizationKey = $bodyParams['orgnization_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "orgnization_key");
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
                        $orgnizationId = $externalSystemService->getOrgnizationIdByOrgnizationKey($this->systemKey, $orgnizationKey);
                    else if ($keyType == "2")
                        $orgnizationId = $orgnizationKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($orgnizationId)) {
                        $model = FwOrgnization::findOne($orgnizationId);

                        $externalSystemService = new ExternalSystemService();
                        $externalSystemService->deleteOrgnizationInfoByOrgnizationId($orgnizationId);
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