<?php


namespace backend\services;


use common\base\BaseActiveRecord;
use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\framework\FwUserManager;
use common\models\framework\FwUserPosition;
use common\models\treemanager\FwTreeNode;
use common\services\framework\DictionaryService;
use common\services\framework\RbacService;
use common\services\framework\UserOrgnizationService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class UserService extends FwUser
{


    /**
     * 搜索用户数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $managerFlag, $parentNodeId, $includeSubNode)
    {
        $query = FwUser::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query
            ->leftJoin(FwOrgnization::realTableName(),
                FwUser::tableName() . "." . self::getQuoteColumnName("orgnization_id") . " = " . FwOrgnization::tableName() . "." . self::getQuoteColumnName("kid"))
            ->leftJoin(FwTreeNode::realTableName(),
                FwOrgnization::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid"))
//            ->innerJoinWith('fwOrgnization.fwTreeNode')
//            ->innerJoinWith('orgnization.treeNode')
            ->andFilterWhere(['like', 'real_name', trim(urldecode($this->real_name))])
            ->andFilterWhere(['like', 'user_name', trim(urldecode($this->user_name))])
            ->andFilterWhere(['=', FwUser::realTableName() . '.status', $this->status])
//            ->andFilterWhere(['=', FwTreeNode::realTableName(). '.is_deleted', FwTreeNode::DELETE_FLAG_NO ])
//            ->andFilterWhere(['=', FwOrgnization::realTableName(). '.is_deleted', FwOrgnization::DELETE_FLAG_NO ])
            ->andFilterWhere(['=', 'manager_flag', $managerFlag]);


        if ($includeSubNode == '1') {
            if ($parentNodeId != '') {
                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";

                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
                    ['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]];
                $query->andFilterWhere($condition);
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
            } else {
                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'",
                    BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null'];
                $query->andFilterWhere($condition);
            }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

        } else {
            if ($parentNodeId == '') {
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null');
            } else {
                $query->andFilterWhere(['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]);
            }
        }


//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    [ 'in',FwOrgnization::realTableName() . '.tree_node_id',$treeNodeIdList],
//                    BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null'
//                    ];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//            else
//            {
//                $condition = [ 'in',FwOrgnization::realTableName() . '.tree_node_id',$treeNodeIdList];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//        }
//        else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null');
//            } else {
//                $query->andFilterWhere(['=', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeIdList]);
//            }
//        }

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $companyId = Yii::$app->user->identity->company_id;

            $rbacService = new RbacService();
            $isSpecialUser = $rbacService->isSpecialUser($userId);
            $isSysManager = $rbacService->isSysManager($userId);

            $selectedResult = null;
            //如果是特殊用户，则取所有数据
            if (!$isSpecialUser) {
                //如果不是特殊用户（超级管理员)，则根据授权范围取，（系统管理员默认是空，所以要特殊过滤成当前企业所有数据）
                $userOrgnizationService = new UserOrgnizationService();
                $userOrgnizations = $userOrgnizationService->getUserManagedOrgnizationList($userId, false);
                if (!empty($userOrgnizations)) {
                    $selectedResult = $userOrgnizationService->getTreeNodeIdListByOrgnizationId($userOrgnizations);
                } else {
                    if ($isSysManager) {
                        $query->andFilterWhere(['=', FwUser::realTableName() . '.company_id', $companyId]);
                    }
                }
            }

            if (isset($selectedResult) && $selectedResult != null) {
                $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                $orgnizationIdList = array_keys($selectedList);

                $query->andFilterWhere(['in', FwUser::realTableName() . '.orgnization_id', $orgnizationIdList]);
            } else {
                if (!$isSpecialUser && !$isSysManager) {
                    $query->andWhere(BaseActiveRecord::getQuoteColumnName("kid") . ' is null');
                }
            }

        }


//            ->andFilterWhere(['like', 'limitation', $this->limitation])
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);

//        $query->addOrderBy([FwTreeNode::tableName() . '.tree_level' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.parent_node_id' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC]);
//        $query->addOrderBy([FwUser::realTableName() .'.user_name' => SORT_ASC]);

        return $dataProvider;
    }

    /**
     * 搜索用户数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchOnline($params, $parentNodeId, $includeSubNode, $onlineMinutes = 10)
    {
        $query = FwUser::find(false);

        $onlineTime = time() - ($onlineMinutes * 60); // 5 mins; 60 secs

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query
//            ->innerJoinWith('orgnization.treeNode')
            ->leftJoin(FwOrgnization::realTableName(),
                FwUser::tableName() . "." . self::getQuoteColumnName("orgnization_id") . " = " . FwOrgnization::tableName() . "." . self::getQuoteColumnName("kid"))
            ->leftJoin(FwTreeNode::realTableName(),
                FwOrgnization::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid"))
            ->andFilterWhere(['like', 'real_name', trim(urldecode($this->real_name))])
            ->andFilterWhere(['like', 'user_name', trim(urldecode($this->user_name))])
            ->andFilterWhere(['=', FwUser::realTableName() . '.status', $this->status])
            ->andFilterWhere(['=', FwUser::realTableName() . '.online_status', FwUser::ONLINE_STATUS_ONLINE])
            ->andWhere(BaseActiveRecord::getQuoteColumnName("last_action_at") . " is not null")
            ->andWhere(BaseActiveRecord::getQuoteColumnName("last_action_at") . " > " . strval($onlineTime));

        if ($includeSubNode == '1') {
            if ($parentNodeId != '') {
                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";

                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
                    ['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]];
                $query->andFilterWhere($condition);
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
            } else {
                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'",
                    BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null'];
                $query->andFilterWhere($condition);
            }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('orgnization');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

        } else {
            if ($parentNodeId == '') {
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null');
            } else {
                $query->andFilterWhere(['=', FwOrgnization::realTableName() . '.tree_node_id', $parentNodeId]);
            }
        }


        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $userOrgnizationService = new UserOrgnizationService();

                $selectedResult = $userOrgnizationService->getSearchListByUserId($userId);

                if (isset($selectedResult) && $selectedResult != null) {
                    $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                    $orgnizationIdList = array_keys($selectedList);
                } else {
                    $orgnizationIdList = null;
                }

                $query->andFilterWhere(['in', FwUser::realTableName() . '.orgnization_id', $orgnizationIdList]);
            }

        }


//            ->andFilterWhere(['like', 'limitation', $this->limitation])
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);

//        $query->addOrderBy([FwTreeNode::tableName() . '.tree_level' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.parent_node_id' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwUser::realTableName() . '.real_name' => SORT_ASC]);

        return $dataProvider;
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
     * 获取用户总数
     * @param $time
     * @return int|string
     */
    public function getTotalUserCount($time)
    {
        $model = new FwUser();
        $query = $model->find(false);

        $count = $query
            ->andFilterWhere(['<=', 'created_at', $time])
            ->count(1);

        return $count;
    }

    /**
     * 获取当前部门，全部可选直属下属用户
     * @param $treeNodeIdList
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAvailableDirectReportUserByTreeNodeId($treeNodeId, $userId, $reportingModel)
    {
//        $reporting_manager_id = FwUser::findOne($userId)->reporting_manager_id;

        $query = FwUser::find(false)
            ->andFilterWhere(['=', FwUser::realTableName() . '.status', self::STATUS_FLAG_NORMAL]);

        $query
//            ->innerJoinWith('fwOrgnization.fwTreeNode')
            ->innerJoin(FwOrgnization::realTableName(),
                FwUser::tableName() . "." . self::getQuoteColumnName("orgnization_id") . " = " . FwOrgnization::tableName() . "." . self::getQuoteColumnName("kid"))
            ->innerJoin(FwTreeNode::realTableName(),
                FwOrgnization::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid"));
//            ->innerJoinWith('orgnization.treeNode')
//            ->andFilterWhere(['=', FwOrgnization::realTableName() . '.status', self::STATUS_FLAG_NORMAL]);

//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    [ 'in',FwOrgnization::realTableName() . '.tree_node_id', $treeNodeIdList],
//                    BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null'
//                ];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//            else
//            {
//                $condition = [ 'in',FwOrgnization::realTableName() . '.tree_node_id', $treeNodeIdList];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//        }
//        else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null');
//            } else {
//                $query->andFilterWhere(['=', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeIdList]);
//            }
//        }
        $treeNodeModel = FwTreeNode::findOne($treeNodeId);
        $nodeIdPath = $treeNodeModel->node_id_path . $treeNodeId . "/%";

        $condition = ['or',
            BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
            ['=', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeId]];
        $query->andFilterWhere($condition);

        $query->andFilterWhere(['<>', FwUser::realTableName() . '.kid', $userId]);

        //需要过滤掉自己的汇报领导，避免循环汇报的情况
        $userManageQuery = FwUserManager::find(false);
        $userManageQuery->select('manager_id')
            ->andFilterWhere(['=', 'user_id', $userId])
            ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'reporting_model', $reportingModel])
            ->distinct();

        $userManageQuerySql = $userManageQuery->createCommand()->rawSql;

        $query->andWhere(FwUser::tableName() . '.' . BaseActiveRecord::getQuoteColumnName("kid") . ' not in (' . $userManageQuerySql . ')');
//        $query->andFilterWhere(['<>', FwUser::tableName() . '.kid', $reporting_manager_id]);


        //如果已经有汇报领导且汇报领导不是自己的人，需要过滤掉
        if ($reportingModel == FwUserManager::REPORTING_MODEL_LINE_MANAGER) {
            $reporting_condition = ['or',
                ['=', FwUser::realTableName() . '.reporting_manager_id', $userId],
                BaseActiveRecord::getQuoteColumnName("reporting_manager_id") . ' is null'
            ];

            $query->andFilterWhere($reporting_condition);
        }


//        $query->addOrderBy([FwTreeNode::tableName() . '.tree_level' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.parent_node_id' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwUser::realTableName() . '.real_name' => SORT_ASC]);

        $result = $query->all();

        if (!empty($result) && count($result) > 0) {
            foreach ($result as $single) {
                $single->user_display_name = $single->getDisplayName();
            }
        }

        return $result;
    }


    /**
     * 获取当前部门，全部可选直属经理用户
     * @param $treeNodeIdList
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAvailableReportingManageUserByTreeNodeId($treeNodeId, $userId, $reportingModel)
    {
//        $reporting_manager_id = FwUser::findOne($userId)->reporting_manager_id;

        $query = FwUser::find(false)
            ->andFilterWhere(['=', FwUser::realTableName() . '.status', self::STATUS_FLAG_NORMAL]);

        $query
//            ->innerJoinWith('fwOrgnization.fwTreeNode')
            ->innerJoin(FwOrgnization::realTableName(),
                FwUser::tableName() . "." . self::getQuoteColumnName("orgnization_id") . " = " . FwOrgnization::tableName() . "." . self::getQuoteColumnName("kid"))
            ->innerJoin(FwTreeNode::realTableName(),
                FwOrgnization::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid"))
//            ->innerJoinWith('orgnization.treeNode')
//            ->andFilterWhere(['=', FwOrgnization::realTableName() . '.status', self::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', FwUser::realTableName() . '.manager_flag', self::MANAGER_FLAG_YES]);

//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    ['in', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeIdList],
//                    BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null'
//                ];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            } else {
//                $condition = ['in', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeIdList];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//        } else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("orgnization_id") . ' is null');
//            } else {
//                $query->andFilterWhere(['=', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeIdList]);
//            }
//        }

        $treeNodeModel = FwTreeNode::findOne($treeNodeId);
//        $nodeIdPath = $treeNodeModel->node_id_path . $treeNodeId . "/%";
//
//        $condition = ['or',
//            BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
//            ['=', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeId]];

        $nodeIdPathColumn = FwTreeNode::tableName() . '.' . BaseActiveRecord::getQuoteColumnName("node_id_path");
        if (BaseActiveRecord::getDatabaseType() == BaseActiveRecord::DATABASE_TYPE_ORACLE) {
            $nodeIdPathColumn = "to_char(" . $nodeIdPathColumn . ")";
        }

        //领导应该是存在当前部门、上级部门、或平级部门间
        $condition = ['or',
            ['=', $nodeIdPathColumn, $treeNodeModel->node_id_path],
            ['=', FwTreeNode::realTableName() . '.parent_node_id', $treeNodeId],
            ['=', FwOrgnization::realTableName() . '.tree_node_id', $treeNodeId]];

        $query->andFilterWhere($condition);


        $query->andFilterWhere(['<>', FwUser::realTableName() . '.kid', $userId]);

        //需要过滤掉自己的汇报下属，避免循环汇报的情况
        $userManageQuery = FwUserManager::find(false);
        $userManageQuery->select('user_id')
            ->andFilterWhere(['=', 'manager_id', $userId])
            ->andFilterWhere(['=', 'status', FwUserManager::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'reporting_model', $reportingModel])
            ->distinct();

        $userManageQuerySql = $userManageQuery->createCommand()->rawSql;

        $query->andWhere(FwUser::tableName() . '.' . BaseActiveRecord::getQuoteColumnName("kid") . ' not in (' . $userManageQuerySql . ')');
//        $query->andFilterWhere(['<>', FwUser::tableName() . '.kid', $reporting_manager_id]);


//        $query->addOrderBy([FwTreeNode::tableName() . '.tree_level' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.parent_node_id' => SORT_ASC]);
//        $query->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwUser::realTableName() . '.real_name' => SORT_ASC]);

        $result = $query->all();

        if (!empty($result) && count($result) > 0) {
            foreach ($result as $single) {
                $single->user_display_name = $single->getDisplayName();
            }
        }

        return $result;
    }


    /**
     * 改变用户列表相关状态
     * @param $userId
     */
    public function changeStatusByUserIdList($userIds, $status)
    {
        if (!empty($userIds)) {
            $sourceMode = new FwUser();


            if ($status == FwUser::STATUS_FLAG_STOP) {
                $attributes = [
                    'status' => $status,
                    'frozen_reason' => FwUser::FROZEN_REASON_ADMIN_STOP,
                ];
            } else {
                $attributes = [
                    'status' => $status,
                    'account_active_token' => null
                ];
            }

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $userIds . ')';

            $sourceMode->updateAll($attributes, $condition);
        }
    }

    /**
     * 改变用户列表相关状态
     * @param $userId
     */
    public function resetPasswordByUserIdList($userIds, $password, $isForceChangePassword)
    {
        if (!empty($userIds)) {
            $sourceMode = new FwUser();

            $passwordHash = Yii::$app->security->generatePasswordHash($password);
            $currentTime = time();

            $attributes = [
                'password_hash' => $passwordHash,
                'frozen_reason' => "",
                'need_pwd_change' => $isForceChangePassword,
                'failed_login_times' => 0,
                'find_pwd_tmp_key' => "",
                'last_pwd_change_at' => $currentTime,
                'last_pwd_change_reason' => FwUser::PASSWORD_CHANGE_REASON_RESET,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $userIds . ')';

            $sourceMode->updateAll($attributes, $condition);
        }
    }

    /**
     * 批量更新直线经理ID
     * @param $userIds
     * @param $reportingManagerId
     */
    public function updateReportingManagerIdByUserIdList($userIds, $reportingManagerId)
    {
        if (!empty($userIds)) {
            $sourceMode = new FwUser();

            $attributes = [
                'reporting_manager_id' => $reportingManagerId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $userIds . ')';

            $sourceMode->updateAll($attributes, $condition);
        }
    }

    /**
     * 清除直线经理ID
     * @param $reportingManagerId
     */
    public function clearReportingManagerIdByManagerId($reportingManagerId)
    {
        if (!empty($reportingManagerId)) {
            $oldMode = new FwUser();
            $oldList = $oldMode->find(false)
                ->andFilterWhere(['=', 'reporting_manager_id', $reportingManagerId])
                ->all();
            if (!empty($oldList) && count($oldList) > 0) {
                foreach ($oldList as $single) {
                    $key = $single->kid;
                    FwUser::removeFromCacheByKid($key);
                }
            }

            $sourceMode = new FwUser();

            $attributes = [
                'reporting_manager_id' => null,
            ];

            $condition = $condition = BaseActiveRecord::getQuoteColumnName("reporting_manager_id") . ' = :reporting_manager_id';

            $params = [
                ':reporting_manager_id' => $reportingManagerId,
            ];

            $sourceMode->updateAll($attributes, $condition, $params);
        }
    }

    /**
     * 移动用户列表
     * @param $userId
     */
    public function moveDataByKidList($kids, $companyId, $orgnizationId, $domainId)
    {
        if (!empty($kids)) {
            $sourceMode = new FwUser();

            $attributes = [
                'company_id' => $companyId,
                'orgnization_id' => $orgnizationId,
                'domain_id' => $domainId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';

            $sourceMode->updateAll($attributes, $condition);
        }
    }

    public function saveImport($data, $file, $fileMd5)
    {
        if (!file_exists($file)) {
            return false;
        }

        $reader = \PHPExcel_IOFactory::createReader('Excel5');

        $objPHPExcel = $reader->load($file);

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->setCellValue('I1', 'result');

        $saveList = [];
        $deleteList = [];

        $dictService = new DictionaryService();

        $defaultPassword = $dictService->getDictionaryValueByCode('system', 'default_password');
        $passwordHash = Yii::$app->security->generatePasswordHash($defaultPassword);

        $forceChangePassword = $dictService->getDictionaryValueByCode('system', 'is_force_change_password');

        $dataFrom = 'UserImport_' . $fileMd5;

        $keyList = [];

        $userNameDict=[];
        $emailDict=[];

        foreach ($data as $index => $item) {
            if ($item['op'] === 'A') {
                if ($this->isExistSameUserName(null, $item['user_name']) || in_array($item['user_name'], $userNameDict)) {
                    $sheet->setCellValue('I' . ($index + 1), 'username already exists');
                    continue;
                }
                if ($this->isExistSameEmail(null, $item['email']) || in_array($item['email'], $emailDict)) {
                    $sheet->setCellValue('I' . ($index + 1), 'email already exists');
                    continue;
                }
                if (empty($item['company_id'])) {
                    $sheet->setCellValue('I' . ($index + 1), 'company code is incorrect');
                    continue;
                }
                if (empty($item['orgnization_id'])) {
                    $sheet->setCellValue('I' . ($index + 1), 'organization code is incorrect');
                    continue;
                }
                if (empty($item['position_id'])) {
                    $sheet->setCellValue('I' . ($index + 1), 'position code is incorrect');
                    continue;
                }
                if (empty($item['domain_id'])) {
                    $sheet->setCellValue('I' . ($index + 1), 'domain code is incorrect');
                    continue;
                }

                $userNameDict[] = $item['user_name'];
                $emailDict[] = $item['email'];

                $model = new FwUser();
                $model->user_name = $item['user_name'];
                $model->real_name = $item['real_name'];
                $model->password_hash = $passwordHash;
                $model->need_pwd_change = $forceChangePassword;
                $model->email = $item['email'];
                $model->gender = FwUser::GENDER_PRIVACY;
                $model->theme = $item['theme'];
                $model->language = $item['language'];
                $model->email = $item['email'];
                $model->description = $item['manager_account'];
                $model->company_id = $item['company_id'];
                $model->orgnization_id = $item['orgnization_id'];
                $model->domain_id = $item['domain_id'];
                $model->manager_flag = $item['is_manager'];
                $model->login_number = 0;
                $model->status = FwUser::STATUS_FLAG_NORMAL;
                $model->data_from = $dataFrom;
                $model->user_type = FwUser::USER_TYPE_USER;

                $sheet->setCellValue('I' . ($index + 1), 'success');
                $saveList[] = $model;
                if ('00000000-0000-0000-0000-000000000005' !== $item['position_id']) {
                    $keyList[$item['user_name']] = $item['position_id'];
                }
            } elseif ($item['op'] === 'D') {
                $model = FwUser::findOne(['user_name' => $item['user_name']]);
                if ($model) {
                    $sheet->setCellValue('I' . ($index + 1), 'success');
                    $deleteList[] = $model;
                } else {
                    $sheet->setCellValue('I' . ($index + 1), 'user not found');
                }
            }
        }

        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save($file);

        $errMsg = '';
        if (count($saveList) > 0) {
            $ret = BaseActiveRecord::batchInsertSqlArray($saveList, $errMsg);

            if ($ret) {
                $query = FwUser::find(false);
                $query->andFilterWhere(['=', 'data_from', $dataFrom])
                    ->select('kid,user_name');

                $kids = $query->asArray()->all();

                $positionSaveList = [];
                foreach ($kids as $kid) {
                    if (array_key_exists($kid['user_name'], $keyList)) {
                        $model = new FwUserPosition();
                        $model->user_id = $kid['kid'];
                        $model->position_id = $keyList[$kid['user_name']];
                        $model->is_master = FwUserPosition::YES;
                        $model->status = FwUserPosition::STATUS_FLAG_NORMAL;
                        $model->start_at = time();
                        $positionSaveList[] = $model;
                    }
                }
                if (count($positionSaveList) > 0) {
                    $ret = BaseActiveRecord::batchInsertSqlArray($positionSaveList, $errMsg);
                }
            } else {
                return $errMsg;
            }

            if ($ret) {
                $reportingModel = Yii::$app->user->identity->fwCompany->reporting_model;

                $transaction = Yii::$app->getDb()->beginTransaction();
                try {
                    $command = Yii::$app->getDb()->createCommand("CALL generate_user_import_data(:data_from,:reporting_model)")
                        ->bindValues([':data_from' => $dataFrom, ':reporting_model' => $reportingModel]);
                    $command->execute();

                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                    return $e->getMessage();
                }
            } else {
                return $errMsg;
            }
        }

        if (count($deleteList) > 0) {
            $ret = BaseActiveRecord::batchDeleteNormalMode($deleteList, $errMsg);
            if (!$ret) {
                return $errMsg;
            }
        }

        return true;
    }
}