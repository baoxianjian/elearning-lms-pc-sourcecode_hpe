<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareCategory;
use common\models\learning\LnResourceDomain;
use common\models\treemanager\FwTreeNode;
use common\services\framework\UserDomainService;
use common\base\BaseActiveRecord;
use Yii;

class CoursewareCategoryService extends LnCoursewareCategory{

    /**
     * 根据企业ID列表获取相关课件目录
     * @param $companyIdList
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCoursewareCategoryByCompanyIdList($companyIdList)
    {
        $coursewareCategoryModel = new LnCoursewareCategory();

        $coursewareCategoryResult = $coursewareCategoryModel->find(false)
            ->andFilterWhere(['in','company_id',$companyIdList])
            ->orderBy('created_at')
            ->all();

        return $coursewareCategoryResult;
    }

    /**
     * 根据树节点ID获取课件目录ID
     * @param $id
     * @return null|string
     */
    public function getCoursewareCategoryIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $CoursewareCategoryModel = new LnCoursewareCategory();

            $CoursewareCategoryResult = $CoursewareCategoryModel->findOne(['tree_node_id' => $id]);

            if ($CoursewareCategoryResult != null)
            {
                $CoursewareCategoryId = $CoursewareCategoryResult->kid;
            }
            else
            {
                $CoursewareCategoryId = null;
            }
        }
        else
        {
            $CoursewareCategoryId = null;
        }

        return $CoursewareCategoryId;
    }

    /**
     * 根据树节点ID，删除相关课件目录ID
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        $model = new LnCoursewareCategory();
        $kids = "";
        if (is_array($treeNodeId)) {
            foreach ($treeNodeId as $key) {
                $kids = $kids . "'" . $key . "',";
                $coursewareCategoryKey = $this->getCoursewareCategoryIdByTreeNodeId($key);
                LnCoursewareCategory::removeFromCacheByKid($coursewareCategoryKey);
            }

            $kids = rtrim($kids, ",");
        }else{
            $kids = "'".$treeNodeId."'";
            $coursewareCategoryKey = $this->getCoursewareCategoryIdByTreeNodeId($treeNodeId);
            LnCoursewareCategory::removeFromCacheByKid($coursewareCategoryKey);
        }

        $model->deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") . " in (".$kids.")");
    }


    /**
     * 激活父节点
     * @param $kid
     */
    public function activeParentNode($kid)
    {
        $model = LnCoursewareCategory::findOne($kid);

        $parent_node_id = $model->parent_category_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = LnCoursewareCategory::findOne($parent_node_id);

            if ($parentModel->status != LnCoursewareCategory::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = LnCoursewareCategory::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->activeParentNode($parentModel->kid);
            }
        }
    }

    /**
     * 根据树节点ID，更新上级课件目录信息
     * @param $treeNodeId
     * @param $targetTreeNodeId
     */
    public function updateParentIdByTreeNodeId($treeNodeId,$targetTreeNodeId)
    {
        $CoursewareCategoryId = $this->getCoursewareCategoryIdByTreeNodeId($treeNodeId);
        $targetCategroyId = $this->getCoursewareCategoryIdByTreeNodeId($targetTreeNodeId);
        if ($CoursewareCategoryId != null) {
            $CoursewareCategoryModel = LnCoursewareCategory::findOne($CoursewareCategoryId);
            $CoursewareCategoryModel->parent_category_id = $targetCategroyId;

            $CoursewareCategoryModel->save();
        }
    }


    /**
     * 获取公司的所有课件目录列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetAllCoursewareCategoryListByCompanyId($companyId = null){
//        if ($companyId == null){
//            return [];
//        }

        $model = LnCoursewareCategory::find(false);

        $query = $model
            ->joinWith('fwTreeNode')
            ->andFilterWhere(['company_id'=>$companyId])
            ->andFilterWhere([FwTreeNode::tableName().'.status' => FwTreeNode::STATUS_FLAG_NORMAL])
            ->addOrderBy([FwTreeNode::tableName().'.display_number' => SORT_ASC])
            ->addOrderBy([FwTreeNode::tableName().'.sequence_number' => SORT_ASC])
            ->all();

        return $query;
    }


    /**
     * 根据tree_node_id获取课件目录的子目录
     * @param $categories array
     * @return string
     */
    public function getCategoriesByTreeNode($tree_node_id){
        $categories = LnCoursewareCategory::findAll(['tree_node_id'=>$tree_node_id],false);
        $result = [];
        foreach($categories as $val){
            $result[] = $val->kid;
            $result = array_merge($result,$this->getSubCategories($val->kid));
        }
        return $result;
    }

    /**
     * 获取课件目录的子目录
     * @param $categoryid
     * @return array
     */
    private function getSubCategories($categoryid){
        $categories = LnCoursewareCategory::findAll(['parent_category_id'=>$categoryid],false);
        $result = [];
        foreach($categories as $val){
            $result[] = $val->kid;
            $result = array_merge($result,$this->getSubCategories($val->kid));
        }
        return $result;
    }


    /**
     * 课件目录选择框
     * @return array
     */
    public function ListCoursewareCategroySelect(){
        $categories = $this->GetAllCoursewareCategoryListByCompanyId();
        $result = array();
        foreach ($categories as $k=>$val) {
            if (!$val->parent_category_id) {
                $result[$val->kid] = $val->category_name;
                $result = array_merge($result,$this->getSubCoursewareCategries($categories,$val->kid,'　'));
            }
        }
        return $result;
    }

    /**
     * 获取课件目录的子目录
     * @param $categories
     * @param $parentid
     * @param $tab
     * @return array
     */
    private function getSubCoursewareCategries($categories,$parentid,$tab){
        $result = array();
        foreach ($categories as $k=>$val) {
            if ($parentid == $val->parent_category_id) {
                $result[$val->kid] = $tab.$val->category_name;
                $result = array_merge($result,$this->getSubCoursewareCategries($categories,$val->kid,$tab.'　'));
            }
        }
        return $result;
    }

	
    /*获取目录课件数*/
    public function getCoursewareCategoryCourse($categoryId){
        $model = new LnCourseware();
        $all = $model->findAll(['category_id'=>$categoryId], false);
        return $all;
    }

    public function GetCategoryCount($treeNodeId, $ListRouteParams = false){
        $model = LnCoursewareCategory::find(false)
            ->andFilterWhere(['=','tree_node_id',$treeNodeId])
            ->one();
        $categoryAll = $this->getSubCategories($model->kid);
        if (!empty($categoryAll)){
            $categoryAll = array_merge(array($model->kid), $categoryAll);
        }else{
            $categoryAll = array($model->kid);
        }
//        $query = LnCourseware::find(false)->andFilterWhere(['=','courseware_category_id',$model->kid]);
        $query = LnCourseware::find(false)->andFilterWhere(['in','courseware_category_id',$categoryAll]);
        $userId = \Yii::$app->user->getId();
        $userDomain = new UserDomainService();
        $GetSearchListByUserId = $userDomain->GetManagedListByUserId($userId);
        $domain = array();
        if ($GetSearchListByUserId) {
            foreach ($GetSearchListByUserId as $t) {
                $domain[] = $t->kid;
            }
        }
        if (!empty($domain)) {
            $query->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in', LnResourceDomain::tableName() . '.domain_id', $domain])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.resource_type', LnResourceDomain::RESOURCE_TYPE_COURSEWARE])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.status', LnResourceDomain::STATUS_FLAG_NORMAL])
                ->distinct();
        }
        if ($ListRouteParams!==false){
            $query->andFilterWhere(['courseware_code' => $ListRouteParams]);
            //$query->andFilterWhere(['status' => LnCourseware::STATUS_FLAG_NORMAL]);
        }
        return $query->count();
    }

    /**
     * 当前节点是否包含课件
     * @param $kid
     * @return bool
     */
    public function isExistCourseware($kid)
    {
        $query = LnCourseware::find(false);

        $query->andFilterWhere(['=', 'courseware_category_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
}