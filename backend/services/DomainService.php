<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace backend\services;


use common\models\framework\FwCompany;
use common\models\framework\FwDomain;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;

class DomainService extends FwDomain{

    /**
     * 获取企业域数
     * @return int|string
     */
    public function getCompanyDomainCount($companyId)
    {
        $commonDomainService = new \common\services\framework\DomainService();
        return $commonDomainService->getCompanyDomainCount($companyId);
    }
    /**
     * 根据树节点ID获取域ID
     * @param $id
     * @return null|string
     */
    public function getDomainIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $domainModel = new FwDomain();

            $domainResult = $domainModel->findOne(['tree_node_id' => $id]);

            if ($domainResult != null)
            {
                $domainId = $domainResult->kid;
            }
            else
            {
                $domainId = null;
            }
        }
        else
        {
            $domainId = null;
        }

        return $domainId;
    }

    /**
     * 根据树节点ID，删除相关域
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        $model = new FwDomain();

        $kids = "";
        foreach ($treeNodeId as $key) {
            $kids = $kids . "'" .  $key . "',";
            $domainKey = $this->getDomainIdByTreeNodeId($key);
            FwDomain::removeFromCacheByKid($domainKey);
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
        $model = FwDomain::findOne($kid);

        $parent_node_id = $model->parent_domain_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = FwDomain::findOne($parent_node_id);

            if ($parentModel->status != FwDomain::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = FwDomain::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->activeParentNode($parentModel->kid);
            }
        }
    }

    /**
     * 根据树节点ID，更新上级域信息
     * @param $treeNodeId
     * @param $targetTreeNodeId
     */
    public function updateParentIdByTreeNodeId($treeNodeId,$targetTreeNodeId)
    {
        $domainId = $this->getDomainIdByTreeNodeId($treeNodeId);
        $targetDomainId = $this->getDomainIdByTreeNodeId($targetTreeNodeId);
        if ($domainId != null) {
            $domainModel = FwDomain::findOne($domainId);
            $domainModel->parent_domain_id = $targetDomainId;

            $domainModel->save();
        }
    }


    /**
     * 获取公司的所有独享域列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getExclusivedDomainListByCompanyId($companyId)
    {
        if ($companyId == null)
            return [];

        $model = FwDomain::find(false);

        $query = $model
            ->joinWith('fwTreeNode')
            ->andFilterWhere(['company_id'=>$companyId])
            ->andFilterWhere(['share_flag'=>FwDomain::SHARE_FLAG_EXCLUSIVE])
            ->andFilterWhere([FwTreeNode::realTableName().'.status' => FwTreeNode::STATUS_FLAG_NORMAL])
            ->addOrderBy([FwTreeNode::realTableName().'.sequence_number' => SORT_ASC])
            ->all();

        return $query;
    }


    /**
     * 当前节点是否包含用户
     * @param $kid
     * @return bool
     */
    public function isExistUser($kid)
    {
        $commonDomainService = new \common\services\framework\DomainService();
        return $commonDomainService->isExistUser($kid);
    }

}