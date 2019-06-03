<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace backend\services;


use common\models\framework\FwOrgnization;
use common\models\framework\FwUser;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;

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
     * 根据树节点ID，删除相关组织
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        $model = new FwOrgnization();

        $kids = "";
        foreach ($treeNodeId as $key) {
            $kids = $kids . "'" .  $key . "',";
            $orgnizationKey = $this->getOrgnizationIdByTreeNodeId($key);
            FwOrgnization::removeFromCacheByKid($orgnizationKey);
        }

        $kids = rtrim($kids, ",");

        $model->deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") . " in (".$kids.")");
    }

    /**
     * 激活父节点
     * @param $kid
     */
    public function activeParentNode($kid)
    {
        $model = FwOrgnization::findOne($kid);

        $parent_node_id = $model->parent_orgnization_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = FwOrgnization::findOne($parent_node_id);

            if ($parentModel->status != FwOrgnization::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = FwOrgnization::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->activeParentNode($parentModel->kid);
            }
        }
    }

    /**
     * 根据树节点ID，更新上级组织信息
     * @param $treeNodeId
     * @param $targetTreeNodeId
     */
    public function updateParentIdByTreeNodeId($treeNodeId,$targetTreeNodeId)
    {
        $orgnizationId = $this->getOrgnizationIdByTreeNodeId($treeNodeId);
        $targetOrgnizationId = $this->getOrgnizationIdByTreeNodeId($targetTreeNodeId);
        if ($orgnizationId != null) {
            $orgnizationModel = FwOrgnization::findOne($orgnizationId);
            $orgnizationModel->parent_orgnization_id = $targetOrgnizationId;

            $orgnizationModel->save();
        }
    }


    /**
     * 根据企业ID，获取全部组织列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllOrgnizationListByCompanyId($companyId)
    {
        if ($companyId == null)
            return [];

        $model = FwOrgnization::find(false);

        $query = $model
//            ->innerJoinWith('fwTreeNode')
            ->innerJoin(FwTreeNode::realTableName(),
                FwOrgnization::realTableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::realTableName() . "." . self::getQuoteColumnName("kid") )
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', FwTreeNode::realTableName() . '.status', FwTreeNode::STATUS_FLAG_NORMAL])

            ->addOrderBy([FwTreeNode::realTableName().'.sequence_number' => SORT_ASC])
            ->all();

        return $query;
    }


    /**
     * 更新相关用户的组织信息
     * @param $orgnizationId
     */
    public function updateReleatedUserOrgnizationInfo($orgnizationId)
    {
        $orgnizationModel = FwOrgnization::findOne($orgnizationId);

        $company_id = $orgnizationModel->company_id;
        $domain_id = $orgnizationModel->domain_id;

        $model = new FwUser();

        $attributes = [
            'company_id' => $company_id,
            'domain_id' => $domain_id,
        ];

        $condiction = 'orgnization_id = :orgnization_id';

        $parameter = [
            'orgnization_id' => $orgnizationId,
        ];

        $model->updateAll($attributes,$condiction,$parameter);
    }



    /**
     * 当前节点是否包含子组织
     * @param $kid
     * @return bool
     */
    public function isExistSubOrgnization($kid)
    {
        $commonOrgnizationService = new \common\services\framework\OrgnizationService();
        return $commonOrgnizationService->isExistSubOrgnization($kid);
    }


    /**
     * 当前节点是否包含用户
     * @param $kid
     * @return bool
     */
    public function isExistUser($kid)
    {
        $commonOrgnizationService = new \common\services\framework\OrgnizationService();
        return $commonOrgnizationService->isExistUser($kid);
    }

}