<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use api\services\OrgnizationService;
use api\services\PositionService;
use common\services\framework\DomainService;
use common\services\framework\RbacService;
use common\services\framework\UserManagerService;
use common\services\framework\UserPositionService;
use common\models\framework\FwUserManager;
use common\models\framework\FwUserPosition;
use common\services\framework\UserRoleService;
use common\helpers\TMessageHelper;
use common\services\framework\ExternalSystemService;
use common\models\framework\FwCompany;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwServiceLog;
use common\models\framework\FwUser;
use api\services\UserService;
use common\models\message\MsTimeline;
use common\models\treemanager\FwCntManageRef;
use common\services\framework\CntManageRefService;
use common\services\learning\CourseService;
use common\services\framework\DictionaryService;
use common\services\framework\ServiceService;
use common\services\framework\UserCompanyService;
use common\services\framework\UserDomainService;
use common\services\framework\UserOrgnizationService;
use common\helpers\TTimeHelper;
use Exception;
use common\services\message\TimelineService;
use Yii;

class UserController extends BaseOpenApiController{

    public $modelClass = 'api\services\UserService';

    /**
     * 获取企业所有用户记录数接口
     * 通过企业ID获取用户记录数信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllUserCount()
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

                    $userService = new UserService();

                    $count = 0;
                    if (!empty($companyId))
                        $count = $userService->getUserListCountByCompanyId($companyId);

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
     * 获取企业所有用户信息接口
     * 通过企业ID获取用户基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAllUserInfo()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['user'] = null;
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

                    $userService = new UserService();

                    if (!empty($companyId))
                        $modelList = $userService->getUserListByCompanyId($companyId,$limit,$offset);

                    if (!empty($modelList) && count($modelList) > 0) {
                        $domain = Yii::$app->request->hostInfo;
                        $totalArray = [];
                        foreach ($modelList as $model) {
                            /* @var $model \common\models\framework\FwUser */
                            $userId = $model->kid;
                            $externalUserKey = $externalSystemService->getUserKeyByUserId($this->systemKey, $userId);


                            $defaultThumb = $model->gender == "female" ? $domain."/static/common/images/woman.jpeg" : $domain."/static/common/images/man.jpeg";

                            $userResult["user_key"] = $externalUserKey;
                            $userResult["user_id"] = $userId;
                            $userResult["user_name"] = $model->user_name;
                            $userResult["real_name"] = $model->real_name;
                            $userResult["nick_name"] = $model->nick_name;
                            $userResult["gender"] = $model->gender;
                            $userResult["birthday"] = empty($model->birthday)?null:$model->birthday;
                            $userResult["id_number"] = empty($model->id_number)?null:$model->id_number;
                            $userResult["email"] = $model->email;
                            $userResult["status"] = $model->status;
                            $userResult["mobile_no"] = empty($model->mobile_no)?null:$model->mobile_no;
                            $userResult["home_phone_no"] = empty($model->home_phone_no)?null:$model->home_phone_no;
                            $userResult["telephone_no"] = empty($model->telephone_no)?null:$model->telephone_no;
                            $userResult["employee_status"] = empty($model->employee_status)?null:$model->employee_status;
                            $userResult["onboard_day"] = empty($model->onboard_day)?null:$model->onboard_day;
                            $userResult["rank"] = empty($model->rank)?null:$model->rank;
                            $userResult["work_place"] = empty($model->work_place)?null:$model->work_place;
                            $userResult["position_mgr_level"] = empty($model->position_mgr_level)?null:$model->position_mgr_level;
                            $userResult["location"] = empty($model->location)?null:$model->location;
                            $userResult["thumb"] = empty($model->thumb) ? $defaultThumb : $domain . $model->thumb;
                            $userResult["description"] = empty($model->description)?null:$model->description;
                            $userResult["user_no"] = empty($model->user_no)?null:$model->user_no;
                            $userResult["company_id"] = $model->company_id;

                            $companyModel = FwCompany::findOne($model->company_id);
                            if (!empty($companyModel)) {
                                /* @var $companyModel \common\models\framework\FwCompany */
                                $userResult["company_name"] = $companyModel->company_name;
                            }
                            else
                                $userResult["company_name"] = null;

                            if (!empty($model->orgnization_id)) {
                                $userResult["orgnization_id"] = $model->orgnization_id;
                                $userResult["orgnization_key"] = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $model->orgnization_id);
                            }
                            else {
                                $userResult["orgnization_id"] = null;
                                $userResult["orgnization_key"] = null;
                            }

                            $orgnizationModel = FwOrgnization::findOne($model->orgnization_id);
                            if (!empty($orgnizationModel))
                                $userResult["orgnization_name"] = $orgnizationModel->orgnization_name;
                            else
                                $userResult["orgnization_name"] = null;

                            if (!empty($model->domain_id)) {
                                $userResult["domain_id"] = $model->domain_id;
                                $userResult["domain_key"] = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $model->domain_id);
                            }
                            else {
                                $userResult["domain_id"] = null;
                                $userResult["domain_key"] = null;
                            }

                            if (!empty($model->domain_id)) {
                                /* @var $domainModel \common\models\framework\FwDomain */
                                $domainModel = FwDomain::findOne($model->domain_id);
                                if (!empty($domainModel))
                                    $userResult["domain_name"] = $domainModel->domain_name;
                                else
                                    $userResult["domain_name"] = null;
                            }
                            else {
                                $userResult["domain_name"] = null;
                            }

                            $userResult["manager_flag"] = $model->manager_flag;

                            if (!empty($model->reporting_manager_id)) {
                                $externalReportingManagerKey = $externalSystemService->getUserKeyByUserId($this->systemKey, $model->reporting_manager_id);
                                $userResult["reporting_manager_key"] = $externalReportingManagerKey;
                                $userResult["reporting_manager_id"] = $model->reporting_manager_id;
                            }
                            else {
                                $userResult["reporting_manager_key"] = null;
                                $userResult["reporting_manager_id"] = null;
                            }

                            $userResult["sequence_number"] = empty($model->sequence_number)?null:$model->sequence_number;
                            $userResult["nationality"] = empty($model->nationality)?null:$model->nationality;
                            $userResult["start_work_day"] = empty($model->start_work_day)?null:$model->start_work_day;
                            $userResult["duty"] = empty($model->duty)?null:$model->duty;
                            $userResult["payroll_place"] = empty($model->payroll_place)?null:$model->payroll_place;
                            $userResult["graduated_from"] = empty($model->graduated_from)?null:$model->graduated_from;
                            $userResult["graduated_major"] = empty($model->graduated_major)?null:$model->graduated_major;
                            $userResult["highest_education"] = empty($model->highest_education)?null:$model->highest_education;
                            $userResult["recruitment_channel"] = empty($model->recruitment_channel)?null:$model->recruitment_channel;
                            $userResult["recruitment_type"] = empty($model->recruitment_type)?null:$model->recruitment_type;
                            $userResult["memo1"] = empty($model->memo1)?null:$model->memo1;
                            $userResult["memo2"] = empty($model->memo2)?null:$model->memo2;
                            $userResult["memo3"] = empty($model->memo3)?null:$model->memo3;
                            $userResult["memo4"] = empty($model->memo4)?null:$model->memo4;
                            $userResult["memo5"] = empty($model->memo3)?null:$model->memo5;

                            $commonUserService = new \common\services\framework\UserService();
                            $userPosition = $commonUserService->getPositionListByUserId($userId, false);
                            if (!empty($userPosition) && count($userPosition) > 0) {
                                $position = [];
                                foreach ($userPosition as $singlePosition) {
                                    $externalPositionKey = $externalSystemService->getPositionKeyByPositionId($this->systemKey, $singlePosition->kid);
                                    $temp = [];
                                    $temp["position_key"] = $externalPositionKey;
                                    $temp["position_id"] = $singlePosition->kid;
                                    $temp["position_code"] = $singlePosition->position_code;
                                    $temp["position_name"] = $singlePosition->position_name;
                                    $temp["is_master"] = $singlePosition->is_master;
                                    array_push($position,$temp);
                                }
                                $userResult["position"] = $position;
                            }
                            else {
                                $userResult["position"] = null;
                            }

                            $userManageOrgList = $commonUserService->getManageOrgListByUserId($userId, false);
                            if (!empty($userManageOrgList) && count($userManageOrgList) > 0) {
                                $userManageOrg = [];
                                foreach ($userManageOrgList as $singleOrgnization) {
                                    $externalOrgnizationKey = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $singleOrgnization->kid);
                                    $temp = [];
                                    $temp["manage_org_key"] = $externalOrgnizationKey;
                                    $temp["manage_org_id"] = $singleOrgnization->kid;
                                    $temp["manage_org_name"] = $singleOrgnization->orgnization_name;
                                    array_push($userManageOrg,$temp);
                                }
                                $userResult["manage_org"] = $userManageOrg;
                            }
                            else {
                                $userResult["manage_org"] = null;
                            }


                            array_push($totalArray, $userResult);
                        }

                        $jsonResult['user'] = $totalArray;

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
     * 获取用户信息接口
     * 通过用户ID获取用户基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetUserInfo()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['user'] = null;

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
                        if (isset($queryParams['user_key']) && $queryParams['user_key'] != "") {
                            $userKey = TMessageHelper::decryptMsg($this->systemKey, $queryParams['user_key'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "user_key");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_key");
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
                            if (isset($bodyParams['user_key']) && $bodyParams['user_key'] != "") {
                                $userKey = $bodyParams['user_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_key");
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
                        $userId = $externalSystemService->getUserIdByUserKey($this->systemKey, $userKey);
                    else if ($keyType == "2")
                        $userId = $userKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($userId))
                        $model = FwUser::findOne($userId);

                    if (!empty($model)) {
                        /* @var $model \common\models\framework\FwUser */
                        $externalUserKey = null;
                        if ($keyType == "1")
                            $externalUserKey = $userKey;
                        else {
                            $externalUserKey = $externalSystemService->getUserKeyByUserId($this->systemKey, $userId);
                        }

                        if (!empty($model->reporting_manager_id)) {
                            $externalReportingManagerKey = $externalSystemService->getUserKeyByUserId($this->systemKey, $model->reporting_manager_id);
                            $userResult["reporting_manager_key"] = $externalReportingManagerKey;
                            $userResult["reporting_manager_id"] = $model->kid;
                        }
                        else {
                            $userResult["reporting_manager_key"] = null;
                            $userResult["reporting_manager_id"] = null;
                        }

                        $domain = Yii::$app->request->hostInfo;
                        $defaultThumb = $model->gender == "female" ? $domain."/static/common/images/woman.jpeg" : $domain."/static/common/images/man.jpeg";

                        $userResult["user_key"] = $externalUserKey;
                        $userResult["user_id"] = $userId;
                        $userResult["user_name"] = $model->user_name;
                        $userResult["real_name"] = $model->real_name;
                        $userResult["nick_name"] = $model->nick_name;
                        $userResult["gender"] = $model->gender;
                        $userResult["birthday"] = empty($model->birthday)?null:$model->birthday;
                        $userResult["id_number"] = empty($model->id_number)?null:$model->id_number;
                        $userResult["email"] = $model->email;
                        $userResult["status"] = $model->status;
                        $userResult["mobile_no"] = empty($model->mobile_no)?null:$model->mobile_no;
                        $userResult["home_phone_no"] = empty($model->home_phone_no)?null:$model->home_phone_no;
                        $userResult["telephone_no"] = empty($model->telephone_no)?null:$model->telephone_no;
                        $userResult["employee_status"] = empty($model->employee_status)?null:$model->employee_status;
                        $userResult["onboard_day"] = empty($model->onboard_day)?null:$model->onboard_day;
                        $userResult["rank"] = empty($model->rank)?null:$model->rank;
                        $userResult["work_place"] = empty($model->work_place)?null:$model->work_place;
                        $userResult["position_mgr_level"] = empty($model->position_mgr_level)?null:$model->position_mgr_level;
                        $userResult["location"] = empty($model->location)?null:$model->location;
                        $userResult["thumb"] = empty($model->thumb) ? $defaultThumb : $domain . $model->thumb;
                        $userResult["description"] = empty($model->description)?null:$model->description;
                        $userResult["user_no"] = empty($model->user_no)?null:$model->user_no;
                        $userResult["company_id"] = $model->company_id;

                        $companyModel = FwCompany::findOne($model->company_id);
                        if (!empty($companyModel)) {
                            /* @var $companyModel \common\models\framework\FwCompany */
                            $userResult["company_name"] = $companyModel->company_name;
                        }
                        else
                            $userResult["company_name"] = null;

                        $userResult["orgnization_id"] = $model->orgnization_id;

                        $userResult["orgnization_key"] = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $model->orgnization_id);

                        $orgnizationModel = FwOrgnization::findOne($model->orgnization_id);
                        if (!empty($orgnizationModel)) {
                            /* @var $orgnizationModel \common\models\framework\FwOrgnization */
                            $userResult["orgnization_name"] = $orgnizationModel->orgnization_name;
                        }
                        else
                            $userResult["orgnization_name"] = null;

                        $userResult["domain_id"] = $model->domain_id;

                        $userResult["domain_key"] = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $model->domain_id);

                        $domainModel = FwDomain::findOne($model->domain_id);
                        if (!empty($domainModel)) {
                            /* @var $domainModel \common\models\framework\FwDomain */
                            $userResult["domain_name"] = $domainModel->domain_name;
                        }
                        else
                            $userResult["domain_name"] = null;

                        $userResult["manager_flag"] = $model->manager_flag;

                        if (!empty($model->reporting_manager_id)) {
                            $externalReportingManagerKey = $externalSystemService->getUserKeyByUserId($this->systemKey, $model->reporting_manager_id);
                            $userResult["reporting_manager_key"] = $externalReportingManagerKey;
                            $userResult["reporting_manager_id"] = $model->reporting_manager_id;
                        }
                        else {
                            $userResult["reporting_manager_key"] = null;
                            $userResult["reporting_manager_id"] = null;
                        }

                        $userResult["sequence_number"] = empty($model->sequence_number)?null:$model->sequence_number;
                        $userResult["nationality"] = empty($model->nationality)?null:$model->nationality;
                        $userResult["start_work_day"] = empty($model->start_work_day)?null:$model->start_work_day;
                        $userResult["duty"] = empty($model->duty)?null:$model->duty;
                        $userResult["payroll_place"] = empty($model->payroll_place)?null:$model->payroll_place;
                        $userResult["graduated_from"] = empty($model->graduated_from)?null:$model->graduated_from;
                        $userResult["graduated_major"] = empty($model->graduated_major)?null:$model->graduated_major;
                        $userResult["highest_education"] = empty($model->highest_education)?null:$model->highest_education;
                        $userResult["recruitment_channel"] = empty($model->recruitment_channel)?null:$model->recruitment_channel;
                        $userResult["recruitment_type"] = empty($model->recruitment_type)?null:$model->recruitment_type;
                        $userResult["memo1"] = empty($model->memo1)?null:$model->memo1;
                        $userResult["memo2"] = empty($model->memo2)?null:$model->memo2;
                        $userResult["memo3"] = empty($model->memo3)?null:$model->memo3;
                        $userResult["memo4"] = empty($model->memo4)?null:$model->memo4;
                        $userResult["memo5"] = empty($model->memo3)?null:$model->memo5;


                        $commonUserService = new \common\services\framework\UserService();
                        $userPosition = $commonUserService->getPositionListByUserId($userId, false);
                        if (!empty($userPosition) && count($userPosition) > 0) {
                            $position = [];
                            foreach ($userPosition as $singlePosition) {
                                $externalPositionKey = $externalSystemService->getPositionKeyByPositionId($this->systemKey, $singlePosition->kid);
                                $temp = [];
                                $temp["position_key"] = $externalPositionKey;
                                $temp["position_id"] = $singlePosition->kid;
                                $temp["position_code"] = $singlePosition->position_code;
                                $temp["position_name"] = $singlePosition->position_name;
                                $temp["is_master"] = $singlePosition->is_master;
                                array_push($position,$temp);
                            }
                            $userResult["position"] = $position;
                        }
                        else {
                            $userResult["position"] = null;
                        }

                        $userManageOrgList = $commonUserService->getManageOrgListByUserId($userId, false);
                        if (!empty($userManageOrgList) && count($userManageOrgList) > 0) {
                            $userManageOrg = [];
                            foreach ($userManageOrgList as $singleOrgnization) {
                                $externalOrgnizationKey = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $singleOrgnization->kid);
                                $temp = [];
                                $temp["manage_org_key"] = $externalOrgnizationKey;
                                $temp["manage_org_id"] = $singleOrgnization->kid;
                                $temp["manage_org_name"] = $singleOrgnization->orgnization_name;
                                array_push($userManageOrg,$temp);
                            }
                            $userResult["manage_org"] = $userManageOrg;
                        }
                        else {
                            $userResult["manage_org"] = null;
                        }

                        $jsonResult['user'] = $userResult;

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
     * 获取用户权限信息接口
     * 通过用户ID获取用户权限信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetUserRight()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['user'] = null;

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
                        if (isset($queryParams['user_key']) && $queryParams['user_key'] != "") {
                            $userKey = TMessageHelper::decryptMsg($this->systemKey, $queryParams['user_key'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "user_key");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_key");
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
                            if (isset($bodyParams['user_key']) && $bodyParams['user_key'] != "") {
                                $userKey = $bodyParams['user_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_key");
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
                        $userId = $externalSystemService->getUserIdByUserKey($this->systemKey, $userKey);
                    else if ($keyType == "2")
                        $userId = $userKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($userId))
                        $model = FwUser::findOne($userId);

                    if (!empty($model)) {
                        /* @var $model \common\models\framework\FwUser */
                        $externalUserKey = null;
                        if ($keyType == "1")
                            $externalUserKey = $userKey;
                        else {
                            $externalUserKey = $externalSystemService->getUserKeyByUserId($this->systemKey, $userId);
                        }
                        $userResult["user_key"] = $externalUserKey;
                        $userResult["user_id"] = $model->kid;
                        $userResult["user_name"] = $model->user_name;

                        $commonUserService = new \common\services\framework\UserService();

                        //下属
                        $directReportList = $commonUserService->getDirectReporterByUserId($model->kid);

                        if (!empty($directReportList) && count($directReportList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($directReportList as $directReport) {
                                $newResult["user_id"] = $directReport->kid;
                                $newResult["user_key"] = $externalSystemService->getUserKeyByUserId($this->systemKey, $directReport->kid);

                                array_push($totalArray, $newResult);
                            }

                            $userResult["direct_reporter"] = $totalArray;
                        } else {
                            $userResult["direct_reporter"] = null;
                        }

                        $userResult["direct_reporter_string"] = $commonUserService->getDirectReporterStringByUserId($model->kid);


                        //经理
                        $reportingManagerList = $commonUserService->getReportingManagerByUserId($model->kid);

                        if (!empty($reportingManagerList) && count($reportingManagerList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($reportingManagerList as $reportingManager) {
                                $newResult["user_id"] = $reportingManager->kid;
                                $newResult["user_key"] = $externalSystemService->getUserKeyByUserId($this->systemKey, $reportingManager->kid);

                                array_push($totalArray, $newResult);
                            }

                            $userResult["reporting_manager"] = $totalArray;
                        } else {
                            $userResult["reporting_manager"] = null;
                        }

                        $userResult["reporting_manager_string"] = $commonUserService->getReportingManagerStringByUserId($model->kid);


                        //可管理的企业清单
                        $userCompanyService = new UserCompanyService();

                        $managedCompanyList = $userCompanyService->getManagedListByUserId($model->kid, FwCntManageRef::STATUS_FLAG_NORMAL, false);

                        if (!empty($managedCompanyList) && count($managedCompanyList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($managedCompanyList as $managedCompany) {
                                $newResult["company_id"] = $managedCompany->kid;

                                array_push($totalArray, $newResult);
                            }

                            $userResult["managed_company"] = $totalArray;
                        } else {
                            $userResult["managed_company"] = null;
                        }

                        //可查询的企业清单
                        $searchCompanyList = $userCompanyService->getSearchListByUserId($model->kid, FwCntManageRef::STATUS_FLAG_NORMAL, false);

                        if (!empty($searchCompanyList) && count($searchCompanyList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($searchCompanyList as $searchCompany) {
                                $newResult["company_id"] = $searchCompany->kid;

                                array_push($totalArray, $newResult);
                            }

                            $userResult["search_company"] = $totalArray;
                        } else {
                            $userResult["search_company"] = null;
                        }

                        //可管理的域清单
                        $userDomainService = new UserDomainService();

                        $managedDomainList = $userDomainService->getManagedListByUserId($model->kid, FwCntManageRef::STATUS_FLAG_NORMAL, false);

                        if (!empty($managedDomainList) && count($managedDomainList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($managedDomainList as $managedDomain) {
                                $newResult["domain_id"] = $managedDomain->kid;
                                $newResult["domain_key"] = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $managedDomain->kid);

                                array_push($totalArray, $newResult);
                            }

                            $userResult["managed_domain"] = $totalArray;
                        } else {
                            $userResult["managed_domain"] = null;
                        }

                        //可查询的域清单
                        $searchDomainList = $userDomainService->getSearchListByUserId($model->kid, FwCntManageRef::STATUS_FLAG_NORMAL, false);

                        if (!empty($searchDomainList) && count($searchDomainList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($searchDomainList as $searchDomain) {
                                $newResult["domain_id"] = $searchDomain->kid;
                                $newResult["domain_key"] = $externalSystemService->getDomainKeyByDomainId($this->systemKey, $searchDomain->kid);

                                array_push($totalArray, $newResult);
                            }

                            $userResult["search_domain"] = $totalArray;
                        } else {
                            $userResult["search_domain"] = null;
                        }


                        //可管理的组织清单
                        $userOrgnizationService = new UserOrgnizationService();

                        $managedOrgnizationList = $userOrgnizationService->getManagedListByUserId($model->kid, FwCntManageRef::STATUS_FLAG_NORMAL, false);

                        if (!empty($managedOrgnizationList) && count($managedOrgnizationList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($managedOrgnizationList as $managedOrgnization) {
                                $newResult["orgnization_id"] = $managedOrgnization->kid;
                                $newResult["orgnization_key"] = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $managedOrgnization->kid);

                                array_push($totalArray, $newResult);
                            }

                            $userResult["managed_orgnization"] = $totalArray;
                        } else {
                            $userResult["managed_orgnization"] = null;
                        }

                        //可查询的组织清单
                        $searchOrgnizationList = $userOrgnizationService->getSearchListByUserId($model->kid, FwCntManageRef::STATUS_FLAG_NORMAL, false);

                        if (!empty($searchOrgnizationList) && count($searchOrgnizationList) > 0) {
                            $totalArray = [];
                            $newResult = [];
                            foreach ($searchOrgnizationList as $searchOrgnization) {
                                $newResult["orgnization_id"] = $searchOrgnization->kid;
                                $newResult["orgnization_key"] = $externalSystemService->getOrgnizationKeyByOrgnizationId($this->systemKey, $searchOrgnization->kid);

                                array_push($totalArray, $newResult);
                            }

                            $userResult["search_orgnization"] = $totalArray;
                        } else {
                            $userResult["search_orgnization"] = null;
                        }


                        $jsonResult['user'] = $userResult;

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
     * 修改用户信息接口
     * 此接口用来维护用户基本信息。
     * 如果用户主键已存在，则认为是更新对应用户，否则增加新用户。
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionModifyUserInfo()
    {
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['user'] = null;

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

                            $userList = $bodyParams['user'];
                            $totalArray = [];
                            if (!empty($userList) && count($userList) > 0) {
                                $externalSystemService = new ExternalSystemService();
                                $userService = new UserService();
                                $dictionaryService = new DictionaryService();

                                foreach ($userList as $user) {
                                    $newInfo['user_key'] = null;
                                    $newInfo['user_id'] = null;
                                    $newInfo['op_result'] = "2";
                                    $newInfo['error_code'] = null;
                                    $newInfo['error_message'] = null;
                                    $add = true;

                                    $userKeyType = null;
                                    $userKey = null;
                                    $userId = null;
                                    $companyId = null;
                                    $userName = null;
                                    $realName = null;
                                    $email = null;
                                    $orgnizationKey = null;
                                    $orgnizationKeyType = null;
                                    $orgnizationId = null;
                                    $orgnizationModel = null;
                                    $sourceTreeNodeId = null;

                                    $checkType = true;

                                    if ($checkType) {
                                        if (isset($user['user_key_type']) && !empty($user['user_key_type'])) {
                                            $userKeyType = $user['user_key_type'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['user_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'user_key_type'])];
                                        }
                                    }

                                    if ($checkType) {

                                        if (isset($user['user_key']) && !empty($user['user_key'])) {
                                            $userKey = $user['user_key'];
                                        } else {
                                            if ($userKeyType != "2") {
                                                //如果是内部Id，允许为空；为空表示创建新记录
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['user_key' => Yii::t('common', 'param_{value}_error', ['value' => 'user_key'])];
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        if ($userKeyType == "1") {
                                            $userId = $externalSystemService->getUserIdByUserKey($this->systemKey, $userKey);
                                            $newInfo['user_key'] = $userKey;
                                            $newInfo['user_id'] = $userId;
                                        } else if ($userKeyType == "2") {
                                            $userId = $userKey;
                                            $newInfo['user_key'] = null;
                                            $newInfo['user_id'] = $userId;
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['user_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'user_key_type'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($user['user_name']) && !empty($user['user_name'])) {
                                            $userName = $user['user_name'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['user_name' => Yii::t('common', 'param_{value}_error', ['value' => 'user_name'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($user['real_name']) && !empty($user['real_name'])) {
                                            $realName = $user['real_name'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['real_name' => Yii::t('common', 'param_{value}_error', ['value' => 'real_name'])];
                                        }
                                    }


                                    if ($checkType) {
                                        if (isset($user['email']) && !empty($user['email'])) {
                                            $email = $user['email'];
                                        }
//                                        else {
//                                            $checkType = false;
//                                            $newInfo['error_code'] = $codeName . "-" . "004";
//                                            $newInfo['error_message'] = ['email' => Yii::t('common', 'param_{value}_error', ['value' => 'email'])];
//                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($user['company_id']) && !empty($user['company_id'])) {
                                            $companyId = $user['company_id'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['company_id' => Yii::t('common', 'param_{value}_error', ['value' => 'company_id'])];
                                        }
                                    }


                                    if ($checkType) {
                                        if (isset($user['orgnization_key_type']) && !empty($user['orgnization_key_type'])) {
                                            $orgnizationKeyType = $user['orgnization_key_type'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key_type'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if (isset($user['orgnization_key']) && !empty($user['orgnization_key'])) {
                                            $orgnizationKey = $user['orgnization_key'];
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_key' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key'])];
                                        }
                                    }

                                    if ($checkType) {
                                        if ($orgnizationKeyType == "1") {
                                            $orgnizationId = $externalSystemService->getOrgnizationIdByOrgnizationKey($this->systemKey, $orgnizationKey);
                                        } else if ($orgnizationKeyType == "2") {
                                            $orgnizationId = $orgnizationKey;
                                        } else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_key_type' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key_type'])];
                                        }

                                        if (!empty($orgnizationId)) {
                                            $orgnizationModel = FwOrgnization::findOne($orgnizationId);
                                        }

                                        if (!empty($orgnizationModel))
                                            $sourceTreeNodeId = $orgnizationModel->tree_node_id;
                                        else {
                                            $checkType = false;
                                            $newInfo['error_code'] = $codeName . "-" . "004";
                                            $newInfo['error_message'] = ['orgnization_key' => Yii::t('common', 'param_{value}_error', ['value' => 'orgnization_key'])];
                                        }
                                    }

                                    $domainKeyType = null;
                                    $domianKey = null;
                                    $domianId = null;

                                    if ($checkType) {
                                        if (isset($user['domain_key_type']) && !empty($user['domain_key_type'])) {
                                            $domainKeyType = $user['domain_key_type'];
                                        }
                                    }

                                    if ($checkType) {
                                        if (!empty($domainKeyType)) {
                                            if (isset($user['domain_key']) && !empty($user['domain_key'])) {
                                                $domianKey = $user['domain_key'];
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

                                    $managerFlag = null;
                                    if ($checkType) {
                                        if (isset($user['manager_flag'])) {
                                            $managerFlag = $user['manager_flag'];

                                            if ($managerFlag == "" || $managerFlag == null || ($managerFlag !== FwUser::NO && $managerFlag !== FwUser::YES)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['manager_flag' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'manager_flag'])];
                                            }
                                        }
                                    }

                                    $status = null;
                                    if ($checkType) {
                                        if (isset($user['status'])) {
                                            $status = $user['status'];

                                            if ($status == "" || $status == null || ($status !== FwUser::STATUS_FLAG_NORMAL && $status !== FwUser::STATUS_FLAG_TEMP
                                                && $status !== FwUser::STATUS_FLAG_STOP)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['status' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'status'])];
                                            }
                                        }
                                    }

                                    $gender = null;
                                    if ($checkType) {
                                        if (isset($user['gender'])) {
                                            $gender = $user['gender'];

                                            if ($gender == "" ||  $gender == null || ($gender !== FwUser::GENDER_FEMALE && $gender !== FwUser::GENDER_MALE
                                                && $gender !== FwUser::GENDER_PRIVACY && $gender !== FwUser::GENDER_OTHER)) {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['gender' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'gender'])];
                                            }
                                        }
                                    }

                                    $userPosition = null;

                                    if ($checkType) {
                                        if (isset($user['position']) && !empty($user['position']) && count($user['position']) > 0) {
                                            $positionErrorMessage = null;
                                            $positionList = $user['position'];
                                            $positionService = new PositionService();
                                            $userPosition = $positionService->addPosition($companyId, $this->systemKey, $positionList, $checkType, $positionErrorMessage);

                                            if (!$checkType) {
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = $positionErrorMessage;
                                            }
                                        }
                                    }




                                    $reportingManagerKeyType = null;
                                    $reportingManagerKey = null;
                                    $reportingManagerId = null;
                                    $reportingManagerSet = false;
                                    if ($checkType) {
                                        if (isset($user['reporting_manager_key_type']) && !empty($user['reporting_manager_key_type'])) {
                                            $reportingManagerKeyType = $user['reporting_manager_key_type'];
                                        }
                                    }

                                    if ($checkType) {
                                        if (!empty($reportingManagerKeyType)) {
                                            if (isset($user['reporting_manager_key']) && !empty($user['reporting_manager_key'])) {
                                                $reportingManagerKey = $user['reporting_manager_key'];
                                            }

                                            if ($reportingManagerKeyType == "1") {
                                                $reportingManagerId = $externalSystemService->getUserIdByUserKey($this->systemKey, $reportingManagerKey);
                                                $reportingManagerSet = true;
                                            } else if ($reportingManagerKeyType == "2") {
                                                $reportingManagerId = $reportingManagerKey;
                                                $reportingManagerSet = true;
                                            }
                                            else {
                                                $checkType = false;
                                                $newInfo['error_code'] = $codeName . "-" . "004";
                                                $newInfo['error_message'] = ['reporting_manager_key_type' => Yii::t('common', 'param_{value}_error',
                                                    ['value' => 'reporting_manager_key_type'])];
                                            }
                                        }
                                    }


                                    $workPlace = null;
                                    if ($checkType) {
                                        if (isset($user['work_place']) && !empty($user['work_place'])) {
                                            $workPlace = $user['work_place'];
                                        }
                                    }

                                    $reportingModel = FwUserManager::REPORTING_MODEL_LINE_MANAGER;

                                    $model = null;
                                    if ($checkType) {
                                        if (!empty($userId)) {
                                            $model = FwUser::findOne($userId);
                                        }

                                        if (isset($model) && !empty($model)) {
                                            $add = false;

                                            if ($model->orgnization_id != $orgnizationId || (!empty($workPlace) && $model->work_place != $workPlace) || empty($model->domain_id)) {
                                                //只有用户换组织或工作地,或者当前域没设置时才会更新
                                                if (empty($domianId)) {
                                                    //如果传入域为空，先根据工作地取对应的域，再根据组织取默认域，如果都为空，则提示错误
                                                    if (!empty($workPlace)) {
                                                        $workPlaceId = $dictionaryService->getDictionaryIdByValue("work_place",$workPlace);

                                                        if (!empty($workPlaceId)) {
                                                            $domainService = new DomainService();
                                                            $domianId = $domainService->getDomainIdByWorkPlaceId($workPlaceId);
                                                        }
                                                    }

                                                    if (empty($domianId))
                                                        $domianId = $orgnizationModel->domain_id;

                                                    if (empty($domianId)) {
                                                        $checkType = false;
                                                        $newInfo['error_code'] = $codeName . "-" . "004";
                                                        $newInfo['error_message'] = Yii::t('common', 'user_need_set_domain') .
                                                            "-" . $userName;
                                                    }
                                                }

                                                $model->domain_id = $domianId;
                                            }
                                        } else {
                                            $model = new FwUser();

                                            if ($gender === null) {
                                                $model->gender = FwUser::GENDER_PRIVACY;
                                            }

                                            if ($status === null) {
                                                $model->status = FwUser::STATUS_FLAG_NORMAL;
                                            }

                                            if ($managerFlag === null) {
                                                $model->manager_flag = FwUser::MANAGER_FLAG_NO;
                                            }

                                            //只有用户换组织,或者当前域没设置时才会更新
                                            if (empty($domianId)) {
                                                //如果传入域为空，先根据工作地取对应的域，再根据组织取默认域，如果都为空，则提示错误
                                                if (!empty($workPlace)) {
                                                    $workPlaceId = $dictionaryService->getDictionaryIdByValue("work_place",$workPlace);

                                                    if (!empty($workPlaceId)) {
                                                        $domainService = new DomainService();
                                                        $domianId = $domainService->getDomainIdByWorkPlaceId($workPlaceId);
                                                    }
                                                }

                                                if (empty($domianId))
                                                    $domianId = $orgnizationModel->domain_id;

                                                if (empty($domianId)) {
                                                    $checkType = false;
                                                    $newInfo['error_code'] = $codeName . "-" . "004";
                                                    $newInfo['error_message'] = Yii::t('common', 'user_need_set_domain') .
                                                        "-" . $userName;
                                                }
                                            }

                                            $model->domain_id = $domianId;
                                        }

                                        $model->company_id = $companyId;
                                        $model->orgnization_id = $orgnizationId;
                                        $model->user_name = $userName;
                                        $model->real_name = $realName;
                                        $model->email = $email;

                                        if ($reportingManagerSet) {
                                            //维护用户汇报经理信息
                                            if (!empty($reportingManagerId)) {
//                                                $reportingModel = FwCompany::findOne($companyId)->reporting_model;

//                                                if ($reportingModel == FwUserManager::REPORTING_MODEL_LINE_MANAGER) {
                                                    $model->reporting_manager_id = $reportingManagerId;
//                                                }
                                            } else {
                                                $model->reporting_manager_id = null;
                                            }
                                        }
                                    }

                                    if ($checkType) {
                                        $model->attributes = $user;
                                        if ($add) {
                                            $model->setScenario("api-manage-add");
                                        }
                                        else {
                                            $model->setScenario("api-manage-update");
                                        }

                                        if ($model->validate()) {

                                            if ($userService->isExistSameUserName($model->kid, $userName)) {
                                                $newInfo['error_code'] = $codeName . "-" . "001";
                                                $newInfo['error_message'] = ["user_name" => Yii::t('common', 'exist_same_code_{value}',
                                                    ['value' => Yii::t('common', 'user_name')]) . $userName];
                                                $checkType = false;
                                            } 
                                            else if (!empty($email) && $userService->isExistSameEmail($model->kid, $email)) {
                                                //如果存在相同email，则自动清空邮箱，让用户前台自行修改
//                                                $model->email = null;
//                                                $newInfo['error_code'] = $codeName . "-" . "002";
//                                                $newInfo['error_message'] = ["email" => Yii::t('common', 'exist_same_code_{value}',
//                                                    ['value' => Yii::t('common', 'email')]) . $email];
//                                                $checkType = false;
                                            }

                                            if ($checkType) {
                                                $password = $user['password'];

                                                if ($add) {

                                                    $limitedUserNumber = 0;
                                                    $companyModel = FwCompany::findOne($companyId);
                                                    if (!empty($companyModel)) {
                                                        $limitedUserNumber = $companyModel->limited_user_number;
                                                    }
                                                 
                                                    if ($limitedUserNumber != 0) {
                                                        $activeUserNumber = $userService->getCompanyUserCount($companyModel->kid);

                                                        if ($activeUserNumber >= $limitedUserNumber) {
                                                            $newInfo['error_code'] = $codeName . "-" . "003";
                                                            $newInfo['error_message'] = ["user_name" => Yii::t('common', "active_user_exceed_{number}", ["number" => $limitedUserNumber])];
                                                            $checkType = false;
                                                        }
                                                    }
                                                    
                                                    if ($checkType) {
                                                        $model->data_from = $this->systemKey;


                                                        $defaultLanguage = $dictionaryService->getDictionaryValueByCode("system", "default_language");
                                                        $defaultTimezone = $dictionaryService->getDictionaryValueByCode("system", "default_timezone");
                                                        $defaultTheme = $dictionaryService->getDictionaryValueByCode("system", "default_theme");
                                                        $defaultPass = $dictionaryService->getDictionaryValueByCode("system", "default_password");

                                                        $model->language = $defaultLanguage;
                                                        $model->timezone = $defaultTimezone;
                                                        $model->theme = $defaultTheme;
                                                        $model->last_pwd_change_at = time();

                                                        $model->setAuthToken();
                                                        $model->generateAuthKey();

                                                        if (!empty($password)) {
                                                            $model->setPassword($password);
                                                        } else {
                                                            $model->setPassword($defaultPass);
                                                        }

                                                        $isForceChangePassword = $dictionaryService->getDictionaryValueByCode("system", "is_force_change_password");
                                                        if ($isForceChangePassword == null) {
                                                            $isForceChangePassword = FwUser::NEED_PWD_CHANGE_YES;;
                                                        }

                                                        $model->need_pwd_change = $isForceChangePassword;

                                                        $model->systemKey = $this->systemKey;
                                                        $model->needReturnKey = true;
                                                        if ($model->save()) {
                                                            $userId = $model->kid;
                                                            $newInfo['user_id'] = $userId;
                                                            $newInfo['op_result'] = "0";

                                                            $orgnizationService = new OrgnizationService();

                                                            if ($model->manager_flag == FwUser::MANAGER_FLAG_YES && $model->status != FwUser::STATUS_FLAG_STOP) {
                                                                $orgnizationService->updateOrgnizationNewManager($this->systemKey, $model->orgnization_id, $userId, $model->sequence_number);
                                                            }

                                                            $userManageOrg = null;

                                                            if (isset($user['manage_org']) && !empty($user['manage_org']) && count($user['manage_org']) > 0) {
                                                                $manageOrgErrorMessage = null;
                                                                $manageOrgList = $user['manage_org'];

                                                                foreach ($manageOrgList as $manageOrg) {

                                                                    $manageOrgKeyType = null;
                                                                    $manageOrgKey = null;

                                                                    if (isset($manageOrg['manage_org_key_type']) && !empty($manageOrg['manage_org_key_type'])) {
                                                                        $manageOrgKeyType = $manageOrg['manage_org_key_type'];
                                                                    }
                                                                    if (isset($manageOrg['manage_org_key']) && !empty($manageOrg['manage_org_key'])) {
                                                                        $manageOrgKey = $manageOrg['manage_org_key'];
                                                                    }

                                                                    if ($manageOrgKeyType == "1") {
                                                                        $orgnizationId = $externalSystemService->getOrgnizationIdByOrgnizationKey($this->systemKey, $orgnizationKey);
                                                                    } else if ($orgnizationKeyType == "2") {
                                                                        $orgnizationId = $manageOrgKey;
                                                                    }

                                                                    $manageSequenceNumber = 1;//强制是第一位的

                                                                    $orgnizationService->updateOrgnizationNewManager($this->systemKey, $orgnizationId, $userId, $manageSequenceNumber, true);
                                                                }
                                                            }

                                                            if ($userKeyType == "1")
                                                                $externalSystemService->addExternalSystemUserKey($this->systemKey, $userKey, $userId);

//                                                            $treeNodeKid = FwOrgnization::findOne($model->orgnization_id)->tree_node_id;

//                                                        if ($treeNodeKid != '-1') {
//                                                            $cntManageModel = new FwCntManageRef();
//                                                            $cntManageModel->subject_id = $treeNodeKid;
//                                                            $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                                                            $cntManageModel->content_id = $userId;
//                                                            $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_USER;
//                                                            $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//
//                                                            $cntManageRefService = new CntManageRefService();
//
//                                                            $cntManageRefService->startRelationship($cntManageModel);
//                                                        }

                                                            //维护用户汇报经理信息
                                                            if (!empty($reportingManagerId)) {
                                                                $userManageService = new UserManagerService();

                                                                $userManageModel = new FwUserManager();

                                                                $userManageModels = [];
                                                                $userManageModel->manager_id = $reportingManagerId;
                                                                $userManageModel->user_id = $userId;
                                                                $userManageModel->reporting_model = $reportingModel;
                                                                $userManageModel->status = FwUserManager::STATUS_FLAG_NORMAL;
                                                                $userManageModel->start_at = time();

                                                                array_push($userManageModels, $userManageModel);

                                                                $userManageService->batchStartRelationship($userManageModels);
                                                            }
                                                        } else {
                                                            $newInfo['error_code'] = $codeName . "-" . "003";
                                                            $newInfo['error_message'] = "Data Insert Error";
                                                        }
                                                    }
                                                } else {

                                                    if (!empty($password)) {
                                                        $model->setPassword($password);
                                                    }

                                                    $model->systemKey = $this->systemKey;
                                                    $model->needReturnKey = true;
                                                    if ($model->save()) {
                                                        $newInfo['user_id'] = $userId;
                                                        $newInfo['op_result'] = "1";

                                                        $orgnizationService = new OrgnizationService();

                                                        if ($model->manager_flag == FwUser::MANAGER_FLAG_YES && $model->status != FwUser::STATUS_FLAG_STOP) {
                                                            $orgnizationService->updateOrgnizationNewManager($this->systemKey,$model->orgnization_id, $userId, $model->sequence_number);
                                                        }

                                                        $userManageOrg = null;

                                                        if (isset($user['manage_org']) && !empty($user['manage_org']) && count($user['manage_org']) > 0) {
                                                            $manageOrgErrorMessage = null;
                                                            $manageOrgList = $user['manage_org'];

                                                            foreach ($manageOrgList as $manageOrg) {

                                                                $manageOrgKeyType = null;
                                                                $manageOrgKey = null;

                                                                if (isset($manageOrg['manage_org_key_type']) && !empty($manageOrg['manage_org_key_type'])) {
                                                                    $manageOrgKeyType = $manageOrg['manage_org_key_type'];
                                                                }
                                                                if (isset($manageOrg['manage_org_key']) && !empty($manageOrg['manage_org_key'])) {
                                                                    $manageOrgKey = $manageOrg['manage_org_key'];
                                                                }

                                                                if ($manageOrgKeyType == "1") {
                                                                    $orgnizationId = $externalSystemService->getOrgnizationIdByOrgnizationKey($this->systemKey, $manageOrgKey);
                                                                } else if ($orgnizationKeyType == "2") {
                                                                    $orgnizationId = $orgnizationKey;
                                                                }

                                                                $manageSequenceNumber = 1;//强制是第一位的

                                                                $orgnizationService->updateOrgnizationNewManager($this->systemKey, $orgnizationId, $userId, $manageSequenceNumber, true);
                                                            }
                                                        }
//                                                        $targetTreeNodeId = FwOrgnization::findOne($model->orgnization_id)->tree_node_id;

//                                                        if ($sourceTreeNodeId != $targetTreeNodeId) {
//                                                            $cntManageRefService = new CntManageRefService();
//
//                                                            $cntManageModel = new FwCntManageRef();
//                                                            $cntManageModel->subject_id = $sourceTreeNodeId;
//                                                            $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_TREE;
//                                                            $cntManageModel->content_id = $userId;
//                                                            $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_USER;
//                                                            $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_BELONG;
//
//                                                            if ($sourceTreeNodeId != '') {
//                                                                $cntManageRefService->StopRelationship($cntManageModel);
//                                                            }
//
//                                                            if ($targetTreeNodeId != '') {
//                                                                $cntManageModel->subject_id = $targetTreeNodeId;
//                                                                $cntManageRefService->startRelationship($cntManageModel);
//                                                            }
//                                                        }

                                                        if ($reportingManagerSet) {
                                                            $userManageService = new UserManagerService();
                                                            $userManageService->stopRelationshipByUserId($userId);

                                                            $userManageModels = [];
                                                            //维护用户汇报经理信息
                                                            if (!empty($reportingManagerId)) {
                                                                $userManageModel = new FwUserManager();

                                                                $userManageModel->manager_id = $reportingManagerId;
                                                                $userManageModel->user_id = $userId;
                                                                $userManageModel->reporting_model = $reportingModel;
                                                                $userManageModel->status = FwUserManager::STATUS_FLAG_NORMAL;
                                                                $userManageModel->start_at = time();

                                                                array_push($userManageModels, $userManageModel);

                                                                $userManageService->batchStartRelationship($userManageModels);
                                                            }
                                                        }
                                                    }
                                                    else {
                                                        $newInfo['error_code'] = $codeName . "-" . "003";
                                                        $newInfo['error_message'] = "Data Update Error";
                                                    }
                                                }

                                                $rbacService = new RbacService();
                                                $userRoleService = new UserRoleService();
                                                $teamManagerRoleId = $rbacService->getRoleId("Team-Manager");
                                                $studentRoleId = $rbacService->getRoleId("Student");
                                                if ($managerFlag == FwUser::MANAGER_FLAG_YES) {
                                                    $userRoleService->startRelationship($userId, $teamManagerRoleId);
                                                }
                                                else {
                                                    $userRoleService->stopRelationshipByUserRoleId($userId, $teamManagerRoleId);
                                                }

                                                if ($add) {
                                                    //自动增加学员角色
                                                    $userRoleService->startRelationship($userId, $studentRoleId);
                                                }
                                                
                                                $userPositionModels = [];
                                                $userPositionService = new UserPositionService();
                                                $userPositionService->stopRelationshipByUserId($userId);

                                                if (!empty($userPosition) && count($userPosition) > 0) {
                                                    foreach ($userPosition as $key => $position) {
                                                        $userPositionModel = new FwUserPosition();

                                                        $userPositionModel->position_id = $key;
                                                        $userPositionModel->user_id = $userId;
                                                        $userPositionModel->status = FwUserPosition::STATUS_FLAG_NORMAL;
                                                        $userPositionModel->is_master = $position["is_master"];
                                                        $userPositionModel->start_at = time();

                                                        array_push($userPositionModels, $userPositionModel);
                                                    }

                                                    $userPositionService->batchStartRelationship($userPositionModels);
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
                                $jsonResult['user'] = $totalArray;
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
     * 删除用户信息接口
     * 通过用户主键删除指定用户基本信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDeleteUserInfo()
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
                            if (isset($bodyParams['user_key']) && $bodyParams['user_key'] != "") {
                                $userKey = $bodyParams['user_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_key");
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
                        $userId = $externalSystemService->getUserIdByUserKey($this->systemKey, $userKey);
                    else if ($keyType == "2")
                        $userId = $userKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }

                    if (!empty($userId)) {
                        $model = FwUser::findOne($userId);

                        $externalSystemService = new ExternalSystemService();
                        $externalSystemService->deleteUserInfoByUserId($userId);
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

    /**
     * 修改用户密码接口
     * 通过用户主键修改指定用户的密码信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionChangePassword()
    {
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['change_result'] = false;

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
                            if (isset($bodyParams['user_key']) && $bodyParams['user_key'] != "") {
                                $userKey = $bodyParams['user_key'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_key");
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

                            if (isset($bodyParams['new_password']) && $bodyParams['new_password'] != "") {
                                $newPassword = $bodyParams['new_password'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "new_password");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }

                            if (isset($bodyParams['old_password']) && $bodyParams['old_password'] != "") {
                                $oldPassword = $bodyParams['old_password'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "old_password");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }
                    }

                    $externalSystemService = new ExternalSystemService();
                    if ($keyType == "1")
                        $userId = $externalSystemService->getUserIdByUserKey($this->systemKey, $userKey);
                    else if ($keyType == "2")
                        $userId = $userKey;
                    else {
                        $number = "001";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }


                    if (!empty($userId)) {
                        $model = FwUser::findOne($userId);
						
						if(strlen($oldPassword) >= 6) {
							$model->setScenario("change-password");
						}
						else {
							$model->setScenario("force-change-password");
						}
                        
                        $oldPasswordHash = $model->password_hash;
                    } else {
                        $oldPasswordHash = null;
                    }

                    if (!empty($model)) {
                        $checkOldPassword = Yii::$app->security->validatePassword($oldPassword, $oldPasswordHash);

                        if (!$checkOldPassword) {
                            $jsonResult["result"] = "password_old_error";
                            $result = TMessageHelper::resultBuild($this->systemKey, $code = 'NO', $name, $message = "password_old_error", $jsonResult);
                            return $result;
                        }
						if($model->getScenario() === 'change-password')
						{
							$model->password_old = $oldPassword;
						}

                        $model->systemKey = $this->systemKey;
                        $model->setPassword($newPassword);
                        $model->last_pwd_change_at = time();
                        $model->last_pwd_change_reason = FwUser::PASSWORD_CHANGE_REASON_CHANGE;
                        $model->need_pwd_change = FwUser::NEED_PWD_CHANGE_NO;

                        if ($model->save()) {
                            $code = "OK";
                            $jsonResult['change_result'] = true;
//                            $jsonResult['result'] = "success";
                            $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);
                            return $result;
                        } else {
                            $errorArray = TMessageHelper::errorBuild($code = "NO", $number = "010", $name, $message, $jsonResult);
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
                $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common', 'api_load_failed') . $codeName . ";" . Yii::t('common', 'api_error_msg') . $e->getMessage());

                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }

    //获取用户待完成列表
    public function actionGetTodoDynamicMessage()
    {
        $code = "Todo";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
//        $codeName = $this->action->id;

        $time = "1";

//        $jsonResult['change_result'] = false;

        if (!$this->systemKeyCheck)
        {
            $number = "002";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
        else {
            try {
                if (Yii::$app->request->isGet) {
                    $number = "003";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
                else {
                    $rawBody = Yii::$app->request->getRawBody();
                    $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                    if (!empty($errorCode)) {
                        $number = "004";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }
                    else {
                        $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object


                        if (isset($bodyParams['page']) && $bodyParams['page'] != "") {
                            $page = $bodyParams['page'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "page");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($bodyParams['current_time']) && $bodyParams['current_time'] != "") {
                            $current_time = $bodyParams['current_time'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "page");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                    }
                }

                if (isset($bodyParams['size']) && $bodyParams['size'] != "") {

                    $size = $bodyParams['size'];
                }
                else{
                    $size = 10;
                }

                $lineService = new TimelineService();
                $userId = $this->user->kid;
                $data = $lineService->getTodoByUid($userId, $time, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);



                $dataArray = array();
                $i = 0;
                foreach($data["data"] as $d){
                    $dataArray[$i]["kid"] = $d->kid;
                    $dataArray[$i]["owner_id"] = $d->owner_id;
                    $dataArray[$i]["sender_id"] = $d->sender_id;
                    $dataArray[$i]["object_id"] = $d->object_id;
                    $dataArray[$i]["object_type"] = $d->object_type;
                    $dataArray[$i]["title"] = $d->title;
                    $dataArray[$i]["content"] = $d->content;
                    $dataArray[$i]["start_at"] = $d->start_at;
                    $dataArray[$i]["end_at"] = $d->end_at;
                    $dataArray[$i]["complete_status"] = $d->complete_status;
                    $dataArray[$i]["from_type"] = $d->from_type;
                    $dataArray[$i]["type_code"] = $d->type_code;
                    $dataArray[$i]["attach_original_filename"] = $d->attach_original_filename;
                    $dataArray[$i]["attach_url"] = $d->attach_url;
                    $dataArray[$i]["created_by"] = $d->created_by;
                    $dataArray[$i]["created_at"] = $d->created_at;
                    $dataArray[$i]["updated_by"] = $d->updated_by;
                    $dataArray[$i]["updated_at"] = $d->updated_at;
                    $dataArray[$i]["is_stick"] = $d -> is_stick;
                    $theme = $d->getCourseTheme();
                    $dataArray[$i]["img_url"] = $theme == null ? "" : $theme;
                    $dataArray[$i]["status"] = TTimeHelper::getCourseExpiredTag($d->end_at);
                    $i++;

                }

                $result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, $dataArray);

                return $result;

            }
            catch (Exception $e) {
                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }

    }


    //当前用户朋友圈列表
    public function actionGetSocialDynamicMessage()
    {
        $code = "social";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;

        if (!$this->systemKeyCheck)
        {
            $number = "002";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
        else {
            try {
                if (Yii::$app->request->isGet) {
                    $number = "003";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
                else {
                    $rawBody = Yii::$app->request->getRawBody();
                    $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                    if (!empty($errorCode)) {
                        $number = "004";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }
                    else {
                        $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object


                        if (isset($bodyParams['page']) && $bodyParams['page'] != "") {
                            $page = $bodyParams['page'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "page");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($bodyParams['current_time']) && $bodyParams['current_time'] != "") {
                            $current_time = $bodyParams['current_time'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "page");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                    }
                }


                $userId = $this->user->kid;
                $size = 10;
                $lineService = new TimelineService();

                $data = $lineService->getSocialByUid($userId, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

//                var_dump($data);
                $dataArray = array();
                $i = 0;
                foreach($data["data"] as $d){
                    $dataArray[$i] = $d -> attributes;
                    if($d->object_type == 3){
                        $dataArray[$i]["real_name"] = $d->soQuestion->fwUser->real_name;
                    }

                    if($d->object_type == 0){
                        $dataArray[$i]["course_type"] = $d->lnCourse->course_type=='0'?Yii::t('frontend', 'course_online'): Yii::t('frontend', 'course_face');
                    }

                    $i++;

                }

                $result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', $name, $message, $dataArray);

                return $result;

            }
            catch (Exception $e) {
                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }



    //获取用户待完成或已完成课程列表
    public function actionGetUserDone()
    {
        $code = "userDone";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;

        $jsonResult['change_result'] = false;

        if (!$this->systemKeyCheck)
        {
            $number = "002";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
        else {
            try {
                if (Yii::$app->request->isGet) {
                    $number = "003";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
                else {
                    $rawBody = Yii::$app->request->getRawBody();
                    $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

                    if (!empty($errorCode)) {
                        $number = "004";
                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                        return $result;
                    }
                    else {
                        $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

                        if (isset($bodyParams['page']) && $bodyParams['page'] != "") {
                            $page = $bodyParams['page'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "page");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($bodyParams['type']) && $bodyParams['type'] != "") {
                            $type = $bodyParams['type'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "type");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($bodyParams['current_time']) && $bodyParams['current_time'] != "") {
                            $current_time = $bodyParams['current_time'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "current_time");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                    }
                }

                $userId = $this->user->kid;

                $size = 10;
                $service = new CourseService();

                $data = $service->GetRegCourseByUserId($userId, $key=null, $type, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);

//                var_dump($data);

                $array = array();
                $i = 0;
                foreach($data["data"] as $d){
                    if($d->lnCourse != NULL){
                        $array[$i]["course_id"] = $d->lnCourse->kid;
                        $array[$i]["course_name"] = $d->lnCourse->course_name;
                        $array[$i]["course_desc"] = $d->lnCourse->course_desc_nohtml;
                        $array[$i]["course_type"] = $d->lnCourse->course_type;
                        $array[$i]["reg_type"] = $d->lnCourse->reg_type;
                        $array[$i]["theme_url"] = $d->lnCourse->theme_url;
                        $array[$i]["start_time"] = $d->lnCourse->start_time;
                        $array[$i]["end_time"] = $d->lnCourse->end_time;
                        $array[$i]["open_status"] = $d->lnCourse->open_status;
                        $array[$i]["enroll_start_time"] = $d->lnCourse->enroll_start_time;
                        $array[$i]["enroll_end_time"] = $d->lnCourse->enroll_end_time;
                        $array[$i]["open_start_time"] = $d->lnCourse->open_start_time;
                        $array[$i]["open_end_time"] = $d->lnCourse->open_end_time;
                        $array[$i]["created_by"] = $d->lnCourse->created_by;
                        $array[$i]["updated_by"] = $d->lnCourse->updated_by;
                        $array[$i]["updated_at"] = $d->lnCourse->updated_at;
                        if($type === 'finished'){
                            $array[$i]["complete_status"] = $d->complete_status;
                            $array[$i]["is_retake"] = $d->is_retake;
                            $array[$i]["complete_score"] = $d->complete_score;
                            $array[$i]["complete_score_last"] = $d->complete_score_last;
                            $array[$i]["last_record_at"] = $d->last_record_at;
                        }

                        $i++;
                    }

                }

                $result = TMessageHelper::resultBuild($this->systemKey, $code ='OK', $name, $message, $array);
                return $result;

            }
            catch (Exception $e) {
                $number = "001";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
        }
    }

    public function actionStickTimeline()
    {
        $code = "StickTimeline";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;

        if (Yii::$app->request->isGet) {
            $number = "003";
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
            return $result;
        }
        else {

            $rawBody = Yii::$app->request->getRawBody();
            $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);

            if (!empty($errorCode)) {
                $number = "004";
                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                return $result;
            }
            else {
                $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object

                if (isset($bodyParams['course_id']) && $bodyParams['course_id'] != "") {
                    $courseId = $bodyParams['course_id'];
                } else {
                    $number = "001";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "course_reg_id");
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }

                if (isset($bodyParams['opt']) && $bodyParams['opt'] != "") {
                    $opt = $bodyParams['opt'];
                } else {
                    $number = "002";
                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "opt");
                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                    return $result;
                }
            }
        }

        $userId = $this->user->kid;
        $model = MsTimeline::findOne($courseId);
        if (empty($model)) {
            $result = TMessageHelper::resultBuild($this->systemKey, $code ='NO', $name="StickTimeline", $message="无数据", ["result"=>"failure"]);
            return $result;
        } else {
            if ($model->owner_id !== $userId) {

                $result = TMessageHelper::resultBuild($this->systemKey, $code ='NO', $name="StickTimeline", $message="无权操作", ["result"=>"failure"]);
                return $result;
            }

            if($opt == "delete"){
                if($model->delete()){
                    $result = TMessageHelper::resultBuild($this->systemKey, $code ='YES', $name="deleteTimeline", $message="删除", ["result"=>"success"]);
                    return $result;
                }
                else{
                    $result = TMessageHelper::resultBuild($this->systemKey, $code ='NO', $name="deleteTimeline", $message="删除失败", ["result"=>"failure"]);
                    return $result;
                }
            }
            if($opt == "add"){
                $model->is_stick='1';
            }
            else if($opt == "cancel"){
                $model->is_stick='0';
            }

            if ($model->save()) {
                if($opt == "add"){
                    $result = TMessageHelper::resultBuild($this->systemKey, $code ='YES', $name="StickTimeline", $message="置顶成功", ["result"=>"success"]);
                    return $result;
                }
                else{
                    $result = TMessageHelper::resultBuild($this->systemKey, $code ='YES', $name="StickTimeline", $message="取消置顶成功", ["result"=>"success"]);
                    return $result;
                }

            } else {
                $result = TMessageHelper::resultBuild($this->systemKey, $code ='NO', $name="StickTimeline", $message="数据保存错误", ["result"=>"failure"]);
                return $result;
            }
        }
    }


    /**
     * 延期授权访问令牌接口
     * 通过强制刷新的方式，延长授权访问令牌的有效期
     * @return array
     */
    public function actionDelayAccessTokenExpire()
    {
//        $queryParams = Yii::$app->request->getQueryParams();
        $currentTime = time();
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['access_token'] = null;
        $jsonResult['user_id'] = null;
        $jsonResult['user_key'] = null;
        $jsonResult['expire'] = null;

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
                    $accessToken = $this->accessToken;

                    $model = FwUser::findIdentityByAccessToken($accessToken);

                    if (!empty($model)) {
                        $commonUserService = new \common\services\framework\UserService();
                        $userId = $model->kid;
                        if ($model->status == FwUser::LOGIN_FAILED_USER_STOP) {
                            $code = $codeName;
                            $number = "002";
                            $name = Yii::t('common', 'login_error_user_stop');
                            $commonUserService->recordFailedLoginInfo($userId, FwUser::LOGIN_FAILED_USER_STOP);

                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        } else if (!empty($model->valid_start_at) && $currentTime < $model->valid_start_at) {
                            $code = $codeName;
                            $number = "002";
                            $name = Yii::t('common', 'login_error_user_stop');
                            $commonUserService->recordFailedLoginInfo($userId, FwUser::LOGIN_FAILED_USER_STOP);

                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        } else if (!empty($model->valid_end_at) && $currentTime > $model->valid_end_at) {
                            $code = $codeName;
                            $number = "002";
                            $name = Yii::t('common', 'login_error_user_stop');
                            $commonUserService->recordFailedLoginInfo($userId, FwUser::LOGIN_FAILED_USER_STOP);

                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        $externalSystemService = new ExternalSystemService();
                        $accessTokenArray = $externalSystemService->delayAccessTokenExpireBySystemKey($this->systemKey, $userId);

//                        $accessTokenArray = $externalSystemService->getAccessTokenArrayBySystemKey($this->systemKey, $userId);

                        $code = "OK";
                        $jsonResult['access_token'] = $accessTokenArray['access_token'];
                        $jsonResult['user_id'] = $userId;
                        $jsonResult['user_key'] = $externalSystemService->getUserKeyByUserId($this->systemKey, $userId);
                        $jsonResult['expire'] = $accessTokenArray['expire'];

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
    
    
    public function actionUpdate() {
        $params = Yii::$app->request->getBodyParams();
        $user = FwUser::findOne(Yii::$app->user->getId());
        $fields = ['birthday','email','gender','home_phone_no','id_number','mobile_no','nick_name','real_name'];
        foreach($params as $key => $val) {
            if(!in_array($key,$fields)) continue;
            $user->{$key} = $val;
        }
        
        Yii::$app->response->format = 'json';
        if(!$user->validate()) {
            return [
                'code' => 'ERROR',
                'result' => json_encode([
                    'message' => $user->errors
                ])
            ];
        }
        $user->save();
        return [
            'code' => 'OK',
            'result' => json_encode([
                'message' => ''
            ])
        ];
    }

    public function actionGetNewsDynamic($current_time, $page)
    {
        $this->layout = false;
        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $createdTime = Yii::$app->user->identity->created_at;

        $size = 10;
        $lineService = new TimelineService();

        $data = $lineService->getNewsByUid($uid, $companyId, $createdTime, $size, $size < 1 ? 0 : ((int)$page - 1) * $size, $current_time);
        Yii::$app->response->format = 'json';

        $data = array_map(function($item){
            try {
                return $item->toArray();
            }catch (\Exception $e) {
                return $e->getMessage();
            }
        },$data);

        $result = TMessageHelper::resultBuild($this->systemKey, $code = 'OK', '', '', $data);

        return $result;
    }
}