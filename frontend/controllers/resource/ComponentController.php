<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/12/4
 * Time: 15:23
 */

namespace frontend\controllers\resource;

use common\models\learning\LnComponent;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareBook;
use common\models\learning\LnExamination;
use common\models\learning\LnFiles;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\services\learning\CoursewareCategoryService;
use common\services\learning\CoursewareService;
use common\services\learning\ComponentService;
use common\services\learning\ExaminationService;
use common\services\learning\ResourceService;
use common\services\learning\CoursewareBookService;
use components\widgets\TPagination;
use frontend\base\BaseFrontController;
use stdClass;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use common\services\learning\InvestigationService;


class ComponentController extends BaseFrontController{
	
    /**
     * 组件选择
     * @return string
     */
    public function actionGetComponent(){
        $service = new ResourceService();
        $resources = $service->getComponents(LnComponent::RESOURCE_CODE, false);
        $actives = $service->getComponents(LnComponent::ACTIVE_CODE, false);
        return $this->renderAjax('get_component', [
            'resources' => $resources,
            'activity' => $actives,
        ]);
    }
    /**
     * 课件列表
     * @return string
     */
    public function actionCourseware()
    {
        $service = new CoursewareService();
        $sequence_number = Yii::$app->request->getQueryParam('sequence_number');
        $component_id = Yii::$app->request->getQueryParam('component_id');
        $domain_id = Yii::$app->request->getQueryParam('domain_id');
        $params = Yii::$app->request->queryParams;
        $pageSize = $this->defaultPageSize;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }
        $params['defaultPageSize'] = $pageSize;
        $dataProvider = $service->GetCourseware($params);
        $componentService = new ComponentService();
        $is_setting_component = $componentService->getRecordScore();
        return $this->renderAjax('courseware', [
            'dataProvider' => $dataProvider,
            'sequence_number' => $sequence_number,
            'component_id' => $component_id,
            'searchModel' => $service,
            'domain_id' => $domain_id,
            'params' => $params,
        	'is_setting_component' => $is_setting_component,
        ]);
    }


    /**
     * 考试
     * @return string
     */
    public function actionExamination(){
        $params = Yii::$app->request->getQueryParams();
        $pageSize = $this->defaultPageSize;
        return $this->renderAjax('examination',[
            'params' => $params,
            'pageSize' => $pageSize,
        ]);
    }

    public function actionExaminationPage(){

        $examQuestionService = new ExaminationService();
        $params = Yii::$app->request->getQueryParams();
        $pageSize = $this->defaultPageSize;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }
        $params['release_status'] = LnExamination::STATUS_FLAG_NORMAL;
        $params['examination_range'] = LnExamination::EXAMINATION_RANGE_COURSE;
        $params['defaultPageSize'] = $pageSize;
        $dataProvider = $examQuestionService->search($params, true);

        $category = $examQuestionService->GetExaminationCategory();

        return $this->renderAjax('examination_page',[
            'params' => $params,
            'dataProvider' => $dataProvider,
            'category' => $category,
        ]);
    }

    /**
     * 调查
     * @return string
     */
    public function actionInvestigation(){
        $component_code = Yii::$app->request->get('component_code');
        $params = Yii::$app->request->queryParams;
        $service = new InvestigationService();
        if ($component_code == 'investigation'){
            $params['investigation_range'] = '1';
        }
        $params['status'] = InvestigationService::STATUS_FORMAL;
        $pageSize = $this->defaultPageSize;
        $params['defaultPageSize'] = $pageSize;
        $dataProvider = $service->search($params, true);
        $service = new ComponentService();
        $is_setting_component = $service->getRecordScore();
        return $this->renderAjax('investigation', [
            'dataProvider' => $dataProvider,
            'searchModel' => $service,
            'params' => $params,
        	'is_setting_component' => $is_setting_component,
        ]);
    }

    /**
     * @param null $id
     * @return string
     */
    public function actionBook($id = null){
        $service = new ComponentService();
        $tempbook = $service->getBookByCoursewareId($id);
        $model = !empty($id) ?$tempbook:new LnCoursewareBook();
        $params = Yii::$app->request->getQueryParams();
        $component = $service->getCompoentByComponentKid($params['component_id']);
        $is_setting_component = $service->getRecordScore();
        return $this->renderAjax('book', [
            'result' => $model->attributes,
            'params' => $params,
            'component' => $component,
        	'is_setting_component' => $is_setting_component,
        ]);
    }

    /**
     * html组件
     * @param null $id
     * @return string
     */
    public function actionHtml($id = null){
        $service = new ComponentService();

        $params = Yii::$app->request->getQueryParams();
        $model = !empty($id) ? LnCourseware::findOne($id) : new LnCourseware();
        $component = $service->getCompoentByComponentKid($params['component_id']);
        
        $is_setting_component = $service->getRecordScore();
        return $this->renderAjax('html', [
            'params' => $params,
            'result'=> $model->attributes,
            'component' => $component,
        	'is_setting_component' => $is_setting_component,
        ]);
    }
    public function actionFileHtml(){
        $compomentservice = new ComponentService();
        $service = new CoursewareService();
        $sequence_number = Yii::$app->request->getQueryParam('sequence_number');
        $component_id = Yii::$app->request->getQueryParam('component_id');
        $domain_id = Yii::$app->request->getQueryParam('domain_id');
        $courseware_type = Yii::$app->request->getQueryParam('courseware_type');
        $entry_mode = Yii::$app->request->getQueryParam('entry_mode');
        $courseware_name = Yii::$app->request->get('CoursewareService');
        $params = Yii::$app->request->getQueryParams();

        $pageSize = 10;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }
        $dataProvider = $service->Search(Yii::$app->request->queryParams, true, true);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);
        $component = $compomentservice->getCompoentByComponentKid($params['component_id']);

        return $this->renderAjax('filehtml-list', [
            'params' => $params,
            'coursewares' => $dataProvider,
            'sequence_number' => $sequence_number,
            'component' =>$component, 
            'component_id' => $component_id,
            'searchModel' => $service,
            'domain_id' => $domain_id,
            'courseware_type'=> $courseware_type,
            'entry_mode'=>$entry_mode,
            'courseware_name' => $courseware_name['courseware_name'],
        ]);
    }
    public function  actionHomework($id = ''){
        $params = Yii::$app->request->getQueryParams();
        $uploadBatch = date("YmdHis");
        $model = !empty($id) ? LnHomework::findOne($id) : new LnHomework();
        $homeworkfile = array();
        if (!empty($model->kid)) {
            $homeworkfile = LnHomeworkFile::findAll(['homework_id' => $model->kid,'homework_file_type'=>0], false);
        }
        $service = new ComponentService();
        $component = $service->getCompoentByComponentKid($params['component_id']);
        $is_setting_component = $service->getRecordScore();
        return $this->renderAjax('homework', [
            'params' => $params,
            'result' => $model->attributes,
            'uploadBatch' => $uploadBatch,
            'homeworkfile' => $homeworkfile,
            'component' => $component,
        	'is_setting_component' => $is_setting_component,
        ]);
    }
    /**
     *配置弹窗
     */
    public function actionConfig()
    {
        $component_code = isset($_REQUEST['component_code'])?$_REQUEST['component_code']:'';
        $component_id = isset($_REQUEST['component_id'])?$_REQUEST['component_id']:'';
        $title = isset($_REQUEST['title'])?$_REQUEST['title']:'';
        $id = isset($_REQUEST['id'])?$_REQUEST['id']:'';
        $mod_num = isset($_REQUEST['mod_num'])?$_REQUEST['mod_num']:'';
        $pass_grade = '';
        //临时判断是否有分

        $service = new ComponentService();
        $component = $service->getCompoentByComponentKid($component_id);  
        $component_name = $component->title;
        $complete_rule = $component->complete_rule;
        $is_score = $component->is_record_score;
        if($complete_rule == LnComponent::COMPLETE_RULE_SCORE){
            $view = true;
            if($component_code=="scorm"){

            }elseif($component_code == "aicc"){

            }elseif($component_code == "examination"){
                $examservice = new ExaminationService();
                $pass_grade = $examservice->GetPassGrade($id);
            }
        }
        $componentService = new ComponentService();
        $is_setting_component = $componentService->getRecordScore();
        return $this->renderAjax('config', [
            'component_name'=>$component_name,
            'component_code'=>$component_code,
            'title'=>$title,
            'is_score'=>$is_score,
            'mod_num'=>$mod_num,
            'kid'=>$id,
            'pass_grade'=>$pass_grade,
            'view' => $view,
        	'params' => Yii::$app->request->queryParams,
        	'is_setting_component' => $is_setting_component,
        ]);
    }
    /**
     *配置列表弹窗
     */
    public function actionConfigList()
    {
    	$componentService = new ComponentService();
    	$is_setting_component = $componentService->getRecordScore();
        return $this->renderAjax('config-list', [
        	'params' => Yii::$app->request->queryParams,
        	'is_setting_component' => $is_setting_component,
        ]);
    }
    /**
     *最终成绩弹窗
     */
    public function actionFinalScore()
    {
        return $this->renderAjax('final-score', []);
    }
}