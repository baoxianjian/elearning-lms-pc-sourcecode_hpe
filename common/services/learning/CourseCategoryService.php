<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 3/21/2015
 * Time: 10:57 AM
 */
namespace common\services\learning;

use common\models\learning\LnCourse;
use common\models\learning\LnCourseCategory;
use common\models\learning\LnResourceDomain;
use common\models\treemanager\FwTreeNode;
use common\services\framework\UserDomainService;
use common\base\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class CourseCategoryService extends LnCourseCategory{


    /**
     * 根据企业ID列表获取相关课程目录
     * @param $companyIdList
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCourseCategoryByCompanyIdList($companyIdList)
    {
        $courseCategoryModel = new LnCourseCategory();

        $courseCategoryResult = $courseCategoryModel->find(false)
            ->andFilterWhere(['in','company_id',$companyIdList])
            ->orderBy('created_at')
            ->all();

        return $courseCategoryResult;
    }

    /**
     * 根据树节点ID获取课程目录ID
     * @param $id
     * @return null|string
     */
    public function getCourseCategoryIdByTreeNodeId($id)
    {
        if ($id != null && $id != "") {
            $courseCategoryModel = new LnCourseCategory();

            $courseCategoryResult = $courseCategoryModel->findOne(['tree_node_id' => $id]);

            if ($courseCategoryResult != null)
            {
                $courseCategoryId = $courseCategoryResult->kid;
            }
            else
            {
                $courseCategoryId = null;
            }
        }
        else
        {
            $courseCategoryId = null;
        }

        return $courseCategoryId;
    }

    /**
     * 根据树节点ID，删除相关课程目录ID
     * @param $treeNodeId
     */
    public function deleteRelateData($treeNodeId)
    {
        if (empty($treeNodeId)) return false;
        $model = new LnCourseCategory();

        $kids = "";
        if (is_array($treeNodeId)) {
            foreach ($treeNodeId as $key) {
                $kids = $kids . "'" . $key . "',";
                $courseCategoryKey = $this->getCourseCategoryIdByTreeNodeId($key);
                LnCourseCategory::removeFromCacheByKid($courseCategoryKey);
            }

            $kids = rtrim($kids, ",");
        }else{
            $kids = "'".$treeNodeId."'";
            $courseCategoryKey = $this->getCourseCategoryIdByTreeNodeId($treeNodeId);
            LnCourseCategory::removeFromCacheByKid($courseCategoryKey);
        }

        $model->deleteAll(BaseActiveRecord::getQuoteColumnName("tree_node_id") . " in (".$kids.")");
    }


    /**
     * 激活父节点
     * @param $kid
     */
    public function activeParentNode($kid)
    {
        $model = LnCourseCategory::findOne($kid);

        $parent_node_id = $model->parent_category_id;

        if ($parent_node_id != null && $parent_node_id != "")
        {
            $parentModel = LnCourseCategory::findOne($parent_node_id);

            if ($parentModel->status != LnCourseCategory::STATUS_FLAG_NORMAL)
            {
                $parentModel->status = LnCourseCategory::STATUS_FLAG_NORMAL;
                $parentModel->needReturnKey = true;
                $parentModel->save();

                $this->activeParentNode($parentModel->kid);
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
        $courseCategoryId = $this->getCourseCategoryIdByTreeNodeId($treeNodeId);
        $targetCategroyId = $this->getCourseCategoryIdByTreeNodeId($targetTreeNodeId);
        if ($courseCategoryId != null) {
            $courseCategoryModel = LnCourseCategory::findOne($courseCategoryId);
            $courseCategoryModel->parent_category_id = $targetCategroyId;

            $courseCategoryModel->save();
        }
    }


    /**
     * 获取公司的所有课程目录列表
     * @param $companyId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetAllCourseCategoryListByCompanyId($companyId = null)
    {
        $model = LnCourseCategory::find(false);

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
        $categories = LnCourseCategory::findAll(['tree_node_id'=>$tree_node_id]);
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
        $categories = LnCourseCategory::findAll(['parent_category_id'=>$categoryid],false);
        $result = [];
        if (!empty($categories)) {
            foreach ($categories as $val) {
                $result[] = $val->kid;
                $result = array_merge($result, $this->getSubCategories($val->kid));
            }
        }
        return $result;
    }


    /**
     * 课程目录选择框
     * @return array
     */
    public function ListCourseCategroySelect($companyId = null)
    {
        if (empty($companyId)) {
            $companyId = Yii::$app->user->identity->company_id;
        }

        $categories = $this->GetAllCourseCategoryListByCompanyId($companyId);
        $result = array();
        foreach ($categories as $k => $val) {
            if (!$val->parent_category_id) {
                $result[$val->kid] = $val->category_name;
                $result = array_merge($result, $this->getSubCourseCategries($categories, $val->kid, '　', 1));
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
    private function getSubCourseCategries($categories,$parentid,$tab, $deep){
        $result = array();
        for ($i = 0; $i < $deep; $i ++){
            $tab .= $tab;
        }
        foreach ($categories as $k=>$val) {
            if ($parentid == $val->parent_category_id) {
                $result[$val->kid] = $tab.$val->category_name;
                $result = array_merge($result,$this->getSubCourseCategries($categories,$val->kid,$tab, $deep++));
            }
        }
        return $result;
    }

	
    /*获取目录课程数*/
    public function getCourseCategoryCourse($categoryId,$isMobile = false)
    {
        $currentTime = time();

        $user_id = Yii::$app->user->getId();

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($user_id);

        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

            $domainIds = array_keys($domainIds);
        }

        $domainQuery = LnResourceDomain::find(false);
        $domainQuery->select('resource_id')
            ->andFilterWhere(['in', 'domain_id', $domainIds])
            ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
            ->distinct();

        $domainQuerySql = $domainQuery->createCommand()->rawSql;

        $courseQuery = LnCourse::find(false);
        $courseQuery
            ->andWhere('kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);

        if ($isMobile)
        {
            $courseQuery->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
        }
        else {
            $courseQuery->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
        }



        $courseQuery->andFilterWhere(['=', 'category_id', $categoryId]);

        $result = $courseQuery->all();

        return $result;
    }

    public function GetCategoryCount($treeNodeId, $ListRouteParams = false){
        $model = LnCourseCategory::find(false)
            ->andFilterWhere(['=','tree_node_id',$treeNodeId])
            ->one();

        $categoryAll = $this->getSubCategories($model->kid);
        if (!empty($categoryAll)){
            $categoryAll = array_merge(array($model->kid), $categoryAll);
        }else{
            $categoryAll = array($model->kid);
        }
        $query = LnCourse::find(false)->andFilterWhere(['in','category_id',$categoryAll]);
        $userId = \Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        $GetSearchListByUserId = $userDomainService->getManagedListByUserId($userId);
        $domain = array();
        if ($GetSearchListByUserId) {
            foreach ($GetSearchListByUserId as $t) {
                $domain[] = $t->kid;
            }
        }
        if (!empty($domain)) {
            $query->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in', LnResourceDomain::tableName() . '.domain_id', $domain])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.status', LnResourceDomain::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
                ->distinct();
        }
		if ($ListRouteParams!==false){
            $query->andFilterWhere(['course_type' => $ListRouteParams]);
        }
        return $query->count();
    }

    public function GetAllCategory($isMobile, $user_id = null, $companyId = null)
    {
        $currentTime = time();

        $uid = $user_id == null ? Yii::$app->user->getId() : $user_id;
        $company_id = $companyId == null ? Yii::$app->user->identity->company_id : $companyId;

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($uid);

        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');
            $domainIds = array_keys($domainIds);
        }

        $cacheKey = implode('-', $domainIds);

        $cacheKey = 'Category-' . md5($cacheKey) . '-' . $uid;

        $result = Yii::$app->cache->get($cacheKey);

        if ($result == false || empty($result)) {
            $domainQuery = LnResourceDomain::find(false);
            $domainQuery->select('resource_id')
                ->andFilterWhere(['in', LnResourceDomain::tableName() . '.domain_id', $domainIds])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.status', LnResourceDomain::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', LnResourceDomain::tableName() . '.resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
                ->distinct();

            $domainQuerySql = $domainQuery->createCommand()->rawSql;

            $query = LnCourseCategory::find(false);

            $query = $query
                ->innerJoin(FwTreeNode::tableName() . ' tree', LnCourseCategory::tableName() . '.tree_node_id = tree.kid and tree.is_deleted = \'0\'')
                ->andFilterWhere([LnCourseCategory::tableName() . '.company_id' => $company_id])
                ->andFilterWhere(['tree.status' => FwTreeNode::STATUS_FLAG_NORMAL])
                ->addOrderBy(['tree.display_number' => SORT_ASC])
                ->addOrderBy(['tree.sequence_number' => SORT_ASC]);

            $tableName = LnCourse::tableName();
            // 按课程受众过滤
            $audienceFilterSql = "NOT EXISTS(SELECT kid FROM eln_ln_resource_audience WHERE `status`='1' and resource_type='1' and resource_id=$tableName.kid and company_id='$company_id') OR" .
                " EXISTS(SELECT sam.kid FROM eln_ln_resource_audience ra INNER JOIN eln_so_audience sa ON ra.`status`='1' and ra.resource_type='1' and ra.company_id='$company_id' and ra.audience_id=sa.kid " .
                "and ra.is_deleted='0' and sa.`status`='1' and sa.company_id='$company_id' and sa.is_deleted='0' LEFT JOIN eln_so_audience_member sam ON sa.kid=sam.audience_id and sa.is_deleted='0' and sam.is_deleted='0' " .
                "WHERE $tableName.kid=ra.resource_id AND sam.user_id='$uid')";

            $queryCourse = LnCourse::find(false)
                ->andWhere($audienceFilterSql)
                ->andWhere(LnCourse::tableName() . '.kid in (' . $domainQuerySql . ') and ' . LnCourse::tableName() . '.category_id = ' . LnCourseCategory::tableName() . '.kid')
                ->andFilterWhere(['=', LnCourse::tableName() . '.status', LnCourse::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
                ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);

            if ($isMobile) {
                $queryCourse->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
            } else {
                $queryCourse->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
            }

            $course_count_sql = $queryCourse->select('count(*)')->createCommand()->rawSql;

//        var_dump($query->select(LnCourseCategory::tableName() . ".*, ($course_count_sql) as num")->createCommand()->rawSql);
//        $result = $query->select(LnCourseCategory::tableName() . '.*, COUNT(c.kid) as num')
            $result = $query->select([LnCourseCategory::tableName() . '.*', 'course_count' => "($course_count_sql)", 'node_code_path' => 'tree.node_code_path'])
//            ->asArray()
                ->all();

            Yii::$app->cache->set($cacheKey, $result, 60 * 60 * 10);
        }

        return $result;
    }

    public function BuildCategoryTree($data, $parent_category_id = null, $level = 0)
    {
        $count = 0;

        $i = 0;

        if ($parent_category_id === null) {
            $content = '<ul class="hotCourse">';
            $content .= '<li><input id="allCatalog" type="checkbox" value="0" checked>全部分类<span class="pull-right">@@@all_count@@@</span></li>';
        } else {
            $content = '<ul>';
        }

        $flag = false;

        foreach ($data as $item) {
            if ($parent_category_id === $item->parent_category_id) {
                $flag = true;
                $content .= '<li><input type="checkbox" value="' . $item->kid . '" class="category_id"  data-parent="' . $item->node_code_path . '" data-path="' . $item->node_code_path . $item->category_code . '/">';
                $content .= Html::encode($item->category_name);

//                $result[$i]['cate'] = $item;
                $childs = $this->BuildCategoryTree($data, $item->kid, $level + 1);
                if (!empty($childs['data'])) {
//                    $result[$i]['childs'] = $childs['data'];
//                    $result[$i]['count'] = $item->course_count + $childs['count'];
                    $temp = $item->course_count + $childs['count'];
                    $count += $item->course_count + $childs['count'];
                } else {
//                    $result[$i]['childs'] = null;
//                    $result[$i]['count'] = $item->course_count;
                    $temp = $item->course_count;
                    $count += $item->course_count;
                }
                $content .= '<span class="pull-right">' . $temp . '</span>';

//                $result[$i]['cate'] = $item;
//                $childs = $this->BuildCategoryTree($data, $item->kid, $level + 1);
                $content .= $childs['data'];
                $content .= '</li>';
                $i++;
            }
        }
        $content .= '</ul>';

        return ['count' => $count, 'data' => $flag ? $content : ''];
//        return $content;
    }

    /**
     * 当前节点是否包含课程
     * @param $kid
     * @return bool
     */
    public function isExistCourse($kid)
    {
        $query = LnCourse::find(false);

        $query->andFilterWhere(['=', 'category_id', $kid]);

        $count = $query->count(1);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
}