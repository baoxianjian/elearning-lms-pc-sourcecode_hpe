<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\services\framework\ExternalSystemService;
use common\models\framework\FwDomain;
use common\services\framework\ServiceService;
use Yii;
use common\helpers\TMessageHelper;
use common\models\treemanager\FwTreeNode;
use common\services\framework\TreeNodeService;
use common\models\framework\FwServiceLog;
use common\services\api\DomainService as ApiDomainService;

class DomainController extends BaseOpenApiController{

    public $modelClass = 'common\services\api\DomainService';

    /**
     * 获取企业所有域记录数接口
     * 通过企业ID获取相关域记录数
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllDomainCount()
    {
        $apiDomainService = new ApiDomainService($this->systemKey);
        if(!$this->systemKeyCheck) {
            return $apiDomainService->exception(['code' => 'common','number' => '002']);
        }
        $commonServiceSerivce = new ServiceService();
        $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
        if(!$isServiceRunning) {
            return $apiDomainService->exception(['code' => 'common','number' => '005']);
        }
        $params = Yii::$app->request->isGet ? Yii::$app->request->getQueryParams() : Yii::$app->request->getRawBody();

        $params = $apiDomainService->parseParams($params, ['company_id'], Yii::$app->request->isGet,true);

        $validator = $apiDomainService->validator($params, [
            'company_id' => 'required'
        ]);
        if (!$validator->success) {
            return $apiDomainService->exception(['code' => 'common', 'number' => '001', 'param' => $validator->errors[0]['field']]);
        }
        $count = 0;
        if (!empty($params['company_id']))
            $count = $apiDomainService->getDomainListCountByCompanyId($params['company_id']);

        return $apiDomainService->response(['code' => 'common','data' => ['count' => $count]]);
    }

    /**
     * 获取企业所有域信息接口
     * 通过企业ID获取相关域基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllDomainInfo()
    {
        $apiDomainService = new ApiDomainService($this->systemKey);
        if(!$this->systemKeyCheck) {
            return $apiDomainService->exception(['code' => 'common','number' => '002']);
        }
        $commonServiceSerivce = new ServiceService();
        $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
        if(!$isServiceRunning) {
            return $apiDomainService->exception(['code' => 'common','number' => '005']);
        }
        $params = Yii::$app->request->isGet ? Yii::$app->request->getQueryParams() : Yii::$app->request->getRawBody();
        $params = $apiDomainService->parseParams($params, ['company_id','offset','limit'], Yii::$app->request->isGet,true);
        $validator = $apiDomainService->validator($params, [
            'company_id' => 'required'
        ]);
        if (!$validator->success) {
            return $apiDomainService->exception(['code' => 'common', 'number' => '001', 'param' => $validator->errors[0]['field']]);
        }
        $externalSystemService = new ExternalSystemService();
        $domains = $apiDomainService->getDomainListByCompanyId($params['company_id'],isset($params['limit']) ? $params['limit'] : 1,intval($params['offset']));

        if (empty($domains)) {
            return $apiDomainService->exception(['number' => '006', 'code' => $this->action->id, 'name' => Yii::t('common', 'data_not_exist')]);
        }
        $totalArray = [];
        foreach($domains as $model) {
            $externalDomainKey = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $model->kid);
            $domainResult["domain_key"] = $externalDomainKey;
            $domainResult["domain_id"] = $model->kid;
            $domainResult["company_id"] = $model->company_id;
            $domainResult["domain_code"] = $model->domain_code;
            $domainResult["domain_name"] = $model->domain_name;
            $domainResult["share_flag"] = $model->share_flag;
            if (!empty($model->parent_domain_id)) {
                $domainResult["parent_domain_id"] = $model->parent_domain_id;
                $externalParentDomainKey = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $model->parent_domain_id);
                $domainResult["parent_domain_key"] = $externalParentDomainKey;
                $parentDomainModel = FwDomain::findOne($model->parent_domain_id);
            }
            else {
                $domainResult["parent_domain_id"] = null;
                $domainResult["parent_domain_key"] = null;
                $parentDomainModel = null;
            }

            if (!empty($parentDomainModel)) {
                $domainResult["parent_domain_name"] = $parentDomainModel->domain_name;
            }
            else
                $domainResult["parent_domain_name"] = null;

            $domainResult["status"] = $model->status;
            $domainResult["description"] = empty($model->description) ? null: $model->description;

            array_push($totalArray, $domainResult);
        }
        return $apiDomainService->response(['code' => 'OK','data' => ['domain' => $totalArray]]);
    }

    /**
     * 获取域信息接口
     * 通过域主键获取相关域基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetDomainInfo()
    {
        $apiDomainService = new ApiDomainService($this->systemKey);
        if(!$this->systemKeyCheck) {
            return $apiDomainService->exception(['code' => 'common','number' => '002']);
        }
        $commonServiceSerivce = new ServiceService();
        $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
        if(!$isServiceRunning) {
            return $apiDomainService->exception(['code' => 'common','number' => '005']);
        }
        $params = Yii::$app->request->isGet ? Yii::$app->request->getQueryParams() : Yii::$app->request->getRawBody();
        $params = $apiDomainService->parseParams($params, ['domain_key','key_type'], Yii::$app->request->isGet,true);
        $validator = $apiDomainService->validator($params, [
            'domain_key' => 'required',
            'key_type' => 'required'
        ]);
        if (!$validator->success) {
            return $apiDomainService->exception(['code' => 'common', 'number' => '001', 'param' => $validator->errors[0]['field']]);
        }

        return $apiDomainService->detail($params['domain_key'],$params['key_type']);
    }


    /**
     * 修改域信息接口
     * 此接口用来维护域基本信息。
     * 如果域主键已存在，则认为是更新对应域，否则增加新域。
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionModifyDomainInfo()
    {
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['domain'] = null;

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

                            $domainList = $bodyParams['domain'];
                            $totalArray = [];

                            if (!empty($domainList) && count($domainList) > 0) {

//                                $domainService = new DomainService();

                                foreach ($domainList as $domain) {
                                    $newInfo['domain_key'] = null;
                                    $newInfo['domain_id'] = null;
                                    $newInfo['op_result'] = "2";
                                    $newInfo['error_code'] = null;
                                    $newInfo['error_message'] = null;
                                    $add = true;

                                    $externalSystemService = new ExternalSystemService();

                                    $checkType = true;

                                    $companyId = null;
                                    $domainKey = null;
                                    $domainKeyType = null;
                                    $domainId = null;
                                    if ($checkType) {
                                        if (isset($domain['company_id']) && !empty($domain['company_id'])) {
                                            $companyId = $domain['company_id'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['company_id' => Yii::t('common', 'param_{value}_error', ['value' => 'company_id'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($domain['domain_key_type']) && !empty($domain['domain_key_type'])) {
                                            $domainKeyType = $domain['domain_key_type'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['domain_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'domain_key_type'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($domain['domain_key']) && !empty($domain['domain_key'])) {
                                            $domainKey = $domain['domain_key'];
                                        } else {
                                            if ($domainKeyType != "2") {
                                                //如果是内部Id，允许为空；为空表示创建新记录
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['domain_key' => Yii::t('common', 'param_{value}_error', ['value' => 'domain_key'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        if ($domainKeyType == "1") {
                                            $domainId = $externalSystemService->getDomainIdByDomainKey($this->systemKey, $domainKey);
                                            $newInfo['domain_key'] = $domainKey;
                                            $newInfo['domain_id'] = $domainId;
                                        } else if ($domainKeyType == "2") {
                                            $domainId = $domainKey;
                                            $newInfo['domain_key'] = null;
                                            $newInfo['domain_id'] = $domainId;
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['domain_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'domain_key_type'])];
                                        }
                                    }

                                    $parentDomainKeyType = null;
                                    $parentDomainKey = null;
                                    $parentDomainId = null;

                                    if ($checkType) {
                                        if (isset($domain['parent_domain_key_type']) && !empty($domain['parent_domain_key_type'])) {
                                            $parentDomainKeyType = $domain['parent_domain_key_type'];
                                        }
                                    }

                                    if ($checkType) {
                                        if (!empty($parentDomainKeyType)) {
                                            if (isset($domain['parent_domain_key']) && !empty($domain['parent_domain_key'])) {
                                                $parentDomainKey = $domain['parent_domain_key'];
                                            }

                                            if ($parentDomainKeyType == "1") {
                                                $parentDomainId = $externalSystemService->getDomainIdByDomainKey($this->systemKey, $parentDomainKey);
                                            } else if ($parentDomainKeyType == "2") {
                                                $parentDomainId = $parentDomainKey;
                                            } else {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['parent_domain_key_type' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'parent_domain_key_type'])];
                                            }
                                        }
                                    }

                                    $status = null;
                                    if ($checkType) {
                                        if (isset($domain['status'])) {
                                            $status = $domain['status'];

                                            if ($status == "" || $status == null || ($status !== FwDomain::STATUS_FLAG_NORMAL && $status !== FwDomain::STATUS_FLAG_STOP)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['status' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'status'])];
                                            }
                                        }
                                    }

                                    $shareFlag = null;
                                    if ($checkType) {
                                        if (isset($domain['share_flag'])) {
                                            $shareFlag = $domain['share_flag'];

                                            if ($shareFlag == "" || $shareFlag == null || ($shareFlag !== FwDomain::SHARE_FLAG_EXCLUSIVE && $shareFlag !== FwDomain::SHARE_FLAG_SHARE)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['status' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'status'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        $model = null;
                                        if (!empty($domainId)) {
                                            $model = FwDomain::findOne($domainId);
                                        }

                                        if (isset($model) && !empty($model)) {
                                            $add = false;
                                        }
                                        else {
                                            $model = new FwDomain();

                                            if ($status === null) {
                                                $model->status = FwDomain::STATUS_FLAG_NORMAL;
                                            }

                                            if ($shareFlag === null) {
                                                $model->share_flag = FwDomain::SHARE_FLAG_EXCLUSIVE;
                                            }
                                        }

                                        $model->company_id = $companyId;
                                        $model->parent_domain_id = $parentDomainId;

                                        $model->attributes = $domain;
                                        $model->setScenario("api-manage");

                                        $treeType = "domain";

                                        $treeNodeService = new TreeNodeService();

                                        if (!empty($model->parent_domain_id)) {
                                            $parentNodeId = FwDomain::findOne($model->parent_domain_id)->tree_node_id;
                                        } else {
                                            $parentNodeId = null;
                                        }

                                        if ($model->validate()) {
                                            $systemKey = $this->systemKey;
                                            $model->data_from = $systemKey;
                                            $model->systemKey = $systemKey;

//                                            if ($domainService->isExistSameDomainCode($model->kid, $companyId, $domainCode)) {
//                                                $newInfo['error_code'] = $codeName . "-" . "001";
//                                                $newInfo['error_message'] = ["orgnization_code" => Yii::t('common', 'exist_same_code_{value}',
//                                                    ['value' => Yii::t('common', 'orgnization_code')])];
//                                                $checkType = false;
//                                            }

                                            if ($add) {
                                                $treeNodeId = $treeNodeService->addTreeNode($treeType, $model->domain_name, $parentNodeId);

                                                $domainCode = FwTreeNode::findOne($treeNodeId)->tree_node_code;
                                                $model->domain_code = $domainCode;
                                                $model->tree_node_id = $treeNodeId;
                                                $model->needReturnKey = true;
                                                if ($model->save()) {

                                                    $domainId = $model->kid;
                                                    $newInfo['domain_id'] = $domainId;
                                                    $newInfo['op_result'] = "0";

                                                    if ($domainKeyType == "1")
                                                        $externalSystemService->addExternalSystemDomainKey($this->systemKey, $domainKey, $domainId);
                                                }
                                                else {
                                                    $newInfo['error_code'] = $codeName . "-" . "003";
                                                    $newInfo['error_message'] = "Data Insert Error";
                                                }
                                            } else {
                                                $treeNodeId = $model->tree_node_id;
                                                $domainName = $model->domain_name;
                                                $treeNodeService->updateTreeNode($treeNodeId, $domainName, $parentNodeId);

                                                $model->systemKey = $systemKey;
                                                if ($model->save()) {
                                                    $newInfo['domain_id'] = $domainId;
                                                    $newInfo['op_result'] = "1";
                                                }
                                                else {
                                                    $newInfo['error_code'] = $codeName . "-" . "003";
                                                    $newInfo['error_message'] = "Data Update Error";
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
                                $jsonResult['domain'] = $totalArray;
                                $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);
                                return $result;

                            } else {
                                $code = $codeName;
                                $number = "006";
                                $name = Yii::t('common', 'DataNotExist');
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
                } catch (\Exception $e) {
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
     * 删除域信息接口
     * 通过域主键删除指定域基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDeleteDomainInfo()
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
                            if (isset($bodyParams['domain_key']) && $bodyParams['domain_key'] != "") {
                                $domainKey = $bodyParams['domain_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "domain_key");
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
                        $domainId = $externalSystemService->getDomainIdByDomainKey($this->systemKey, $domainKey);
                    else if ($keyType == "2")
                        $domainId = $domainKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($domainId)) {
                        $model = FwDomain::findOne($domainId);

                        $externalSystemService = new ExternalSystemService();
                        $externalSystemService->deleteDomainInfoByDomainId($domainId);
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
                            $name = Yii::t('common', 'Operation_Confirm_Warning_Failure');
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);

                            return $result;
                        }
                    } else {
                        $code = $codeName;
                        $number = "006";
                        $name = Yii::t('common', 'DataNotExist');
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
            } catch (\Exception $e) {
                $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());

                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }
}