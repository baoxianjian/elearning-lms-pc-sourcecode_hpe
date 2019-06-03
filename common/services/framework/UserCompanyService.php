<?php


namespace common\services\framework;


use common\interfaces\MutliTreeNodeInterface;
use common\models\framework\FwCompany;
use common\models\framework\FwRole;
use common\models\framework\FwUser;
use common\models\framework\FwUserRole;
use common\models\treemanager\FwCntManageRef;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;

class UserCompanyService extends FwCntManageRef implements MutliTreeNodeInterface {


    /**
     * 获取当前节点选中状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getSelectedStatus($kid, $nodeId)
    {
        if ($nodeId == '-1')
            return false;
        else {
            if ($kid != null) {

                $companyService = new CompanyService();
                $companyId = $companyService->getCompanyIdByTreeNodeId($nodeId);

                if ($companyId != null) {
                    $cntManageModel = new FwCntManageRef();
                    $cntManageModel->subject_id = $kid;
                    $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_USER;
                    $cntManageModel->content_id = $companyId;
                    $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_COMPANY;
                    $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_MANGER;

                    $cntManageRefService = new CntManageRefService();

                    if ($cntManageRefService->isRelationshipExist($cntManageModel)) {
                        return true;
                    } else {
                        $userCompanyId = FwUser::findOne($kid)->company_id;
                        if ($userCompanyId == $companyId)
                        {
                            //默认选中用户所属企业
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                }
                else{
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * 获取当前节点可用状态
     * @param $kid
     * @param $nodeId
     * @return boolean
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
     * 获取当前节点显示状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getDisplayedStatus($kid, $nodeId)
    {
        return true;
    }

    /**
     * 设置树节点上ID值的模式
     * 对于混合类型的树（即包括2种以上类型节点，则有可能出现ID一致无法判断的情况，所以需要增加树类型，以便区分）
     * 值格式为“树类型_ID”
     * @return boolean
     */
    public function isTreeNodeIdIncludeTreeType($kid, $nodeId)
    {
        return false;
    }

    public function getTreeNodeIdListByCompanyId($companyIds) {
        if (!empty($companyIds)) {
            $result = FwCompany::find(false)
                ->andFilterWhere(['in', 'kid', $companyIds])
                ->all();

            return $result;
        }
        else {
            return null;
        }
    }

    /**
     * 判断用户是否对这个企业有管理权限
     * @param $userId
     * @param $companyId
     * @return bool
     */
    public function isUserManagedCompany($userId, $companyId)
    {
        $rbacService = new RbacService();
//        $sysRoleId = $rbacService->getRoleId("Sys-Admin");
//        $roleList = $rbacService->getRoleListIncludeSpecialByUserId($userId);
        $isSpecialUser = $rbacService->isSpecialUser($userId);

        if ($isSpecialUser) {
            return true;//超级管理员对所有企业有管理权限
        }
        else {
            $companyModel = FwCompany::findOne($companyId);
            if (!empty($companyModel) && $companyModel->status == FwCompany::STATUS_FLAG_NORMAL) {
                $userModel = FwUser::findOne($userId);
                //只对可管理的企业 和 自己所在企业，有管理权限

                //自己所在企业
                if ($userModel != null && $userModel->company_id != null && $userModel->company_id == $companyId) {
                    return true;
                } else {
                    //可管理的域
                    $cntManageModel = new FwCntManageRef();
                    $cntManageModel->subject_id = $userId;
                    $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_USER;
                    $cntManageModel->content_id = $companyId;;
                    $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_COMPANY;
                    $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_MANGER;

                    $cntManageRefService = new CntManageRefService();

                    $companyList = $cntManageRefService->getContentList($cntManageModel);

                    if (count($companyList) > 0) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
            else {
                return false;
            }
        }
    }


    /**
     * 判断用户是否对这个企业有查询权限
     * @param $userId
     * @param $companyId
     * @return bool
     */
    public function isUserSearchedCompany($userId, $companyId)
    {
        return $this->isUserManagedCompany($userId, $companyId);
    }

    /**
     * 获取可查询列表文字信息
     * @param $userId
     * @return string
     */
    public function getSearchedListStringByUserId($userId)
    {
        $list = $this->getSearchListByUserId($userId);

        $result = "";
        if ($list != null) {

            foreach ($list as $model )
            {
                $name = $model->company_name;
                $result = $result . $name . ",";
            }

            if ($result != "")
            {
                $result = rtrim($result,",");
            }
        }

        return $result;
    }


    /**
     * 获取可管理列表文字信息
     * @param $userId
     * @return string
     */
    public function getManagedListStringByUserId($userId)
    {
        $list = $this->getManagedListByUserId($userId);

        $result = "";
        if ($list != null) {

            foreach ($list as $model )
            {
                $name = $model->company_name;
                $result = $result . $name . ",";
            }

            if ($result != "")
            {
                $result = rtrim($result,",");
            }
        }

        return $result;
    }

    /**
     * 获取用户可管理的企业清单（如有Session优先用）
     * @param $userId
     * @param null $status
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getManagedListByUserId($userId,$status = self::STATUS_FLAG_NORMAL,$withSession = true,
                                           $needReturnAll = true, &$isAll = false, $parentNodeId = null,$includeSubNode = "0",$nodeIdPath = null)
    {
        if (!empty($userId)) {
            $sessionKey = "ManagedCompanyList_" . $userId;
            if ($status == null) {
                $withSession = false;
            }

            if ($withSession && Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);

//            if ($selected_keys_string != null && $selected_keys_string != "")
//                $selected_keys = explode(',', $selected_keys_string);
//            else
//                $selected_keys = null;
            } else {

                $rbacService = new RbacService();

                $isSpecialUser = $rbacService->isSpecialUser($userId);


                if ($isSpecialUser) {
                    $selected_keys = null;
                    $isAll = true;
                } else {
                    $selected_keys = $this->getUserManagedCompanyList($userId, $withSession);
                }

//            if ($selected_keys != null)
//                $selected_keys_string = implode(',', $selected_keys);//将数组拼接成字符串
//            else
//                $selected_keys_string = "";
//
//            if ($withSession) {
//                Yii::$app->session->set("ManagedCompanyList", $selected_keys_string);
//            }

                if (!$needReturnAll && $isAll) {
                    return null;//对于超级管理员,不需要进行查询
                }
                else {
                    $query = FwCompany::find(false)
                        ->innerJoin(FwTreeNode::realTableName(),
                            FwCompany::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid") )
                        ->andFilterWhere(['=', FwCompany::realTableName() . '.status', $status]);

                    if ($selected_keys != null)
                        $query->andFilterWhere(['in', FwCompany::realTableName() . '.kid', $selected_keys]);

                    if (!$needReturnAll) {
                        if ($includeSubNode == "1") {
                            $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
                        }
                        else {
                            if ($parentNodeId != null)
                                $query->andFilterWhere(['=', FwTreeNode::realTableName() . '.parent_node_id', $parentNodeId]);
                            else {
                                $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
                            }
                        }
                    }


                    $query
                        ->addOrderBy(['tree_level' => SORT_ASC])
                        ->addOrderBy(['parent_node_id' => SORT_ASC])
                        ->addOrderBy(['display_number' => SORT_ASC])
                        ->addOrderBy(['sequence_number' => SORT_ASC]);
//        $query->addOrderBy(['parent_company_id' => SORT_ASC]);
//        $query->addOrderBy(['created_at' => SORT_ASC]);

                    $result = $query->all();

                    if ($withSession) {
                        Yii::$app->session->set($sessionKey, $result);
                    }

                    return $result;
                }
            }
        } else {
            return null;
        }
    }


    /**
     * 获取用户可查询的企业清单（如有Session优先用）
     * @param $userId
     * @param null $status
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSearchListByUserId($userId,$status = self::STATUS_FLAG_NORMAL,$withSession = true,$needReturnAll = true, &$isAll = false, $parentNodeId = null)
    {
        return $this->getManagedListByUserId($userId,$status,$withSession,$needReturnAll,$isAll,$parentNodeId);
    }

    /**
     * 获取用户管理的公司列表
     * @param $userId
     * @return array
     */
    public function getUserManagedCompanyList($userId,$withSession = true)
    {
        $cntManageModel = new FwCntManageRef();
        $cntManageModel->subject_id = $userId;
        $cntManageModel->subject_type = FwCntManageRef::SUBJECT_TYPE_USER;
        $cntManageModel->content_type = FwCntManageRef::CONTENT_TYPE_COMPANY;
        $cntManageModel->reference_type = FwCntManageRef::REFERENCE_TYPE_MANGER;

        $cntManageRefService = new CntManageRefService();

        $companyList = $cntManageRefService->getContentList($cntManageModel);

        $userModel = FwUser::findOne($userId);

        $companyId = $userModel->company_id;

        if ($companyId != null)
        {
            $companyModel = FwCompany::findOne($companyId);
            if ($companyModel != null) {
                if (!array_key_exists($companyId,$companyList)) {
                    array_push($companyList, $companyId);//当前企业
                }
            }
        }
        if (!empty($companyList)) {
            $companyList = array_unique($companyList);
        }
        return $companyList;
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
        }
    }

    /**
     * 获取同一企业下的学习管理员
     * @param $companyId
     * @param bool|true $withSession
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getCompanyAllLearningAdminList($companyId, $withSession = true){
        $sessionKey = "CompanyAllLearningAdminList_" . $companyId;

        if ($withSession && Yii::$app->session->has($sessionKey)) {
            return Yii::$app->session->get($sessionKey);
        } else {
            $learningAdminRole = FwRole::findOne(['role_code' => 'Learning-Admin']);
            $model = FwUser::find(false);
            $currentTime = time();
            $model->andFilterWhere(['=', FwUser::tableName().'.company_id', $companyId])
                ->andFilterWhere(['=', FwUser::tableName().'.status', FwUser::STATUS_FLAG_NORMAL])
                ->leftJoin(FwUserRole::tableName(), FwUserRole::tableName().'.user_id='.FwUser::tableName().'.kid')
                ->andFilterWhere(['=', FwUserRole::tableName().'.role_id', $learningAdminRole->kid])
                ->andFilterWhere(['=', FwUserRole::tableName().'.status', FwUserRole::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['>', FwUserRole::tableName().'.start_at', $currentTime])
                ->andWhere(['or', ['<=', FwUserRole::tableName().'.end_at', $currentTime], FwUserRole::tableName().'.end_at is null']);
            $result = $model->select(FwUser::tableName().".kid")->all();
            if ($withSession) {
                Yii::$app->session->set($sessionKey, $result);
            }
            return $result;
        }
    }
}