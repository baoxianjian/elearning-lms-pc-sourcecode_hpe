<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/26/2015
 * Time: 9:45 PM
 */

namespace common\services\framework;

use common\models\framework\FwCompany;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPrimaryKey;
use common\models\treemanager\FwTreeType;
use common\services\learning\CourseCategoryService;
use common\services\learning\CoursewareCategoryService;
use common\services\framework\RbacService;
use common\services\framework\UserOrgnizationService;
use common\services\learning\ExaminationCategoryService;
use common\services\learning\ExaminationQuestionCategoryService;
use common\services\learning\ExamPaperManageService;
use common\services\social\AudienceCategoryService;
use common\base\BaseActiveRecord;
use Yii;
use common\models\treemanager\FwTreeNode;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


class TreeNodeService extends FwTreeNode{

    const PADDING_COUNT = 3;

    /**
     * 搜索树数据
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$treeType,$parentNodeId,$includeSubNode)
    {
        $query = FwTreeNode::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

//        if (!$this->validate()) {
//            // uncomment the following line if you do not want to any records when validation fails
//            // $query->where('0=1');
//            return $dataProvider;
//        }

        $treeTypeId = $this->getTreeTypeId($treeType);

//        $tree_type_id = $params["treeTypeId"];
//        $parent_node_id = $params["parentNodeId"];

//        if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
//            $query->andWhere('parent_node_id is null');
//        else
//            $query->andWhere('parent_node_id = :parent_node_id', [':parent_node_id' => $parentNodeId]);

        $query->andFilterWhere(['=', 'tree_type_id', $treeTypeId])
            ->andFilterWhere(['like', 'tree_node_code', trim(urldecode($this->tree_node_code))])
            ->andFilterWhere(['like', 'tree_node_name', trim(urldecode($this->tree_node_name))])
            ->andFilterWhere(['=', 'status', $this->status]);


        if ($includeSubNode == '1') {
            if ($parentNodeId != '') {
                $treeNodeModel = FwTreeNode::findOne($parentNodeId);
                $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";
            } else {
                $nodeIdPath = "/%";
            }

            $query->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
        }
        else
        {
            if ($parentNodeId == '') {
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
            } else {
                $query->andFilterWhere(['=', FwTreeNode::realTableName() . '.parent_node_id', $parentNodeId]);
            }
        }


//        if (is_array($treeNodeIdList)) {
//            if (in_array('', $treeNodeIdList)) {
////                $condition = ;
//
//                $condition = ['or',
//                    [ 'in',FwTreeNode::realTableName() . '.kid',$treeNodeIdList],
//                    BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null'
//                ];
//                //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
//                $query->andFilterWhere($condition);
//            }
//            else
//            {
//                if (count($treeNodeIdList) > 0)
//                {
//                    $query->andFilterWhere(['in', FwTreeNode::realTableName() . '.kid', $treeNodeIdList]);
//                }
//                else {
//                    if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
//                        $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
//                    else
//                        $query->andWhere(['=','parent_node_id',$parentNodeId]);
//                }
//            }
//        }
//        else {
//            if ($treeNodeIdList == '') {
//                $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
//            } else {
//                $query->andFilterWhere(['=', FwTreeNode::realTableName() . '.parent_node_id', $treeNodeIdList]);
//            }
//        }

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();
            $companyId = Yii::$app->user->identity->company_id;
//            $needReturnAll = false;

//            $nodeIdPath = null;
//            if ($includeSubNode == '1') {
//                if (!empty($parentNodeId)) {
//                    $treeNodeModel = FwTreeNode::findOne($parentNodeId);
//                    $nodeIdPath = $treeNodeModel->node_id_path . $parentNodeId . "/%";
//                } else {
//                    $nodeIdPath = "/%";
//                }
//            }

            $rbacService = new RbacService();
            $isSysManager = $rbacService->isSysManager($userId);
            $isSpecialUser = $rbacService->isSpecialUser($userId);

//            $isAll = false;
            $selectedResult = null;
            if ($treeType == 'company') {
                $userCompanyService = new UserCompanyService();
                $userCompanys = $userCompanyService->getUserManagedCompanyList($userId,false);
                if (!empty($userCompanys)) {
                    $selectedResult = $userCompanyService->getTreeNodeIdListByCompanyId($userCompanys);
                }
                if ($isSysManager) {
                    $rawSql = FwCompany::find(false)
                        ->andFilterWhere(['=','kid',$companyId])
                        ->select(BaseActiveRecord::getQuoteColumnName("tree_node_id"))
                        ->createCommand()
                        ->rawSql;
                    $query->andWhere(BaseActiveRecord::getQuoteColumnName("kid") .  ' in (' . $rawSql .')');
                }
//                $selectedResult = $userCompanyService->getManagedListByUserId($userId,null,false,$needReturnAll,$isAll,$parentNodeId,$includeSubNode,$nodeIdPath);
            } else if ($treeType == 'domain') {
                $userDomainService = new UserDomainService();
                $userDomains = $userDomainService->getUserManagedDomainList($userId,false);
                if (!empty($userDomains)) {
                    $selectedResult = $userDomainService->getTreeNodeIdListByDomainId($userDomains);
                }
                else {
                    $isDomainManager = $rbacService->isDomainManager($userId);
                    if ($isSysManager || $isDomainManager) {
                        $rawSql = FwDomain::find(false)
                            ->andFilterWhere(['=','company_id',$companyId])
                            ->select(BaseActiveRecord::getQuoteColumnName("tree_node_id"))
                            ->createCommand()
                            ->rawSql;
                        $query->andWhere(BaseActiveRecord::getQuoteColumnName("kid") .  ' in (' . $rawSql .')');
                    }
                }
//                $selectedResult = $userDomainService->getManagedListByUserId($userId,null,false,$needReturnAll,$isAll,$parentNodeId,$includeSubNode,$nodeIdPath);
            } else if ($treeType == 'orgnization') {
                //如果是特殊用户，则取所有数据
                if (!$isSpecialUser) {
                    //如果不是特殊用户（超级管理员)，则根据授权范围取，（系统管理员默认是空，所以要特殊过滤成当前企业所有数据）
                    $userOrgnizationService = new UserOrgnizationService();
                    $userOrgnizations = $userOrgnizationService->getUserManagedOrgnizationList($userId, false);
                    if (!empty($userOrgnizations)) {
                        $selectedResult = $userOrgnizationService->getTreeNodeIdListByOrgnizationId($userOrgnizations);
                    }
                    else {
                        if ($isSysManager) {
                            $rawSql = FwOrgnization::find(false)
                                ->andFilterWhere(['=','company_id',$companyId])
                                ->select(BaseActiveRecord::getQuoteColumnName("tree_node_id"))
                                ->createCommand()
                                ->rawSql;
                            $query->andWhere(BaseActiveRecord::getQuoteColumnName("kid") .  ' in (' . $rawSql .')');
                        }
                    }
                }

//                $selectedResult = $userOrgnizationService->getManagedListByUserId($userId,null,false,$needReturnAll,$isAll,$parentNodeId,$includeSubNode,$nodeIdPath);
            } else if ($treeType == 'course-category') {
                $userCompanyService = new UserCompanyService();
                $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId,null,false);

                $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');

                $courseCategoryService = new CourseCategoryService();
                $selectedResult = $courseCategoryService->getCourseCategoryByCompanyIdList($selectedCompanyList);
            } else if ($treeType == 'courseware-category') {
                $userCompanyService = new UserCompanyService();
                $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId,null,false);

                $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');

                $coursewareCategoryService = new CoursewareCategoryService();
                $selectedResult = $coursewareCategoryService->getCoursewareCategoryByCompanyIdList($selectedCompanyList);
            }

            if (isset($selectedResult) && $selectedResult != null) {
                $selectedList = ArrayHelper::map($selectedResult, 'tree_node_id', 'tree_node_id');

                $selectedListIdList = array_keys($selectedList);

                $query->andFilterWhere(['in', 'kid', $selectedListIdList]);
            }
            else {
                if ($treeType == 'domain') {
                    if (!$isSpecialUser && !$isSysManager && !$isDomainManager) {
                        $query->andWhere('kid is null');
                    }
                }
                else {
                    if (!$isSpecialUser && !$isSysManager) {
                        $query->andWhere('kid is null');
                    }
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
        $query
            ->addOrderBy(['tree_level' => SORT_ASC])
            ->addOrderBy(['parent_node_id' => SORT_ASC])
            ->addOrderBy(['display_number' => SORT_ASC])
            ->addOrderBy(['sequence_number' => SORT_ASC]);
//        $query->addOrderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }

    /**
     * 删除相关子节点
     * @param $treeTypeId
     * @param $rootNodeId
     * @param $nodeIdPath
     */
    public function deleteSubNode($treeTypeId,$rootNodeId,$nodeIdPath)
    {
        $searchModel = new FwTreeNode();
        $result = $searchModel->find(false)
            ->andFilterWhere(["=","tree_type_id", $treeTypeId])
            ->andFilterWhere(["=","root_node_id", $rootNodeId])
            ->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath ."'")
            ->all();

        foreach ($result as $treeNode) {
            $key = $treeNode->kid;
            FwTreeNode::removeFromCacheByKid($key);
        }


        $condition = self::getQuoteColumnName("tree_type_id") . " = :treeTypeId " .
            "and " . self::getQuoteColumnName("root_node_id") .  " = :root_node_id " .
            "and " . self::getQuoteColumnName("node_id_path") .  " like :nodeIdPath";

        $param = [
            ':treeTypeId'=>$treeTypeId,
            ':root_node_id'=>$rootNodeId,
            ':nodeIdPath'=>$nodeIdPath
        ];

        $model = new FwTreeNode();

        $model->deleteAll($condition,$param);
    }

    /**
     * 更新相同子节点的状态
     * @param $treeTypeId
     * @param $nodeIdPath
     * @param $status
     */
    public function changeStatusSubNode($treeTypeId,$rootNodeId,$nodeIdPath,$status)
    {
        $model = new FwTreeNode();

        $attributes = [
            'status' => $status,
        ];

        $condition = self::getQuoteColumnName("tree_type_id") . " = :treeTypeId " .
            "and " . self::getQuoteColumnName("root_node_id") .  " = :root_node_id " .
            "and " . self::getQuoteColumnName("node_id_path") .  " like :nodeIdPath";

        $param = [
            ':treeTypeId'=>$treeTypeId,
            ':root_node_id'=>$rootNodeId,
            ':nodeIdPath'=>$nodeIdPath
        ];

        $model->updateAll($attributes,$condition,$param);
    }


    /**
     * 激活父节点
     * @param $kid
     */
    public function activeParentNode($kid)
    {
        $model = FwTreeNode::findOne($kid);

        $parent_node_id = $model->parent_node_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = FwTreeNode::findOne($parent_node_id);

            if ($parentModel->status != FwTreeNode::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = FwTreeNode::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->activeParentNode($parentModel->kid);
            }
        }
    }

    /**
     * 获取最大序列号
     * @param $treeTypeId
     * @param $parentNodeId
     * @return int|mixed
     */
    public function findMaxSequenceNumber($treeTypeId, $parentNodeId)
    {
        $model = new FwTreeNode();

        $query = $model->find(false)
            ->andFilterWhere(['=','tree_type_id',$treeTypeId]);

        if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
        else
            $query->andFilterWhere(['=','parent_node_id',$parentNodeId]);

        $maxSequenceNumber = $query->max(BaseActiveRecord::getQuoteColumnName("sequence_number"));

        if ($maxSequenceNumber != null)
        {
            $maxSequenceNumber = $maxSequenceNumber + 1;
        }
        else
        {
            $maxSequenceNumber = 1;
        }

        return $maxSequenceNumber;
    }


    /**
     * 查找最大的树节点代码
     * @param $treeTypeId
     * @param $parentNodeId
     * @param $codePrefix
     * @return string
     */
    public function findMaxNodeCode($treeTypeId, $parentNodeId, $codePrefix)
    {
        $model = new FwTreeNode();
        $query = $model->find(false)
            ->andFilterWhere(['=','tree_type_id',$treeTypeId]);


        if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
        else
            $query->andFilterWhere(['=','parent_node_id',$parentNodeId]);


        if ($codePrefix == null)
            $codePrefix = "";

        $maxTreeNodeCode = $query->max("substring(" . BaseActiveRecord::getQuoteColumnName("tree_node_code") .
            ",locate('" . $codePrefix . "', ".BaseActiveRecord::getQuoteColumnName("tree_node_code").")+length('" . $codePrefix . "'))");
//        substring(node_code_path,LOCATE($codePrefix, node_code_path)+length($codePrefix))
//        $maxTreeNodeCode = $query->max("REPLACE(tree_node_code,'".$codePrefix."','') ");
//            ->andWhere($condition)
//            ->where('year(CREATE_DATE) = :year',[':year'=>$Year])
//            ->andWhere();


        if ($maxTreeNodeCode != null)
        {
            $maxTreeNodeCode = intval($maxTreeNodeCode) + 1;
        }
        else
        {
            $maxTreeNodeCode = 1;
        }


        $result = self::calculateFinalTreeNodeCode($codePrefix,$maxTreeNodeCode, self::PADDING_COUNT,$treeTypeId);

        return $result;
    }


    /**
     * 计算树节点代码（增加前缀等数据）
     * @param $codePrefix
     * @param $maxTreeNodeCode
     * @param $paddingCount
     * @return string
     */
    private function calculateTreeNodeCode($codePrefix,$maxTreeNodeCode,$paddingCount)
    {
        return $codePrefix . str_pad(strval($maxTreeNodeCode), $paddingCount, "0", STR_PAD_LEFT);
    }

    /**
     * 计算最终的树节点代码（如果存在重复的值，则自动加1）
     * @param $codePrefix
     * @param $maxTreeNodeCode
     * @param $paddingCount
     * @param $treeTypeId
     * @return string
     */
    private function calculateFinalTreeNodeCode($codePrefix,$maxTreeNodeCode,$paddingCount,$treeTypeId)
    {
        $result = $this->calculateTreeNodeCode($codePrefix,$maxTreeNodeCode,$paddingCount);

        if ($this->isExistSameTreeNodeCode(null,$result,$treeTypeId))
        {
            $maxTreeNodeCode = $maxTreeNodeCode + 1;
            return $this->calculateFinalTreeNodeCode($codePrefix,$maxTreeNodeCode,$paddingCount,$treeTypeId);
        }
        else
        {
            return $result;
        }
    }

    /**
     * 是否存在相同的树节点代码
     * @param $kid
     * @param $treeNodeCode
     * @param $treeTypeId
     * @return bool
     */
    public function isExistSameTreeNodeCode($kid, $treeNodeCode, $treeTypeId)
    {
        $model = new FwTreeNode();
        $query = $model->find(false)
            ->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'tree_type_id', $treeTypeId])
            ->andFilterWhere(['=', 'tree_node_code', $treeNodeCode]);


        $count = $query->count(1);


        if ($count > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * 增加树节点
     * @param $treeType
     * @param $treeNodeName
     * @param $parentNodeId
     * @return null|string
     */
    public function addTreeNode($treeType, $treeNodeName, $parentNodeId, $treeNodeCode = null)
    {
        $treeTypeId = $this->getTreeTypeId($treeType);
        $model = new FwTreeNode();
        $model->tree_type_id = $treeTypeId;
        $model->tree_node_name = $treeNodeName;

        $treeTypeModel = FwTreeType::findOne($treeTypeId);
        $parentTreeModel = null;
        $parentCode = null;

        if (!empty($parentNodeId)) {
            $parentTreeModel = FwTreeNode::findOne($parentNodeId);
            $parentCode = $parentTreeModel->tree_node_code;
        }

        if (empty($parentTreeModel)) {
            $parentNodeId = null;
        }

        if ($treeTypeModel->code_gen_way == FwTreeType::CODE_GEN_WAY_SYSTEM) {
            if (!empty($parentCode))
                $parentCode = $treeTypeModel->code_prefix;
            else
                $parentCode = null;

            $model->tree_node_code = $this->findMaxNodeCode($treeTypeId, $parentNodeId, $parentCode);
        }
        else {
            $model->tree_node_code = $treeNodeCode;
        }

        $model->sequence_number = $this->findMaxSequenceNumber($treeTypeId, $parentNodeId);

        $genkey = FwPrimaryKey::generateNextPrimaryID($model->tableName());
        $model->kid = $genkey;
        if (empty($parentTreeModel)) {
            $model->root_node_id = $genkey;
            $model->tree_level = 1;
            $model->node_id_path = "/";
            $model->node_name_path = "/";
            $model->node_code_path = "/";
        } else {
            $model->parent_node_id = $parentNodeId;
            $model->root_node_id = $parentTreeModel->root_node_id;
            $model->tree_level = $parentTreeModel->tree_level + 1;
            $model->node_id_path = $parentTreeModel->node_id_path . $parentTreeModel->kid . "/";
            $model->node_name_path = $parentTreeModel->node_name_path . $parentTreeModel->tree_node_name . "/";
            $model->node_code_path = $parentTreeModel->node_code_path . $parentTreeModel->tree_node_code . "/";
        }
        $model->needReturnKey = true;
        if ($model->save()) {
            return $genkey;
        } else {
            return null;
        }
    }


    /**
     * 更新树节点
     * @param $treeNodeId
     * @param $treeNodeName
     * @param $parentNodeId
     */
    public function updateTreeNode($treeNodeId, $treeNodeName, $parentNodeId, $treeNodeCode = null)
    {
        $model = FwTreeNode::findOne($treeNodeId);

        if (!empty($model)) {
            $oldParentNodeId = $model->parent_node_id;
            $oldTreeNodeName = $model->tree_node_name;
            $oldTreeNodeCode = $model->tree_node_code;
            $treeTypeId = $model->tree_type_id;

            if ($treeNodeName != $oldTreeNodeName || $oldParentNodeId != $parentNodeId || ($treeNodeCode != null && $oldTreeNodeCode != $treeNodeCode)) {
                $model->tree_node_name = $treeNodeName;

                if ($treeNodeCode != null) {
                    $model->tree_node_code = $treeNodeCode;
                }

                if (!empty($parentNodeId)) {
                    $model->parent_node_id = $parentNodeId;
                } else {
                    $model->parent_node_id = null;
                }

                if ($oldParentNodeId != $parentNodeId) {
                    $model->sequence_number = $this->findMaxSequenceNumber($treeTypeId, $parentNodeId);

                    $newModel = null;
                    if (!empty($parentNodeId)) {
                        $newModel = FwTreeNode::findOne($parentNodeId);
                    }

                    if (!empty($newModel)) {
                        $newNodeIdPath = $newModel->node_id_path . $newModel->kid . '/';
                        $newNodeNamePath = $newModel->node_name_path . $newModel->tree_node_name . '/';
                        $newNodeCodePath = $newModel->node_code_path . $newModel->tree_node_code . '/';

                        $model->node_id_path = $newNodeIdPath;
                        $model->node_code_path = $newNodeCodePath;
                        $model->node_name_path = $newNodeNamePath;
                        $model->root_node_id = $newModel->root_node_id;
                        $model->tree_level = $newModel->tree_level + 1;

                    } else {
                        $newNodeIdPath = '/';
                        $newNodeNamePath = '/';
                        $newNodeCodePath = '/';

                        $model->parent_node_id = null;
                        $model->node_id_path = $newNodeIdPath;
                        $model->node_code_path = $newNodeCodePath;
                        $model->node_name_path = $newNodeNamePath;
                        $model->root_node_id = $model->kid;
                        $model->tree_level = 1;
                    }
                }


                $model->save();
            }
        }
    }

    /**
     * 列出树节点数据
     * @return
     */
    public function listTreeData($otherService = null, $otherKid = null,$isManage = true,$withSession = true)
    {
        $isShowAllNodes = self::isShowAllNodes();

        //强制显示所有节点
        if ($isShowAllNodes == "Y")
            return self::listAllTreeNodes($otherService,$otherKid,"all");

        //超级管理员,显示所有节点
//        if (AuthUtil.isSuperAdmin())
//            return listAllTreeNodes();

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

            $treeNodeIdList = null;
            $treeType = Yii::$app->request->getQueryParam("TreeType");
            $includeRoot = true;
            $includeRootStr = Yii::$app->request->getQueryParam("IncludeRoot");
            if (!empty($includeRootStr)) {
                $includeRoot = $includeRootStr == "True"?true:false;
            }

            $mergeRoot = false;
            $mergeRootStr = Yii::$app->request->getQueryParam("MergeRoot");
            if (!empty($mergeRootStr)) {
                $mergeRoot = $mergeRootStr == "True"?true:false;
            }

            $showContentCount = false;
            $showContentCountStr = Yii::$app->request->getQueryParam("ShowContentCount");
            if (!empty($showContentCountStr)) {
                $showContentCount = $showContentCountStr == "True"?true:false;
            }

            $deleteNode = false;
            $deleteNodeStr = Yii::$app->request->getQueryParam("DeleteNode");
            if (!empty($deleteNodeStr)) {
                $deleteNode = $deleteNodeStr == "True"?true:false;
            }

            $editNode = false;
            $editNodeStr = Yii::$app->request->getQueryParam("EditNode");
            if (!empty($editNodeStr)) {
                $editNode = $editNodeStr == "True"?true:false;
            }

            $openAllNode = false;
            $openAllNodeStr = Yii::$app->request->getQueryParam("OpenAllNode");
            if (!empty($openAllNodeStr)) {
                $openAllNode = $openAllNodeStr == "True"?true:false;
            }

            $ListRouteParams = Yii::$app->request->getQueryParam("ListRouteParams");
            if (!isset($ListRouteParams)) {
                $ListRouteParams = false;
            }

            /*复制课程时专用*/
            $companyId = Yii::$app->request->getQueryParam("companyId");
            if (!isset($companyId)) {
                $companyId = false;
            }

            $needReturnAll = false;
            $isAll = false;
            $treeNodeKid = Yii::$app->request->getQueryParam("ID");
            if ($treeNodeKid == '#' || $treeNodeKid == "-1") {
                $parentNodeId = null;
            }
            else {
                $parentNodeId = $treeNodeKid;
            }

            if ($treeType == 'company') {
                $userCompanyService = new UserCompanyService();
                if ($isManage) {
                    $selectedResult = $userCompanyService->getManagedListByUserId($userId,null,$withSession,$needReturnAll,$isAll,$parentNodeId);
                } else {
                    $selectedResult = $userCompanyService->getSearchListByUserId($userId,null,$withSession,$needReturnAll,$isAll,$parentNodeId);
                }
            }
            else if ($treeType == 'permission') {
                $treeNodeIdList = "all";
            }
            else if ($treeType == 'domain') {
                $userDomainService = new UserDomainService();
                if ($isManage) {
                    $selectedResult = $userDomainService->getManagedListByUserId($userId,null,$withSession,null,$needReturnAll,$isAll,$parentNodeId);
                } else {
                    $selectedResult = $userDomainService->getSearchListByUserId($userId,null,$withSession,null,$needReturnAll,$isAll,$parentNodeId);
                }
            } else if ($treeType == 'orgnization') {
                $userOrgnizationService = new UserOrgnizationService();
                if ($isManage) {
                    $selectedResult = $userOrgnizationService->getManagedListByUserId($userId,null,$withSession,$needReturnAll,$isAll,$parentNodeId);
                } else {
                    $selectedResult = $userOrgnizationService->getSearchListByUserId($userId,null,$withSession,$needReturnAll,$isAll,$parentNodeId);
                }
            } else if ($treeType == 'course-category') {
                if (empty($companyId)) {
                    $userCompanyService = new UserCompanyService();
                    $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId, null, $withSession);
                    $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');
                }else{
                    $selectedCompanyList = array($companyId);
                }

                $courseCategoryService = new CourseCategoryService();
                $selectedResult = $courseCategoryService->getCourseCategoryByCompanyIdList($selectedCompanyList);
            } else if ($treeType == 'courseware-category') {
                $userCompanyService = new UserCompanyService();
                $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId,null,$withSession);

                $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');

                $coursewareCategoryService = new CoursewareCategoryService();
                $selectedResult = $coursewareCategoryService->getCoursewareCategoryByCompanyIdList($selectedCompanyList);
            }else if ($treeType == 'examination-paper-category') {
                $userCompanyService = new UserCompanyService();
                $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId,null,$withSession);

                $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');

                $examPaperManageService = new ExamPaperManageService();
                $selectedResult = $examPaperManageService->getExaminationPaperCategoryByCompanyIdList($selectedCompanyList);
            }
            else if ($treeType == 'examination-question-category') {
                $userCompanyService = new UserCompanyService();
                $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId,null,$withSession);

                $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');

                $coursewareCategoryService = new ExaminationQuestionCategoryService();
                $selectedResult = $coursewareCategoryService->GetExaminationQuestionCategoryByCompanyIdList($selectedCompanyList);
            }
            else if ($treeType == 'examination-category') {
                if (empty($companyId)) {
                    $userCompanyService = new UserCompanyService();
                    $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId, null, $withSession);
                    $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');
                }else{
                    $selectedCompanyList = array($companyId);
                }
                $coursewareCategoryService = new ExaminationCategoryService();
                $selectedResult = $coursewareCategoryService->GetExaminationCategoryByCompanyIdList($selectedCompanyList);
            }
            else if ($treeType == 'audience-category') {
                $userCompanyService = new UserCompanyService();
                $selectedCompanyResult = $userCompanyService->getManagedListByUserId($userId,null,$withSession);

                $selectedCompanyList = ArrayHelper::map($selectedCompanyResult, 'kid', 'kid');

                $coursewareCategoryService = new AudienceCategoryService();
                $selectedResult = $coursewareCategoryService->GetAudienceCategoryByCompanyIdList($selectedCompanyList);
            }



            if (isset($selectedResult) && $selectedResult != null && !is_string($selectedResult)) {
                $selectedList = ArrayHelper::map($selectedResult, 'tree_node_id', 'tree_node_id');

                $treeNodeIdList = array_keys($selectedList);
            }

            return self::listManagedTreeNodes($otherService, $otherKid, $treeNodeIdList,$includeRoot,$mergeRoot,
                $showContentCount,$deleteNode,$editNode, $ListRouteParams,$openAllNode,$isAll);
        } else {
            return null;
        }
    }


    /**
     * 获取是否显示所有节点标志
     * @return mixed|null
     */
    private function isShowAllNodes()
    {
        $isShowAllNodes = Yii::$app->request->getQueryParam("ShowAll");

        if ($isShowAllNodes == null)
            return null;
        else
            return $isShowAllNodes;
    }

    /**
     * 根据树类型代码获取类型ID
     * @param $treeType
     * @return int|string
     */
    public function getTreeTypeId($treeType, $withCache = true)
    {
        $result = null;
        if ($treeType != null && $treeType != "") {
            $cacheKey = "TreeType_Code_" . $treeType;

            $result = self::loadFromCache($cacheKey, $withCache, $hasCache);
            if (empty($result) && !$hasCache) {
                $treeTypeModel = new FwTreeType();

                $result = $treeTypeModel->findOne(['tree_type_code' => $treeType]);

                self::saveToCache($cacheKey, $result);
            }
        }

        if ($result != null) {
            $treeTypeId = $result->kid;
        } else {
            $treeTypeId = null;
        }

        return $treeTypeId;
    }
    /**
     * 显示所有树节点
     * @return array
     */
    private function listAllTreeNodes($otherService,$otherKid,$treeNodeIdList = null,$includeRoot=true,$mergeRoot = false,$showContentCount = false,$deleteNode = false, $editNode = false, $ListRouteParams = false,$openAllNode = false,$isAll = false)
    {
        $treeType = Yii::$app->request->getQueryParam("TreeType");
        $treeNodeKid = Yii::$app->request->getQueryParam("ID");
        $status = Yii::$app->request->getQueryParam("Status");

        $treeTypeModel = new FwTreeType();
        $treeTypeName = null;

        if ($treeType != null && $treeType != "")
        {
            $treeTypeResult = $treeTypeModel->findOne(['tree_type_code' => $treeType]);

            if ($treeTypeResult != null)
            {
                $treeTypeKid = $treeTypeResult->kid;
                $treeTypeName = $treeTypeResult->getI18nName();

                if ($treeTypeResult->limitation == FwTreeType::LIMITATION_HIDDEN)
                {
                    return null;
                }
            }
            else
            {
                return null;
            }
        }
        else {
            $treeTypeKid = null;
        }

        $finalResult = [];

        if ($treeTypeKid != null) {

            $tnList = self::getTreeNodes($treeNodeKid,$treeTypeKid,$status,$treeNodeIdList,$isAll);

            $topLevel = false;

            // 打开页面时,将根节点和第一层的节点显示出来
            if ($treeNodeKid == "#") {
                $topLevel = true;

                $finalResult = [];

                $selected = true;
                $disabled = false;
                $displayed = true;
                $opened = false;
                $IsTreeNodeIdIncludeTreeType = false;

                $treeNodeKid = '-1';

                if (isset($otherService) && $otherService != null && $otherService != '')
                {
                    $temp = new $otherService;

                    if($temp instanceof \common\interfaces\MutliTreeNodeInterface) {
                        $selected = $temp->getSelectedStatus($otherKid, $treeNodeKid);
                        $disabled = $temp->getDisabledStatus($otherKid, $treeNodeKid);
                        $displayed = $temp->getDisplayedStatus($otherKid, $treeNodeKid);
                        $opened = $temp->getOpenedStatus($otherKid, $treeNodeKid);
                        $IsTreeNodeIdIncludeTreeType = $temp->isTreeNodeIdIncludeTreeType($otherKid, $treeNodeKid);
                    }
                }

                if ($IsTreeNodeIdIncludeTreeType == true)
                {
                    $treeNodeKidValue = $treeNodeKid . '_' . $treeType;
                }
                else
                {
                    $treeNodeKidValue = $treeNodeKid;
                }


                if ($displayed) {

//                    $subChildren = ($tnList != null && $tnList->count(1) > 0) ? true : false;
                    $subChildren = $this->hasSubNode($treeNodeKid,$treeTypeKid,$status,null,true);

                    if (!$includeRoot) {
                        $finalResult = [];
                        $finalResult = self::buildChildTreeNodeData($tnList, $treeType, $treeTypeKid, $status, $finalResult, $otherService,
                            $otherKid,$treeNodeIdList,$topLevel,$showContentCount,$deleteNode,$editNode, $ListRouteParams,$openAllNode,$isAll);
                    }
                    else {

                        if ($subChildren == true)
                            $opened = true;

                        if ($mergeRoot) {
                            $subChildren = false;
                            $opened = false;
                        }

                        if ($openAllNode) {
                            $opened = true;
                        }

                        // 创建虚拟根节点
                        $rootResult =
                            [
                                'id' => $treeNodeKidValue,
                                'text' => Html::encode($treeTypeName),
                                'children' => $subChildren, //子节点如果是直接挂在根节点，此行需要等于false
                                'type' => 'root',
                                'state' => [
                                    'opened' => $opened,  // is the node open 子节点如果是直接挂在根节点，需要注释此行
                                    'disabled' => $disabled,  // is the node disabled
                                    'selected' => $selected,  // is the node selected
                                    'deleted' => false,
                                    'edited' => false
                                ],
                                'attr' => [
                                    'tree_node_id' => $treeNodeKid,
                                    'tree_node_code' => '',
                                    'tree_node_name' => Html::encode($treeTypeName),
                                    'parent_node_id' => '',
                                    'root_node_id' => '',
                                    'tree_type' => $treeType,
                                    'status' => self::STATUS_FLAG_NORMAL,
                                ]
                            ];

                        array_push($finalResult, $rootResult);

                        //子节点如果是直接挂在根节点，需要去掉此行注释
                        if ($mergeRoot) {
                            $finalResult = self::buildChildTreeNodeData($tnList, $treeType, $treeTypeKid, $status, $finalResult,
                                $otherService, $otherKid, $treeNodeIdList,false,$showContentCount,$deleteNode,$editNode,
                                $ListRouteParams,$openAllNode,$isAll);
                        }
                    }
                }
                else
                {
                    $finalResult = null;
                }
            }
            else {
                $finalResult = [];
                $finalResult = self::buildChildTreeNodeData($tnList, $treeType, $treeTypeKid, $status, $finalResult,
                    $otherService, $otherKid,$treeNodeIdList,$topLevel,$showContentCount,$deleteNode,$editNode,
                    $ListRouteParams,$openAllNode,$isAll);
            }
        }

        return $finalResult;
    }


    /**
     * 获取树节点
     * @param $treeNodeKid
     * @param $treeTypeKid
     * @param $status
     * @return static
     */
    private function getTreeNodes($parentNodeId, $treeTypeId, $status, $treeNodeIdList, $isAll = false, $isOrderBy = true)
    {
        $treeNodeModel = new FwTreeNode();

//        if ($parentNodeId <> "") {
        $tnList = $treeNodeModel->find(false)
            ->andFilterWhere(['=', 'tree_type_id', $treeTypeId]);


        if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
            $tnList->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
        else
            $tnList->andFilterWhere(['=', 'parent_node_id', $parentNodeId]);

        if (isset($treeNodeIdList) && $treeNodeIdList != null) {
            if (is_array($treeNodeIdList) && count($treeNodeIdList) > 0) {
                $tnList->andFilterWhere(['in', 'kid', $treeNodeIdList]);
            } else if ($treeNodeIdList == "all") {
                //不过滤数据
            }
        } else {
            if (!$isAll)
                $tnList->andWhere(BaseActiveRecord::getQuoteColumnName("kid") . ' is null');
        }

        if ($status != null)
            $tnList->andFilterWhere(['=', 'status', $status]);
        else
            $tnList->andFilterWhere(['=', 'status', self::STATUS_FLAG_NORMAL]);

        if ($isOrderBy) {
            $tnList
                ->addOrderBy(['display_number' => SORT_ASC])
                ->addOrderBy(['sequence_number' => SORT_ASC]);
        }

        return $tnList;
    }

    /**
     * 是否有子节点
     * @param $treeNodeKid
     * @param $treeTypeKid
     * @param $status
     * @return bool
     */
    public function hasSubNode($treeNodeKid,$treeTypeKid,$status,$treeNodeIdList,$isAll)
    {
        $subNodeList = self::getTreeNodes($treeNodeKid,$treeTypeKid,$status,$treeNodeIdList,$isAll, false);

        $subChildren = $subNodeList->count(1) > 0 ? true : false;

        return $subChildren;
    }


    /**
     * 构建树子节点
     * @param $tnList
     * @param $treeType
     * @param $treeTypeKid
     * @param $status
     * @param $finalResult
     * @return mixed
     */
    private function buildChildTreeNodeData($tnList,$treeType, $treeTypeKid, $status, $finalResult, $otherService, $otherKid,
                                            $treeNodeIdList, $topLevel, $showContentCount, $deleteNode, $editNode, $ListRouteParams,$openAllNode,$isAll=false)
    {
        if ($tnList != null && $tnList->count(1) > 0) {
            $number = 1;
            foreach ($tnList->all() as $treeNode) {
//                $subNodeList = self::getTreeNodes($treeNode->kid,$treeTypeKid,$status,$treeNodeIdList,$isAll);
//
//                $subChildren =  $subNodeList->count(1) > 0 ? true : false;

                $subChildren = $this->hasSubNode($treeNode->kid,$treeTypeKid,$status,null,true);

                $selected = false;
                if ($topLevel == true && $number == 1)
                {
                    $selected = true;
                }
                $disabled = false;
                $displayed = true;
                $opened = false;
                $IsTreeNodeIdIncludeTreeType = false;

                $treeNodeKid = $treeNode->kid;

                if (isset($otherService) && $otherService != null && $otherService != '')
                {
                    $temp = new $otherService;
                    if($temp instanceof \common\interfaces\MutliTreeNodeInterface) {
                        $selected = $temp->getSelectedStatus($otherKid, $treeNodeKid);
                        $disabled = $temp->getDisabledStatus($otherKid, $treeNodeKid);
                        $displayed = $temp->getDisplayedStatus($otherKid, $treeNodeKid);
                        $opened = $temp->getOpenedStatus($otherKid, $treeNodeKid);
                        $IsTreeNodeIdIncludeTreeType = $temp->isTreeNodeIdIncludeTreeType($otherKid, $treeNodeKid);
                    }
                }

                if ($IsTreeNodeIdIncludeTreeType == true)
                {
                    $treeNodeKidValue = $treeNodeKid . '_' . $treeType;
                }
                else
                {
                    $treeNodeKidValue = $treeNodeKid;
                }
                $count = 0;
                $treeNodeName = $treeNode->tree_node_name;
                if ($showContentCount) {
                    if ($treeType == "course-category") {
                        $courseCategoryService = new CourseCategoryService();
                        $count = $courseCategoryService->GetCategoryCount($treeNodeKid, $ListRouteParams);
                        $treeNodeName .= "(" . strval($count) . ")";
                    }else if ($treeType == "courseware-category") {
                        $coursewareCategoryService = new CoursewareCategoryService();
                        $count = $coursewareCategoryService->GetCategoryCount($treeNodeKid);
                        $treeNodeName .= "(" . strval($count) . ")";
                    }else if ($treeType == "examination-question-category") {
                        $examinationCategoryService = new ExaminationQuestionCategoryService();
                        $count = $examinationCategoryService->GetQuestionCategoryCount($treeNodeKid);
                        $treeNodeName .= "(" . strval($count) . ")";
                    } else if ($treeType == "examination-paper-category") {
                        $examPaperManageService = new ExamPaperManageService();
                        $count = $examPaperManageService->GetExaminationPaperCategoryCount($treeNodeKid);
                        $treeNodeName .= "(" . strval($count) . ")";
                    } else if ($treeType == "examination-category") {
                        $examinationCategoryServicee = new ExaminationCategoryService();
                        $count = $examinationCategoryServicee->GetExaminationCategoryCount($treeNodeKid, $ListRouteParams);
                        $treeNodeName .= "(" . strval($count) . ")";
                    } else if ($treeType == "audience-category") {
                        $audienceCategoryServicee = new AudienceCategoryService();
                        $count = $audienceCategoryServicee->GetAudienceCategoryCount($treeNodeKid, $ListRouteParams);
                        $treeNodeName .= "(" . strval($count) . ")";
                    }

                }
                if ($deleteNode && $count < 1 && !$subChildren) {
                    $isDeleteNode = true;
                }else{
                    $isDeleteNode = false;
                }

                /*判断是否管理员*/
                $rbacService = new RbacService();
                $userId = Yii::$app->user->getId();
                $isSpecialUser = $rbacService->isSpecialUser($userId);

                if ($treeType == 'course-category' || $treeType == 'courseware-category'){
                    if (!$isSpecialUser){
                        $isDeleteNode = false;
                    }
                }

                if ($displayed) {

                    if ($openAllNode) {
                        $opened = true;
                    }
                    $newResult =
                        [
                            'id' => $treeNodeKidValue,
                            'text' => Html::encode($treeNodeName),
                            'children' => $subChildren,
                            'type' => 'default',
                            'state' => [
                                'opened'   => $opened,  // is the node open
                                'disabled' => $disabled,  // is the node disabled
                                'selected' => $selected,  // is the node selected
                                'deleted' => $isDeleteNode, // is the node deleted
                                'edited' => $editNode // is the node edit
                            ],
                            'attr' => [
                                'tree_node_id' => $treeNodeKid,
                                'tree_node_code' => Html::encode($treeNode->tree_node_code),
                                'tree_node_name' => Html::encode($treeNode->tree_node_name),
                                'parent_node_id' => $treeNode->parent_node_id,
                                'root_node_id' => $treeNode->root_node_id,
                                'tree_type' => $treeType,
                                'status' => $treeNode->status,
                            ]
                        ];

                    array_push($finalResult, $newResult);
                    $number = $number + 1;
                }
            }
        }

        return $finalResult;
    }

    /**
     * 获取管理范围内的树节点
     * @return array
     */
    private function listManagedTreeNodes($otherService,$otherKid,$treeNodeIdList,$includeRoot,$mergeRoot,
                                          $showContentCount,$deleteNode,$editNode, $ListRouteParams,$openAllNode,$isAll)
    {
        return self::listAllTreeNodes($otherService,$otherKid,$treeNodeIdList,$includeRoot,$mergeRoot,$showContentCount,
            $deleteNode,$editNode, $ListRouteParams,$openAllNode,$isAll);
    }


    /**
     * 获取当前节点及所有子节点
     * @param $kid
     * @param $treeTypeId
     * @param $nodeIdPath
     * @param null $status
     * @return array
     */
    public function getAllNodeIdIncludeSub($kid,$treeTypeId,$nodeIdPath,$status = null)
    {
        $model = new FwTreeNode();
//        $tableName = $model->realTableName();
        $query = $model->find(false);

        if ($status != null)
        {
            $query->andFilterWhere(['=','status',$status]);
        }

        if ($kid != '') {
            $root_node_id = FwTreeNode::findOne($kid)->root_node_id;
            $condition = ['or',
                [ '=',  'kid', $kid],
                ['and',
                    ['=',  'tree_type_id', $treeTypeId],
                    ['=',  'root_node_id', $root_node_id],
                    BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'"
                ]
            ];
            //$condition[] = ['in', Orgnization::tableName() . '.tree_node_id', $treeNodeIdList];
            $query->andFilterWhere($condition);

//            $sql = "select kid from " . $tableName . " where is_deleted ='".FwTreeNode::DELETE_FLAG_NO."' ".$statusSql.
//                " and (kid = '" . $kid . "' or (tree_type_id = '" . $treeTypeId . "' and node_id_path like '" . $nodeIdPath . "' and root_node_id='" . $root_node_id . "'))";
//        $where = "kid = '".$kid."' or (tree_type_id = '".$treeTypeId."' and node_id_path like '".$nodeIdPath."')";

        }
        else
        {
            $query->andFilterWhere(['=','tree_type_id',$treeTypeId])
                ->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");
//            $sql = "select kid from " . $tableName . " where is_deleted ='".FwTreeNode::DELETE_FLAG_NO."' ".$statusSql." and tree_type_id = '" . $treeTypeId . "' and node_id_path like '" . $nodeIdPath . "'";
        }

        $result = $query->select(BaseActiveRecord::getQuoteColumnName("kid"))->column();
//        $result = $model->findBySql($sql)->column();

        if ($kid == '')
            array_push($result,'');

//       $query = new Query();
//        $result = $query->select('kid')
//            ->from($tableName)
//            ->where($where)
//            ->all();

        return $result;
    }

    /**
     * 获取全部子节点
     * @param $treeTypeId
     * @param $nodeIdPath
     * @return array
     */
    public function getAllSubNodeId($kid,$treeTypeId,$nodeIdPath,$status)
    {
        $model = new FwTreeNode();
        $query = $model->find(false);

        if ($status != null)
        {
            $query->andFilterWhere(['=','status',$status]);
        }

        $query->andFilterWhere(['=','tree_type_id',$treeTypeId])
            ->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $nodeIdPath . "'");

        if ($kid != '') {
            $root_node_id = $model->findOne($kid)->root_node_id;
            $query->andFilterWhere(['=','root_node_id',$root_node_id]);

        }

        $result = $query->select(BaseActiveRecord::getQuoteColumnName("kid"))->column();

        if ($kid == '')
            array_push($result,'');


        return $result;
    }


    /**
     * 计算树的层级
     * @param $treeTypeId
     * @param $kid
     * @return mixed
     */
    public function calculateSubLevel($treeTypeId, $kid)
    {
        $model = new FwTreeNode();
        $sourceModel = FwTreeNode::findOne($kid);

        $oldSubNodeIdPath = $sourceModel->node_id_path;

        $likeNodeIdPath = $oldSubNodeIdPath .'%';
        $oldTreeLevel = $sourceModel->tree_level;
        $oldRootNodeId = $sourceModel->root_node_id;

        $query = $model->find(false)
            ->andFilterWhere(['=','tree_type_id',$treeTypeId])
            ->andFilterWhere(['=','root_node_id',$oldRootNodeId])
            ->andFilterWhere(['>=','tree_level',$oldTreeLevel])
            ->andWhere(BaseActiveRecord::getQuoteColumnName("node_id_path") . " like '" . $likeNodeIdPath . "'");

        $maxLevel = $query->max(BaseActiveRecord::getQuoteColumnName("tree_level"));
        $minLevel = $query->min(BaseActiveRecord::getQuoteColumnName("tree_level"));

        return $maxLevel - $minLevel;
    }

    /**
     * 更新序列号（自动排序）
     * @param $treeNodeId
     * @param $treeTypeId
     * @param $parentNodeId
     * @param $oldSequenceNumber
     * @param $newSequenceNumber
     * @param $flag
     */
    public function updateSequenceNumber($treeNodeId,$treeTypeId,$parentNodeId,$oldSequenceNumber,$newSequenceNumber,$flag)
    {
        if ($flag == '0') {
            //增加或更新时用
            if ($oldSequenceNumber == $newSequenceNumber) {
                return;
            } else if ($newSequenceNumber < $oldSequenceNumber) {
                $model = new FwTreeNode();
                $query = $model->find(false);

                $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);


                if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
                    $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
                else
                    $query->andFilterWhere(['=','parent_node_id',$parentNodeId]);

                $query->andFilterWhere(['<>', 'kid', $treeNodeId])
                    ->andFilterWhere(['>=', 'sequence_number', $newSequenceNumber])
//                ->andFilterWhere(['<=', 'sequence_number', $newSequenceNumber])
                    ->addOrderBy(['sequence_number' => SORT_ASC]);

                $results = $query->all();


                $tempSequenceNumber = $newSequenceNumber + 1;
                if (isset($results) && count($results) > 0) {
                    foreach ($results as $data) {
                        if ($data->sequence_number <> $tempSequenceNumber) {
                            $data->sequence_number = $tempSequenceNumber;
                            $data->save();
                        }
                        $tempSequenceNumber = $tempSequenceNumber + 1;
                    }
                }
            } else if ($newSequenceNumber > $oldSequenceNumber) {
                $model = new FwTreeNode();
                $query = $model->find(false);

                $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

                if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
                    $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
                else
                    $query->andFilterWhere(['=','parent_node_id',$parentNodeId]);

                $query->andFilterWhere(['<>', 'kid', $treeNodeId])
                    ->andFilterWhere(['<=', 'sequence_number', $newSequenceNumber])
                    ->andFilterWhere(['>=', 'sequence_number', $oldSequenceNumber])
                    ->addOrderBy(['sequence_number' => SORT_ASC]);

                $results = $query->all();


                $tempSequenceNumber = $oldSequenceNumber;
                if (isset($results) && count($results) > 0) {
                    foreach ($results as $data) {
                        if ($data->sequence_number <> $tempSequenceNumber) {
                            $data->sequence_number = $tempSequenceNumber;
                            $data->save();
                        }
                        $tempSequenceNumber = $tempSequenceNumber + 1;
                    }
                }
            }
        }
        else
        {
            //删除时用
            $model = new FwTreeNode();
            $query = $model->find(false);

            $query->andFilterWhere(['=','tree_type_id',$treeTypeId]);

            if ($parentNodeId == null || $parentNodeId == "#" || $parentNodeId == "-1")
                $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_node_id") . ' is null');
            else
                $query->andFilterWhere(['=','parent_node_id',$parentNodeId]);

            $query->andFilterWhere(['<>', 'kid', $treeNodeId])
                ->andFilterWhere(['>=', 'sequence_number', $newSequenceNumber])
//                ->andFilterWhere(['>=', 'sequence_number', $oldSequenceNumber])
                ->addOrderBy(['sequence_number' => SORT_ASC]);

            $results = $query->all();


            $tempSequenceNumber = $newSequenceNumber;
            if (isset($results) && count($results) > 0) {
                foreach ($results as $data) {
                    if ($data->sequence_number <> $tempSequenceNumber) {
                        $data->sequence_number = $tempSequenceNumber;
                        $data->save();
                    }
                    $tempSequenceNumber = $tempSequenceNumber + 1;
                }
            }
        }
    }

    /**
     * 移动所有子节点
     * @param $treeTypeId
     * @param $kid
     * @param $targetParentNodeId
     */
    public function moveSubNodePath($treeTypeId, $kid, $targetParentNodeId)
    {

        $model = new FwTreeNode();

//        $sourceParentModel = TreeNode::findOne($sourceParentNodeId);
        $targetParentModel = FwTreeNode::findOne($targetParentNodeId);

        $sourceModel = FwTreeNode::findOne($kid);

        $oldSubNodeIdPath = $sourceModel->node_id_path;
        $oldSubNodeNamePath = $sourceModel->node_name_path;
        $oldSubNodeCodePath = $sourceModel->node_code_path;

        $likeNodeIdPath = $oldSubNodeIdPath .'%';
        $oldTreeLevel = $sourceModel->tree_level;
        $oldRootNodeId = $sourceModel->root_node_id;


        if ($targetParentNodeId == null || $targetParentNodeId == '-1')
        {
            $newSubNodeIdPath = '/';// . $kid . '/';
            $newSubNodeNamePath = '/';//. $sourceModel->tree_node_name . '/';
            $newSubNodeCodePath = '/';//. $sourceModel->tree_node_code . '/';
            $newRootNodeId = $kid;
            $newTreeLevel = 1;
        }
        else {
            $newSubNodeIdPath = $targetParentModel->node_id_path . $targetParentModel->kid . '/';
            $newSubNodeNamePath = $targetParentModel->node_name_path . $targetParentModel->tree_node_name . '/';
            $newSubNodeCodePath = $targetParentModel->node_code_path . $targetParentModel->tree_node_code . '/';
            $newRootNodeId = $targetParentModel->root_node_id;
            $newTreeLevel = $targetParentModel->tree_level + 1;
        }


        $attributes = [
            'node_id_path' => new Expression("concat(:newNodeIdPath,substring(" . BaseActiveRecord::getQuoteColumnName("node_id_path") .",locate(:oldNodeIdPath, " . BaseActiveRecord::getQuoteColumnName("node_id_path") .")+length(:oldNodeIdPath)))",
                [':oldNodeIdPath' => $oldSubNodeIdPath, ':newNodeIdPath' => $newSubNodeIdPath]),
            'node_name_path' => new Expression("concat(:newNodeNamePath,substring(" . BaseActiveRecord::getQuoteColumnName("node_name_path") .",locate(:oldNodeNamePath, " . BaseActiveRecord::getQuoteColumnName("node_name_path") .")+length(:oldNodeNamePath)))",
                [':oldNodeNamePath' => $oldSubNodeNamePath, ':newNodeNamePath' => $newSubNodeNamePath]),
            'node_code_path' => new Expression("concat(:newNodeCodePath,substring(" . BaseActiveRecord::getQuoteColumnName("node_code_path") .",locate(:oldNodeCodePath, " . BaseActiveRecord::getQuoteColumnName("node_code_path") .")+length(:oldNodeCodePath)))",
                [':oldNodeCodePath' => $oldSubNodeCodePath, ':newNodeCodePath' => $newSubNodeCodePath]),
            'tree_level' => new Expression(BaseActiveRecord::getQuoteColumnName("tree_level") . " + :newTreeLevel - :oldTreeLevel", [':newTreeLevel' => $newTreeLevel, ':oldTreeLevel' => $oldTreeLevel]),
            'root_node_id' => $newRootNodeId,
        ];


        $condition = self::getQuoteColumnName("tree_type_id") . " = :treeTypeId " .
            "and " . self::getQuoteColumnName("root_node_id") .  " = :rootNodeId " .
            "and " . self::getQuoteColumnName("node_id_path") .  " like :nodeIdPath ".
            "and " . self::getQuoteColumnName("tree_level") .  " > :treeLevel ";

        $param = [':treeTypeId'=>$treeTypeId,
            ':treeLevel' => $oldTreeLevel,
            ':rootNodeId' => $oldRootNodeId,
            ':nodeIdPath'=>$likeNodeIdPath
        ];

        $model->updateAll($attributes,
            $condition,$param);
    }

    /**
     * 根据树节点ID，更改相关数据的状态
     * @param $treeNodeId
     * @param $status
     */
    public function changeStatusRelateData($model,$treeNodeId,$status)
    {
        $kids = "";
        foreach ($treeNodeId as $key) {
            $kids = $kids . "'" .  $key . "',";
        }

        $kids = rtrim($kids, ",");

        $condition = BaseActiveRecord::getQuoteColumnName("tree_node_id") . " in (".$kids.")";

        $list = $model->find(false)
            ->andWhere($condition)
            ->all();

        foreach ($list as $single) {
            $calculatedTableName = $model->realTableName();
            $key = $single->kid;
            $cacheKey = $calculatedTableName . "-" . $key;
            BaseActiveRecord::removeFromCache($cacheKey);
        }


        $attributes = [
            'status' => $status,
        ];



        $model->updateAll($attributes,$condition);
    }


    /**
     * 当前节点是否包含子节点
     * @param $kid
     * @return bool
     */
    public function isExistSubTreeNode($kid)
    {
        $model = new FwTreeNode();
        $query = $model->find(false);

        $query->andFilterWhere(['=', 'parent_node_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }
}

?>