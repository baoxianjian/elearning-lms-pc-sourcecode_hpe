<?php

namespace frontend\controllers;

use common\models\framework\FwUser;
use common\models\learning\LnComponent;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCourseware;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCoursewareBook;
use common\models\learning\LnExaminationCategory;
use common\models\learning\LnExamQuestionCategory;
use common\models\learning\LnFiles;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnHomeworkResult;
use common\models\social\SoAudienceCategory;
use common\services\learning\CourseCompleteService;
use common\services\learning\ExaminationCategoryService;
use common\services\learning\ResourceCompleteService;
use common\services\message\TaskService;
use common\services\framework\TreeNodeService;
use common\services\learning\CoursewareBookService;
use common\services\social\AudienceCategoryService;
use common\base\BaseActiveRecord;
use yii;
use yii\db;
use yii\web\Response;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use frontend\base\BaseFrontController;
use common\services\learning\ResourceService;
use common\services\learning\CoursewareService;
use common\services\learning\InvestigationService;
use common\helpers\TFileModelHelper;
use components\widgets\TPagination;
use common\services\learning\CoursewareCategoryService;
use common\services\learning\CourseCategoryService;
use common\services\framework\ExternalSystemService;
use common\services\learning\ExaminationQuestionCategoryService;
use common\models\learning\LnCoursewareCategory;
use common\models\learning\LnCourseCategory;
use common\models\treemanager\FwTreeType;
use common\models\treemanager\FwTreeNode;
use common\models\learning\LnResourceDomain;
use common\helpers\TURLHelper;
use common\services\framework\PointRuleService;


/**
 * 资源管理向导，包含：课程、scrom课件、问答、文件、视频等
 * Class ResourceController
 * @package frontend\controllers
 */
class ResourceController extends BaseFrontController
{
    public $layout = 'frame';
    private $uploadStep = 'CoursewareStep';

    public function behaviors()
    {
        $baseBehaviors = parent::behaviors();
        $newBehaviors = [
            'access' => [
                'class' => AccessControl::className(),
                'except' => ['down','addhomeworkresult','delhomeworkfile'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
        $finalBehaviors = array_merge($baseBehaviors,$newBehaviors);
        return $finalBehaviors;
    }


    /**
     * 资源管理引导首页s
     * @return string
     */
    public function actionIndex()
    {
        $uid = Yii::$app->user->getId();

        $service = new ResourceService();
        $resourceStatus = $service->getResourceStatus();

        $taskService = new TaskService();
        $taskCount = $taskService->getTaskCount($uid);

        return $this->render('index', [
            'result' => $resourceStatus,
            'task_count' => $taskCount,
        ]);
    }


    /**
     * 资源状态
     * @return string
     */
    public function actionStatus()
    {
        $service = new ResourceService();

        $resourceStatus = $service->getResourceStatus();

        return $this->renderAjax('status', [
            'resourceStatus' => $resourceStatus,
        ]);
    }


    /**
     * 显示右侧拖动选项卡
     * @return string
     */
    public function actionComponents($component_type)
    {
        $service = new ResourceService();
        $components = $service->getComponents($component_type, false);

        if ($component_type == '0') {
            $component_name = 'resource';
        } else if ($component_type == '1') {
            $component_name = 'active';
        }
        return $this->renderAjax('components', [
            'components' => $components,
            'component_name' => $component_name,
        ]);
    }


    /**
     * 组件切换卡，用于课程编辑页中添加组件
     * @return string
     */
    public function actionCoursewares()
    {
        $service = new CoursewareService();
        $sequence_number = Yii::$app->request->getQueryParam('sequence_number');
        $component_id = Yii::$app->request->getQueryParam('component_id');
        $domain_id = Yii::$app->request->getQueryParam('domain_id');
        $courseware_name = Yii::$app->request->get('CoursewareService');
        $pageSize = 10;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }
        $dataProvider = $service->Search(Yii::$app->request->queryParams, true, true);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        return $this->renderAjax('coursewares', [
            'coursewares' => $dataProvider,
            'sequence_number' => $sequence_number,
            'component_id' => $component_id,
            'searchModel' => $service,
            'domain_id' => $domain_id,
            'courseware_name' => $courseware_name['courseware_name'],
        ]);
    }

    /*活动组件*/
    public function actionActivity(){
        $component_code = Yii::$app->request->get('component_code');
        $params = Yii::$app->request->queryParams;
        $service = new InvestigationService();
        if ($component_code == 'investigation'){
            $params['investigation_range'] = '1';
        }
        $params['status'] = InvestigationService::STATUS_FORMAL;
        $dataProvider = $service->search($params);
        $count = $dataProvider->totalCount;
        $size = $this->defaultPageSize;
        $page = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
        $dataProvider->setPagination($page);
        return $this->renderAjax('activity', [
            'result' => $dataProvider,
            'searchModel' => $service,
            'params' => $params,
        ]);
    }
    /*html组件*/
    public function actionNewhtml(){
        $data = $_REQUEST;
        return $this->renderAjax('new-html', [
            'data' => $data,
            'coursewareid'=>'',
            'result'=>''
        ]);
    }

    /*html组件添加*/
    public function actionAddhtml()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $htmlkid = isset($_REQUEST['kid']) ? $_REQUEST['kid'] : '';
        $title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
        $content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
        $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
        $display = isset($_REQUEST['display']) ? $_REQUEST['display'] : '';
        $component_id = isset($_REQUEST['component_id']) ? $_REQUEST['component_id'] : '';
        $sequence_number = isset($_REQUEST['sequence_number']) ? $_REQUEST['sequence_number'] : '';
        $domain_id = isset($_REQUEST['domain_id']) ? $_REQUEST['domain_id'] : '';
        $component_code = isset($_REQUEST['component_code']) ? $_REQUEST['component_code'] : '';
        $courseware_type = isset($_REQUEST['courseware_type']) ? $_REQUEST['courseware_type'] : '';

        if (strlen($content) > 65535) {
            return ['result' => 'other', 'message' => Yii::t('frontend', 'html_content_too_long')];
        }

        if (!empty($htmlkid)) {
            $LnCoursewareModel = LnCourseware::findOne($htmlkid);
        } else {
            $LnCoursewareModel = new LnCourseware();
        }
        //  $LnComponentModel = new LnComponent();
        $component = LnComponent::findOne($component_id);
        $LnCoursewareModel->file_id = null;
        $LnCoursewareModel->company_id = Yii::$app->user->identity->company_id;
        $LnCoursewareModel->courseware_name = $title;
        $LnCoursewareModel->entrance_address = '';
        $LnCoursewareModel->courseware_time = $component['default_time'];
        $LnCoursewareModel->courseware_desc = '';
        $LnCoursewareModel->embed_url = $url;
        $LnCoursewareModel->embed_code = $content;
        $LnCoursewareModel->courseware_type = $courseware_type;
        $LnCoursewareModel->courseware_code = $LnCoursewareModel->setCoursewareCode();
        $LnCoursewareModel->default_credit = $component['default_credit'];
        $LnCoursewareModel->courseware_category_id = null;
        $LnCoursewareModel->is_display_pc = empty($component['is_display_pc']) ? LnCourseware::DISPLAY_PC_NO : LnCourseware::DISPLAY_PC_YES;
        $LnCoursewareModel->is_display_mobile = empty($component['is_display_mobile']) ? LnCourseware::DISPLAY_MOBILE_NO : LnCourseware::DISPLAY_MOBILE_YES;
        $LnCoursewareModel->is_allow_download = empty($component['is_allow_download']) ? LnCourseware::ALLOW_DOWNLOAD_NO : LnCourseware::ALLOW_DOWNLOAD_YES;
        $LnCoursewareModel->vendor = null;
        $LnCoursewareModel->display_position = $display;
        $LnCoursewareModel->start_at = time();
        $LnCoursewareModel->end_at = null;
        $LnCoursewareModel->entry_mode = "1";
        $LnCoursewareModel->resource_version = $LnCoursewareModel->getCoursewareVersion($LnCoursewareModel->kid);
        $LnCoursewareModel->component_id = $component->kid;

        if (!empty($htmlkid)) {
            if ($LnCoursewareModel->update()) {
                $kid = $LnCoursewareModel->kid;

                return ['result' => 'success', 'kid' => $kid];
            } else {
                return ['result' => 'failure'];
            }
        } else {
            $LnCoursewareModel->needReturnKey = true;
            if ($LnCoursewareModel->save()) {
                $kid = $LnCoursewareModel->kid;
                return ['result' => 'success', 'kid' => $kid];
            } else {
                return ['result' => 'failure'];
            }
        }
    }
    /*获取title*/
    public function actionGettitle(){
        $url = isset($_POST['url'])? $_POST['url']:'';
        set_time_limit(10);
        $title = TURLHelper::getTitleByUrl($url);
        echo $title;

    }
    /*抓取图书方法*/
    public function actionGetBook(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $word = trim(Yii::$app->request->get('q'));
        $id = trim(Yii::$app->request->get('id'));
        $isbn=trim(Yii::$app->request->get('isbn'));
        $name = trim(Yii::$app->request->get('name'));
        
        $bookService = new CoursewareBookService();
        $books = $bookService->searchBookFromDouban($word,$id,$isbn,$name);

        //id,isbn优先
        if($id || $isbn || $name)
        {
            return $books;
        }
        //其次search
        if (!empty($books)) {
            $new_array = array();
            foreach ($books as $item) {
    
                $new_array[] = array('kid' => $item['id'], 'title' => $item['title'].'('.$item['publisher'].')');
            }
            $books = array('results' => $new_array);
        }
        return $books ? $books : '';
    }
    /*html组件更新*/
    public function actionUpdatenewhtml(){
        $coursewareid = isset($_GET['coursewareid'])?$_GET['coursewareid']:'';
        $typeno = isset($_GET['typeno'])?$_GET['typeno']:'';
        $courseware = LnCourseware::findOne($coursewareid);

        return $this->renderAjax('new-html', [
            'data' => $_REQUEST,
            'coursewareid'=>$coursewareid,
            'result' => $courseware,
            'typeno'=>$typeno,
        ]);

    }
       /*图书组件更新*/
    public function actionUpdatebook(){
        $coursewareid = isset($_GET['coursewareid'])?$_GET['coursewareid']:'';
        $book = LnCoursewareBook::findOne(['courware_id'=>$coursewareid]);
        return $this->renderAjax('book', [
            'data' => $_REQUEST,
            'coursewareid'=>$coursewareid,
            'result' => $book,
        ]);
    }
    /*图书组件*/
    public function actionBook(){
        $data = json_encode($_REQUEST);
        return $this->renderAjax('book', [
            'data' => $data,
            'coursewareid'=>'',
            'result' => '',

        ]);
    }
    /*图书组件*/
    public function actionAddbook(){
        $title = isset($_REQUEST['title'])? $_REQUEST['title']:null;
        $title = urldecode($title);
        $bookkid = isset($_REQUEST['kid'])? $_REQUEST['kid']:'';
        $content = isset($_REQUEST['content'])? $_REQUEST['content']:null;
        $url = isset($_REQUEST['url'])? $_REQUEST['url']:null;
        $url = urldecode($url);
        $display = isset($_REQUEST['display'])? $_REQUEST['display']:null;
        $component_id = isset($_REQUEST['component_id'])? $_REQUEST['component_id']:null;
        $courseware_type = isset($_REQUEST['courseware_type'])? $_REQUEST['courseware_type']:null;
        $isbn_no = isset($_REQUEST['bookno'])? $_REQUEST['bookno']:null;
        $isbn_no = urldecode($isbn_no);
        $author_name = isset($_REQUEST['author'])? $_REQUEST['author']:null;
        $author_name = urldecode($author_name);
        $publisher_name = isset($_REQUEST['publisher'])? $_REQUEST['publisher']:null;
        $publisher_name = urldecode($publisher_name);
        $original_book_name = isset($_REQUEST['alttitle'])? $_REQUEST['alttitle']:null;
        $original_book_name = urldecode($original_book_name);
        $translator = isset($_REQUEST['translator'])? $_REQUEST['translator']:null;
        $translator = urldecode($translator);
        $publisher_date = isset($_REQUEST['pubdate'])? $_REQUEST['pubdate']:null;
        $publisher_date = urldecode($publisher_date);
        $price = isset($_REQUEST['price'])? $_REQUEST['price']:null;
        $price = urldecode($price);
        $page_number = isset($_REQUEST['pages'])? $_REQUEST['pages']:null;
        $page_number = urldecode($page_number);
        $binding_layout = isset($_REQUEST['binding'])? $_REQUEST['binding']:null;
        $binding_layout = urldecode($binding_layout);
        $description = isset($_REQUEST['intro'])? $_REQUEST['intro']:null;
        $description = urldecode($description);
        $external_url = isset($_REQUEST['url'])? $_REQUEST['url']:null;
        $external_date_type = isset($_REQUEST['booktype'])? $_REQUEST['booktype']:0;
        $external_date_type = urldecode($external_date_type);

        if(!empty($bookkid)){
            $LnCoursewareModel = LnCourseware::findOne($bookkid);
        }else{
            $LnCoursewareModel = new LnCourseware();
        }
        //  $LnComponentModel = new LnComponent();
        $component = LnComponent::findOne($component_id);
        $LnCoursewareModel->file_id = null;
        $LnCoursewareModel->company_id = Yii::$app->user->identity->company_id;
        $LnCoursewareModel->courseware_name =$title;
        $LnCoursewareModel->entrance_address = null;
        $LnCoursewareModel->courseware_time = $component['default_time'];
        $LnCoursewareModel->courseware_desc = null;
        $LnCoursewareModel->embed_url = $url;
        $LnCoursewareModel->embed_code = $content;
        $LnCoursewareModel->courseware_type = $courseware_type;
        $LnCoursewareModel->courseware_code = $LnCoursewareModel->setCoursewareCode();
        $LnCoursewareModel->default_credit = $component['default_credit'];
        $LnCoursewareModel->courseware_category_id = null;
        $LnCoursewareModel->is_display_pc = empty($component['is_display_pc']) ? LnCourseware::DISPLAY_PC_NO : LnCourseware::DISPLAY_PC_YES;
        $LnCoursewareModel->is_display_mobile = empty($component['is_display_mobile']) ? LnCourseware::DISPLAY_MOBILE_NO : LnCourseware::DISPLAY_MOBILE_YES;
        $LnCoursewareModel->is_allow_download = empty($component['is_allow_download']) ? LnCourseware::ALLOW_DOWNLOAD_NO : LnCourseware::ALLOW_DOWNLOAD_YES;
        $LnCoursewareModel->vendor = null;
        $LnCoursewareModel->start_at = time();
        $LnCoursewareModel->end_at =null;
        $LnCoursewareModel->entry_mode="1";
        $LnCoursewareModel->resource_version = $LnCoursewareModel->getCoursewareVersion($LnCoursewareModel->kid);
        $LnCoursewareModel->component_id = $component->kid;
       if(!empty($bookkid)){
           if($LnCoursewareModel->update()){

               $kid = $LnCoursewareModel->kid;

               $courware_id = LnCoursewareBook::findOne(['courware_id'=>$kid]);
               if(!empty($courware_id)){
                   $LnCoursewareBookModel = $courware_id;
                   $LnCoursewareBookModel->courware_id =$kid;
                   $LnCoursewareBookModel->book_name = $title;
                   $LnCoursewareBookModel->isbn_no =$isbn_no;
                   $LnCoursewareBookModel->author_name =$author_name;
                   $LnCoursewareBookModel->publisher_name =$publisher_name;
                   $LnCoursewareBookModel->original_book_name = $original_book_name;
                   $LnCoursewareBookModel->translator = $translator;
                   $LnCoursewareBookModel->publisher_date =$publisher_date;
                   $LnCoursewareBookModel->price =$price;
                   $LnCoursewareBookModel->page_number =$page_number;
                   $LnCoursewareBookModel->binding_layout =$binding_layout;
                   $LnCoursewareBookModel->description =$description;
                   $LnCoursewareBookModel->external_url =$external_url;
                   $LnCoursewareBookModel->external_date_type = "$external_date_type";

                   if($LnCoursewareBookModel->update()){
                       $key = $LnCoursewareBookModel->kid;
                       return $kid;
                   }else{
                       return false;
                   }

               }else{
                   $LnCoursewareBookModel = new LnCoursewareBook();
                   $LnCoursewareBookModel->courware_id =$kid;
                   $LnCoursewareBookModel->book_name = $title;
                   $LnCoursewareBookModel->isbn_no =$isbn_no;
                   $LnCoursewareBookModel->author_name =$author_name;
                   $LnCoursewareBookModel->publisher_name =$publisher_name;
                   $LnCoursewareBookModel->original_book_name = $original_book_name;
                   $LnCoursewareBookModel->translator = $translator;
                   $LnCoursewareBookModel->publisher_date =$publisher_date;
                   $LnCoursewareBookModel->price =$price;
                   $LnCoursewareBookModel->page_number =$page_number;
                   $LnCoursewareBookModel->binding_layout =$binding_layout;
                   $LnCoursewareBookModel->description =$description;
                   $LnCoursewareBookModel->external_url =$external_url;
                   $LnCoursewareBookModel->external_date_type = "$external_date_type";

                   if($LnCoursewareBookModel->save()){
                       return $kid;
                   }else{
                       return false;
                   }

               }
           }else{
               return false;
           }
       }else{
           $LnCoursewareModel->needReturnKey = true;
           if($LnCoursewareModel->save()){
               $kid = $LnCoursewareModel->kid;
               $LnCoursewareBookModel = new LnCoursewareBook();
               $courware_id = LnCoursewarebook::findOne(['courware_id'=>$kid]);
               if(!empty($courware_id)){
                   $LnCoursewareBookModel->courware_id =$kid;
                   $LnCoursewareBookModel->book_name = $title;
                   $LnCoursewareBookModel->isbn_no =$isbn_no;
                   $LnCoursewareBookModel->author_name =$author_name;
                   $LnCoursewareBookModel->publisher_name =$publisher_name;
                   $LnCoursewareBookModel->original_book_name = $original_book_name;
                   $LnCoursewareBookModel->translator = $translator;
                   $LnCoursewareBookModel->publisher_date =$publisher_date;
                   $LnCoursewareBookModel->price =$price;
                   $LnCoursewareBookModel->page_number =$page_number;
                   $LnCoursewareBookModel->binding_layout =$binding_layout;
                   $LnCoursewareBookModel->description =$description;
                   $LnCoursewareBookModel->external_url =$external_url;
                   $LnCoursewareBookModel->external_date_type = "$external_date_type";

                   if($LnCoursewareBookModel->update()){
                       return $kid;
                   }else{
                       return false;
                   }

               }else{
                   $LnCoursewareBookModel->courware_id =$kid;
                   $LnCoursewareBookModel->book_name = $title;
                   $LnCoursewareBookModel->isbn_no =$isbn_no;
                   $LnCoursewareBookModel->author_name =$author_name;
                   $LnCoursewareBookModel->publisher_name =$publisher_name;
                   $LnCoursewareBookModel->original_book_name = $original_book_name;
                   $LnCoursewareBookModel->translator = $translator;
                   $LnCoursewareBookModel->publisher_date =$publisher_date;
                   $LnCoursewareBookModel->price =$price;
                   $LnCoursewareBookModel->page_number =$page_number;
                   $LnCoursewareBookModel->binding_layout =$binding_layout;
                   $LnCoursewareBookModel->description =$description;
                   $LnCoursewareBookModel->external_url =$external_url;
                   $LnCoursewareBookModel->external_date_type = "$external_date_type";
                   if($LnCoursewareBookModel->save()){
                       return $kid;
                   }else{
                       return false;
                   }
               }
           }else{
               return false;
           }
       }
    }
    public function actionUpdatehomework()
    {
        $courseactivityid = isset($_GET['id']) ? $_GET['id'] : '';
        $homework = LnHomework::findOne($courseactivityid);
        $homeworkfile = LnHomeworkFile::findAll(['homework_id'=>$homework->kid],false);
        $uploadBatch = date("YmdHis");
        return $this->renderAjax('homework', [
            'data' => $_REQUEST,
            'courseactivityid' => $courseactivityid,
            'homeworkfile' =>$homeworkfile,
            'result' => $homework,
            'uploadBatch' => $uploadBatch,
        ]);
    }
        /*作业组件*/
    public function actionHomework($error = ''){
        $data = json_encode($_REQUEST);
        $file_info = Yii::$app->request->post('file_info');
        /*解析字符串*/
        $tempArr = array();
        if (!empty($file_info)){
            $string = json_decode($file_info, true);
            foreach ( $string as $val){
                $item = json_decode($val, true);
                $ln_info = LnFiles::findOne($item['file_id']);
                $item['component_id'] = $ln_info->component_id;
                $lncomponent_info = LnComponent::findOne($ln_info->component_id);
                $item['icon'] = $lncomponent_info['icon'];
                $tempArr[] = $item;
            }
        }
        $model = new LnCourseware();
        $request = array();
        if (Yii::$app->request->isPost){
            $request['domain_id'] = Yii::$app->request->post('domain_id');
            $model->load(Yii::$app->request->post());

            $tree_node_id = str_replace(array('["','"]','"'),'',Yii::$app->request->post('jsTree_tree_selected_result'));
            if (!empty($tree_node_id)) {
                $coursewareService = new CoursewareCategoryService();
                $model->courseware_category_id = $coursewareService->getCoursewareCategoryIdByTreeNodeId($tree_node_id);
            }
        }else{
            $model->start_at = date('Y-m-d');
        }
        $lncomponent = LnComponent::findAll([],false);
        $lncomponent_title = array();
        foreach ($lncomponent as $val){
            $lncomponent_title[$val->kid] = urlencode($val->title);
        }
        $uploadBatch = date("YmdHis");
        $result = new LnHomework();
        return $this->renderAjax('homework', [
            'data' => $data,
            'courseactivityid'=>'',
            'result' => $result,
            'uploadStep' => $this->uploadStep,
            'uploadBatch' => $uploadBatch,
            'error' => $error,
            'tempArr' => $tempArr,
            'model' => $model,
            'request' => $request,
            'lncomponent_title' => $lncomponent_title,
        ]);
    }
    /*html组件添加*/
    public function actionAddhomework()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $hwkid = isset($_POST['kid']) ? $_POST['kid'] : '';
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $endline = isset($_POST['endline']) ? $_POST['endline'] : 0;
        $hwmode = isset($_POST['hwmode']) ? $_POST['hwmode'] : 0;
        $fileids = isset($_POST['fileids']) ? $_POST['fileids'] : '';
        $component_id = isset($_POST['component_id']) ? $_POST['component_id'] : '';
        $sequence_number = isset($_POST['sequence_number']) ? $_POST['sequence_number'] : '';
        $domain_id = isset($_POST['domain_id']) ? $_POST['domain_id'] : '';
        $component_code = isset($_POST['component_code']) ? $_POST['component_code'] : '';
        $courseware_type = isset($_POST['courseware_type']) ? $_POST['courseware_type'] : '';
        
        if (!empty($hwkid)) {
            $LnHomeworkModel = LnHomework::findOne($hwkid);
        } else {
            $LnHomeworkModel = new LnHomework();
        }
        //  $LnComponentModel = new LnComponent();
        $component = LnComponent::findOne($component_id);
        $LnHomeworkModel->company_id = $companyId;
        $LnHomeworkModel->title = $title;
        $LnHomeworkModel->requirement = $content;
        $LnHomeworkModel->finish_before_at = strtotime("$endline");
        $LnHomeworkModel->homework_mode = $hwmode;
        $LnHomeworkModel->description = $content;
        
        //结束日期小于当前时间，则无效
        if($LnHomeworkModel->finish_before_at < time())
        {
            return false;
        }
        
        

        if (!empty($hwkid)) {
            if ($LnHomeworkModel->update()) {

                $kid = $LnHomeworkModel->kid;
                if (!empty($fileids)) {
                    $fileidarr = explode(',', $fileids);
                    array_pop($fileidarr);
                    foreach ($fileidarr as $k => $v) {
                        $file = LnHomeworkFile::findOne($v);
                        $file->homework_id = $kid;
                        $file->save();

                    }
                }
                return $kid;
            } else {
                return false;
            }
        } else {
            $LnHomeworkModel->needReturnKey = true;
            if ($LnHomeworkModel->save()) {
                $kid = $LnHomeworkModel->kid;
                if (!empty($fileids)) {
                    $fileidarr = explode(',', $fileids);
                    array_pop($fileidarr);
                    foreach ($fileidarr as $k => $v) {
                        $file = LnHomeworkFile::findOne($v);
                        $file->homework_id = $kid;
                        $file->save();
                    }
                }
                return $kid;
            } else {
                return false;
            }
        }
    }

    public function actionDelhomeworkfile(){
        $fileid = isset($_POST['fileid'])?$_POST['fileid']:'';
        $file = LnHomeworkFile::findOne($fileid);
        $file->delete();
    }
    /*作业组件添加*/
    public function actionAddhomeworkresult($company_id=null,$userId=null){
         Yii::$app->response->format = Response::FORMAT_JSON;
         
        $companyId = Yii::$app->user->identity->company_id;
        $user_id = Yii::$app->user->getId();
        if(empty($companyId)){
            $companyId = $company_id;
        }

        if(empty($user_id)){
            $user_id = $userId;
        }
        $kid = isset($_POST['kid'])?$_POST['kid']:'';
        $hwkid = isset($_POST['hwkid'])? $_POST['hwkid']:'';
        $result = isset($_POST['result'])? $_POST['result']:'';
        $description = isset($_POST['description'])? $_POST['description']:'';
        $courseId = isset($_POST['course_id'])? $_POST['course_id']:'';
        $courseRegId = isset($_POST['course_reg_id'])? $_POST['course_reg_id']:'';
        $modId = isset($_POST['mod_id'])? $_POST['mod_id']:'';
        $modResId = isset($_POST['mod_res_id'])? $_POST['mod_res_id']:'';
        $courseactivityId = isset($_POST['courseactivity_id'])? $_POST['courseactivity_id']:'';
        $componentd = isset($_POST['component_id'])? $_POST['component_id']:'';
        $courseCompleteProcessId = isset($_POST['courseCompleteProcessId'])? $_POST['courseCompleteProcessId']:'';
        $courseCompleteFinalId = isset($_POST['courseCompleteFinalId'])? $_POST['courseCompleteFinalId']:'';
        $resCompleteId = isset($_POST['resCompleteId'])? $_POST['resCompleteId']:'';
        $courseAttemptNumber = isset($_POST['courseAttemptNumber']) ? $_POST['courseAttemptNumber'] : 1;
        $fileIds = isset($_POST['fileIds'])? $_POST['fileIds']:'';

        $attempt = LnCourseComplete::findOne($courseCompleteProcessId)->attempt_number;
        if(empty($fileIds) && empty($result))
        {
            return false;
        }
        $fileIds = substr($fileIds,0,strlen($fileIds)-1);
        $arrfile = explode(',',$fileIds);
        $homeworkService = new ResourceService();
        foreach($arrfile as $value){
            $homeworkService->HomeworkFileAddId($value,$hwkid);
        }
        if(!empty($kid)){
            $LnHomeworkResultModel = LnHomeworkResult::findOne($kid);
        }else{
            $LnHomeworkResultModel = new LnHomeworkResult();
        }
        $LnHomeworkResultModel->homework_id = $hwkid;
        $LnHomeworkResultModel->user_id = $user_id;
        $LnHomeworkResultModel->company_id = $companyId;
        $LnHomeworkResultModel->homework_result = $result;
        $LnHomeworkResultModel->course_id = $courseId;
        $LnHomeworkResultModel->course_reg_id = $courseRegId;
        $LnHomeworkResultModel->mod_id = $modId;
        $LnHomeworkResultModel->mod_res_id = $modResId;
        $LnHomeworkResultModel->courseactivity_id = $courseactivityId;
        $LnHomeworkResultModel->component_id = $componentd;
        $LnHomeworkResultModel->course_complete_id = $courseCompleteProcessId;
        $LnHomeworkResultModel->res_complete_id = $resCompleteId;
        if (!empty($courseId)){
            $courseModel = LnCourse::findOne($courseId);
            if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
                $attempt = intval($courseAttemptNumber);
                /*查找本次保存是否上传文件*/
                $findHomeworkFile = LnHomeworkFile::findOne(['homework_id' => $hwkid, 'user_id' => $user_id, 'course_id' => $courseId, 'mod_res_id' => $modResId, 'course_attempt_number' => $attempt]);
                /*有数据则是删除上一次上传数据，无数据则更新上次提交的数据course_attempt_number值*/
                if (!empty($findHomeworkFile)) {
                    /*面授保存最后一次上传的数据*/
                    LnHomeworkFile::deleteAll(
                        'homework_id=:homework_id and user_id=:user_id and course_id=:course_id and mod_id=:mod_id and mod_res_id=:mod_res_id and course_attempt_number<>:course_attempt_number',
                        [
                            ':homework_id' => $hwkid,
                            ':user_id' => $user_id,
                            ':course_id' => $courseId,
                            ':mod_id' => $modId,
                            ':mod_res_id' => $modResId,
                            ':course_attempt_number' => $attempt,
                        ]
                    );
                }else{
                    /*上传第一次不做修改*/
                    if ($attempt > 1) {
                        LnHomeworkFile::updateAll(['course_attempt_number' => $attempt,], "homework_id=:homework_id and user_id=:user_id and course_id=:course_id and mod_id=:mod_id and mod_res_id=:mod_res_id and course_attempt_number=:course_attempt_number",
                            [
                                ':homework_id' => $hwkid,
                                ':user_id' => $user_id,
                                ':course_id' => $courseId,
                                ':mod_id' => $modId,
                                ':mod_res_id' => $modResId,
                                ':course_attempt_number' => $attempt - 1,
                            ]
                        );
                        /*更新再删除不合适的数据*/
                        LnHomeworkFile::deleteAll(
                            'homework_id=:homework_id and user_id=:user_id and course_id=:course_id and mod_id=:mod_id and mod_res_id=:mod_res_id and course_attempt_number<>:course_attempt_number',
                            [
                                ':homework_id' => $hwkid,
                                ':user_id' => $user_id,
                                ':course_id' => $courseId,
                                ':mod_id' => $modId,
                                ':mod_res_id' => $modResId,
                                ':course_attempt_number' => $attempt,
                            ]
                        );
                    }
                }
            }
        }
        $LnHomeworkResultModel->course_attempt_number = $attempt;
        $LnHomeworkResultModel->needReturnKey = true;
        if(!empty($kid)){
            $result = $LnHomeworkResultModel->update();
        }else {
            $result = $LnHomeworkResultModel->save();
        }
        if ($result !== false){
            $kid = $LnHomeworkResultModel->kid;

            $courseComplete=false;
            $getCetification=false;
            $courseId=null;
            $certificationId=null;
            $resourceCompleteService = new ResourceCompleteService();
            $resourceCompleteService->addResCompleteDoneInfo($courseCompleteFinalId,$courseRegId,$modResId,LnCourseComplete::COMPLETE_TYPE_FINAL,null,null,false,null,true,false,$courseComplete,$getCetification,$courseId,$certificationId);

            //edit by baoxianjian 11:27 2016/3/31
            $pointRuleService=new PointRuleService();
            $pointResult=$pointRuleService->countCourseAndCetificationPoint($courseComplete,$getCetification,$courseId,$certificationId);

            return ['result' => 'success','kid'=>$kid,'pointResult'=>$pointResult];

        }else{
            return ['result' => 'failed'];
        }
    }
    /*资源下载*/
    public function actionDown($id, $file_name)
    {
        $TFileModelHelper = new TFileModelHelper();
        $TFileModelHelper->Play($id, 1, $file_name);
    } 
       /*作业资源下载*/
    public function actionHomeworkDown($id, $file_name)
    {
        $TFileModelHelper = new TFileModelHelper();
        $file_name = urlencode($file_name);
        $houzhui = substr(strrchr($file_name, '.'), 1);
        $file_name = basename($file_name,".".$houzhui);
        $TFileModelHelper->HomeworkPlay($id, 1, $file_name);
    }

    /*添加分类*/
    public function actionAddCategory(){
        $tree_type_code = Yii::$app->request->get('tree_type_code');
        $tree_node_id = Yii::$app->request->get('tree_node_id');
        $catlog = array();
        $companyId = array(Yii::$app->user->identity->company_id);
        if ($tree_type_code == 'courseware-category') {
            $categoryService = new CoursewareCategoryService();
            $category = $categoryService->getCoursewareCategoryByCompanyIdList($companyId);
            if (!empty($category)) {
                foreach ($category as $val) {
                    if (empty($val->parent_category_id)) {
                        $catlog['parent'][] = $val->attributes;
                    } else {
                        $catlog['sub'][$val->parent_category_id][] = $val->attributes;
                    }
                }
            }
            if ($tree_node_id){
                $category_id = $categoryService->getCoursewareCategoryIdByTreeNodeId($tree_node_id);
                $model = LnCoursewareCategory::findOne($category_id);
            }
        }elseif($tree_type_code == 'course-category'){
            $categoryService = new CourseCategoryService();
            $category = $categoryService->getCourseCategoryByCompanyIdList($companyId);
            if (!empty($category)) {
                foreach ($category as $val) {
                    if (empty($val->parent_category_id)) {
                        $catlog['parent'][] = $val->attributes;
                    } else {
                        $catlog['sub'][$val->parent_category_id][] = $val->attributes;
                    }
                }
            }
            if ($tree_node_id){
                $category_id = $categoryService->getCourseCategoryIdByTreeNodeId($tree_node_id);
                $model = LnCourseCategory::findOne($category_id);
            }
        }elseif($tree_type_code == 'examination-question-category'){
            $categoryService = new ExaminationQuestionCategoryService();
            $category = $categoryService->GetExaminationQuestionCategoryByCompanyIdList($companyId);
            if (!empty($category)) {
                foreach ($category as $val) {
                    if (empty($val->parent_category_id)) {
                        $catlog['parent'][] = $val->attributes;
                    } else {
                        $catlog['sub'][$val->parent_category_id][] = $val->attributes;
                    }
                }
            }
        }elseif($tree_type_code == 'examination-category'){
            $categoryService = new ExaminationCategoryService();
            $category = $categoryService->GetExaminationCategoryByCompanyIdList($companyId);
            if (!empty($category)) {
                foreach ($category as $val) {
                    if (empty($val->parent_category_id)) {
                        $catlog['parent'][] = $val->attributes;
                    } else {
                        $catlog['sub'][$val->parent_category_id][] = $val->attributes;
                    }
                }
            }
            if ($tree_node_id){
                $category_id = $categoryService->GetExaminationCategoryIdByTreeNodeId($tree_node_id);
                $model = LnExaminationCategory::findOne($category_id);
            }
        }elseif($tree_type_code == 'audience-category'){
            $categoryService = new AudienceCategoryService();
            $category = $categoryService->GetAudienceCategoryByCompanyIdList($companyId);
            if (!empty($category)) {
                foreach ($category as $val) {
                    if (empty($val->parent_category_id)) {
                        $catlog['parent'][] = $val->attributes;
                    } else {
                        $catlog['sub'][$val->parent_category_id][] = $val->attributes;
                    }
                }
            }
            if ($tree_node_id){
                $category_id = $categoryService->GetAudienceCategoryIdByTreeNodeId($tree_node_id);
                $model = SoAudienceCategory::findOne($category_id);
            }
        }
        $title = Yii::$app->request->get('title');
        if (empty($title)){
            $title = Yii::t('common', 'catelog');
        }
        if (empty($model)){
            $model = new LnCourseCategory();
        }
        return $this->renderAjax('add-category',[
            'tree_type_code' => $tree_type_code,
            'tree_node_id' => $tree_node_id,
            'model' => $model,
            'catlog' => $catlog,
            'title' => $title,
        ]);
    }
    public function actionFileHtml(){
        $service = new CoursewareService();
        $sequence_number = Yii::$app->request->getQueryParam('sequence_number');
        $component_id = Yii::$app->request->getQueryParam('component_id');
        $domain_id = Yii::$app->request->getQueryParam('domain_id');
        $courseware_type = Yii::$app->request->getQueryParam('courseware_type');
        $entry_mode = Yii::$app->request->getQueryParam('entry_mode');
        $courseware_name = Yii::$app->request->get('CoursewareService');
        $pageSize = 10;
        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }
        $dataProvider = $service->Search(Yii::$app->request->queryParams, true, true);
        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize' => $pageSize, 'totalCount' => $count]);
        $dataProvider->setPagination($page);

        return $this->renderAjax('filehtml-list', [
            'coursewares' => $dataProvider,
            'sequence_number' => $sequence_number,
            'component_id' => $component_id,
            'searchModel' => $service,
            'domain_id' => $domain_id,
            'courseware_type'=> $courseware_type,
            'entry_mode'=>$entry_mode,
            'courseware_name' => $courseware_name['courseware_name'],
        ]);
    }

    /*添加课件目录*/
    public function actionAddCoursewareCategory(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $category_name = Yii::$app->request->post('category_name');
        $parent_category_id = Yii::$app->request->post('parent_category_id');
        $id = Yii::$app->request->post('id');
        if (empty($category_name)){
            return ['result' => 'fail'];
        }
        $companyId = Yii::$app->user->identity->company_id;
        $query = LnCoursewareCategory::find(false)
            ->andFilterWhere(['category_name'=>$category_name, 'company_id'=>$companyId, 'status'=>LnCoursewareCategory::STATUS_FLAG_NORMAL]);
        if (!empty($id)){
            $query->andFilterWhere(['<>', 'kid', $id]);
        }
        $count = $query->count();
        if ($count){
            return ['result' => 'reply', 'errmsg' => Yii::t('frontend', 'exist_identical_catalog')];
        }
        $tree_parent_id = "";
        if (!empty($parent_category_id)){
            $parentCategory = LnCoursewareCategory::findOne($parent_category_id);
            $tree_parent_id = $parentCategory->tree_node_id;
        }
        $coursewareCategory = empty($id) ? new LnCoursewareCategory() : LnCoursewareCategory::findOne($id);
        $treeNodeService = new TreeNodeService();
        if ($id){
            $treeNodeService->updateTreeNode($coursewareCategory->tree_node_id, $category_name, $tree_parent_id);
            $tree_node_id = $coursewareCategory->tree_node_id;
        }else{
            $tree_node_id = $treeNodeService->addTreeNode('courseware-category', $category_name, $tree_parent_id);
        }
        if ($tree_node_id) {
            $treeNode = FwTreeNode::findOne($tree_node_id);
            $coursewareCategory->tree_node_id = $tree_node_id;
            $coursewareCategory->parent_category_id = empty($parent_category_id) ? null : $parent_category_id;
            $coursewareCategory->company_id = $companyId;
            $coursewareCategory->category_code = $treeNode->tree_node_code;
            $coursewareCategory->category_name = $category_name;
            $coursewareCategory->status = LnCoursewareCategory::STATUS_FLAG_NORMAL;
            if ($id){
                $coursewareCategory->update();
            }else {
                $coursewareCategory->save();
            }
            return ['result' => 'success'];
        }else{
            return ['result' => 'fail'];
        }
    }

    /*添加课程目录*/
    public function actionAddCourseCategory(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $category_name = Yii::$app->request->post('category_name');
        $parent_category_id = Yii::$app->request->post('parent_category_id');
        $id = Yii::$app->request->post('id');
        if (empty($category_name)){
            return ['result' => 'fail'];
        }
        $companyId = Yii::$app->user->identity->company_id;
        $query = LnCourseCategory::find(false)
            ->andFilterWhere(['category_name'=>$category_name, 'company_id'=>$companyId, 'status'=>LnCourseCategory::STATUS_FLAG_NORMAL]);
        if (!empty($id)){
            $query->andFilterWhere(['<>', 'kid', $id]);
        }
        $count = $query->count();
        if ($count){
            return ['result' => 'reply', 'errmsg' => Yii::t('frontend', 'exist_identical_catalog')];
        }
        $tree_parent_id = "";
        if (!empty($parent_category_id)){
            $parentCategory = LnCourseCategory::findOne($parent_category_id);
            $tree_parent_id = $parentCategory->tree_node_id;
        }
        $coursewareCategory = empty($id) ? new LnCourseCategory() : LnCourseCategory::findOne($id);
        $treeNodeService = new TreeNodeService();
        if ($id){
            $treeNodeService->updateTreeNode($coursewareCategory->tree_node_id, $category_name, $tree_parent_id);
            $tree_node_id = $coursewareCategory->tree_node_id;
        }else{
            $tree_node_id = $treeNodeService->addTreeNode('course-category', $category_name, $tree_parent_id);
        }

        if ($tree_node_id) {
            $treeNode = FwTreeNode::findOne($tree_node_id);
            $coursewareCategory->tree_node_id = $tree_node_id;
            $coursewareCategory->parent_category_id = empty($parent_category_id) ? null : $parent_category_id;
            $coursewareCategory->company_id = $companyId;
            $coursewareCategory->category_code = $treeNode->tree_node_code;
            $coursewareCategory->category_name = $category_name;
            $coursewareCategory->status = LnCourseCategory::STATUS_FLAG_NORMAL;
            if ($id){
                $coursewareCategory->update();
            }else {
                $coursewareCategory->save();
            }
            return ['result' => 'success'];
        }else{
            return ['result' => 'fail'];
        }
    }

    /*添加考试目录*/
    public function actionAddExaminationCategory(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $category_name = Yii::$app->request->post('category_name');
        $parent_category_id = Yii::$app->request->post('parent_category_id');
        $id = Yii::$app->request->post('id');
        if (empty($category_name)){
            return ['result' => 'fail'];
        }
        $companyId = Yii::$app->user->identity->company_id;
        $query = LnExaminationCategory::find(false)
            ->andFilterWhere(['category_name'=>$category_name, 'company_id'=>$companyId, 'status'=>LnExaminationCategory::STATUS_FLAG_NORMAL]);
        if (!empty($id)){
            $query->andFilterWhere(['<>', 'kid', $id]);
        }
        $count = $query->count();
        if ($count){
            return ['result' => 'reply', 'errmsg' => Yii::t('frontend', 'catelog_name_isset')];
        }
        $tree_parent_id = "";
        if (!empty($parent_category_id)){
            $parentCategory = LnExaminationCategory::findOne($parent_category_id);
            $tree_parent_id = $parentCategory->tree_node_id;
        }
        $treeNodeService = new TreeNodeService();
        $examinationCategory = empty($id) ? new LnExaminationCategory() : LnExaminationCategory::findOne($id);
        if ($id){
            $treeNodeService->updateTreeNode($examinationCategory->tree_node_id, $category_name, $tree_parent_id);
            $tree_node_id = $examinationCategory->tree_node_id;
        }else {
            $tree_node_id = $treeNodeService->addTreeNode('examination-category', $category_name, $tree_parent_id);
        }
        if ($tree_node_id) {
            $treeNode = FwTreeNode::findOne($tree_node_id);
            $examinationCategory->tree_node_id = $tree_node_id;
            $examinationCategory->parent_category_id = empty($parent_category_id) ? null : $parent_category_id;
            $examinationCategory->company_id = $companyId;
            $examinationCategory->category_code = $treeNode->tree_node_code;
            $examinationCategory->category_name = $category_name;
            $examinationCategory->status = LnExaminationCategory::STATUS_FLAG_NORMAL;
            if ($id){
                $examinationCategory->update();
            }else{
                $examinationCategory->save();
            }
            return ['result' => 'success'];
        }else{
            return ['result' => 'fail'];
        }
    }

    /**
     * 添加受众目录
     * @return array
     * @throws \Exception
     */
    public function actionAddAudienceCategory(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $category_name = Yii::$app->request->post('category_name');
        $parent_category_id = Yii::$app->request->post('parent_category_id');
        $id = Yii::$app->request->post('id');
        if (empty($category_name)){
            return ['result' => 'fail'];
        }
        $companyId = Yii::$app->user->identity->company_id;
        $userId = Yii::$app->user->getId();
        $query = SoAudienceCategory::find(false)
            ->andFilterWhere(['category_name'=>$category_name, 'owner_id'=>$userId, 'company_id'=>$companyId, 'status'=>LnExaminationCategory::STATUS_FLAG_NORMAL]);
        if (!empty($id)){
            $query->andFilterWhere(['<>', 'kid', $id]);
        }
        $count = $query->count();
        if ($count){
            return ['result' => 'reply', 'errmsg' => Yii::t('frontend', 'catelog_name_isset')];
        }
        $tree_parent_id = "";
        if (!empty($parent_category_id)){
            $parentCategory = SoAudienceCategory::findOne($parent_category_id);
            $tree_parent_id = $parentCategory->tree_node_id;
        }
        $treeNodeService = new TreeNodeService();
        $audienceCategory = empty($id) ? new SoAudienceCategory() : SoAudienceCategory::findOne($id);
        if ($id){
            $treeNodeService->updateTreeNode($audienceCategory->tree_node_id, $category_name, $tree_parent_id);
            $tree_node_id = $audienceCategory->tree_node_id;
        }else {
            $tree_node_id = $treeNodeService->addTreeNode('audience-category', $category_name, $tree_parent_id);
        }
        if ($tree_node_id) {
            $treeNode = FwTreeNode::findOne($tree_node_id);
            $audienceCategory->tree_node_id = $tree_node_id;
            $audienceCategory->parent_category_id = empty($parent_category_id) ? null : $parent_category_id;
            $audienceCategory->company_id = $companyId;
            $audienceCategory->owner_id = $userId;
            $audienceCategory->category_code = $treeNode->tree_node_code;
            $audienceCategory->category_name = $category_name;
            $audienceCategory->status = LnExaminationCategory::STATUS_FLAG_NORMAL;
            if ($id){
                $audienceCategory->update();
            }else{
                $audienceCategory->save();
            }
            return ['result' => 'success'];
        }else{
            return ['result' => 'fail'];
        }
    }

}
