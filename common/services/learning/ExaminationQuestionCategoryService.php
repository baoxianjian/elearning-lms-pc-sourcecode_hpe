<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionCategory;
use common\models\learning\LnResourceDomain;
use common\models\treemanager\FwTreeNode;
use common\services\framework\TreeNodeService;
use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

class ExaminationQuestionCategoryService extends LnExamQuestionCategory{


    /**
     * 根据企业ID列表获取相关课程目录
     * @param $companyIdList
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetExaminationQuestionCategoryByCompanyIdList($companyIdList)
    {
        $examinationQuestionCategoryModel = new LnExamQuestionCategory();

        $courseCategoryResult = $examinationQuestionCategoryModel->find(false)
            ->andFilterWhere(['in','company_id',$companyIdList])
            ->orderBy('created_at')
            ->all();

        return $courseCategoryResult;
    }

    public function GetQuestionCategoryCount($treeNodeId){
        $model = LnExamQuestionCategory::find(false)
            ->andFilterWhere(['=','tree_node_id',$treeNodeId])
            ->one();

        $companyId = $model->company_id;
        return LnExaminationQuestion::find(false)
            ->andFilterWhere(['=','category_id',$model->kid])
            ->andFilterWhere(['=','company_id',$companyId])
            ->count('kid');
    }

    /**
     * 根据树节点ID获取课程目录ID
     * @param $id
     * @return null|string
     */
    public function GetExaminationQuestionCategoryIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $examinationQuestionCategoryModel = new LnExamQuestionCategory();

            $examinationQuestionCategoryResult = $examinationQuestionCategoryModel->findOne(['tree_node_id' => $id]);

            if ($examinationQuestionCategoryResult != null)
            {
                $examinationQuestionCategoryId = $examinationQuestionCategoryResult->kid;
            }
            else
            {
                $examinationQuestionCategoryId = null;
            }
        }
        else
        {
            $examinationQuestionCategoryId = null;
        }

        return $examinationQuestionCategoryId;
    }

    /**
     * 根据树节点ID，删除相关课程目录ID
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        $model = new LnExamQuestionCategory();

        $kids = "";
        if (is_array($treeNodeId)) {
            foreach ($treeNodeId as $key) {
                $kids = $kids . "'" . $key . "',";

                $examQuestionCategoryKey = $this->GetExaminationQuestionCategoryIdByTreeNodeId($key);
                LnExamQuestionCategory::removeFromCacheByKid($examQuestionCategoryKey);
            }
            $kids = rtrim($kids, ",");
        }else{
            $kids = "'".$treeNodeId."'";
            $examQuestionCategoryKey = $this->GetExaminationQuestionCategoryIdByTreeNodeId($treeNodeId);
            LnExamQuestionCategory::removeFromCacheByKid($examQuestionCategoryKey);
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
        $model = LnExamQuestionCategory::findOne($kid);

        $parent_node_id = $model->parent_category_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = LnExamQuestionCategory::findOne($parent_node_id);

            if ($parentModel->status != LnExamQuestionCategory::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = LnExamQuestionCategory::STATUS_FLAG_NORMAL;
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
        $examinationQuestionCategoryId = $this->GetExaminationQuestionCategoryIdByTreeNodeId($treeNodeId);
        $targetCategroyId = $this->GetExaminationQuestionCategoryByCompanyIdList($targetTreeNodeId);
        if ($examinationQuestionCategoryId != null) {
            $examinationQuestionCategoryModel = LnExamQuestionCategory::findOne($examinationQuestionCategoryId);
            $examinationQuestionCategoryModel->parent_category_id = $targetCategroyId;
            $examinationQuestionCategoryModel->save();
        }
    }

    /**
     * 获取公司的所有课程目录列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetAllExaminationQuestionCategoryListByCompanyId($companyId = null)
    {
        $model = LnExamQuestionCategory::find(false);

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
        $categories = LnExamQuestionCategory::findAll(['tree_node_id'=>$tree_node_id],false);
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
        $categories = LnExamQuestionCategory::findAll(['parent_category_id'=>$categoryid],false);
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
    public function ListExaminationQuestionCategroySelect()
    {
        $companyId=Yii::$app->user->identity->company_id;

        $categories = $this->GetAllExaminationQuestionCategoryListByCompanyId($companyId);
        $result = array();
        foreach ($categories as $k => $val) {
            if (!$val->parent_category_id) {
                $result[$val->kid] = $val->category_name;
                $result = array_merge($result, $this->getSubExaminationQuestionCategories($categories, $val->kid, '　'));
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
    private function getSubExaminationQuestionCategories($categories,$parentid,$tab){
        $result = array();
        foreach ($categories as $k=>$val) {
            if ($parentid == $val->parent_category_id) {
                $result[$val->kid] = $tab.$val->category_name;
                $result = array_merge($result,$this->getSubExaminationQuestionCategories($categories,$val->kid,$tab.'　'));
            }
        }
        return $result;
    }

    /**
     * 创建目录企业考试临时目录
     * @param $companyId
     * @return mixed|string
     */
    public function getExaminationQuestionTempCategoryId($companyId){
        $categoryTemp = LnExamQuestionCategory::find(false)->andFilterWhere(['company_id' => $companyId,'category_name' => Yii::t('common', 'temp_category')])->one();
        if (empty($categoryTemp)){
            $treeNodeService = new TreeNodeService();
            $tree_node_id = $treeNodeService->addTreeNode('examination-question-category', Yii::t('common', 'temp_category'), "");
            $treeNode = FwTreeNode::findOne($tree_node_id);
            $category = new LnExamQuestionCategory();
            $category->tree_node_id = $tree_node_id;
            $category->parent_category_id = null;
            $category->company_id = $companyId;
            $category->category_code = $treeNode->tree_node_code;
            $category->category_name = Yii::t('common', 'temp_category');
            $category->status = LnExamQuestionCategory::STATUS_FLAG_NORMAL;
            $category->needReturnKey = true;
            $category->save();
            $categoryId = $category->kid;
        }else{
            $categoryId = $categoryTemp->kid;
        }
        return $categoryId;
    }

}