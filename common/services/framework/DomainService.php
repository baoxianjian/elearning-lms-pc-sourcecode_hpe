<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 12/5/2015
 * Time: 3:14 PM
 */

namespace common\services\framework;


use common\models\framework\FwDomainWorkplace;
use common\models\framework\FwDomain;
use common\models\framework\FwUser;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;

class DomainService extends FwDomain{

    /**
     * 获取域数
     * @return int|string
     */
    public function getDomainCount($withCache = false)
    {
        $cacheKey = "TotalDomainCount";

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = new FwDomain();
            $query = $model->find(false);

            $result = $query->count(1);

            if ($withCache) {
//                $dependencySql = "SELECT max(updated_at) FROM " . self::calculateTableName(FwDomain::tableName());
                self::saveToCache($cacheKey, $result);
            }
        }

        return $result;
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
     * 判断是否同级共享域
     * @param $companyId
     * @param $parentDomainId
     * @param $domainId
     * @return array|bool
     */
    public function isSameLevelSharedDomain($companyId, $parentDomainId, $domainId)
    {
        if ($companyId == null)
            return [];

        $model = FwDomain::find(false);

        $query = $model
            ->andFilterWhere(['company_id'=>$companyId])
            ->andFilterWhere(['status'=>FwDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['share_flag'=>FwDomain::SHARE_FLAG_SHARE])
            ->andFilterWhere(['domain_id'=>$domainId]);

        if ($parentDomainId != null) {
            $query->andFilterWhere(['parent_domain_id' => $parentDomainId]);
        }
        else{
            $query->andWhere('parent_domain_id is null');
        }

        $result = $query->count('kid');

        if ($result > 0 ){
            return true;
        }
        else {
            return false;
        }

    }

    /**
     * 获取公司的所有域列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllDomainListByCompanyId($companyId, $needReturnAll = true, $parentNodeId,$includeSubNode = "0", $nodeIdPath = null)
    {
        if ($companyId == null)
            return [];

        $model = FwDomain::find(false);

        $query = $model
            ->innerJoin(FwTreeNode::realTableName(),
                FwDomain::realTableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::realTableName() . "." . self::getQuoteColumnName("kid") )
            ->andFilterWhere(['=','company_id',$companyId])
//            ->andFilterWhere(['share_flag'=>FwDomain::SHARE_FLAG_EXCLUSIVE])
//            ->andFilterWhere([FwTreeNode::realTableName().'.status' => FwTreeNode::STATUS_FLAG_NORMAL])
            ->andFilterWhere([FwDomain::realTableName().'.status' => FwDomain::STATUS_FLAG_NORMAL]);

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

        $result = $query
            ->addOrderBy([FwTreeNode::realTableName() . '.sequence_number' => SORT_ASC])
            ->all();

        return $result;
    }

    /**
     * 获取同一级下的共享的域列表
     * @param $companyId
     * @param $parentDomainId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSharedDomainListByCompanyId($companyId,$parentDomainId)
    {
        if ($companyId == null)
            return [];

        $model = FwDomain::find(false);

        $query = $model
//            ->joinWith('fwTreeNode')
            ->andFilterWhere(['=','company_id',$companyId])
            ->andFilterWhere(['=','share_flag',FwDomain::SHARE_FLAG_SHARE])
//            ->andFilterWhere([FwTreeNode::realTableName().'.status' => FwTreeNode::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=','status' , FwDomain::STATUS_FLAG_NORMAL]);

        if ($parentDomainId != null) {
            $query->andFilterWhere(['=','parent_domain_id',$parentDomainId]);
        }
        else{
            $query->andWhere(BaseActiveRecord::getQuoteColumnName("parent_domain_id") . ' is null');
        }
//        $query->addOrderBy([FwTreeNode::realTableName().'.sequence_number' => SORT_ASC]);

        $result = $query->all();

        return $result;
    }

    /**
     * 判断域代码是否重复
     * @param $kid
     * @param $companyId
     * @param $domainCode
     * @return bool
     */
    public function isExistSameDomainCode($kid, $companyId, $domainCode)
    {
        $model = new FwDomain();
        $query = $model->find(false);

        $query->andFilterWhere(['<>', 'kid', $kid])
            ->andFilterWhere(['=', 'domain_code', $domainCode])
            ->andFilterWhere(['=', 'company_id', $companyId]);

        $count = $query->count();

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

        $query->andFilterWhere(['=', 'domain_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 获取企业域数
     * @return int|string
     */
    public function getCompanyDomainCount($companyId)
    {
        $model = new FwDomain();
        $query = $model->find(false);
        $query->andFilterWhere(['=','company_id',$companyId]);

        return $query->count(1);
    }


    /**
     * 根据工作地获取域Id
     * @param $workPlaceId
     * @param $withCache
     * @return mixed|null
     */
    public function getDomainIdByWorkPlaceId($workPlaceId, $withCache = true)
    {
        $cacheKey = "GetDomainId_WorkPlaceId_" . $workPlaceId;

        $result = self::loadFromCache($cacheKey, $withCache, $hasCache);

        if (empty($result) && !$hasCache) {
            $model = new FwDomainWorkplace();
            $query = $model->find(false)
                ->andFilterWhere(['=', 'workplace_id', $workPlaceId])
                ->andFilterWhere(['=', 'status', FwDomainWorkplace::STATUS_FLAG_NORMAL])
                ->one();

            if (!empty($query)) {
                $result = $query->domain_id;

                if ($withCache) {
                    self::saveToCache($cacheKey, $result);
                }
            }
        }

        return $result;
    }
}