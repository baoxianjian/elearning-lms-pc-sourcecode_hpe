<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/03/15
 * Time: 10:57 AM
 */
namespace common\services\social;

use common\models\social\SoAudience;
use common\models\social\SoAudienceCategory;
use common\models\treemanager\FwTreeNode;
use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

class AudienceCategoryService extends SoAudienceCategory{


    /**
     * 根据企业ID列表获取相关目录
     * @param $companyIdList
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetAudienceCategoryByCompanyIdList($companyIdList)
    {
        $categoryModel = new SoAudienceCategory();

        $userId = Yii::$app->user->getId();

        $courseCategoryResult = $categoryModel->find(false)
            ->andFilterWhere(['in','company_id',$companyIdList])
            ->andFilterWhere(['=','owner_id',$userId])
            ->orderBy('created_at')
            ->all();

        return $courseCategoryResult;
    }

    public function GetAudienceCategoryCount($treeNodeId, $ListRouteParams = false){
        $userId = Yii::$app->user->getId();
        $model = SoAudienceCategory::find(false)
            ->andFilterWhere(['=','tree_node_id',$treeNodeId])
            ->andFilterWhere(['=','owner_id',$userId])
            ->select('kid,company_id')
            ->one();

        $categoryAll = $this->getSubCategories($model->kid);
        if (!empty($categoryAll)){
            $categoryAll = array_merge(array($model->kid), $categoryAll);
        }else{
            $categoryAll = array($model->kid);
        }

        $companyId = $model->company_id;
        $query = SoAudience::find(false)
            //->andFilterWhere(['=','category_id',$model->kid])
            ->andFilterWhere(['in','category_id',$categoryAll])
            ->andFilterWhere(['=','company_id',$companyId])
            ->andFilterWhere(['=','owner_id',$userId]);
            return $query->count('kid');
    }

    /**
     * 根据树节点ID获取目录ID
     * @param $id
     * @return null|string
     */
    public function GetAudienceCategoryIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $categoryModel = new SoAudienceCategory();

            $categoryResult = $categoryModel->findOne(['tree_node_id' => $id]);

            if ($categoryResult != null)
            {
                $examinationCategoryId = $categoryResult->kid;
            }
            else
            {
                $examinationCategoryId = null;
            }
        }
        else
        {
            $examinationCategoryId = null;
        }

        return $examinationCategoryId;
    }

    /**
     * 根据树节点ID，删除相关目录ID
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        $model = new SoAudienceCategory();

        $kids = "";
        if (is_array($treeNodeId)) {
            foreach ($treeNodeId as $key) {
                $kids = $kids . "'" . $key . "',";

                $audienceCategoryKey = $this->GetAudienceCategoryIdByTreeNodeId($key);
                SoAudienceCategory::removeFromCacheByKid($audienceCategoryKey);
            }

            $kids = rtrim($kids, ",");
        }else{
            $kids = "'".$treeNodeId."'";

            $audienceCategoryKey = $this->GetAudienceCategoryIdByTreeNodeId($treeNodeId);
            SoAudienceCategory::removeFromCacheByKid($audienceCategoryKey);
        }

        $model->deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") ." in (".$kids.")");
        FwTreeNode::deleteAll(BaseActiveRecord::getQuoteColumnName("kid") . " in (".$kids.")");

        return true;
    }


    /**
     * 激活父节点
     * @param $kid
     */
    public function ActiveParentNode($kid)
    {
        $model = SoAudienceCategory::findOne($kid);

        $parent_node_id = $model->parent_category_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = SoAudienceCategory::findOne($parent_node_id);

            if ($parentModel->status != SoAudienceCategory::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = SoAudienceCategory::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->ActiveParentNode($parentModel->kid);
            }
        }
    }

    /**
     * 根据树节点ID，更新上级目录信息
     * @param $treeNodeId
     * @param $targetTreeNodeId
     */
    public function updateParentIdByTreeNodeId($treeNodeId,$targetTreeNodeId)
    {
        $categoryId = $this->GetAudienceCategoryIdByTreeNodeId($treeNodeId);
        $targetCategroyId = $this->GetAudienceCategoryByCompanyIdList($targetTreeNodeId);
        if ($categoryId != null) {
            $categoryModel = SoAudienceCategory::findOne($categoryId);
            $categoryModel->parent_category_id = $targetCategroyId;
            $categoryModel->save();
        }
    }

    /**
     * 获取公司的所有目录列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetAllAudienceCategoryListByCompanyId($companyId = null)
    {
        $model = SoAudienceCategory::find(false);
        $userId = Yii::$app->user->getId();

        $query = $model
            ->joinWith('fwTreeNode')
            ->andFilterWhere(['=','owner_id',$userId])
            ->andFilterWhere(['company_id' => $companyId])
            ->andFilterWhere([FwTreeNode::tableName() . '.status' => FwTreeNode::STATUS_FLAG_NORMAL])
            ->addOrderBy([FwTreeNode::tableName() . '.display_number' => SORT_ASC])
            ->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC])
            ->all();

        return $query;
    }


    /**
     * 根据tree_node_id获取目录的子目录
     * @param $categories array
     * @return string
     */
    public function getCategoriesByTreeNode($tree_node_id){
        $categories = SoAudienceCategory::findAll(['tree_node_id'=>$tree_node_id],false);
        $result = [];
        foreach($categories as $val){
            $result[] = $val->kid;
            $result = array_merge($result,$this->getSubCategories($val->kid));
        }
        return $result;
    }

    /**
     * 获取目录的子目录
     * @param $categoryid
     * @return array
     */
    private function getSubCategories($categoryid){
        $categories = SoAudienceCategory::findAll(['parent_category_id'=>$categoryid],false);
        $result = [];
        foreach($categories as $val){
            $result[] = $val->kid;
            $result = array_merge($result,$this->getSubCategories($val->kid));
        }
        return $result;
    }


    /**
     * 目录选择框
     * @return array
     */
    public function ListAudienceCategroySelect()
    {
        $companyId=Yii::$app->user->identity->company_id;

        $categories = $this->GetAllAudienceCategoryListByCompanyId($companyId);
        $result = array();
        foreach ($categories as $k => $val) {
            if (!$val->parent_category_id) {
                $result[$val->kid] = $val->category_name;
                $result = array_merge($result, $this->getSubAudienceCategories($categories, $val->kid, '　'));
            }
        }
        return $result;
    }

    /**
     * 获取目录的子目录
     * @param $categories
     * @param $parentid
     * @param $tab
     * @return array
     */
    private function getSubAudienceCategories($categories,$parentid,$tab){
        $result = array();
        foreach ($categories as $k=>$val) {
            if ($parentid == $val->parent_category_id) {
                $result[$val->kid] = $tab.$val->category_name;
                $result = array_merge($result,$this->getSubAudienceCategories($categories,$val->kid,$tab.'　'));
            }
        }
        return $result;
    }

}