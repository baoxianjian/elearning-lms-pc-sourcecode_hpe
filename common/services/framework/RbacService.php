<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 5/8/2015
 * Time: 12:15 PM
 */

namespace common\services\framework;

use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use common\models\framework\FwPermission;
use common\models\framework\FwRole;
use common\models\framework\FwRolePermission;
use common\models\framework\FwUser;
use common\models\framework\FwUserRole;


class RbacService
{

    /**
     * 根据权限代码获取已经被控制的Action列表
     * @param $systemFlag
     * @param $actionUrl
     * @param null $actionParameter
     * @return array
     */
    private function getControlledActionList($systemFlag, $actionUrl, $actionParameter = null, $withCache = true)
    {
        if ($actionParameter == null) {
            $cacheKey = "ControlledActionList_SystemFlag_" . $systemFlag . "_ActionUrl_" . $actionUrl;
        }
        else {
            $cacheKey = "ControlledActionList_SystemFlag_" . $systemFlag . "_ActionUrl_" . $actionUrl . "_actionParameter_" . json_encode($actionParameter);
        }

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $permissionModel = new FwPermission();
            $query = $permissionModel->find(false)
                ->andFilterWhere(['=', 'action_type', FwPermission::ACTION_TYPE_ACTION])
                ->andFilterWhere(['=', 'action_parameter', $actionParameter])
                ->andFilterWhere(['=', 'action_url', $actionUrl])
//            ->andFilterWhere(['=', 'status', FwPermission::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'system_flag', $systemFlag]);

            $result = $query->all();

            BaseActiveRecord::saveToCache($cacheKey, $result, null, BaseActiveRecord::DURATION_DAY, $withCache);
        }

        $controlled_keys = ArrayHelper::map($result, 'kid', 'permission_name');

        return $controlled_keys;
    }

    /**
     * 根据权限代码获取已经被控制的URL列表
     * @param $systemFlag
     * @param $url
     * @return array
     */
    private function getControlledUrlList($systemFlag, $url, $withCache = true)
    {
        $cacheKey = "ControlledUrlList_SystemFlag_" . $systemFlag . "_Url_" . $url;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $permissionModel = new FwPermission();
            $query = $permissionModel->find(false)
                ->andFilterWhere(['=', 'action_type', FwPermission::ACTION_TYPE_URL])
                ->andFilterWhere(['=', 'action_url', $url])
//            ->andFilterWhere(['=', 'status', FwPermission::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'system_flag', $systemFlag]);

            $result = $query->all();

            BaseActiveRecord::saveToCache($cacheKey, $result, null, BaseActiveRecord::DURATION_DAY, $withCache);
        }

        $controlled_keys = ArrayHelper::map($result, 'kid', 'permission_name');

        return $controlled_keys;
    }

    /**
     * 根据权限代码获取已经被控制的权限列表
     * @param $systemFlag
     * @param $permissionCode
     * @return array
     */
    private function getControlledPermissionList($systemFlag, $permissionCode, $withCache = true)
    {
        $cacheKey = "ControlledPermissionList_SystemFlag_" . $systemFlag . "_PermissionCode_" . $permissionCode;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $permissionModel = new FwPermission();
            $query = $permissionModel->find(false)
                ->andFilterWhere(['=', 'permission_code', $permissionCode])
//            ->andFilterWhere(['=', 'status', FwPermission::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'system_flag', $systemFlag]);

            $result = $query->all();

            BaseActiveRecord::saveToCache($cacheKey, $result, null, BaseActiveRecord::DURATION_DAY, $withCache);
        }


        $controlled_keys = ArrayHelper::map($result, 'kid', 'permission_name');

        return $controlled_keys;
    }

    /**
     * 判断是否能访问指定的Action
     * @param $userId
     * @param $systemFlag
     * @param $actionUrl
     * @param null $actionParameter
     * @return bool
     */
    public function canAction($userId, $systemFlag, $actionUrl, $actionParameter = null, $withSession = true, $withCache = true)
    {
        $controlled_keys = $this->getControlledActionList($systemFlag, $actionUrl, $actionParameter, $withCache);

        if ($controlled_keys != null && isset($controlled_keys) && count($controlled_keys) > 0) {
            if ($userId == null)
                return false;

            $roleList = $this->getRoleListIncludeSpecialByUserId($userId);

            if ($actionParameter == null) {
                $sessionKey = "CanAction_SystemFlag_" . $systemFlag . "_ActionUrl_" . $actionUrl;
            }
            else {
                $sessionKey = "CanAction_SystemFlag_" . $systemFlag . "_ActionUrl_" . $actionUrl . "_actionParameter_" . json_encode($actionParameter);
            }

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            }
            else {
                $result = $this->getRoleControllerPermissionList($controlled_keys,$roleList);

                $can = false;
                if (isset($result) && $result > 0) {
                    $can = true;
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $can);
                }

                return $can;
            }
        } else {
            return true;
        }
    }

    /**
     * 判断是否能访问指定的权限代码
     * @param $userId
     * @param $systemFlag
     * @param $permissionCode
     * @return bool
     */
    public function canPermisionCode($userId, $systemFlag, $permissionCode, $withSession = true, $withCache = true)
    {
        $controlled_keys = $this->getControlledPermissionList($systemFlag, $permissionCode,$withCache);

        if ($controlled_keys != null && isset($controlled_keys) && count($controlled_keys) > 0) {
            if ($userId == null)
                return false;

            $roleList = $this->getRoleListIncludeSpecialByUserId($userId);

            $sessionKey = "CanPermisionCode_SystemFlag_" . $systemFlag . "_PermissionCode_" . $permissionCode;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            }
            else {
                $result = $this->getRoleControllerPermissionList($controlled_keys,$roleList);

                $can = false;
                if (isset($result) && $result > 0) {
                    $can = true;
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $can);
                }

                return $can;
            }
        } else {
            return true;
        }
    }


    /**
     * 判断是否能访问指定的Url
     * @param $userId
     * @param $systemFlag
     * @param $url
     * @return bool
     */
    public function canUrl($userId, $systemFlag, $url, $withSession = true, $withCache = true)
    {
        $controlled_keys = $this->getControlledUrlList($systemFlag, $url, $withCache);

        if ($controlled_keys != null && isset($controlled_keys) && count($controlled_keys) > 0) {
            if ($userId == null)
                return false;

            $roleList = $this->getRoleListIncludeSpecialByUserId($userId);

            $sessionKey = "CanUrl_SystemFlag_" . $systemFlag . "_Url_" . $url;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            }
            else {
                $result = $this->getRoleControllerPermissionList($controlled_keys,$roleList);

                $can = false;
                if (isset($result) && $result > 0) {
                    $can = true;
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $can);
                }

                return $can;
            }
        } else {
            return true;
        }
    }

    /**
     * 获取角色是否有对controller的操作权限
     * @param $controlled_keys
     * @param $roleList
     * @return int|string
     */
    private function getRoleControllerPermissionList($controlled_keys, $roleList) {
        $roleModel = new FwRolePermission();
        $query = $roleModel->find(false);

        $query->innerJoin(FwPermission::tableName(), FwPermission::tableName() . "." . BaseActiveRecord::getQuoteColumnName("kid") .
            "=" . FwRolePermission::tableName() . "." . BaseActiveRecord::getQuoteColumnName("permission_id") .
            " AND " . FwPermission::tableName() . "." . BaseActiveRecord::getQuoteColumnName("is_deleted") .
            "= '" . FwPermission::DELETE_FLAG_NO . "'")
            ->andFilterWhere(['in', FwRolePermission::realTableName() . '.permission_id', array_keys($controlled_keys)])
            ->andFilterWhere(['=', FwRolePermission::realTableName() . '.status', FwRolePermission::STATUS_FLAG_NORMAL]);

        if ($roleList != null && count($roleList) > 0)
            $query->andFilterWhere(['in', FwRolePermission::realTableName() . '.role_id', $roleList]);
        else
            $query->andWhere(FwRolePermission::realTableName() . '.role_id is null');

        $result = $query->count(1);

        return $result;
    }

    /**
     * 根据角色代码获取角色ID
     * @param $roleCode
     * @return string
     */
    public function getRoleId($roleCode, $withCache = true)
    {
        $cacheKey = "Role_Code_" . $roleCode;

        $result = BaseActiveRecord::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = FwRole::find(false);

            $result = $model
                ->andFilterWhere(['=','role_code',$roleCode])
                ->andFilterWhere(['=','status',FwRole::STATUS_FLAG_NORMAL])
                ->one();

//            $dependencySql = "SELECT * FROM " . self::calculateTableName(FwActionLogFilter::tableName()) . " WHERE filter_code = '" . $filterCode . "' and is_deleted ='0'";

            BaseActiveRecord::saveToCache($cacheKey, $result);
        }

        if ($result != null) {
            $roleId = $result->kid;
        } else {
            $roleId = "";
        }
        return $roleId;
    }

    /**
     * 当前用户所属角色组（含特殊角色）
     * @param $userId
     * @return array
     */
    public function getRoleListIncludeSpecialByUserId($userId,$withSession = true)
    {
        if (!empty($userId)) {

            $sessionKey = "RoleList_" . $userId;

            if ($withSession && Yii::$app->session->has($sessionKey))
            {
                return Yii::$app->session->get($sessionKey);

//                if ($selected_keys_string != null && $selected_keys_string != "")
//                    $selected_keys = explode(',',$selected_keys_string);
//                else
//                    $selected_keys = null;
//
//                return $selected_keys;
            }
            else {
                $userModel = FwUser::findOne($userId);
                $commonUserService = new UserService();
                $selectedResult = $commonUserService->getRoleListByUserId($userId);
                $selectedList = ArrayHelper::map($selectedResult, 'role_id', 'role_display_name');

                $selected_keys = array_keys($selectedList);

                if ($userModel->user_type == FwUser::USER_TYPE_SETUPER) {
                    $roleId = $this->getRoleId("Setuper");

//                    $count = $this->GetActiveUserCountByRoleId($roleId);

                    if (!in_array($roleId, $selected_keys)) {
                        array_push($selected_keys, $roleId);
                    }
                } else if ($userModel->user_type == FwUser::USER_TYPE_SUPER_ADMIN) {
                    $roleId = $this->getRoleId("Super-Admin");

                    if (!in_array($roleId, $selected_keys)) {
                        array_push($selected_keys, $roleId);
                    }
                }

//                if ($selected_keys != null)
//                    $selected_keys_string = implode(',', $selected_keys);//将数组拼接成字符串
//                else
//                    $selected_keys_string = "";


                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $selected_keys);
                }

                return $selected_keys;
            }
        }
        else
        {
            return null;
        }
    }

    /**
     * 当前角色中激活用户数
     * @param $roleId
     * @return int|string
     */
    public function getActiveUserCountByRoleId($roleId)
    {
        $query = FwUserRole::find(false)
            ->innerJoin(FwUser::tableName(), FwUser::tableName() . "." . BaseActiveRecord::getQuoteColumnName("kid") .
                 "=" . FwUserRole::tableName() . "." . BaseActiveRecord::getQuoteColumnName("user_id") . " " .
                 "AND " . FwUser::tableName() . "." . BaseActiveRecord::getQuoteColumnName("is_deleted") .
                 "= '" . FwUser::DELETE_FLAG_NO . "'")
            ->andFilterWhere(['=', 'role_id', $roleId])
            ->andFilterWhere(['=', FwUser::realTableName() . '.status', FwUser::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', FwUserRole::realTableName() . '.status', FwUserRole::STATUS_FLAG_NORMAL])
            ->count(1);

        return $query;
    }

    /**
     * 判断是否特殊用户，如初始化用户，超级管理员等
     * @param $userId
     * @return bool
     */
    public function isSpecialUser($userId,$withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "IsSpecialUser_" . $userId;
            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
//            $selected_keys = $selected_keys_string == "True" ? true : false;
//            return $selected_keys;
            } else {
                $setuperRoleId = $this->getRoleId("Setuper");
                $superAdminRoleId = $this->getRoleId("Super-Admin");

                $roleList = $this->getRoleListIncludeSpecialByUserId($userId, $withSession);

                $isSpecialUser = false;

                if ($roleList != null && (in_array($setuperRoleId, $roleList) || in_array($superAdminRoleId, $roleList))) {
                    $isSpecialUser = true;
                }

//              $selected_keys_string = $isSpecialUser ? "True" : "False";//将数组拼接成字符串

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $isSpecialUser);
                }

                return $isSpecialUser;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 判断是否系统管理员
     * @param $userId
     * @param bool $withSession
     * @return mixed|null
     */
    public function isSysManager($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "IsSystemManager_" . $userId;
            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
//            $selected_keys = $selected_keys_string == "True" ? true : false;
//            return $selected_keys;
            } else {
                $sysAdminRoleId = $this->getRoleId("Sys-Admin");

                $roleList = $this->getRoleListIncludeSpecialByUserId($userId, $withSession);

                $isSysManager = false;

                if ($roleList != null && in_array($sysAdminRoleId, $roleList)) {
                    $isSysManager = true;
                }

//              $selected_keys_string = $isSpecialUser ? "True" : "False";//将数组拼接成字符串

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $isSysManager);
                }

                return $isSysManager;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 判断是否系统管理员
     * @param $userId
     * @param bool $withSession
     * @return mixed|null
     */
    public function isDomainManager($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "IsDomainManager_" . $userId;
            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
//            $selected_keys = $selected_keys_string == "True" ? true : false;
//            return $selected_keys;
            } else {
                $domainAdminRoleId = $this->getRoleId("Domain-Admin");

                $roleList = $this->getRoleListIncludeSpecialByUserId($userId, $withSession);

                $isDomainManager = false;

                if ($roleList != null && in_array($domainAdminRoleId, $roleList)) {
                    $isDomainManager = true;
                }

//              $selected_keys_string = $isSpecialUser ? "True" : "False";//将数组拼接成字符串

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $isDomainManager);
                }

                return $isDomainManager;
            }
        }
        else {
            return false;
        }
    }
}