<?php


namespace backend\services;


use common\interfaces\MutliTreeNodeInterface;
use common\models\framework\FwCompany;
use common\models\framework\FwPermission;
use common\models\framework\FwRole;
use common\models\framework\FwRolePermission;
use common\models\framework\FwUser;
use common\models\framework\FwUserRole;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class RoleService extends FwRole implements MutliTreeNodeInterface
{

    /**
     * 搜索角色数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $parentNodeId, $includeSubNode)
    {
        $query = FwRole::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith('fwCompany.fwTreeNode')
//            ->innerJoinWith('orgnization.treeNode')
            ->andFilterWhere(['like', 'role_code', trim(urldecode($this->role_code))])
            ->andFilterWhere(['like', 'role_name', trim(urldecode($this->role_name))])
            ->andFilterWhere(['<>', 'limitation', FwRole::LIMITATION_HIDDEN])
            ->andFilterWhere(['=', FwRole::realTableName() . '.status', $this->status]);

        if ($includeSubNode == '1') {
            if ($parentNodeId != '') {
                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";

                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'",
                    ['=', FwCompany::realTableName() . '.tree_node_id', $parentNodeId]];
            } else {
                $condition = ['or',
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '/%'",
                    BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];
            }

//            $treeNodeService = new TreeNodeService();
//            $treeTypeId = $treeNodeService->getTreeTypeId('company');
//            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

            $query->andFilterWhere($condition);
//            $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
        } else {
            if ($parentNodeId == '') {
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
            } else {
                $query->andFilterWhere(['=', FwCompany::realTableName() . '.tree_node_id', $parentNodeId]);
            }
        }

//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    [ 'in',FwCompany::realTableName() . '.tree_node_id',$treeNodeIdList],
//                    BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//            else
//            {
//                $condition = [ 'in',FwCompany::realTableName() . '.tree_node_id',$treeNodeIdList];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//        }
//        else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
//            } else {
//                $query->andFilterWhere(['=', FwCompany::realTableName() . '.tree_node_id', $treeNodeIdList]);
//            }
//        }

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $userCompanyService = new UserCompanyService();

//                if ($isManage) {
                $selectedResult = $userCompanyService->getManagedListByUserId($userId, null, false);
//                } else {
//                    $selectedResult = $userCompanyService->getSearchListByUserId($userId);
//                }

                if (isset($selectedResult) && $selectedResult != null) {
                    $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                    $companyIdList = array_keys($selectedList);
                } else {
                    $companyIdList = null;
                }

                $condition = ['or',
                    ['in', 'company_id', $companyIdList],
                    BaseActiveRecord::getQuoteColumnName("company_id") . ' is null'];
                $query->andFilterWhere($condition);

//                $query->andFilterWhere(['in', FwRole::tableName() . '.company_id', $companyIdList]);
            }

        }
//            ->andFilterWhere(['like', 'limitation', $this->limitation])
//            ->andFilterWhere(['like', 'code_gen_way', $this->code_gen_way])
//            ->andFilterWhere(['like', 'code_prefix', $this->code_prefix]);
//        $sort->attributes=['LPT_NAME'=> [
//            'asc' => ['LPT_NAME' => SORT_ASC],
//            'desc' => ['LPT_NAME' => SORT_DESC]]];
        $dataProvider->setSort(false);

        $query->addOrderBy([FwTreeNode::realTableName() . '.tree_level' => SORT_ASC]);
        $query->addOrderBy([FwTreeNode::realTableName() . '.parent_node_id' => SORT_ASC]);
        $query->addOrderBy([FwTreeNode::realTableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwRole::realTableName() . '.created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 判断是否存在相同角色代码的数据
     * @param $kid
     * @param $roleCode
     * @return bool
     */
    public function isExistSameRoleCode($kid, $companyId, $roleCode)
    {
        $model = new FwRole();
        $query = $model->find(false);

        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'role_code', $roleCode]);

        if ($companyId == null || $companyId == "")
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
        else
            $query->andFilterWhere(['=', 'company_id', $companyId]);

        $count = $query->count();

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 获取当前企业可用角色（含共享的角色）
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAvailableRoleByCompanyId($companyId)
    {
        $query = FwRole::find(false);

        $query->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['<>', 'limitation', self::LIMITATION_HIDDEN]);

        $condition = ['or',
            ['=', 'company_id', $companyId],
            ['=', 'share_flag', self::SHARE_FLAG_SHARE],
        ];

        $query->andFilterWhere($condition);

        $query->addOrderBy(['company_id' => SORT_ASC]);
        $query->addOrderBy(['created_at' => SORT_DESC]);

        $result = $query->all();

        if (!empty($result) && count($result) > 0) {
            foreach ($result as $single) {
                $single->role_display_name = $single->role_name . "(" . $single->role_code . ")";
            }
        }
        return $result;
    }


    /**
     * 根据角色ID获取该角色的所有用户信息
     * @param $roleId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUserListByRoleId($roleId)
    {
        $model = FwUser::find(false);
        $model->joinWith('userRoles')
            ->andFilterWhere(['=', FwUserRole::realTableName() . '.role_id', $roleId])
            ->andFilterWhere(['=', FwUserRole::realTableName() . '.status', self::STATUS_FLAG_NORMAL]);

        $result = $model->all();

        return $result;
    }

    /**
     * 获取权限树的选中状态
     * @param $kid
     * @param $nodeId
     * @return bool
     */
    public function getSelectedStatus($kid, $nodeId)
    {
        if ($nodeId == '-1')
            return false;
        else {
            if ($kid != null) {
                $permissionService = new PermissionService();

                $permissionId = $permissionService->getPermissionIdByTreeNodeId($nodeId);

                if ($permissionId != null) {
                    $rolePermissionService = new RolePermissionService();

                    $rolePermissionModel = new FwRolePermission();
                    $rolePermissionModel->role_id = $kid;
                    $rolePermissionModel->permission_id = $permissionId;

//                    //判断子权限是否有，如果有子权限了就以子权限为准
//                    if ($rolePermissionService->isSubRelationshipExist($kid, $permissionId))
//                        return false;

                    if ($rolePermissionService->isRelationshipExist($rolePermissionModel))
                        return true;
                    else
                        return false;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * 获取权限树的停用状态
     * @param $kid
     * @param $nodeId
     * @return bool
     */
    public function getDisabledStatus($kid, $nodeId)
    {
        if ($nodeId == '-1')
            return true;
        else {
            return false;
        }
    }


    /**
     * @return bool
     */
    public function isTreeNodeIdIncludeTreeType($kid, $nodeId)
    {
        return false;
    }

    /**
     * 获取当前节点显示状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getDisplayedStatus($kid, $nodeId)
    {
        if ($nodeId == '-1')
            return true;
        else {
            $permissionService = new PermissionService();
            $permissionId = $permissionService->getPermissionIdByTreeNodeId($nodeId);

            if ($permissionId != null && $permissionService->findOne($permissionId)->limitation == FwPermission::LIMITATION_HIDDEN) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 获取角色权限列表
     * @param $roleId
     * @param bool $withCache
     * @return array|mixed|null|\yii\db\ActiveRecord[]
     */
    public function getPermissionListByRoleId($roleId, $withCache = true)
    {
        $cacheKey = "PermissionList_RoleId_" . $roleId;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = FwRolePermission::find(false)
                ->andFilterWhere(['=', 'role_id', $roleId])
                ->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL])
                ->select('permission_id');

            $result = $model->all();

            if ($withCache) {
                self::saveToCache($cacheKey, $result);
            }
        }

        return $result;
    }

    /**
     * 获取当前节点打开状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getOpenedStatus($kid, $nodeId)
    {
        if ($nodeId == '-1')
            return true;
        else {
            return false;
//            $permissionService = new PermissionService();
//
//            $permissionId = $permissionService->getPermissionIdByTreeNodeId($nodeId);
//            $permissionList = $this->getPermissionListByRoleId($kid);
//            $check = false;
//            if (!empty($permissionList) && count($permissionList) > 0) {
//                foreach ($permissionList as $permisionModel) {
//                    if (!$check) {
//                        $check = $this->isParentPermission($permissionId, $permisionModel->permission_id);
//                    }
//                }
//            }
//            return $check;
        }
    }

    /**
     * 当前权限是否为父权限
     * @param string $orgnizationId 父权限ID
     * @param string $currentPermissionId 当前权限ID
     */
    private function isParentPermission($permissionId, $currentPermissionId)
    {
        $parentPermissionId = FwPermission::findOne($currentPermissionId)->parent_permission_id;
        if (empty($parentPermissionId)) {
            return false;
        } else {
            if ($permissionId == $parentPermissionId) {
                return true;
            } else {
                return $this->isParentPermission($permissionId, $parentPermissionId);
            }
        }

    }

    /**
     * 改变列表相关状态
     * @param $kids
     */
    public function changeStatusByKidList($kids, $status)
    {
        if (!empty($kids)) {
            $sourceMode = new FwRole();

            $attributes = [
                'status' => $status,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }

    /**
     * 移动列表相关数据
     * @param $userId
     */
    public function moveDataByKidList($kids, $companyId)
    {
        if (!empty($kids)) {
            $sourceMode = new FwRole();

            $attributes = [
                'company_id' => $companyId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }
}