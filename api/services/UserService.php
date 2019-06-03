<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/24/15
 * Time: 3:06 PM
 */

namespace api\services;

use common\services\framework\ExternalSystemService;
use common\services\framework\PointRuleService;
use common\helpers\TMessageHelper;
use common\models\framework\FwUser;
use Yii;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

class UserService extends FwUser implements Linkable{


    /**
     * 设定可输出的字段列表
     * @return array
     */
    public function fields()
    {
//        自定义返回值示例
//        return [
//            // field name is the same as the attribute name
//            'kid',
//            // field name is "email_address", the corresponding attribute name is "email"
//            'email_address' => 'email',
//            // field name is "name", its value is defined by a PHP callback
//            'name' => function ($model) {
//                return $model->user_name . ' ' . $model->real_name;
//            },
//        ];


//        移除特定列示例
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

        return $fields;
    }

    /**
     * 设定可输出的额外字段列表
     * @return array
     */
    public function extraFields()
    {
        return ['fwCompany'];
    }

    /**
     * Returns a list of links.
     *
     * Each link is either a URI or a [[Link]] object. The return value of this method should
     * be an array whose keys are the relation names and values the corresponding links.
     *
     * If a relation name corresponds to multiple links, use an array to represent them.
     *
     * For example,
     *
     * ```php
     * [
     *     'self' => 'http://example.com/users/1',
     *     'friends' => [
     *         'http://example.com/users/2',
     *         'http://example.com/users/3',
     *     ],
     *     'manager' => $managerLink, // $managerLink is a Link object
     * ]
     * ```
     *
     * @return array the links
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF =>  Url::to(['user/view', 'id' => $this->kid], true),
        ];
    }


    /**
     * 通过企业ID获取相关用户列表信息
     * @param $companyId
     * @param int $limit
     * @param int $offset
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUserListByCompanyId($companyId, $limit = 1, $offset = 0) {
        $domainModel = new FwUser();
        $result = $domainModel->find(false)
            ->andFilterWhere(['=','company_id', $companyId])
            ->andFilterWhere(['=','status', FwUser::STATUS_FLAG_NORMAL])
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 通过企业ID获取相关用户列表记录数信息
     * @param $companyId
     * @return Integer
     */
    public function getUserListCountByCompanyId($companyId) {
        $domainModel = new FwUser();
        $result = $domainModel->find(false)
            ->andFilterWhere(['=','company_id', $companyId])
            ->andFilterWhere(['=','status', FwUser::STATUS_FLAG_NORMAL])
            ->count(1);

        return $result;
    }


    /**
     * 系统登录接口
     * @param $systemKey
     * @param $codeName
     * @param $userName
     * @param $password
     * @return array
     */
    public function normalLoginByUsername($systemKey, $codeName, $userName, $password)
    {
        $commonUserService = new \common\services\framework\UserService();
        $model = new FwUser();
        $user = $model->findByUsername($userName);
        $success = false;
        $code = null;
        $name = null;
        $number = null;
        $message = null;
        $userId = null;
        $jsonResult['user_id'] = null;
        $jsonResult['user_key'] = null;

        if (!$user) {
            $code = "common";
            $number = "006";
            $name = Yii::t('common','data_not_exist');
        } else if ($user->status == self::STATUS_FLAG_TEMP) {
            $code = $codeName;
            $number = "001";
            $name = Yii::t('common','login_error_user_temp');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_TEMP);
        }else if ($user->status == self::STATUS_FLAG_STOP) {
            $code = $codeName;
            $number = "002";
            $name = Yii::t('common','login_error_user_stop');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
        } else if (!$user->validatePassword($password)) {
            $code = $codeName;
            $number = "003";
            $name = Yii::t('common','login_error_password_validate');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_PASSWORD_VALIDATION);
        }
        else  {
            $currentTime = time();

            if (!empty($user->valid_start_at) && $currentTime < $user->valid_start_at)
            {
                $code = $codeName;
                $number = "002";
                $name = Yii::t('common','login_error_user_stop');
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
            }
            else if (!empty($user->valid_end_at) && $currentTime > $user->valid_end_at)
            {
                $code = $codeName;
                $number = "002";
                $name = Yii::t('common','login_error_user_stop');
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
            }
            else {
                $userId = $user->kid;
                $commonUserService->login($userId);
                $success = true;
            }
        }

        if ($success) {
            $code = "OK";
            $jsonResult['user_id'] = $userId;

            $externalSystemService = new ExternalSystemService();
            $jsonResult['user_key'] = $externalSystemService->getUserKeyByUserId($systemKey,$userId);


            // 增加积分
            $companyId = $user->company_id;
            $pointRuleService = new PointRuleService();
            $scenCode = "Mobile-Training";
            $pointRuleService->checkActionForPoint($companyId,$userId,'Login',$scenCode);
            
            $result = TMessageHelper::resultBuild($systemKey, $code, $name, $message, $jsonResult);
        }
        else {
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
        }

        return $result;
    }


    /**
     * OpenAuth登录
     * @param $systemKey
     * @param $codeName
     * @param $userName
     * @param $password
     * @return array
     */
    public function openAuthLoginByUsername($systemKey, $codeName, $userName, $password)
    {
        $commonUserService = new \common\services\framework\UserService();
        $model = new FwUser();
        $user = $model->findByUsername($userName);
        $success = false;
        $code = null;
        $name = null;
        $number = null;
        $message = null;
        $userId = null;

        $jsonResult['access_token'] = null;
        $jsonResult['user_id'] = null;
        $jsonResult['user_key'] = null;
        $jsonResult['expire'] = null;
        $jsonResult['need_pwd_change'] = null;

        if (!$user) {
            $code = "common";
            $number = "006";
            $name = Yii::t('common','data_not_exist');
        } else if ($user->status == self::STATUS_FLAG_TEMP) {
            $code = $codeName;
            $number = "001";
            $name = Yii::t('common','login_error_user_temp');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_TEMP);
        }else if ($user->status == self::STATUS_FLAG_STOP) {
            $code = $codeName;
            $number = "002";
            $name = Yii::t('common','login_error_user_stop');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
        } else if (!$user->validatePassword($password)) {
            $code = $codeName;
            $number = "003";
            $name = Yii::t('common','login_error_password_validate');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_PASSWORD_VALIDATION);
        }
        else  {
            $currentTime = time();

            if (!empty($user->valid_start_at) && $currentTime < $user->valid_start_at)
            {
                $code = $codeName;
                $number = "002";
                $name = Yii::t('common','login_error_user_stop');
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
            }
            else if (!empty($user->valid_end_at) && $currentTime > $user->valid_end_at)
            {
                $code = $codeName;
                $number = "002";
                $name = Yii::t('common','login_error_user_stop');
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
            }
            else {
                $userId = $user->kid;
                $commonUserService->login($userId);
                $success = true;
            }
        }

        if ($success) {
            $externalSystemService = new ExternalSystemService();
            $accessTokenArray = $externalSystemService->getAccessTokenArrayBySystemKey($systemKey, $userId);

            if (empty($accessTokenArray)) {
                $accessTokenArray = $externalSystemService->generateAccessTokenBySystemKey($systemKey, $userId);
//                                $accessTokenArray = $externalSystemService->getAccessTokenArrayBySystemKey($this->systemKey, $userId);
            }

            $code = "OK";
            $jsonResult['access_token'] = $accessTokenArray['access_token'];
            $jsonResult['user_id'] = $userId;
            $jsonResult['user_key'] = $externalSystemService->getUserKeyByUserId($systemKey, $userId);
            $jsonResult['expire'] = $accessTokenArray['expire'];
            $jsonResult['need_pwd_change'] = ($user->need_pwd_change === FwUser::NEED_PWD_CHANGE_YES);

            // 增加积分
            $companyId = $user->company_id;
            $scenCode = "Mobile-Training";
            $pointRuleService = new PointRuleService();
            $pointRuleService->checkActionForPoint($companyId,$userId,'Login',$scenCode);

            $result = TMessageHelper::resultBuild($systemKey, $code, $name, $message, $jsonResult);
        }
        else {
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
        }

        return $result;
    }

    /**
     * 刷新令牌
     * @param $systemKey
     * @param $codeName
     * @param $userName
     * @param $password
     * @return array
     */
    public function refreshAccessTokenByUsername($systemKey, $codeName, $userName, $password)
    {
        $commonUserService = new \common\services\framework\UserService();
        $model = new FwUser();
        $user = $model->findByUsername($userName);
        $success = false;
        $code = null;
        $name = null;
        $number = null;
        $message = null;
        $userId = null;

        $jsonResult['access_token'] = null;
        $jsonResult['user_id'] = null;
        $jsonResult['user_key'] = null;
        $jsonResult['expire'] = null;

        if (!$user) {
            $code = "common";
            $number = "006";
            $name = Yii::t('common','data_not_exist');
        } else if ($user->status == self::STATUS_FLAG_TEMP) {
            $code = $codeName;
            $number = "001";
            $name = Yii::t('common','login_error_user_temp');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_TEMP);
        }else if ($user->status == self::STATUS_FLAG_STOP) {
            $code = $codeName;
            $number = "002";
            $name = Yii::t('common','login_error_user_stop');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
        } else if (!$user->validatePassword($password)) {
            $code = $codeName;
            $number = "003";
            $name = Yii::t('common','login_error_password_validate');

            $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_PASSWORD_VALIDATION);
        }
        else  {
            $currentTime = time();

            if (!empty($user->valid_start_at) && $currentTime < $user->valid_start_at)
            {
                $code = $codeName;
                $number = "002";
                $name = Yii::t('common','login_error_user_stop');
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
            }
            else if (!empty($user->valid_end_at) && $currentTime > $user->valid_end_at)
            {
                $code = $codeName;
                $number = "002";
                $name = Yii::t('common','login_error_user_stop');
                $commonUserService->recordFailedLoginInfo($user->kid,FwUser::LOGIN_FAILED_USER_STOP);
            }
            else {
                $userId = $user->kid;
//                $commonUserService->login($userId);
                $success = true;
            }
        }

        if ($success) {
            $externalSystemService = new ExternalSystemService();

            $accessTokenArray = $externalSystemService->generateAccessTokenBySystemKey($systemKey, $userId);

            $code = "OK";
            $jsonResult['access_token'] = $accessTokenArray['access_token'];
            $jsonResult['user_id'] = $userId;
            $jsonResult['user_key'] = $externalSystemService->getUserKeyByUserId($systemKey, $userId);
            $jsonResult['expire'] = $accessTokenArray['expire'];

            $result = TMessageHelper::resultBuild($systemKey, $code, $name, $message, $jsonResult);
        }
        else {
            $errorArray = TMessageHelper::errorBuild($code, $number, $name, $message, null);
            $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
        }

        return $result;
    }

    /**
     * 判断是否存在相同的用户名
     * @param $kid
     * @param $userName
     * @return bool
     */
    public function isExistSameUserName($kid, $userName)
    {
        $commonUserService = new \common\services\framework\UserService();
        return $commonUserService->isExistSameUserName($kid, $userName);
    }

    /**
     * 判断是否存在相同的Email
     * @param $kid
     * @param $email
     * @return bool
     */
    public function isExistSameEmail($kid, $email)
    {
        $commonUserService = new \common\services\framework\UserService();
        return $commonUserService->isExistSameEmail($kid, $email);
    }

    /**
     * 获取企业用户数
     * @return int|string
     */
    public function getCompanyUserCount($companyId)
    {
        $commonUserService = new \common\services\framework\UserService();
        return $commonUserService->getCompanyUserCount($companyId);
    }
    /**
     * 根据user identity 获取 access token
     * @param $systemKey
     * @param FwUser $user
     * @return array
     */
    public function getAccessTokenByUserIdentity($systemKey, FwUser $user) {
        $externalSystemService = new ExternalSystemService();
        $accessTokenArray = $externalSystemService->getAccessTokenArrayBySystemKey($systemKey, $user->kid);

        if (empty($accessTokenArray)) {
            $accessTokenArray = $externalSystemService->generateAccessTokenBySystemKey($systemKey, $user->kid);
        }

        $code = "OK";
        $jsonResult['access_token'] = $accessTokenArray['access_token'];
        $jsonResult['user_id'] = $user->kid;
        $jsonResult['user_key'] = $externalSystemService->getUserKeyByUserId($systemKey, $user->kid);
        $jsonResult['expire'] = $accessTokenArray['expire'];
        $jsonResult['need_pwd_change'] = ($user->need_pwd_change === FwUser::NEED_PWD_CHANGE_YES);

        $result = TMessageHelper::resultBuild($systemKey, $code, null, null, $jsonResult);
        return $result;
    }
}