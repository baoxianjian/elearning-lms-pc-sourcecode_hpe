<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/15/15
 * Time: 10:54 AM
 **/

namespace common\services\framework;

use common\helpers\TBaseHelper;
use common\helpers\TTimeHelper;
use common\models\framework\FwDictionary;
use common\models\framework\FwGrowth;
use common\models\framework\FwPointRule;
use common\models\framework\FwUserDisplayInfo;
use common\models\framework\FwUserPointDetail;
use common\models\framework\FwUserPointSummary;
use common\models\learning\LnResourceDomain;
use common\models\framework\FwActionLog;
use common\models\framework\FwCompany;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\framework\FwRole;
use common\models\framework\FwUserManager;
use common\models\framework\FwUserPosition;
use common\models\framework\FwUserRole;
use common\models\message\MsSubscribeSetting;
use common\models\message\MsSubscribeType;
use common\services\learning\CourseService;
use common\base\BaseActiveRecord;
use common\eLearningLMS;
use common\helpers\TNetworkHelper;
use components\widgets\TPagination;
use Yii;
use common\models\framework\FwUser;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class UserService extends FwUser
{
    /**
     * 获取企业用户数
     * @return int|string
     */
    public function getCompanyUserCount($companyId)
    {
        $model = new FwUser();
        $query = $model->find(false);
        $query->andFilterWhere(['=', 'company_id', $companyId]);

        return $query->count(1);
    }

    public function getLikeNameByValue($companyId, $nameValue, $courseId)
    {
        $domain = LnResourceDomain::find(false)
            ->andFilterWhere(['=', 'resource_id', $courseId])
            ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->select('domain_id')
            ->distinct('domain_id')
            ->all();
        $domainIds = ArrayHelper::map($domain, 'domain_id', 'domain_id');

        $query = FwDomain::find(false);
        $domainList = $query->andFilterWhere(['in', 'kid', $domainIds])->all();

        $domainService = new UserDomainService();
        foreach ($domainList as $domain) {
            if ($domain->share_flag === FwDomain::SHARE_FLAG_SHARE) {
                $temp = $domainService->getUniqueDomain($domain->company_id, $domain->parent_domain_id);
                $temp = ArrayHelper::map($temp, 'kid', 'kid');
                $domainIds = array_merge($domainIds, $temp);
            }
        }

        $model = new FwUser();

        $query = $model->find(false)
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['like', 'real_name', $nameValue])
            ->andFilterWhere(['=', 'status', FwUser::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['in', 'domain_id', $domainIds])
            ->groupBy('kid');

        $result = $query->all();
        if ($result != null) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * 根据用户ID获取该用户的所有下属
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDirectReporterByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserDirectReporter_" . $userId;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            } else {
                $userModel = FwUser::findOne($userId);
                $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;

                $userManageQuery = FwUserManager::find(false);
                $userManageQuery->select('user_id')
                    ->andFilterWhere(['=', 'manager_id', $userId])
                    ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', 'reporting_model', $reportingModel])
                    ->distinct();
                $userManageQuerySql = $userManageQuery->createCommand()->rawSql;


                $query = FwUser::find(false);


//                $query->innerJoinWith('fwOrgnization')
//            ->innerJoinWith('orgnization.treeNode')
                $query->andFilterWhere(['=', FwUser::realTableName() . '.status', self::STATUS_FLAG_NORMAL])
                    ->andWhere(FwUser::tableName() . '.' . BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $userManageQuerySql . ')');;

//            $query->addOrderBy([FwTreeNode::tableName() . '.tree_level' => SORT_ASC]);
//            $query->addOrderBy([FwTreeNode::tableName() . '.parent_node_id' => SORT_ASC]);
//            $query->addOrderBy([FwTreeNode::tableName() . '.display_number' => SORT_ASC]);
//            $query->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC]);
                $query->addOrderBy([FwUser::realTableName() . '.real_name' => SORT_ASC]);

                $result = $query->all();

                if (!empty($result) && count($result) > 0) {
                    foreach ($result as $single) {
                        $single->user_display_name = $single->getDisplayName();
                    }
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $result);
                }

                return $result;
            }
        } else {
            return null;
        }
    }

    /**
     * 根据用户ID获取该用户的所有下属（转换成文本字符串）
     * @param $userId
     * @return string
     */
    public function getDirectReporterStringByUserId($userId, $withSession = true)
    {
        $list = $this->getDirectReporterByUserId($userId, $withSession);

        $result = null;
        if ($list != null) {

            foreach ($list as $user) {
                $realName = $user->getDisplayName();
                $result = $result . $realName . ",";
            }

            if ($result != "") {
                $result = rtrim($result, ",");
            }
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
        $dictionaryService = new DictionaryService();
        $isAllowRepeatStop = $dictionaryService->getDictionaryValueByCode("system", "is_allow_repeat_stop");

        $model = new FwUser();
        $query = $model->find(false)
            ->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'user_name', $userName]);

        if ($isAllowRepeatStop == FwDictionary::YES) {
            $query->andFilterWhere(['<>', 'status', FwUser::STATUS_FLAG_STOP]);
        }

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断是否存在相同的Email
     * @param $kid
     * @param $email
     * @return bool
     */
    public function isExistSameEmail($kid, $email)
    {
        if (empty($email)) {
            return false;
        } else {
            $dictionaryService = new DictionaryService();
            $isAllowRepeatStop = $dictionaryService->getDictionaryValueByCode("system", "is_allow_repeat_stop");

            $model = new FwUser();
            $query = $model->find(false)
                ->andFilterWhere(['<>', 'kid', $kid])
                ->andFilterWhere(['=', 'email', $email]);

            if ($isAllowRepeatStop == FwDictionary::YES) {
                $query->andFilterWhere(['<>', 'status', FwUser::STATUS_FLAG_STOP]);
            }

            $count = $query->count(1);

            if ($count > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 判断Email是否重复
     * @param $email
     * @return bool
     */
    public function isEmailRepeat($email)
    {
        $dictionaryService = new DictionaryService();
        $isAllowRepeatStop = $dictionaryService->getDictionaryValueByCode("system", "is_allow_repeat_stop");

        $model = new FwUser();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'email', $email]);

        if ($isAllowRepeatStop == FwDictionary::YES) {
            $query->andFilterWhere(['<>', 'status', FwUser::STATUS_FLAG_STOP]);
        }

        $count = $query->count(1);

        if ($count > 1) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 根据用户ID判断是否在线
     * @param $userId
     * @param int $onlineTime
     * @return bool
     */
    public function isOnline($userId, $onlineMinutes = 10)
    {
        if ($userId != null) {
            FwUser::removeFromCacheByKid($userId);

            $model = FwUser::findOne($userId);

            $lastActionAt = $model->last_action_at;

            if ($lastActionAt == null || $model->status != FwUser::STATUS_FLAG_NORMAL || $model->online_status != FwUser::ONLINE_STATUS_OFFLINE) {
                return false;
            } else {
                $onlineTime = time() - ($onlineMinutes * 60); // 5 mins; 60 secs

                if ($lastActionAt > $onlineTime) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 保持用户在线状态
     * @param $userId
     */
    public function keepOnline($userId, $login = false)
    {
        if ($userId != null) {
            $currentTime = time();
            $user = new FwUser();
            $requestInfo = Yii::$app->getRequest();
            $actionUrl = $requestInfo->url;
            $actionIp = TNetworkHelper::getClientRealIP();

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . " = :kid";

            $param = [
                ':kid' => $userId,
            ];

            $attributes = [
                'last_action_at' => $currentTime,
                'online_status' => FwUser::ONLINE_STATUS_ONLINE,
                'last_action_ip' => $actionIp,
            ];

            if ($login) {
                $newAttributes = [
                    'login_number' => new Expression(BaseActiveRecord::getQuoteColumnName("login_number") . "+ 1"),
                ];
                $attributes = array_merge(
                    $attributes,
                    $newAttributes
                );

                //如果当天前台，没有登录记录，则构造一条虚拟的登录记录
                $machineLabel = null;
                if (isset(Yii::$app->params['machine_label']))
                    $machineLabel = Yii::$app->params['machine_label'];

                if (Yii::$app->session->has("system_id")) {
                    $systemId = Yii::$app->session->get("system_id");
                } else {
                    $systemId = null;
                }

                $encryptMode = FwActionLog::ENCRYPT_MODE_NONE;

                $httpMode = FwActionLog::HTTP_MODE_POST;

                $day = date('Y-m-d', $currentTime);

                $sessionKey = "TodayLogined_" . $day;

                $logined = false;
                if (Yii::$app->session->has($sessionKey)) {
                    $logined = Yii::$app->session->get($sessionKey);
                }

                if (!$logined) {
                    $actionLogFilterService = new ActionLogFilterService();
                    $actionLogModel = $actionLogFilterService->getActionLogFilterByCode("frontend_login");
                    if (!empty($actionLogModel)) {

//                    $startAt = strtotime($day);// 当天的24
//                    $endAt = $startAt + 24 * 60 * 60;

                        $actionFilterId = $actionLogModel->kid;
                        $controllerId = $actionLogModel->controller_id;
                        $actionId = $actionLogModel->action_id;
                        $paramterQuery = null;
                        $paramterBody = null;
                        $actionLogService = new ActionLogService();
                        $startAt = strtotime(TTimeHelper::getCurrentDayStart());
                        $endAt = strtotime(TTimeHelper::getCurrentDayEnd());
                        $exist = $actionLogService->checkDailyActionLogExist($actionFilterId, $userId, $startAt, $endAt);
                        if (!$exist) {
                            $actionLogService = new ActionLogService();
                            $actionLogService->insertActionLog($systemId, $actionFilterId, $userId, $controllerId, $actionId, $paramterQuery, $paramterBody, $encryptMode, $httpMode,
                                $actionUrl, "eln_frontend", $actionIp, $currentTime, $currentTime, 0, $machineLabel);
                        }

                        Yii::$app->session->set($sessionKey, true);
                    }
                }
            }


            $user->updateAll($attributes, $condition, $param);
            FwUser::removeFromCacheByKid($userId);
        }
    }

    /**
     * 保持用户离线状态
     * @param $userId
     */
    public function keepOffline($userId)
    {
        if ($userId != null) {
            $user = new FwUser();

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . " = :kid";

            $param = [
                ':kid' => $userId,
            ];

            $attributes = [
                'online_status' => FwUser::ONLINE_STATUS_OFFLINE,
            ];

            $user->updateAll($attributes, $condition, $param);
            FwUser::removeFromCacheByKid($userId);
        }
    }

    /**
     * 记录失败的登录信息
     * @param $userId
     * @param $failedLoginReason
     */
    public function recordFailedLoginInfo($userId, $failedLoginReason)
    {
        if ($userId != null) {
            $oldUser = FwUser::findOne($userId);
            $user = new FwUser();

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . " = :kid";

            $param = [
                ':kid' => $userId,
            ];

            $attributes = [
                'failed_login_reason' => $failedLoginReason,
            ];

            if ($oldUser->failed_login_start_at == null) {
                $newAttributes = [
                    'failed_login_start_at' => time(),
                ];

                $attributes = array_merge(
                    $attributes,
                    $newAttributes
                );

            }

            if ($oldUser->failed_login_times == null) {
                $newAttributes = [
                    'failed_login_times' => 1,
                ];

                $attributes = array_merge(
                    $attributes,
                    $newAttributes
                );
            } else {
                $newAttributes = [
                    'failed_login_times' => new Expression(BaseActiveRecord::getQuoteColumnName("failed_login_times") . " + 1"),
                ];
                $attributes = array_merge(
                    $attributes,
                    $newAttributes
                );
            }

            $newAttributes = [
                'failed_login_last_at' => time(),
            ];

            $attributes = array_merge(
                $attributes,
                $newAttributes
            );

            $user->updateAll($attributes, $condition, $param);
            FwUser::removeFromCacheByKid($userId);
        }

    }

    /**
     * 登录系统
     * @param $userId
     */
    public function login($userId)
    {
        if ($userId != null) {
            $user = new FwUser();

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . " = :kid";

            $param = [
                ':kid' => $userId,
            ];

            $attributes = [
                'last_login_at' => time(),
                'last_login_ip' => TNetworkHelper::getClientRealIP(),
                'failed_login_start_at' => null,
                'failed_login_last_at' => null,
                'failed_login_times' => null,
                'failed_login_reason' => null,
                'online_status' => FwUser::ONLINE_STATUS_ONLINE,
                'login_number' => new Expression(BaseActiveRecord::getQuoteColumnName("login_number") . "+ 1"),
            ];

            $user->updateAll($attributes, $condition, $param);
            FwUser::removeFromCacheByKid($userId);
        }
    }

    /**
     * 登录检查
     * @param $form
     * @param string $systemKey
     * @return bool
     */
    public function loginCheck($form, $systemKey = "PC")
    {
        if ($systemKey == "PC") {
            if ($form->validate()) {
                $user = $form->getFwUser();
                $rememberMe = $form->remember_me ? 3600 * 24 * $form->remember_time : 0;
                $license = null;
                if (isset(Yii::$app->params['license'])) {
                    $license = Yii::$app->params['license'];
                }
                return $this->loginByPC($user, $rememberMe, $license);
            } else {
                return false;
            }
        } else {
            //todo 根据其他客户端的情况进行自定义
            return false;
        }
    }

    /**
     * PC端登录
     * @param $user
     * @param $rememberMe
     * @param $license
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function loginByPC($user, $rememberMe, $license)
    {
        $hostUrl = Yii::$app->request->getHostInfo();
        $hostName = str_replace(['http:', 'https:', '/'], ['', '', ''], $hostUrl);
        $position = strpos($hostName, ":");
        if ($position == false || $position == 0) {
            //不包含端口号，不做处理
        } else {
            $hostName = substr($hostName, 0, $position);
        }

        $isDevEnvironment = in_array($hostName, TBaseHelper::$devEnvironmentSites);

//        Yii::getLogger()->log("start Login 12", Logger::LEVEL_ERROR);
        if ($isDevEnvironment || $license != null) {
//            Yii::getLogger()->log("start Login 13", Logger::LEVEL_ERROR);
            if (!$isDevEnvironment && !TBaseHelper::checkLicense($license, $errMessage)) {
                //echo $errMessage;
                header("Location: " . Url::toRoute(['site/license']));
                exit;
                //Yii::$app->end();
            } else {
//                    Yii::getLogger()->log("start Login 15", Logger::LEVEL_ERROR);
                $loginResult = Yii::$app->user->login($user, $rememberMe);//单位:天
//                    Yii::getLogger()->log("start Login 16", Logger::LEVEL_ERROR);
                if ($loginResult) {
                    $userId = Yii::$app->user->getId();
                    $this->login($userId);
                }
//                    Yii::getLogger()->log("start Login 17:".$loginResult, Logger::LEVEL_ERROR);
                return $loginResult;
            }
        } else {
            $macAddress = TBaseHelper::getMacAddress();
            if (TBaseHelper::getMachineCode($macAddress, $machineCode, $errMessage)) {
                $errMsg = Yii::t('system', 'frontend_name') . Yii::t('common', 'license_error') . Yii::t('common', 'machine_code_info_{code}', ['code' => $machineCode]);
            } else {
                $errMsg = Yii::t('system', 'frontend_name') . Yii::t('common', 'license_error') . Yii::t('common', 'machine_code_error_{errorMsg}', ['errorMsg' => $errMessage]);
            }

            echo $errMsg;
            Yii::$app->end();
        }
    }

    /**
     * 获取在线用户列表
     * @param int $onlineMinutes
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getOnlineUserList($onlineMinutes = 10)
    {
        $model = FwUser::find(false);

        $onlineTime = time() - ($onlineMinutes * 60); // 5 mins; 60 secs

        $params = [
            ':status' => FwUser::STATUS_FLAG_NORMAL,
            ':online_status' => FwUser::ONLINE_STATUS_ONLINE
        ];


        $condition = "status = :status AND online_status = :online_status AND last_action_at is not null AND last_action_at > " . strval($onlineTime);

        $query = $model->andWhere($condition, $params);

        return $query->all();
    }

    /**
     * 获取系统用户数
     * @return int|string
     */
    public function getUserCount($companyId, $isSpecial, $withCache = false)
    {
        $cacheKey = "TotalUserCount";

        if (!$isSpecial) {
            $cacheKey .= "_Company_" . $companyId;
        }

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = new FwUser();
            $query = $model->find(false);

            if (!$isSpecial) {
                $query->andFilterWhere(['=', 'company_id', $companyId]);
            }

            $result = $query->count(1);

            if ($withCache) {
                self::saveToCache($cacheKey, $result);
            }
        }

        return $result;
    }


    /**
     * 获取在线用户数
     * @param int $onlineMinutes
     * @return int|string
     */
    public function getOnlineUserCount($onlineMinutes = 5)
    {
        $model = FwUser::find(false);

        $onlineTime = time() + ($onlineMinutes * 60); // 5 mins; 60 secs

        $params = [
            ':status' => FwUser::STATUS_FLAG_NORMAL,
            ':online_status' => FwUser::ONLINE_STATUS_ONLINE
        ];

        $condition = "status = :status AND online_status = :online_status AND last_action_at is not null AND " . $onlineTime . " - last_action_at > 0";

        $query = $model->andWhere($condition, $params);

        return $query->count('kid');
    }

    /**
     * 根据用户ID获取该用户的所有角色信息
     * @param $userId
     * @return array|FwUserRole[]
     */
    public function getRoleListByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {

            $sessionKey = "UserRole_" . $userId;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            } else {
                $query = FwUserRole::find(false);

                $query->innerJoin(FwRole::tableName(), FwRole::tableName() . "." . BaseActiveRecord::getQuoteColumnName("kid") .
                    "=" . FwUserRole::tableName() . "." . BaseActiveRecord::getQuoteColumnName("role_id") . " " .
                    "AND " . FwRole::tableName() . "." . BaseActiveRecord::getQuoteColumnName("is_deleted") .
                    "= '" . self::DELETE_FLAG_NO . "'")
                    ->andFilterWhere(['=', FwUserRole::realTableName() . '.status', FwUserRole::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', FwRole::realTableName() . '.status', FwRole::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', FwUserRole::realTableName() . '.user_id', $userId]);

                $query->addOrderBy([FwRole::realTableName() . '.company_id' => SORT_ASC]);
                $query->addOrderBy([FwRole::realTableName() . '.created_at' => SORT_DESC]);

                $query->select([
                    'kid' => FwUserRole::realTableName() . '.kid',
                    'role_id' => FwUserRole::realTableName() . '.role_id',
//                    'role_display_name' =>
//                        'concat(' . FwRole::tableName() .  '.' . BaseActiveRecord::getQuoteColumnName("role_name") .
//                            ',"(",' .
//                            FwRole::tableName() . '.' . BaseActiveRecord::getQuoteColumnName("role_code") . ',")")',
                    'role_name' => 'role_name',
                    'role_code' => 'role_code'
                ]);

                $result = $query->all();

                if (!empty($result) && count($result) > 0) {
                    foreach ($result as $single) {
                        $single->role_display_name = $single->role_name . "(" . $single->role_code . ")";
                    }
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $result);
                }

                return $result;
            }
        } else {
            return null;
        }
    }

    /**
     * 根据用户ID获取该用户的所有角色信息（转换成文本字符串）
     * @param $userId
     * @return string
     */
    public function getRoleListStringByUserId($userId, $withSession = true)
    {
        $list = $this->getRoleListByUserId($userId, $withSession);

        $result = "";
        if ($list != null) {
            foreach ($list as $userRole) {
                $roleName = $userRole->role_name;
                $result = $result . $roleName . ",";
            }

            if ($result != "") {
                $result = rtrim($result, ",");
            }
        }
        return $result;
    }


    /**
     * 根据用户ID获取该用户的所有岗位信息
     * @param $userId
     * @return array|FwUserPosition[]
     */
    public function getPositionListByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserPosition_" . $userId;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            } else {
                $query = FwUserPosition::find(false);

                $query->innerJoinWith('fwPosition')
//            ->innerJoinWith('orgnization.treeNode')
                    ->andFilterWhere(['=', FwUserPosition::realTableName() . '.status', self::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', FwPosition::realTableName() . '.status', self::STATUS_FLAG_NORMAL])
                    ->andFilterWhere(['=', FwUserPosition::realTableName() . '.user_id', $userId]);

                $query->addOrderBy([FwUserPosition::realTableName() . '.is_master' => SORT_DESC]);
                $query->addOrderBy([FwUserPosition::realTableName() . '.position_id' => SORT_ASC]);
                $query->addOrderBy([FwUserPosition::realTableName() . '.created_at' => SORT_DESC]);

                $query->select([
                    'kid' => FwUserPosition::realTableName() . '.kid',
                    'position_id' => FwUserPosition::realTableName() . '.position_id',
//                    'position_display_name' => 'concat(' . FwPosition::tableName() . '.position_name,"(",' . FwPosition::tableName() . '.position_code,")")',
                    'position_name' => 'position_name',
                    'position_code' => 'position_code',
                    'is_master' => 'is_master',
                ]);

                $result = $query->all();

                if (!empty($result) && count($result) > 0) {
                    foreach ($result as $single) {
                        $single->position_display_name = $single->position_name . "(" . $single->position_code . ")";
                    }
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $result);
                }

                return $result;
            }

        } else {
            return null;
        }
    }


    /**
     * 根据用户ID获取该用户的所有管理的企业
     * @param $userId
     * @return array|FwOrgnization[]
     */
    public function getManageOrgListByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserManageOrgnization_" . $userId;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            } else {
                $query = FwOrgnization::find(false)
                    ->andFilterWhere(['=', 'orgnization_manager_id', $userId])
                    ->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL]);

                $result = $query->all();

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $result);
                }

                return $result;
            }

        } else {
            return null;
        }
    }


    /**
     * 根据用户ID获取该用户的所有岗位信息（转换成文本字符串）
     * @param $userId
     * @return string
     */
    public function getPositionListStringByUserId($userId, $withSession = true)
    {
        $list = $this->getPositionListByUserId($userId, $withSession);

        $result = "";
        if ($list != null) {

            foreach ($list as $userPosition) {
                $positionName = $userPosition->position_name;
                $result = $result . $positionName . ",";
            }

            if ($result != "") {
                $result = rtrim($result, ",");
            }
        }

        return $result;
    }

    /**
     * 根据用户ID获取该用户的企业名称信息（转换成文本字符串）
     * @param $userId
     * @return string
     */
    public function getCompanyStringByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserCompanyString_" . $userId;
        } else {
            return null;
        }

        if ($withSession && Yii::$app->session->has($sessionKey)) {
            return Yii::$app->session->get($sessionKey);
        } else {
            $userModel = FwUser::findOne($userId);

            $result = "";

            if ($userModel != null) {
                $companyId = $userModel->company_id;
                if ($companyId != null) {
                    $companyModel = FwCompany::findOne($companyId);

                    if ($companyModel != null) {
                        $result = $companyModel->company_name;
                    }
                }
            }

            if ($withSession) {
                Yii::$app->session->set($sessionKey, $result);
            }
        }

        return $result;
    }


    /**
     * 根据用户ID获取该用户的组织部门名称信息（转换成文本字符串）
     * @param $userId
     * @return string
     */
    public function getOrgnizationStringByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserOrgnizationString_" . $userId;
        } else {
            return null;
        }

        if ($withSession && Yii::$app->session->has($sessionKey)) {
            return Yii::$app->session->get($sessionKey);
        } else {

            $userModel = FwUser::findOne($userId);

            $result = "";

            if ($userModel != null) {
                $orgnizationId = $userModel->orgnization_id;
                if ($orgnizationId != null) {
                    $orgnizationModel = FwOrgnization::findOne($orgnizationId);

                    if ($orgnizationModel != null) {
                        $result = $orgnizationModel->orgnization_name;
                    }
                }
            }

            if ($withSession) {
                Yii::$app->session->set($sessionKey, $result);
            }
        }

        return $result;
    }


    /**
     * 根据用户ID获取该用户的经理名称信息（转换成文本字符串）
     * @param $userId
     * @return string
     */
    public function getReportingManagerStringByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserReportingManagerString_" . $userId;
        } else {
            return null;
        }

        if ($withSession && Yii::$app->session->has($sessionKey)) {
            return Yii::$app->session->get($sessionKey);
        } else {
            $userModel = FwUser::findOne($userId);

            $result = null;

            if ($userModel != null) {
                $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;

                if ($reportingModel == FwCompany::REPORTING_MODEL_LINE_MANAGER) {
                    $reportingManagerId = $userModel->reporting_manager_id;
                    if ($reportingManagerId != null) {
                        $reportingManagerModel = FwUser::findOne($reportingManagerId);

                        if ($reportingManagerModel != null) {
                            $result = $reportingManagerModel->getDisplayName();
                        }
                    }
                } else {
                    $list = $this->getReportingManagerByUserId($userId, $withSession);

                    if ($list != null) {
                        foreach ($list as $user) {
                            //$realName = $user->real_name . "(" . $user->email . ")";
                            $realName = $user->user_display_name;
                            $result = $result . $realName . ",";
                        }

                        if ($result != "") {
                            $result = rtrim($result, ",");
                        }
                    }
                }
            }
        }

        return $result;
    }


    /**
     * 根据用户ID获取该用户的所有经理
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getReportingManagerByUserId($userId, $withSession = true)
    {
        $result = null;
        if (!empty($userId)) {
            $sessionKey = "UserReportingManager_" . $userId;

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            } else {

                $userModel = FwUser::findOne($userId);
                $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;

                if ($reportingModel == FwCompany::REPORTING_MODEL_LINE_MANAGER) {
                    if ($userModel->reporting_manager_id != null) {
                        $result = FwUser::findOne($userModel->reporting_manager_id);

                        if ($result != null) {
                            $result->user_display_name = $result->getDisplayName();
                        }
                    }
                } else {
                    $userManageQuery = FwUserManager::find(false);
                    $userManageQuery->select('manager_id')
                        ->andFilterWhere(['=', 'user_id', $userId])
                        ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
                        ->andFilterWhere(['=', 'reporting_model', $reportingModel])
                        ->distinct();
                    $userManageQuerySql = $userManageQuery->createCommand()->rawSql;


                    $query = FwUser::find(false);


//                $query->innerJoinWith('fwOrgnization')
////            ->innerJoinWith('orgnization.treeNode')
//                    ->andFilterWhere(['=', FwOrgnization::tableName() . '.status', self::STATUS_FLAG_NORMAL])
                    $query->andFilterWhere(['=', FwUser::realTableName() . '.manager_flag', self::MANAGER_FLAG_YES])
                        ->andWhere(FwUser::tableName() . '.' . BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $userManageQuerySql . ')');

//            $query->addOrderBy([FwTreeNode::tableName() . '.tree_level' => SORT_ASC]);
//            $query->addOrderBy([FwTreeNode::tableName() . '.parent_node_id' => SORT_ASC]);
//            $query->addOrderBy([FwTreeNode::tableName() . '.display_number' => SORT_ASC]);
//            $query->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC]);
                    $query->addOrderBy([FwUser::realTableName() . '.real_name' => SORT_ASC]);

                    $result = $query->all();

                    if (!empty($result) && count($result) > 0) {
                        foreach ($result as $single) {
                            $single->user_display_name = $single->getDisplayName();
                        }
                    }
                }

                if ($withSession) {
                    Yii::$app->session->set($sessionKey, $result);
                }

                return $result;
            }
        } else {
            return $result;
        }
    }

    /**
     * 取用户的审批人信息
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getApproverByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserApproverString_" . $userId;
        } else {
            return null;
        }
        if ($withSession && Yii::$app->session->has($sessionKey)) {
            return Yii::$app->session->get($sessionKey);
        } else {
            $courseService = new CourseService();
            $result = $courseService->getUserSpecialApproval($userId);/*审批人user_id*/
            if ($withSession) {
                Yii::$app->session->set($sessionKey, $result);
            }

            return $result;
        }
    }

    /**
     * 根据用户ID获取该用户的域名称信息（转换成文本字符串）
     * @param $userId
     * @return string
     */
    public function getDomainStringByUserId($userId, $withSession = true)
    {
        if (!empty($userId)) {
            $sessionKey = "UserDomainString_" . $userId;
        } else {
            return null;
        }

        if ($withSession && Yii::$app->session->has($sessionKey)) {
            return Yii::$app->session->get($sessionKey);
        } else {
            $userModel = FwUser::findOne($userId);

            $result = "";

            if ($userModel != null) {
                $domainId = $userModel->domain_id;
                if ($domainId != null) {
                    $domainModel = FwDomain::findOne($domainId);

                    if ($domainModel != null) {
                        $result = $domainModel->domain_name;
                    }
                }
            }
        }

        return $result;
    }


    public function getAttentionByUid($uid, $filter, $time = null, $size, $page, $current_time)
    {
        $sql = '';
        $time_condition = '';

        if ($time == 1) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK)) ';
        } else if ($time == 2) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) ';
        } else if ($time == 3) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) ';
        }

        $offset = $this->getOffset($page, $size);

        $question_sql = "SELECT\n" .
            "	t1.question_id as kid,t2.title,t1.created_at,t2.created_at as a_created_at,t3.real_name as sender,'q' as type\n" .
            "FROM\n" .
            "	eln_so_question_care t1\n" .
            "LEFT JOIN eln_so_question t2 ON t1.question_id = t2.kid\n" .
            "LEFT JOIN eln_fw_user t3 ON t2.created_by = t3.kid\n" .
            "WHERE t1.user_id='$uid' AND t1.is_deleted=0 AND t1.status=1 AND t2.is_deleted=0\n";

        $person_sql = "SELECT\n" .
            "	t1.attention_id as kid,t2.real_name as title,t1.created_at,'','','u' as type\n" .
            "FROM\n" .
            "	eln_so_user_attention t1\n" .
            "LEFT JOIN eln_fw_user t2 ON t1.attention_id = t2.kid\n" .
            "WHERE t1.user_id='$uid' AND t1.is_deleted=0 AND t2.is_deleted=0 AND t1.status=1\n";

        $all_sql = "select * from ($question_sql" .
            "UNION\n" .
            "$person_sql) as t1 WHERE t1.created_at < $current_time\n";

        if ($filter == 3) {
            $sql = $all_sql;
        } else if ($filter == 2) {
            $sql = $question_sql . "AND t1.created_at < $current_time\n";
        } else if ($filter == 1) {
            $sql = $person_sql . "AND t1.created_at < $current_time\n";
        }

        $sql = $sql .
            $time_condition .
            "ORDER BY t1.created_at desc\n" .
            "LIMIT $size OFFSET $offset";

        return eLearningLMS::queryAll($sql);
    }

    public function getAttentionOneByUid($uid, $filter, $time = null, $size, $page, $current_time)
    {
        $sql = '';
        $time_condition = '';

        if ($time == 1) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 WEEK)) ';
        } else if ($time == 2) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) ';
        } else if ($time == 3) {
            $time_condition = ' AND t1.created_at > UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) ';
        }

        $offset = $this->getOffset($page + 1, $size) - 1;

        $question_sql = "SELECT\n" .
            "	t1.question_id as kid,t2.title,t1.created_at,t2.created_at as a_created_at,t3.real_name as sender,'q' as type\n" .
            "FROM\n" .
            "	eln_so_question_care t1\n" .
            "LEFT JOIN eln_so_question t2 ON t1.question_id = t2.kid\n" .
            "LEFT JOIN eln_fw_user t3 ON t2.created_by = t3.kid\n" .
            "WHERE t1.user_id='$uid' AND t1.is_deleted=0 AND t1.status=1 AND t2.is_deleted=0\n";

        $person_sql = "SELECT\n" .
            "	t1.attention_id as kid,t2.real_name as title,t1.created_at,'','','u' as type\n" .
            "FROM\n" .
            "	eln_so_user_attention t1\n" .
            "LEFT JOIN eln_fw_user t2 ON t1.attention_id = t2.kid\n" .
            "WHERE t1.user_id='$uid' AND t1.is_deleted=0 AND t2.is_deleted=0 AND t1.status=1\n";

        $all_sql = "select * from ($question_sql" .
            "UNION\n" .
            "$person_sql) as t1 WHERE t1.created_at < $current_time\n";

        if ($filter == 3) {
            $sql = $all_sql;
        } else if ($filter == 2) {
            $sql = $question_sql . "AND t1.created_at < $current_time\n";
        } else if ($filter == 1) {
            $sql = $person_sql . "AND t1.created_at < $current_time\n";
        }

        $sql = $sql .
            $time_condition .
            "ORDER BY t1.created_at desc\n" .
            "LIMIT 1 OFFSET $offset";

        return eLearningLMS::queryAll($sql);
    }

    public function getSubscribeSetting($uid)
    {
        $cacheKey = "SubscribeSettingList_" . $uid;

        if (Yii::$app->cache->exists($cacheKey)) {
            return Yii::$app->cache->get($cacheKey);
        } else {
            $tableName = MsSubscribeType::tableName();
            $query = MsSubscribeType::find(false);
            $query->leftJoin(MsSubscribeSetting::tableName() . ' s', "$tableName.kid = s.type_id" .
                " and s.user_id = '$uid' and s.is_deleted = '" . BaseActiveRecord::DELETE_FLAG_NO . "'")
                ->select("$tableName.kid as type_id,type,type_name,type_code,ifnull(s.`status`,default_status) as `status`,is_turnoff,i18n_flag");

            $data = $query->orderBy("$tableName.kid")
                ->asArray()
                ->all();

            Yii::$app->cache->add($cacheKey, $data);

            return $data;
        }
    }

    /**
     * 设置消息订阅接收状态
     * @param string $uid 用户ID
     * @param string $typeId 消息订阅类型ID
     * @param string $status 接收状态
     * @return bool TRUE 成功 FALSE 失败
     */
    public function setSubscribeSettingStatus($uid, $typeId, $status)
    {
        $model = MsSubscribeSetting::findOne(['user_id' => $uid, 'type_id' => $typeId]);

        $subscribeType = MsSubscribeType::findOne($typeId);

        $subscribeTypeCacheKey = 'UserSubscribeType_' . $uid . '_' . $subscribeType->type;

        // 删除相关订阅类型缓存
        Yii::$app->cache->delete($subscribeTypeCacheKey);

        if ($model) {
            $model->status = $status;
            return $model->save();
        }

        $model = new MsSubscribeSetting();
        $model->type_id = $typeId;
        $model->user_id = $uid;
        $model->status = $status;

        return $model->save();
    }

    /**
     * 获取直线经理下所有用户
     * @param string $rpId 直线经理ID
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUserByReportManager($rpId, $keyword = "")
    {
        $userModel = FwUser::findOne($rpId);
        $reportingModel = FwCompany::findOne($userModel->company_id)->reporting_model;
        $userManageQuery = FwUserManager::find(false);
        $userManageQuery->select('user_id')
            ->andFilterWhere(['=', 'manager_id', $rpId])
            ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'reporting_model', $reportingModel])
            ->distinct();
        $userManageQuerySql = $userManageQuery->createCommand()->rawSql;
        //获取用户信息
        $userList = FwUser::find(false);
        $userList->joinWith('fwUserPositions.fwPosition')
            ->andWhere(FwUser::tableName() . '.kid in (' . $userManageQuerySql . ')');//根据领导获取下属信息

        if ($keyword) {
            $userList->andFilterWhere(['like', 'real_name', $keyword]);
        }
        $userList->select(FwUser::tableName() . '.*,position_name');
        $users = $userList->orderBy('user_name')
            ->asArray()
            ->all();
        return $users;
    }

//    public function getOffset($page, $size)
//    {
//        $_page = (int)$page - 1;
//
//        return $size < 1 ? 0 : $_page * $size;
//    }

    /**
     * 获取用户的直线经理
     * @author adophper 2016-03-01
     * @param string $userId
     * @return boolean|unknown
     */
    public function getUserManager($userId)
    {
        if (empty($userId)) return false;
        $userManager = FwUserManager::find(false)
            ->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'reporting_model', FwUserManager::REPORTING_MODEL_LINE_MANAGER])
            ->one();
        if (empty($userManager)) {
            return false;
        } else {
            return $userManager;
        }
    }

    /**
     * 获取用户可用积分
     * @author adophper 2016-03-08
     * @param $userId
     * @param $companyId
     * @return int
     */
    public function getUserIntegral($userId, $companyId, $field = 'available_point')
    {
        $res = FwUserPointSummary::find(false)
            ->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->select('available_point,get_point,deduct_point,transfer_in_point,transfer_out_point')
            ->orderBy('updated_at desc')
            ->one();
        if (empty($res)) {
            $integral = 0;
        } else {
            if ($field == 'available_point') {
                $integral = $res['available_point'];
            } elseif ($field == 'total') {
                //$integral = doubleval($res['get_point'])-doubleval($res['deduct_point'])+doubleval($res['transfer_in_point'])-doubleval($res['transfer_out_point']);
                $integral = doubleval($res['get_point']) + doubleval($res['transfer_in_point']);
                $integral = round($integral, 2);
            }
        }
//        return sprintf("%.2f", $integral);
        return doubleval($integral);
    }

    /**
     * 用户积分详情
     * @param $userId
     * @param $companyId
     * @param null $params
     * @return array
     */
    public function getUserIntegralList($userId, $companyId, $params = null, $offset = -1)
    {
        $model = FwUserPointDetail::find(false)
            ->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'company_id', $companyId]);
        if (isset($params['point_type']) && $params['point_type'] != '') {
            $model->andFilterWhere(['=', 'point_type', intval($params['point_type'])]);
        }
        if (!empty($params['start_time'])) {
            $model->andFilterWhere(['>=', 'get_at', strtotime($params['start_time'])]);
        }
        if (!empty($params['end_time'])) {
            $model->andFilterWhere(['<=', 'get_at', strtotime($params['end_time'])]);
        }
        $count = $model->count('kid');
        if ($count > 0) {
            $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
            $data = $model->offset($offset < 0 ? $pages->offset : $offset)
                ->limit($pages->limit)
                ->select('kid,reason,point,point_type,get_at')
                ->orderBy('get_at desc')
                ->asArray()
                ->all();
            return ['data' => $data, 'pages' => $pages];
        } else {
            return ['data' => '', 'pages' => ''];
        }
    }

    public function getUserIntegralDetail($userId, $companyId, $time = null)
    {
        $model = FwUserPointDetail::find(false)
            ->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'company_id', $companyId]);

        if ($time == 'year') {
            $start = strtotime(date('Y-01-01 00:00:00'));
            $model->andFilterWhere(['>=', 'get_at', $start]);
        } else if ($time == 'month') {
            $start = strtotime(date('Y-m-01 00:00:00'));
            $model->andFilterWhere(['>=', 'get_at', $start]);
        }
        $outModel = clone $model;
        $getModel = clone $model;
        $minModel = clone $model;
        $in = $model->andFilterWhere(['=', 'point_type', FwUserPointDetail::POINT_TYPE_IN])->sum('point');
        $out = $outModel->andFilterWhere(['=', 'point_type', FwUserPointDetail::POINT_TYPE_OUT])->sum('point');
        $get = $getModel->andFilterWhere(['=', 'point_type', FwUserPointDetail::POINT_TYPE_GET])->sum('point');
        $min = $minModel->andFilterWhere(['=', 'point_type', FwUserPointDetail::POINT_TYPE_MIN])->sum('point');
        $total = doubleval($in) + doubleval($get) - doubleval($out) - doubleval($min);
        $total = round($total, 2);
//        return sprintf("%.2f", $total);
        return doubleval($total);
    }

    /**
     * 获取积分规则
     * @param $companyId
     * @param $params
     * @return array
     */
    public function getIntegralPointRule($companyId, $params, $offset = -1)
    {
        $model = FwPointRule::find(false)
            ->andFilterWhere(['=', 'company_id', $companyId]);
        $count = $model->count('kid');
        if ($count > 0) {
            $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
            $data = $model->offset($offset < 0 ? $pages->offset : $offset)
                ->limit($pages->limit)
                ->select('point_name,cycle_range,standard_value')
                ->asArray()
                ->all();
            return ['data' => $data, 'pages' => $pages];
        } else {
            return ['data' => '', 'pages' => ''];
        }
    }

    /**
     * 获取成长体系
     * @param $companyId
     * @param $params
     * @return array
     */
    public function getIntegralGrowth($companyId, $params)
    {
        $model = FwGrowth::find(false);
//            ->andFilterWhere(['=', 'company_id', $companyId]);
        $count = $model->count('kid');
        if ($count > 0) {
            $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
            $data = $model->offset($pages->offset)
                ->limit($pages->limit)
                ->select('stage_name,level_name,description,require_point,sequence_number')
                ->asArray()
                ->all();
            return ['data' => $data, 'pages' => $pages];
        } else {
            return ['data' => '', 'pages' => ''];
        }
    }


    /**
     * 获得用户显示信息
     * @param $userId
     * @param bool $withCache
     * @return mixed|null
     */
    public function getUserDisplayInfo($userId, $withCache = false)
    {
        $cacheKey = "UserDisplayInfo_UserId_" . $userId;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = new FwUserDisplayInfo();
            $query = $model->find(false)
                ->andFilterWhere(['=', 'user_id', $userId]);

            $result = $query->one();

            self::saveToCache($cacheKey, $result, null, BaseActiveRecord::DURATION_HALFDAY, $withCache);
        }

        return $result;
    }


    /**
     * 通过token登录
     * @param $token token
     * @param $secruityMode 加密模式
     * @return bool
     */
    public function loginByToken($token, $secruityMode)
    {
        if ($secruityMode === 'NA') {
            $user = FwUser::findByUsername($token);

            if (empty($user)) {
                return false;
            }

            $rememberMe = 3600 * 24;
            $license = null;
            if (isset(Yii::$app->params['license'])) {
                $license = Yii::$app->params['license'];
            }
            return $this->loginByPC($user, $rememberMe, $license);
        }

        return false;
    }
}