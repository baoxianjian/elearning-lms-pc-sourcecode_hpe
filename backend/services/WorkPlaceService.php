<?php
/**
 * Created by PhpStorm.
 * FwUser: Alex Liu
 * Date: 5/5/2016
 * Time: 13:49 PM
 */

namespace backend\services;


use common\interfaces\MutliTreeNodeInterface;
use common\models\framework\FwCompany;
use common\models\framework\FwDictionary;
use common\models\framework\FwDictionaryCategory;
use common\models\framework\FwDomainWorkplace;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class WorkPlaceService extends FwDictionary implements MutliTreeNodeInterface
{

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

                $domianService = new DomainService();
                $domainId = $domianService->getDomainIdByTreeNodeId($nodeId);

                if ($domainId != null) {
                    $domainWorkplaceModel = new FwDomainWorkplace();
                    $domainWorkplaceModel->domain_id = $domainId;
                    $domainWorkplaceModel->workplace_id = $kid;

                    $domainWorkplaceService = new DomainWorkplaceService();

                    if ($domainWorkplaceService->isRelationshipExist($domainWorkplaceModel)) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
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
            //return false;
            return true;
        }
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

    /**
     * 搜索数据列表
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $parentNodeId, $includeSubNode)
    {
        $query = FwDictionary::find(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->innerJoin(FwDictionaryCategory::realTableName(),
            FwDictionary::tableName() . "." . self::getQuoteColumnName("dictionary_category_id") . " = " . FwDictionaryCategory::tableName() . "." . self::getQuoteColumnName("kid"))
            ->innerJoin(FwCompany::realTableName(),
                FwDictionary::tableName() . "." . self::getQuoteColumnName("company_id") . " = " . FwCompany::tableName() . "." . self::getQuoteColumnName("kid"))
            ->innerJoin(FwTreeNode::realTableName(),
                FwCompany::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid"))
            ->andFilterWhere(['like', 'dictionary_code', trim(urldecode($this->dictionary_code))])
            ->andFilterWhere(['like', 'dictionary_name', trim(urldecode($this->dictionary_name))])
            ->andFilterWhere(['=', FwDictionary::realTableName() . '.status', $this->status])
            ->andFilterWhere(['=', FwDictionaryCategory::realTableName() . '.cate_code', 'work_place']);

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

        if (!Yii::$app->user->getIsGuest()) {
            $userId = Yii::$app->user->getId();

            $rbacService = new RbacService();

            if (!$rbacService->isSpecialUser($userId)) {
                $userCompanyService = new UserCompanyService();

                $selectedResult = $userCompanyService->getManagedListByUserId($userId, null, false);

                if (isset($selectedResult) && $selectedResult != null) {
                    $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');

                    $companyIdList = array_keys($selectedList);
                } else {
                    $companyIdList = null;
                }

                $condition = ['or',
                    ['in', FwDictionary::realTableName() . '.company_id', $companyIdList],
                    FwDictionary::realTableName() . '.company_id is null'];
                $query->andFilterWhere($condition);
//                $query->andFilterWhere(['in', FwPosition::tableName() . '.company_id', $companyIdList]);
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
        $query->addOrderBy([FwDictionary::realTableName() . '.sequence_number' => SORT_ASC]);
        $query->addOrderBy([FwDictionary::realTableName() . '.created_at' => SORT_DESC]);

        return $dataProvider;
    }


    /**
     * 判断是否存在相同字典代码
     * @param $kid
     * @param $companyId
     * @param $dictionaryCode
     * @return bool
     */
    public function isExistSameDictionaryCode($kid, $companyId, $dictionaryCode)
    {
        $model = new FwDictionary();
        $query = $model->find(false);

        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'dictionary_code', $dictionaryCode]);

        if ($companyId == null || $companyId == "")
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("company_id") . ' is null');
        else
            $query->andFilterWhere(['=', 'company_id', $companyId]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 改变数据相关状态
     * @param $kids
     */
    public function changeStatusByKidList($kids, $status)
    {
        if (!empty($kids)) {
            $sourceMode = new FwDictionary();


            $attributes = [
                'status' => $status,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }


    /**
     * 移动数据
     * @param $userId
     */
    public function moveDataByKidList($kids, $companyId)
    {
        if (!empty($kids)) {
            $sourceMode = new FwDictionary();

            $attributes = [
                'company_id' => $companyId,
            ];

            $condition = BaseActiveRecord::getQuoteColumnName("kid") . ' in (' . $kids . ')';


            $sourceMode->updateAll($attributes, $condition);
        }
    }
}