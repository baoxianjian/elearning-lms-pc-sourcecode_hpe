<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\models\learning\LnExamination;
use common\models\learning\LnExaminationCategory;
use common\models\learning\LnResourceDomain;
use common\models\treemanager\FwTreeNode;
use common\services\framework\TreeNodeService;
use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

class ExaminationCategoryService extends LnExaminationCategory{


    /**
     * 根据企业ID列表获取相关课程目录
     * @param $companyIdList
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetExaminationCategoryByCompanyIdList($companyIdList)
    {
        $examinationCategoryModel = new LnExaminationCategory();

        $courseCategoryResult = $examinationCategoryModel->find(false)
            ->andFilterWhere(['in','company_id',$companyIdList])
            ->orderBy('created_at')
            ->all();

        return $courseCategoryResult;
    }

    public function GetExaminationCategoryCount($treeNodeId, $ListRouteParams = false){
        $model = LnExaminationCategory::find(false)
            ->andFilterWhere(['=','tree_node_id',$treeNodeId])
            ->select('kid,company_id')
            ->one();

        $categoryAll = $this->getSubCategories($model->kid);
        if (!empty($categoryAll)){
            $categoryAll = array_merge(array($model->kid), $categoryAll);
        }else{
            $categoryAll = array($model->kid);
        }

        $companyId = $model->company_id;
        $query = LnExamination::find(false)
            ->andFilterWhere(['in','category_id',$categoryAll])
            ->andFilterWhere(['=','company_id',$companyId]);
        if ($ListRouteParams){
            $query->andFilterWhere(['examination_range' => $ListRouteParams]);
            $query->andFilterWhere(['release_status' => LnExamination::STATUS_FLAG_NORMAL]);
        }
            return $query->count('kid');
    }

    /**
     * 根据树节点ID获取课程目录ID
     * @param $id
     * @return null|string
     */
    public function GetExaminationCategoryIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $examinationCategoryModel = new LnExaminationCategory();

            $examinationCategoryResult = $examinationCategoryModel->findOne(['tree_node_id' => $id]);

            if ($examinationCategoryResult != null)
            {
                $examinationCategoryId = $examinationCategoryResult->kid;
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
     * 根据树节点ID，删除相关课程目录ID
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        $model = new LnExaminationCategory();

        $kids = "";
        if (is_array($treeNodeId)) {
            foreach ($treeNodeId as $key) {
                $kids = $kids . "'" . $key . "',";

                $examinationCategoryKey = $this->GetExaminationCategoryIdByTreeNodeId($key);
                LnExaminationCategory::removeFromCacheByKid($examinationCategoryKey);
            }

            $kids = rtrim($kids, ",");
        }else{
            $kids = "'".$treeNodeId."'";

            $examinationCategoryKey = $this->GetExaminationCategoryIdByTreeNodeId($treeNodeId);
            LnExaminationCategory::removeFromCacheByKid($examinationCategoryKey);
        }

        $model->deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") . " in (".$kids.")");

        FwTreeNode::deleteAll(BaseActiveRecord::getQuoteColumnName("kid") . " in (".$kids.")");
    }


    /**
     * 激活父节点
     * @param $kid
     */
    public function ActiveParentNode($kid)
    {
        $model = LnExaminationCategory::findOne($kid);

        $parent_node_id = $model->parent_category_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = LnExaminationCategory::findOne($parent_node_id);

            if ($parentModel->status != LnExaminationCategory::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = LnExaminationCategory::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->ActiveParentNode($parentModel->kid);
            }
        }
    }

    /**
     * 根据树节点ID，更新上级课程目录信息
     * @param $treeNodeId
     * @param $targetTreeNodeId
     */
    public function updateParentIdByTreeNodeId($treeNodeId,$targetTreeNodeId)
    {
        $examinationCategoryId = $this->GetExaminationCategoryIdByTreeNodeId($treeNodeId);
        $targetCategroyId = $this->GetExaminationCategoryByCompanyIdList($targetTreeNodeId);
        if ($examinationCategoryId != null) {
            $examinationCategoryModel = LnExaminationCategory::findOne($examinationCategoryId);
            $examinationCategoryModel->parent_category_id = $targetCategroyId;
            $examinationCategoryModel->save();
        }
    }

    /**
     * 获取公司的所有课程目录列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetAllExaminationCategoryListByCompanyId($companyId = null)
    {
        $model = LnExaminationCategory::find(false);

        $query = $model
            ->joinWith('fwTreeNode')
            ->andFilterWhere(['company_id' => $companyId])
            ->andFilterWhere([FwTreeNode::tableName() . '.status' => FwTreeNode::STATUS_FLAG_NORMAL])
            ->addOrderBy([FwTreeNode::tableName() . '.display_number' => SORT_ASC])
            ->addOrderBy([FwTreeNode::tableName() . '.sequence_number' => SORT_ASC])
            ->all();

        return $query;
    }


    /**
     * 根据tree_node_id获取课程目录的子目录
     * @param $categories array
     * @return string
     */
    public function getCategoriesByTreeNode($tree_node_id){
        $categories = LnExaminationCategory::findAll(['tree_node_id'=>$tree_node_id],false);
        $result = [];
        foreach($categories as $val){
            $result[] = $val->kid;
            $result = array_merge($result,$this->getSubCategories($val->kid));
        }
        return $result;
    }

    /**
     * 获取课程目录的子目录
     * @param $categoryid
     * @return array
     */
    private function getSubCategories($categoryid){
        $categories = LnExaminationCategory::findAll(['parent_category_id'=>$categoryid],false);
        $result = [];
        foreach($categories as $val){
            $result[] = $val->kid;
            $result = array_merge($result,$this->getSubCategories($val->kid));
        }
        return $result;
    }


    /**
     * 课程目录选择框
     * @return array
     */
    public function ListExaminationCategroySelect()
    {
        $companyId=Yii::$app->user->identity->company_id;

        $categories = $this->GetAllExaminationCategoryListByCompanyId($companyId);
        $result = array();
        foreach ($categories as $k => $val) {
            if (!$val->parent_category_id) {
                $result[$val->kid] = $val->category_name;
                $result = array_merge($result, $this->getSubExaminationCategories($categories, $val->kid, '　'));
            }
        }
        return $result;
    }

    /**
     * 获取课程目录的子目录
     * @param $categories
     * @param $parentid
     * @param $tab
     * @return array
     */
    private function getSubExaminationCategories($categories,$parentid,$tab){
        $result = array();
        foreach ($categories as $k=>$val) {
            if ($parentid == $val->parent_category_id) {
                $result[$val->kid] = $tab.$val->category_name;
                $result = array_merge($result,$this->getSubExaminationCategories($categories,$val->kid,$tab.'　'));
            }
        }
        return $result;
    }

    /**
     * 创建目录企业考试临时目录
     * @param $companyId
     * @return mixed|string
     */
    public function getExaminationTempCategoryId($companyId){
        $examinationCategoryTemp = LnExaminationCategory::find(false)->andFilterWhere(['company_id' => $companyId,'category_name' => Yii::t('common', 'temp_category')])->one();
        if (empty($examinationCategoryTemp)){
            $treeNodeService = new TreeNodeService();
            $tree_node_id = $treeNodeService->addTreeNode('examination-category', Yii::t('common', 'temp_category'), "");
            $treeNode = FwTreeNode::findOne($tree_node_id);
            $examinationCategory = new LnExaminationCategory();
            $examinationCategory->tree_node_id = $tree_node_id;
            $examinationCategory->parent_category_id = null;
            $examinationCategory->company_id = $companyId;
            $examinationCategory->category_code = $treeNode->tree_node_code;
            $examinationCategory->category_name = Yii::t('common', 'temp_category');
            $examinationCategory->status = LnExaminationCategory::STATUS_FLAG_NORMAL;
            $examinationCategory->needReturnKey = true;
            $examinationCategory->save();
            $examinationCategoryId = $examinationCategory->kid;
        }else{
            $examinationCategoryId = $examinationCategoryTemp->kid;
        }
        return $examinationCategoryId;
    }

}