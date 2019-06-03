<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace api\modules\v1\controllers;

use api\base\BaseNormalApiController;
use common\helpers\TMessageHelper;
use common\models\framework\FwServiceLog;
use api\services\UserService;
use common\services\framework\ServiceService;
use common\viewmodels\framework\PasswordResetRequestForm;
use Yii;
use yii\base\Exception;
use common\services\framework\PointRuleService;
use common\models\framework\FwUser;


class UserController extends BaseNormalApiController
{

    public $modelClass = 'api\services\UserService';


    /**
     * 普通登录接口
     * 通过帐号和密码进行传统方式的登录验证
     * @return array
     */
    public function actionNormalLogin()
    {
        $queryParams = Yii::$app->request->getQueryParams();

        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['user_id'] = null;
        $jsonResult['user_key'] = null;

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
                        if (isset($queryParams['user_name']) && $queryParams['user_name'] != "") {
                            $username = TMessageHelper::decryptMsg($this->systemKey, $queryParams['user_name'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "user_name");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_name");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($queryParams['password']) && $queryParams['password'] != "") {
                            $password = TMessageHelper::decryptMsg($this->systemKey, $queryParams['password'], $errorCode, $errorMessage);

                            if (!empty($errorCode)) {
                                $number = "004";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "password");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "password");
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
                            if (isset($bodyParams['user_name']) && $bodyParams['user_name'] != "") {
                                $username = $bodyParams['user_name'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_name");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }

                            if (isset($bodyParams['password']) && $bodyParams['password'] != "") {
                                $password = $bodyParams['password'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "password");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }
                    }

                    $userService = new UserService();
                    $result = $userService->normalLoginByUsername($this->systemKey, $codeName, $username, $password);
                    
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
     * OpenAuth登录接口
     * 通过帐号和密码进行OpenAuth方式的登录验证，通过验证后能获取到授权访问令牌
     * @return array
     */
    public function actionOpenAuthLogin()
    {
//        $queryParams = Yii::$app->request->getQueryParams();
//        $currentTime = time();
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
                        }

                        $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
                        if (isset($bodyParams['user_name']) && $bodyParams['user_name'] != "") {
                            $username = $bodyParams['user_name'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_name");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }

                        if (isset($bodyParams['password']) && $bodyParams['password'] != "") {
                            $password = $bodyParams['password'];
                        } else {
                            $number = "001";
                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "password");
                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                            return $result;
                        }
                    }

                    $userService = new UserService();
                    $result = $userService->openAuthLoginByUsername($this->systemKey, $codeName, $username, $password);

                  
                    
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
     * 请求重置密码接口
     * 通过Email地址请求发送重置密码邮件
     * @return array
     */
    public function actionRequestPasswordReset()
    {
//        $queryParams = Yii::$app->request->getQueryParams();
        $code = "common";
        $name = null;
        $message = null;

        $errorCode = null;
        $errorMessage = null;
        $codeName = $this->action->id;

        $jsonResult['request_result'] = null;

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
                            if (isset($bodyParams['email']) && $bodyParams['email'] != "") {
                                $email = $bodyParams['email'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "email");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }
                    }

                    $model = new PasswordResetRequestForm();
                    $model->email = $email;
                    if ($model->validate() && $model->sendEmail()) {
                        $code = "OK";
                        $jsonResult['request_result'] = true;

                        $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);
                        return $result;
                    } else {
                        $code = $codeName;
                        $number = "001";
                        $name = Yii::t('common', 'send_mail_error');
                        $message = $model->getFirstError("email");
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
     * 刷新授权访问令牌接口
     * 通过强制刷新的方式，重新生成一个授权访问令牌
     * @return array
     */
    public function actionRefreshAccessToken()
    {
//        $queryParams = Yii::$app->request->getQueryParams();
//        $currentTime = time();
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
                            if (isset($bodyParams['user_name']) && $bodyParams['user_name'] != "") {
                                $username = $bodyParams['user_name'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_name");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }

                            if (isset($bodyParams['password']) && $bodyParams['password'] != "") {
                                $password = $bodyParams['password'];
                            } else {
                                $number = "001";
                                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "password");
                                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
                                return $result;
                            }
                        }
                    }

                    $userService = new UserService();
                    $result = $userService->refreshAccessTokenByUsername($this->systemKey, $codeName, $username, $password);

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


//    /**
//     * 获取授权访问令牌接口
//     * 通过用户主键获取授权访问令牌
//     * @return array
//     */
//    public function actionGetAccessToken()
//    {
//        $queryParams = Yii::$app->request->getQueryParams();
//
//        $currentTime = time();
//        $code = "common";
//        $name = null;
//        $message = null;
//
//        $errorCode = null;
//        $errorMessage = null;
//        $codeName = $this->action->id;
//
//        $jsonResult['access_token'] = null;
//        $jsonResult['user_id'] = null;
//        $jsonResult['user_key'] = null;
//        $jsonResult['expire'] = null;
//
//        if ($this->systemKeyCheck == false) {
//            $number = "002";
//            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
//            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//            return $result;
//        } else {
//            $commonServiceSerivce = new ServiceService();
//            try {
//                $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
//                if ($isServiceRunning) {
//                    if (Yii::$app->request->isGet) {
//                        $number = "003";
//                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
//                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                        return $result;
//                    } else {
//                        $rawBody = Yii::$app->request->getRawBody();
//                        $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $rawBody, $errorCode, $errorMessage);
//
//                        if (!empty($errorCode)) {
//                            $number = "004";
//                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $errorMessage, "post");
//                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                            return $result;
//                        }
//
//                        $bodyParams = json_decode($rawDecryptBody, true);//true返回值是数组,否则返回值为object
//                        if (isset($bodyParams['user_key']) && $bodyParams['user_key'] != "") {
//                            $userKey = $bodyParams['user_key'];
//                        } else {
//                            $number = "001";
//                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "user_key");
//                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                            return $result;
//                        }
//
//                        if (isset($bodyParams['key_type']) && $bodyParams['key_type'] != "") {
//                            $keyType = $bodyParams['key_type'];
//                        } else {
//                            $number = "001";
//                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
//                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                            return $result;
//                        }
//                    }
//
//                    $externalSystemService = new ExternalSystemService();
//                    if ($keyType == "1")
//                        $userId = $externalSystemService->getUserIdByUserKey($this->systemKey, $userKey);
//                    else if ($keyType == "2")
//                        $userId = $userKey;
//                    else {
//                        $number = "001";
//                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, "key_type");
//                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                        return $result;
//                    }
//
//                    if (!empty($userId))
//                        $model = FwUser::findOne($userId);
//
//                    if (!empty($model)) {
//                        $commonUserService = new \common\services\framework\UserService();
//                        if ($model->status == FwUser::LOGIN_FAILED_USER_STOP) {
//                            $code = $codeName;
//                            $number = "002";
//                            $name = Yii::t('common', 'login_error_user_stop');
//                            $commonUserService->recordFailedLoginInfo($userId, FwUser::LOGIN_FAILED_USER_STOP);
//
//                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
//                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                            return $result;
//                        } else if (!empty($model->valid_start_at) && $currentTime < $model->valid_start_at) {
//                            $code = $codeName;
//                            $number = "002";
//                            $name = Yii::t('common', 'login_error_user_stop');
//                            $commonUserService->recordFailedLoginInfo($userId, FwUser::LOGIN_FAILED_USER_STOP);
//
//                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
//                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                            return $result;
//                        } else if (!empty($model->valid_end_at) && $currentTime > $model->valid_end_at) {
//                            $code = $codeName;
//                            $number = "002";
//                            $name = Yii::t('common', 'login_error_user_stop');
//                            $commonUserService->recordFailedLoginInfo($userId, FwUser::LOGIN_FAILED_USER_STOP);
//
//                            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
//                            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                            return $result;
//                        }
//
//                        $externalSystemService = new ExternalSystemService();
//                        $accessTokenArray = $externalSystemService->getAccessTokenArrayBySystemKey($this->systemKey, $userId);
//
//                        if (empty($accessTokenArray)) {
//                            $externalSystemService->generateAccessTokenBySystemKey($this->systemKey, $userId);
//
//                            $accessTokenArray = $externalSystemService->getAccessTokenArrayBySystemKey($this->systemKey, $userId);
//                        }
//
//                        $code = "OK";
//                        $jsonResult['access_token'] = $accessTokenArray['access_token'];
//                        $jsonResult['user_id'] = $userId;
//                        $jsonResult['user_key'] = $externalSystemService->getUserKeyByUserId($this->systemKey, $userId);
//                        $jsonResult['expire'] = $accessTokenArray['expire'];
//
//                        $result = TMessageHelper::resultBuild($this->systemKey, $code, $name, $message, $jsonResult);
//                        return $result;
//                    } else {
//                        $code = $codeName;
//                        $number = "003";
//                        $name = Yii::t('common', 'login_error_user_not_exsit');
//                        $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
//                        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                        return $result;
//                    }
//                } else {
//                    $number = "005";
//                    $errorArray = TMessageHelper::errorBuild($code, $number, $name, null, null);
//                    $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                    return $result;
//                }
//            } catch (Exception $e) {
//                $commonServiceSerivce->recordServiceLog($this->serviceId, FwServiceLog::ACTION_STATUS_ERROR, Yii::t('common','api_load_failed') . $codeName . ";" . Yii::t('common','api_error_msg') . $e->getMessage());
//                $number = "001";
//                $errorArray = TMessageHelper::errorBuild($code, $number, $name, $e->getMessage(), null);
//                $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
//                return $result;
//            }
//        }
//    }
}