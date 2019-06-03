<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/6/10
 * Time: 下午3:00
 */

namespace common\services\learning;


use common\helpers\TArrayHelper;
use common\models\framework\FwDomain;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCoursewareCategory;
use common\models\learning\LnCourseware;
use common\models\learning\LnFiles;
use common\models\learning\LnResourceDomain;
use common\models\learning\LnVendor;
use common\models\treemanager\FwTreeNode;
use common\services\framework\UserCompanyService;
use common\services\learning\CoursewareCategoryService;
use common\services\framework\UserDomainService;
use components\widgets\TPagination;
use common\helpers\TStringHelper;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class CoursewareService extends LnCourseware {

    /**
     * 获取多媒体资源
     * @param $params
     * @return ActiveDataProvider
     */
    public function Search($params,$queryAll = false, $selectComponent = false, $selectedResult = null)
    {
        $query = LnCourseware::find(false)
        ->andFilterWhere(['=','entry_mode',LnCourseware::ENTRY_MODE_UPLOAD]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $courseware_category_id = "";
        $domain = [];
        $domain_id = isset($params['domain_id']) && $params['domain_id'] ? $params['domain_id'] : "";
        if (empty($params['courseware_category_id']) && !empty($params['TreeNodeKid']) && $params['TreeNodeKid'] != '-1'){
            $coursewareCategoryService = new CoursewareCategoryService();
            $courseware_category_id = $coursewareCategoryService->getCoursewareCategoryIdByTreeNodeId($params['TreeNodeKid']);
        }
        if (isset($params['courseware_type'])){
            $query->andFilterWhere(['courseware_type'=>$params['courseware_type']]);
        }
        if (isset($params['entry_mode'])){
             $query->andFilterWhere(['entry_mode'=>$params['entry_mode']]);
        }

        if (!empty($params['companyId'])){
            $query->andFilterWhere(['=', LnCourseware::tableName().'.company_id', $params['companyId']]);
        }

        $this->component_id = isset($params['component_id']) && $params['component_id'] ? $params['component_id'] : null;
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $userId = \Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        if ($selectComponent){/*1、组件查询*/
            $domain = explode(',', $domain_id);
            //添加共享域的查询
            $shareDomain = $userDomainService->getSearchListByUserId($userId,self::STATUS_FLAG_NORMAL,true, true);
            $shareDomain_arr = false;
            if ($shareDomain) {
                $shareDomainList = ArrayHelper::map($shareDomain, 'kid', 'kid');
                $shareDomain_arr = array_keys($shareDomainList);
            }
            if ($domain && $shareDomain_arr){
                $domain = array_merge($domain, $shareDomain_arr);
                $domain = array_unique($domain);
            }else if (empty($domain) && $shareDomain_arr){
                $domain = $shareDomain_arr;
            }
        }else if (!$selectComponent && !empty($domain_id)){/*2、选择目录查询*/
            $domain = array($domain_id);
        }else{
            if (isset($selectedResult) && $selectedResult != null) {/*3、列表查询*/
                $selectedList = ArrayHelper::map($selectedResult, 'kid', 'kid');
                $domain = array_keys($selectedList);
            }
        }

        if (!empty($domain)){
            $query->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in',LnResourceDomain::tableName().'.domain_id', $domain])
                ->distinct();
        }
        else {
            $query->andWhere('kid is null');
        }

        if (!empty($this->courseware_name)) {
            $keywords = TStringHelper::clean_xss($this->courseware_name);
            $query->leftJoin(LnFiles::tableName() .' as f', "f.kid = ".LnCourseware::tableName() .".file_id and f.is_deleted='0'");
            $query->andWhere("courseware_code like '%{$keywords}%' OR courseware_name like '%{$keywords}%' OR vendor like '%{$keywords}%' or f.file_name like '%{$keywords}%'");
        }
        if($this->component_id){
            $query->andFilterWhere(['=', LnCourseware::tableName().'.component_id',  trim(urldecode($this->component_id))]);
        }

        if (!empty($courseware_category_id)){
            $query->andFilterWhere(['=',  LnCourseware::tableName().'.courseware_category_id', $courseware_category_id]);
        }
        $dataProvider->setSort(false);
        $query->addOrderBy([LnCourseware::tableName().'.created_at' => SORT_DESC]);

        if($queryAll){
            $start_at = strtotime(date('Y-m-d'));
            $query->andWhere("ISNULL(" . LnCourseware::tableName().".start_at) OR " . LnCourseware::tableName().".start_at<={$start_at}");
            $query->andWhere("ISNULL(" . LnCourseware::tableName().".end_at) OR " . LnCourseware::tableName().".end_at>{$start_at}");
            return $dataProvider/*$query->all()*/;
        }else{
//            echo $query->createCommand()->getRawSql();
            return $dataProvider;
        }
    }
    /**
     * 课程组件选择课件列表
     * @param $params
     * @return ActiveDataProvider
     */
    public function GetCourseware($params)
    {
        $query = LnCourseware::find(false)
            ->andFilterWhere(['=','entry_mode',LnCourseware::ENTRY_MODE_UPLOAD]);
        $userId = \Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        $domainList = $userDomainService->getManagedListByUserId($userId);
        $domain = [];
        if ($domainList){
            foreach ($domainList as $t){
                $domain[] = $t->kid;
            }
        }

        if (!empty($params['companyId'])){
            $query->andFilterWhere(['=', LnCourseware::tableName().'.company_id', $params['companyId']]);
        }

        if (isset($params['is_display_mobile']) && $params['is_display_mobile'] == LnCourseware::DISPLAY_MOBILE_YES) {
                $query->andFilterWhere(['=', "is_display_mobile", LnCourseware::DISPLAY_MOBILE_YES]);
                $query->andFilterWhere(['=', "is_display_pc", LnCourseware::DISPLAY_PC_YES]);
        }

        if (isset($params['is_display_pc']) && $params['is_display_pc'] == LnCourseware::DISPLAY_PC_YES) {
                $query->andFilterWhere(['=', "is_display_pc", LnCourseware::DISPLAY_PC_YES]);
        }

        if (isset($params['courseware_type'])){
            $query->andFilterWhere(['courseware_type'=>$params['courseware_type']]);
        }
        $component_id = isset($params['component_id']) && $params['component_id'] ? $params['component_id'] : null;
        $userId = \Yii::$app->user->getId();
        $userDomainService = new UserDomainService();
        //添加共享域的查询
        $shareDomain = $userDomainService->getSearchListByUserId($userId, self::STATUS_FLAG_NORMAL, true, true);
        $shareDomain_arr = false;
        if ($shareDomain) {
            $shareDomainList = ArrayHelper::map($shareDomain, 'kid', 'kid');
            $shareDomain_arr = array_keys($shareDomainList);
        }
        if ($domain && $shareDomain_arr){
            $domain = array_merge($domain, $shareDomain_arr);
            $domain = array_unique($domain);
        }else if (empty($domain) && $shareDomain_arr){
            $domain = $shareDomain_arr;
        }

        $domain = array_filter($domain);
        if (!empty($domain)){
            $query->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in',LnResourceDomain::tableName().'.domain_id', $domain])
                ->distinct();
        }
        else {
            $query->andWhere('kid is null');
        }

        if (!empty($params['courseware_name'])) {
            $keywords = trim(urldecode($params['courseware_name']));
            $query->andWhere("courseware_code like '%{$keywords}%' OR courseware_name like '%{$keywords}%' OR vendor like '%{$keywords}%'");
        }
        if($component_id){
            $query->andFilterWhere(['=', 'component_id', $component_id]);
        }

        if (!empty($params['courseware_category_id'])){
            $query->andFilterWhere(['=', 'courseware_category_id', $params['courseware_category_id']]);
        }
        $query->addOrderBy([LnCourseware::tableName().'.created_at' => SORT_DESC]);

        $start_at = strtotime(date('Y-m-d'));
        $query->andWhere("ISNULL(" . LnCourseware::tableName().".start_at) OR " . LnCourseware::tableName().".start_at<={$start_at}");
        $query->andWhere("ISNULL(" . LnCourseware::tableName().".end_at) OR " . LnCourseware::tableName().".end_at>{$start_at}");

        $count = $query->count();
        $pages = new TPagination(['defaultPageSize' => $params['defaultPageSize'], 'totalCount' => $count]);
        $data = $query->offset($pages->offset)->limit($pages->limit)->all();
        $dataProvider = array(
            'pages' => $pages,
            'data' => $data,
        );
        return $dataProvider;

    }

    /**
     * 查询供应商返回json
     * @param $vendorName
     * @return array|bool|string
     */
    public function getVendorByName($vendorName, $vendorId = null){
        if (empty($vendorName)) return false;
        if (!empty($vendorId)){
            $model = LnVendor::findOne($vendorId);
            if (empty($model)){
                $result = array('results' => array(array('kid' => "", 'title' => urlencode($vendorName))));
                $result = urldecode(json_encode($result));
                return $result;
            }
            $title = urlencode($model->vendor_name).(!empty($model->vendor_code) ? '('.$model->vendor_code.')' : '');
            $result = array('results' => array(array('kid' => $model->kid, 'title' => $title)));
            $result = urldecode(json_encode($result));
            return $result;
        }else{
            if (!empty($vendorName)){
                $result = array('results' => array(array('kid' => "", 'title' => urlencode($vendorName))));
                $result = urldecode(json_encode($result));
                return $result;
            }else {
                return false;
            }
        }
    }

    /**
     * 课件查询供应商
     * @param $keyword
     * @param $companyId
     * @return ActiveDataProvider
     */
    public function getCoursewareVendor($keyword, $companyId = null){
        if (empty($keyword)) return false;
        $keyword = htmlspecialchars($keyword);
        if (empty($companyId)) $companyId = Yii::$app->user->identity->company_id;
        $model = LnVendor::find(false);
        $result = $model->andWhere("`vendor_name` like '%{$keyword}%' or `vendor_code` like '%{$keyword}%'")
            ->andFilterWhere(['=', 'company_id', $companyId])
            ->andFilterWhere(['=', 'status', LnVendor::STATUS_FLAG_NORMAL])
            ->select('kid,vendor_name,vendor_code')
            ->asArray()
            ->all();

        if (empty($result)){
            return false;
        }
        return $result;
    }

    /**
     * 检测课件是否重复
     * @param $coursewareName
     * @param $userId
     * @return array
     */
    public function checkCoursewareName($coursewareName, $userId, $kid = false){
        /*获取用户可管理的公司*/
        $userCompanyService = new UserCompanyService();
        $companyList = $userCompanyService->getManagedListByUserId($userId);
        $c_l = TArrayHelper::map($companyList, 'kid', 'kid');
        $model = LnCourseware::find(false)
            ->andFilterWhere(['=', 'courseware_name', $coursewareName])
            ->andFilterWhere(['in', 'company_id', $c_l]);
        if ($kid){
            $model->andFilterWhere(['<>', 'kid', $kid]);
        }
           $count =  $model->count(1);

        if ($count > 0) {
            return ['result' => 'fail'];
        }else{
            return ['result' => 'success'];
        }
    }

}