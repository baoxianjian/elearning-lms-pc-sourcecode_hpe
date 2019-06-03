<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 12/5/2015
 * Time: 3:16 PM
 */

namespace common\services\framework;


use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use yii\helpers\ArrayHelper;

class OrgnizationService extends FwOrgnization{
    /**
     * 根据树节点ID获取组织ID
     * @param $id
     * @return null|string
     */
    public function getOrgnizationIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $orgnizationModel = new FwOrgnization();

            $orgnizationResult = $orgnizationModel->findOne(['tree_node_id' => $id]);

            if ($orgnizationResult != null)
            {
                $orgnizationId = $orgnizationResult->kid;
            }
            else
            {
                $orgnizationId = null;
            }
        }
        else
        {
            $orgnizationId = null;
        }

        return $orgnizationId;
    }

    /**
     * 返回所有KID
     * @param $treeNodeId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getAllOrgnizationIdByTreeNodeId($treeNodeId){
        if (empty($treeNodeId)) return null;
        $result = FwOrgnization::find(false)
            ->andFilterWhere(['in', 'tree_node_id', $treeNodeId])
            ->andFilterWhere(['=', 'status', FwOrgnization::STATUS_FLAG_NORMAL])
            ->select('kid')
            ->all();

        $allOrgnization = [];

        if (!empty($result)){
            $allOrgnization = ArrayHelper::map($result, 'kid', 'kid');
            $allOrgnization = array_keys($allOrgnization);
        }

        return $allOrgnization;
    }

    /**
     * 获取默认组织（第一个）
     * @param $companyId
     * @return array|null|FwOrgnization
     */
    public function getTopOrgnization($companyId)
    {
        $orgnizationModel = new FwOrgnization();

        $result = $orgnizationModel->find(false)
            ->andFilterWhere(['=','company_id',$companyId])
            ->addOrderBy(['kid'=>SORT_ASC])
            ->one();

        return $result;
    }

    /**
     * 获取默认注册组织
     * @param $companyId
     * @return array|null|FwOrgnization
     */
    public function getTopDefaultRegisterOrgnization($companyId)
    {
        $orgnizationModel = new FwOrgnization();

        $result = $orgnizationModel->find(false)
            ->andFilterWhere(['=','is_default_orgnization',FwOrgnization::YES])
            ->addOrderBy(['kid'=>SORT_ASC])
            ->one();

        return $result;
    }

    /**
     * 根据企业ID，获取全部组织列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllOrgnizationListByCompanyId($companyId,$needReturnAll = true, $parentNodeId = null, $includeSubNode = "0", $nodeIdPath = null)
    {
        if ($companyId == null)
            return [];

        $model = FwOrgnization::find(false);

        $query = $model
            ->innerJoinWith('fwTreeNode')
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', FwTreeNode::realTableName() . '.status', FwTreeNode::STATUS_FLAG_NORMAL]);

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

        $result = $query->addOrderBy([FwTreeNode::realTableName() . '.sequence_number' => SORT_ASC])
            ->all();

        return $result;
    }

    /**
     * 判断组织代码是否重复
     * @param $kid
     * @param $companyId
     * @param $orgnizationCode
     * @return bool
     */
    public function isExistSameOrgnizationCode($kid, $companyId, $orgnizationCode)
    {
        $model = new FwOrgnization();
        $query = $model->find(false);

        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'orgnization_code', $orgnizationCode])
            ->andFilterWhere(['=', 'company_id', $companyId]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 当前节点是否包含子组织
     * @param $kid
     * @return bool
     */
    public function isExistSubOrgnization($kid)
    {
        $model = new FwOrgnization();
        $query = $model->find(false);

        $query->andFilterWhere(['=', 'parent_orgnization_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * 当前节点是否包含用户
     * @param $kid
     * @return bool
     */
    public function isExistUser($kid)
    {
        $model = new FwUser();
        $query = $model->find(false);

        $query->andFilterWhere(['=', 'orgnization_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 获取子目录
     * @param $kid
     * @return array
     */
    public function getSubOrgnization($kid, $companyId){
        if (empty($kid)) return [];
        if (!is_array($companyId)){
            $companyId = array($companyId);
        }
        if (is_array($kid)) {
            $all = FwOrgnization::find(false)->andFilterWhere(['in', 'company_id', $companyId])->andFilterWhere(['in', 'parent_orgnization_id', $kid])->select('kid')->all();
        }else{
            $all = FwOrgnization::find(false)->andFilterWhere(['in', 'company_id', $companyId])->andFilterWhere(['parent_orgnization_id'=>$kid])->select('kid')->all();
        }
        $result = [];
        if (!empty($all)) {
            foreach ($all as $val) {
                if (!empty($val->kid)) {
                    $result[] = $val->kid;
                    if ($this->isExistSubOrgnization($val->kid)) {
                        $sub = $this->getSubOrgnization($val->kid, $companyId);
                        if (!empty($sub)) {
                            $result = array_merge($result, $sub);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 根据父级组织ID获取所有子组织ID
     * @param string $parentId 父组织ID
     * @param string $companyId 公司ID
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getSubOrgByParentId($parentId, $companyId)
    {
        $model = FwOrgnization::findOne($parentId);

        $typeService = new TreeNodeService();
        $treeTypeId = $typeService->getTreeTypeId('orgnization');

        if ($model) {
            $orgTableName = FwOrgnization::tableName();

            $query = FwOrgnization::find(false);

            $query->innerJoin(FwTreeNode::tableName() . ' node', "node.tree_type_id='$treeTypeId' and node.is_deleted='0'" .
                " and $orgTableName.company_id='$companyId' and $orgTableName.tree_node_id=node.kid")
                ->andFilterWhere(['like', 'node_id_path', $model->tree_node_id])
                ->select("$orgTableName.kid");

            return $query->asArray()->all();
        } else {
            return null;
        }
    }
}