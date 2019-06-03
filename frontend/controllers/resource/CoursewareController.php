<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:52
 */

namespace frontend\controllers\resource;

use common\models\learning\LnCourse;
use common\models\learning\LnCoursewareScormRelate;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnHomeworkResult;
use common\services\learning\ResourceDomainService;
use common\models\framework\FwUser;
use common\models\learning\LnCoursewareCategory;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnFiles;
use common\models\learning\LnResourceDomain;
use common\services\framework\UserCompanyService;
use common\services\scorm\ScormService;
use common\services\framework\UserDomainService;
use common\services\learning\CoursewareCategoryService;
use common\helpers\TStringHelper;
use yii;
use yii\web\Response;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use components\widgets\TPagination;
use frontend\base\BaseFrontController;
use common\services\learning\ResourceService;
use common\models\learning\LnComponent;
use common\helpers\TFileModelHelper;
use common\services\learning\CoursewareService;
use common\models\learning\LnCourseware;
use frontend\viewmodels\learning\CoursewareCommonForm;
use common\models\treemanager\FwTreeNode;
use common\models\treemanager\FwTreeType;
use common\services\framework\TreeNodeService;

class CoursewareController extends BaseFrontController {

    public $layout = 'frame';

    private $uploadStep = 'CoursewareStep';

    public $fileTypes = ['scorm'=>['zip'],'pdf'=>['pdf'],'video'=>['mp4','flv','wmv'],'office'=>['doc','docx','ppt','pptx','xls','xlsx'],'audio'=>['mp3']];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = ['save-homework-file'];
        return $behaviors;
    }
    /**
     * 管理页
     * @return string
     */
    public function actionManage(){
        return $this->render('manage');
    }

    /**
     * 列表页
     * @return string
     */
    public function actionList(){

        $pageSize = 10;

        if (Yii::$app->request->getQueryParam('PageSize') != null) {
            $pageSize = Yii::$app->request->getQueryParam('PageSize');
        }

        $userId = Yii::$app->user->getId();

        $domain = array();
        $userDomainService = new UserDomainService();
        $domainList = $userDomainService->getManagedListByUserId($userId);
        if ($domainList){
            foreach ($domainList as $t){
                $domain[$t->kid] = $t->domain_name;
            }
        }


        $service = new CoursewareService();
        $dataProvider = $service->Search(Yii::$app->request->queryParams,false,false,$domainList);
        $components = LnComponent::find(false)
            ->andFilterWhere(['=','component_type',LnComponent::RESOURCE_CODE])
            ->andFilterWhere(['=','is_need_upload',LnComponent::YES])
            ->all();

        $componentArray = [];
        $componentArray[] = Yii::t('frontend', 'select_course_type');
        foreach($components as $ct){
            $componentArray[$ct->kid] = $ct->title;
        }

        $count = $dataProvider->totalCount;
        $page = new TPagination(['defaultPageSize'=>$pageSize,'totalCount'=>$count]);
        $dataProvider->setPagination($page);


        return $this->renderAjax('list', [
            'componentArray'=>$componentArray,
            'page' => $page,
            'searchModel'=>$service,
            'dataProvider' => $dataProvider,
            'pageSize'=>$pageSize,
            'domain' => $domain,
            'TreeNodeKid' => Yii::$app->request->get('TreeNodeKid'),
        ]);
    }


    /**
     * 上传控制界而
     * 选择上传类型
     * @return string
     */
    public function actionUpload($error = ''){
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
        $lncomponent = LnComponent::find(false)->select('kid,title')->all();
        $lncomponent_title = array();
        foreach ($lncomponent as $val){
            $lncomponent_title[$val->kid] = urlencode($val->title);
        }
        $uploadBatch = date("YmdHis");
        return $this->render('upload',[
            'uploadStep' => $this->uploadStep,
            'uploadBatch' => $uploadBatch,
            'error' => $error,
            'tempArr' => $tempArr,
            'model' => $model,
            'request' => $request,
            'lncomponent_title' => $lncomponent_title,
        ]);
    }
    /**
     * 多媒体上传、验证、存储控制
     * @return array
     */
    public function actionSaveFile($uploadBatch){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $TFileModelHelper = new TFileModelHelper();
        $result = $TFileModelHelper->UploadFile($_FILES['Filedata'],$uploadBatch);
        return $result;
    }

    /**
     * 多媒体上传、验证、存储控制
     * @return array
     */
    public function actionSaveHomeworkFile($uploadBatch,$type = 0,$id = null,$course_id = '0',$course_reg_id = '0',$mod_id = '0',$mod_res_id = '0',$courseactivity_id = '0',$component_id = '0',$course_complete_id = '0',$res_complete_id = '0',$user_id=null,$company_id=null,$course_attempt_number = 1, $result_id = null){
        Yii::$app->response->format = Response::FORMAT_JSON;
        /*if($type == 1){
            $id = null;
        }*/
        $TFileModelHelper = new TFileModelHelper();
        $userId = Yii::$app->user->getId();
        if(empty($userId)){
            $userId = $user_id;
        }
        $companyId = Yii::$app->user->identity->company_id;
        if(empty($companyId)){
            $companyId = $company_id;
        }
        $courseModel = LnCourse::findOne($course_id);
        if (!empty($result_id) && !empty($courseModel) && $courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
            LnHomeworkResult::updateAll(['course_attempt_number' => $course_attempt_number], 'kid=:kid', [':kid' => $result_id]);

        //   echo "homework_id='{$id}' and user_id='{$userId}' and course_id='{$course_id}' and mod_id='{$mod_id}' and mod_res_id='{$mod_res_id}' and course_attempt_number<>'{$course_attempt_number}'";
/*
            LnHomeworkFile::deleteAll(
                'homework_id=:homework_id and user_id=:user_id and course_id=:course_id and mod_id=:mod_id and mod_res_id=:mod_res_id and course_attempt_number<>:course_attempt_number',
                [
                    ':homework_id' => $id,
                    ':user_id' => $userId,
                    ':course_id' => $course_id,
                    ':mod_id' => $mod_id,
                    ':mod_res_id' => $mod_res_id,
                    ':course_attempt_number' => $course_attempt_number-1,
                ]
            );
*/
        }

        $result = $TFileModelHelper->UploadHomeworkFile($userId,$companyId,$type,$_FILES['Filedata'],$uploadBatch,$id,'',$course_id,$course_reg_id,$mod_id,$mod_res_id,$courseactivity_id,$component_id,$course_complete_id,$res_complete_id,$course_attempt_number);
        return $result;
    }

    /**
     * 二，课件上传步骤3
     * 设置课件公共信息：管理域、及有效期
     * @return array|string
     */
    public function actionCommon(){
        $model = new LnCourseware();
        $string = array();
        $request = array();
        $count = 0;
        /*判断是否POST数据*/
        if(Yii::$app->request->isPost && Yii::$app->request->post('action') == 'common'){
            $file_id = Yii::$app->request->post('file_id');
            if (empty($file_id)) {
                $this->redirect(Yii::$app->urlManager->createUrl($this->id.'/upload'));
            }
            $file_name = Yii::$app->request->post('file_name');
            $courseware_name = Yii::$app->request->post('courseware_name');
            $entrance_address = Yii::$app->request->post('entrance_address');
            $courseware_time = Yii::$app->request->post('courseware_time');
            $default_credit = Yii::$app->request->post('default_credit');
            $is_display_pc = Yii::$app->request->post('is_display_pc');
            $is_display_mobile = Yii::$app->request->post('is_display_mobile');
            $is_allow_download = Yii::$app->request->post('is_allow_download');

            foreach ($file_id as $val){
                $row = array(
                    'file_id' => $val,
                    'file_name' => urlencode($file_name[$val]),
                    'courseware_name' => TStringHelper::clean_xss($courseware_name[$val]),
                    'entrance_address' => urlencode($entrance_address[$val]),
                    'courseware_time' => $courseware_time[$val],
                    'default_credit' => $default_credit[$val],
                    'is_display_pc' => empty($is_display_pc[$val]) ? 0 : 1,
                    'is_display_mobile' => empty($is_display_mobile[$val]) ? 0 :1,
                    'is_allow_download' => empty($is_allow_download[$val]) ? 0 :1,
                );
                $string[] = urldecode(json_encode($row));
                $count ++;
            }
            $string = json_encode($string);
            $domain_id = Yii::$app->request->post('domain_id');
            $request['domain_id'] = explode(',', $domain_id);
            $model->load(Yii::$app->request->post());
            $courseware_category_id = $model->courseware_category_id;
            $supplier = $model->vendor;
            $vendor_id = $model->vendor_id;
            $start_at = $model->start_at ? $model->start_at : date('Y-m-d');
            $end_at = $model->end_at;
            /*category_id转换成tree_node_id*/
            $coursewareCategories = new LnCoursewareCategory();
            $findOne = $coursewareCategories->findOne($courseware_category_id);
            $request['courseware_category_id'] = $courseware_category_id;
            $request['tree_node_id'] = isset($findOne) ? $findOne->tree_node_id : "";
            $request['vendor'] = $supplier;
            $request['vendor_id'] = $vendor_id;
            $request['start_at'] = $start_at;
            $request['end_at'] = $end_at;
        }else if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && Yii::$app->request->post('action') == 'confirm'){
            $file_id = Yii::$app->request->post('file_id');
            if (empty($file_id)) {
                $this->redirect(Yii::$app->urlManager->createUrl($this->id.'/upload'));
            }
            $file_name = Yii::$app->request->post('file_name');
            $courseware_name = Yii::$app->request->post('courseware_name');
            $entrance_address = Yii::$app->request->post('entrance_address');
            $courseware_time = Yii::$app->request->post('courseware_time');
            $default_credit = Yii::$app->request->post('default_credit');
            $is_display_pc = Yii::$app->request->post('is_display_pc');
            $is_display_mobile = Yii::$app->request->post('is_display_mobile');
            $is_allow_download = Yii::$app->request->post('is_allow_download');
            foreach ($file_id as $val){
                $row = array(
                    'file_id' => $val,
                    'file_name' => urlencode($file_name[$val]),
                    'courseware_name' => TStringHelper::clean_xss($courseware_name[$val]),
                    'entrance_address' => urlencode($entrance_address[$val]),
                    'courseware_time' => $courseware_time[$val],
                    'default_credit' => $default_credit[$val],
                    'is_display_pc' => empty($is_display_pc[$val]) ? 0 : 1,
                    'is_display_mobile' => empty($is_display_mobile[$val]) ? 0 :1,
                    'is_allow_download' => empty($is_allow_download[$val]) ? 0 :1,
                );
                $string[] = urldecode(json_encode($row));
            }
            $string = json_encode($string);
            $domain_id = Yii::$app->request->post('domain_id');
            $request['domain_id'] = explode(',', $domain_id);
            $courseware_category_id = $model->courseware_category_id;
            $supplier = $model->vendor;
            $vendor_id = $model->vendor_id;
            $start_at = $model->start_at;
            $end_at = $model->end_at;
            /*category_id转换成tree_node_id*/
            $coursewareCategories = new LnCoursewareCategory();
            $findOne = $coursewareCategories->findOne($courseware_category_id);
            $request['courseware_category_id'] = $courseware_category_id;
            $request['tree_node_id'] = $findOne->tree_node_id;
            $request['vendor'] = $supplier;
            $request['vendor_id'] = $vendor_id;
            $request['start_at'] = $start_at;
            $request['end_at'] = $end_at;
        }

        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getManagedListByUserId(Yii::$app->user->getId());
        $coursewareCategoryService = new CoursewareCategoryService();
        $coursewareCategories = $coursewareCategoryService->ListCoursewareCategroySelect();

        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId('courseware-category');

        if ($treeTypeId != "") {
            $treeTypeModel = FwTreeType::findOne($treeTypeId);
            $TreeTypeName = $treeTypeModel->getI18nName();
        }
        $coursewareService = new CoursewareService();
        $supplier = $coursewareService->getVendorByName($supplier);

        return $this->render('common',[
            'model' => $model,
            'domain' => $domain,
            'coursewareCategories' => $coursewareCategories,
            'file_info' => $string,
            'request' => $request,
            'count' => $count,
            'TreeTypeName' => $TreeTypeName,
            'supplier' => $supplier,
        ]);
    }


    /**
     * 三，课件上传步骤4
     * 课件上传信息确认及编辑界面
     * @return array|string
     */
    public function actionConfirm(){

        $model = new LnCourseware();
        $tempArr = array();
        $request = array();
        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && Yii::$app->request->post('action') == 'common'){
            $file_info = Yii::$app->request->post('file_info');
            $domain_id = Yii::$app->request->post('domain_id');
            $courseware_category_id = $model->courseware_category_id;
            $supplier = $model->vendor;
            $vendor_id = $model->vendor_id;
            $start_at = $model->start_at ? $model->start_at : date('Y-m-d');
            $end_at = $model->end_at;
            list($start_at, $end_at) = !empty($end_at) && strtotime($start_at) > strtotime($end_at) ? array($end_at, $start_at) : array($start_at, $end_at);
            /*解析字符串*/
            if (!empty($file_info) && empty($fresh)){
                $string = json_decode($file_info, true);
                foreach ( $string as $val){
                    $temp = json_decode($val, true);
                    $fileModel = LnFiles::findOne($temp['file_id']);
                    $component = LnComponent::findOne($fileModel->component_id);
                    $temp['file_icon'] = $component->icon;
                    $temp['component_text'] = $component->title;
                    $tempArr[] = $temp;
                }
            }
            $request['domain_id'] = join(',', $domain_id);
            $request['vendor'] = $supplier;
            $request['vendor_id'] = $vendor_id;
            $request['start_at'] = $start_at;
            $request['end_at'] = $end_at;
            $coursewareService = new CoursewareCategoryService();
            $courseware_category_id = $coursewareService->getCoursewareCategoryIdByTreeNodeId($courseware_category_id);/*node-kid->category_id*/
            $request['courseware_category_id'] = $courseware_category_id;
            $lnCoursewareCategory = LnCoursewareCategory::findOne($courseware_category_id);
            if (!empty($lnCoursewareCategory)) {
                $request['courseware_category_name'] = $lnCoursewareCategory->category_name;
            }
            else
            {
                $request['courseware_category_name'] = "";
            }
        }else if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && Yii::$app->request->post('action') == 'confirm'){
            $file_id = Yii::$app->request->post('file_id');
            if (empty($file_id)) {
                $this->redirect(Yii::$app->urlManager->createUrl($this->id.'/upload'));
            }
            $companyId = Yii::$app->user->identity->company_id;
            $domain_id = Yii::$app->request->post('domain_id');
            $courseware_name = Yii::$app->request->post('courseware_name');
            $entrance_address = Yii::$app->request->post('entrance_address');
            $courseware_time = Yii::$app->request->post('courseware_time');
            $default_credit = Yii::$app->request->post('default_credit');
            $is_display_pc = Yii::$app->request->post('is_display_pc');
            $is_display_mobile = Yii::$app->request->post('is_display_mobile');
            $is_allow_download = Yii::$app->request->post('is_allow_download');
            $TFileModelHelper = new TFileModelHelper();
            foreach ($file_id as $val){
                $LnCoursewareModel = new LnCourseware();
                $fileModel = LnFiles::findOne($val,false);
                $component = LnComponent::findOne($fileModel->component_id);
                $LnCoursewareModel->file_id = $val;
                if (empty($courseware_name[$val])){
                    $courseware_name[$val] = $fileModel->file_title;
                }
                $LnCoursewareModel->company_id = $companyId;
                $LnCoursewareModel->courseware_name = $courseware_name[$val];
                $LnCoursewareModel->entrance_address = $entrance_address[$val];
                $LnCoursewareModel->courseware_time = $courseware_time[$val];
                $LnCoursewareModel->courseware_desc = '';
                $LnCoursewareModel->courseware_code = $LnCoursewareModel->setCoursewareCode();
                $LnCoursewareModel->default_credit = $default_credit[$val];
                $LnCoursewareModel->courseware_category_id = $model->courseware_category_id;
                $LnCoursewareModel->is_display_pc = empty($is_display_pc[$val]) ? LnCourseware::DISPLAY_PC_NO : LnCourseware::DISPLAY_PC_YES;
                $LnCoursewareModel->is_display_mobile = empty($is_display_mobile[$val]) ? LnCourseware::DISPLAY_MOBILE_NO : LnCourseware::DISPLAY_MOBILE_YES;
                $LnCoursewareModel->is_allow_download = empty($is_allow_download[$val]) ? LnCourseware::ALLOW_DOWNLOAD_NO : LnCourseware::ALLOW_DOWNLOAD_YES;
                $LnCoursewareModel->vendor = $model->vendor ? TStringHelper::clean_xss($model->vendor) : null;
                $LnCoursewareModel->vendor_id = $model->vendor_id ? $model->vendor_id : null;
                $LnCoursewareModel->start_at = $model->start_at ? strtotime($model->start_at) : time();
                $LnCoursewareModel->end_at = $model->end_at ? strtotime($model->end_at.' 23:59:59') : null;
                $LnCoursewareModel->resource_version = $LnCoursewareModel->getCoursewareVersion($LnCoursewareModel->kid);
                $LnCoursewareModel->component_id = $component->kid;
                $LnCoursewareModel->needReturnKey = true;
                if($LnCoursewareModel->save()){
                    $coursewareId = $LnCoursewareModel->kid;
                    if (!is_array($domain_id)) $domain_id = explode(',', $domain_id);
                    $resourceDomainService = new ResourceDomainService();
                    $resourceDomainService->updateStatus($model->kid,LnResourceDomain::RESOURCE_TYPE_COURSEWARE,LnResourceDomain::STATUS_FLAG_STOP);
                    foreach ($domain_id as $t){
                        $findResource = LnResourceDomain::findOne(['resource_id'=>$coursewareId,'domain_id'=>$t]);
                        $resourceDomain = !empty($findResource->kid) ? $findResource : new LnResourceDomain();
                        $resourceDomain->resource_id = $coursewareId;
                        $resourceDomain->start_at = $model->start_at ? strtotime($model->start_at) : time();;
                        $resourceDomain->end_at = $model->end_at ? strtotime($model->end_at.' 23:59:59') : null;
                        $resourceDomain->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSEWARE;/*资源类型课程*/
                        $resourceDomain->domain_id = $t;
                        $resourceDomain->company_id = $companyId;
                        $resourceDomain->status = LnResourceDomain::STATUS_FLAG_NORMAL;
                        $resourceDomain->save();
                    }
                    if ($component->component_code == 'scorm')
                    {
                        /*针对Scorm课件进行初始化数据处理*/
                        if ($manifestInfo = $TFileModelHelper->isScormDir($fileModel->file_dir)) {
                            $scormService = new ScormService();
                            $scormModel = new LnCoursewareScorm();

                            if (!empty($LnCoursewareModel->default_credit))
                                $scormModel->total_score = $LnCoursewareModel->default_credit;
                            $scormModel->needReturnKey = true;
                            if ($scormModel->save()) {
                                $scormService->scorm_parse_scorm($scormModel, $manifestInfo);

                                $relateModel = new LnCoursewareScormRelate();
                                $relateModel->courseware_id = $coursewareId;
                                $relateModel->scorm_id = $scormModel->kid;
                                $relateModel->save();
                            }
                        }
                    }
                    else if ($component->component_code == 'aicc')
                    {
                        /*针对AICC课件进行初始化数据处理*/
                        if ($manifestInfo = $TFileModelHelper->isAiccDir($fileModel->file_dir)) {
                            $scormService = new ScormService();
                            $scormModel = new LnCoursewareScorm();

                            if (!empty($LnCoursewareModel->default_credit))
                                $scormModel->total_score = $LnCoursewareModel->default_credit;
                            $scormModel->needReturnKey = true;
                            if ($scormModel->save()) {
                                $scormService->scorm_parse_aicc($scormModel, $fileModel->file_dir);

                                $relateModel = new LnCoursewareScormRelate();
                                $relateModel->courseware_id = $coursewareId;
                                $relateModel->scorm_id = $scormModel->kid;
                                $relateModel->save();
                            }
                        }
                    }
                }
            }
            $this->redirect(Yii::$app->urlManager->createUrl($this->id.'/manage'));
        }
        return $this->render('confirm',[
            'request' => $request,
            'model'=>$model,
            'tempArr'=>$tempArr,
        ]);
    }
     public function  actionCodeView($coursewareId){
         $code = LnCourseware::findOne($coursewareId)->embed_code;
         return $this->renderAjax('code-view',['code'=>$code]);
     }

    /*预览上传课件*/
    public function actionPreview($coursewareId,$scoId = null)
    {
        $this->layout = "modalWin";
        if (!empty($coursewareId)) {
            //课程模块及组件
            $coursewareModel = LnCourseware::findOne($coursewareId);
            $coursewareName = $coursewareModel->courseware_name;
            $componentId = $coursewareModel->component_id;
            $compoentModel = LnComponent::findOne($componentId);
            $componentCode = $compoentModel->component_code;

            if (empty($scoId) && ($componentCode == LnComponent::COMPONENT_CODE_SCORM || $componentCode == LnComponent::COMPONENT_CODE_AICC)){
                /*$coursewareScormRelate = LnCoursewareScormRelate::findOne(['courseware_id' => $coursewareId]);
                if (!empty($coursewareScormRelate)){
                    $scoId = $coursewareScormRelate->scorm_id;
                }*/
                $scormService = new ScormService();
                $scormModel = $scormService->getScormByCoursewareId($coursewareId);

                if ($scoId == null || $scoId == "") {
                    $scoId = $scormModel->launch_scorm_sco_id;
                }
            }

            return $this->renderAjax('preview', [
                'coursewareName' => $coursewareName,
                'coursewareId' => $coursewareId,
                'componentCode' => $componentCode,
                'model' => $coursewareModel,
                'scoId' => $scoId
            ]);
        }
    }

    /*preview-iframe*/
    public function actionPreviewIframe($coursewareId,$scoId = null){
        $this->layout = "modalWin";
        if (!empty($coursewareId)) {
            //课程模块及组件
            $coursewareModel = LnCourseware::findOne($coursewareId);
            $coursewareName = $coursewareModel->courseware_name;
            $componentId = $coursewareModel->component_id;
            $compoentModel = LnComponent::findOne($componentId);
            $componentCode = $compoentModel->component_code;
            return $this->render('preview-iframe', [
                'coursewareName' => $coursewareName,
                'coursewareId' => $coursewareId,
                'componentCode' => $componentCode,
                'model' => $coursewareModel,
                'scoId' => $scoId
            ]);
        }
    }

    /**
     * 课程名称重复检测
     * @param string $courseware_name
     * @param string $id
     * @return array [result => success/fail]
     */
    public function actionCheckCourseware($courseware_name = "", $id = false){
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($courseware_name)) return ['result' => 'failure'];
        $uid = Yii::$app->session->getId();
        $coursewareService = new CoursewareService();
        $res = $coursewareService->checkCoursewareName($courseware_name,$uid, $id);
        return $res;
    }

    /**
     * 查看已经上传的文件
     * @param $kid
     * @return array
     */
    public function actionView($id,$download = false,$ajax = true){
        if($model = LnCourseware::findOne($id)){
            $courseName = $model->courseware_name;

            $component = LnComponent::findOne($model->component_id);
            if($component->component_code == 'pdf'){
                $TFileModelHelper = new TFileModelHelper();
                $TFileModelHelper->Play($model->file_id,$download,$courseName);
            }elseif($component->component_code == 'scorm'){
                if ($download){
                    $TFileModelHelper = new TFileModelHelper();
                    $TFileModelHelper->Play($model->file_id,$download,$courseName);
                }else {
                    if ($fileModel = LnFiles::findOne($model->file_id)) {
                        if (is_dir(Yii::$app->basePath . '/..' . $fileModel->file_dir . 'res/')) {
                            $httpPath = Yii::$app->urlManager->hostInfo . $fileModel->file_dir . 'res/'/*.$model->entry_path*/
                            ;
                            $this->redirect($httpPath);
                        } else {
                            /*文件不存在*/
                        }
                    }
                }
            }else{
                if($ajax){
                    $TFileModelHelper = new TFileModelHelper();
                    $TFileModelHelper->Play($model->file_id,$download,$courseName);
                }else{
                    return $this->render('view',[
                        'model'=>$model,
                        'download'=>$download,
                    ]);
                }
            }
        }
    }

    /**
     * 查看已经上传的文件
     * @param $kid
     * @return array
     */
    public function actionSee($id){
        $this->layout = false;
        $model = LnCourseware::findOne($id);
        $model->start_at = $model->start_at ? date('Y-m-d', $model->start_at) : "";
        $model->end_at = $model->end_at ? date('Y-m-d', $model->end_at) : "";
        $model->getFileLink();
        $lncomponent = LnComponent::findOne($model->component_id);
        $fileMod = LnFiles::findOne($model->file_id);
        /*课件域*/
        $userDomainService = new UserDomainService();
        $domain = $userDomainService->getSearchListByUserId(Yii::$app->user->getId());
        /*获取课件域*/
        $resourceDomainAll = LnResourceDomain::findAll(['resource_id' => $model->kid], false);
        $resourceDomain = array();
        foreach ($resourceDomainAll as $val){
            $resourceDomain[] = $val->domain_id;
        }
        /*tree_node_id*/
        /*category_id转换成tree_node_id*/
        $coursewareCategories = new LnCoursewareCategory();
        $findOne = $coursewareCategories->findOne($model->courseware_category_id);
        return $this->renderAjax('see', [
            'model' => $model,
            'component' => $lncomponent,
            'fileMod' => $fileMod,
            'domain' => $domain,
            'resource' => $resourceDomain,
            'tree_node_id' => $findOne->tree_node_id,
        ]);
    }



    /**
     * 课件编辑
     * @param string $id
     * @return array
     */
    public function actionEdit($id)
    {
        $this->layout = 'modalWin';
        $model = LnCourseware::findOne($id);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = Yii::$app->request->post();
            $model->start_at = !empty($model->start_at) ? strtotime($model->start_at) : time();
            $model->end_at = !empty($model->end_at) ? strtotime($model->end_at.' 23:59:59') : "";
            $model->is_display_pc = isset($data['is_display_pc']) ? LnCourseware::DISPLAY_PC_YES : LnCourseware::DISPLAY_PC_NO;
            $model->is_display_mobile = isset($data['is_display_mobile']) ? LnCourseware::DISPLAY_MOBILE_YES : LnCourseware::DISPLAY_MOBILE_NO;
            $model->is_allow_download = isset($data['is_allow_download']) ? LnCourseware::ALLOW_DOWNLOAD_YES : LnCourseware::ALLOW_DOWNLOAD_NO;
            $domain_id = Yii::$app->request->post('domain_id');
            if (empty($model->courseware_category_id) || empty($domain_id)){
                return ['result' => 'failure'];
            }
            $model->courseware_time = (!is_null($data['LnCourseware']['courseware_time']) || $data['LnCourseware']['courseware_time'] != '') ? intval($data['LnCourseware']['courseware_time']) : null;
            $service = new LnCourseware();
            $model->resource_version = $service->getCoursewareVersion();
            /*var_export($check);*/
            $coursewareService = new CoursewareCategoryService();
            $courseware_category_id = $coursewareService->getCoursewareCategoryIdByTreeNodeId($model->courseware_category_id);
            $model->courseware_category_id = $courseware_category_id;
            $model->vendor_id = empty($model->vendor_id) ? null : $model->vendor_id;
            if ($model->update()!==false){

//                $key = $model->kid;

                $domain = Yii::$app->request->post('domain_id');
                LnResourceDomain::deleteAll("`resource_id`='{$model->kid}'");/*删除所有资源信息*/
                $companyId = Yii::$app->user->identity->company_id;
                foreach ($domain as $val){
                    $lnresourcedomain = new LnResourceDomain();
                    $lnresourcedomain->resource_id = $model->kid;
                    $lnresourcedomain->domain_id = $val;
                    $lnresourcedomain->company_id = $companyId;
                    $lnresourcedomain->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSEWARE;
                    $lnresourcedomain->start_at = $model->start_at;
                    $lnresourcedomain->end_at = $model->end_at;
                    $findOne = $lnresourcedomain->findOne(['resource_id' => $model->kid, 'domain_id'=>$val]);
                    if (!empty($findOne->kid)){
                        $lnresourcedomain->kid = $findOne->kid;
                        $lnresourcedomain->is_deleted = LnResourceDomain::DELETE_FLAG_NO;
                        $lnresourcedomain->update();
                    }else{
                        $lnresourcedomain->save();
                    }
                }
                return ['result' => 'success'];
            }else{
                return ['result' => 'failure'];
            }
        }else {
            $model = LnCourseware::findOne($id);
            $model->start_at = $model->start_at ? date('Y-m-d', $model->start_at) : "";
            $model->end_at = $model->end_at ? date('Y-m-d', $model->end_at) : "";
            $model->getFileLink();
            $lncomponent = LnComponent::findOne($model->component_id);
            $fileMod = LnFiles::findOne($model->file_id);
            /*课件域*/
            $userDomainService = new UserDomainService();
            $domain = $userDomainService->getManagedListByUserId(Yii::$app->user->getId());
            /*获取课件域*/
            $resourceDomainAll = LnResourceDomain::findAll(['resource_id' => $model->kid], false);
            $resourceDomain = array();
            foreach ($resourceDomainAll as $val){
                $resourceDomain[] = $val->domain_id;
            }
            /*tree_node_id*/
            /*category_id转换成tree_node_id*/
            $coursewareCategories = new LnCoursewareCategory();
            $findOne = $coursewareCategories->findOne($model->courseware_category_id);

            $coursewareService = new CoursewareService();
            $supplier = $coursewareService->getVendorByName($model->vendor, $model->vendor_id);

            return $this->render('edit', [
                'model' => $model,
                'component' => $lncomponent,
                'fileMod' => $fileMod,
                'domain' => $domain,
                'resource' => $resourceDomain,
                'tree_node_id' => $findOne->tree_node_id,
                'supplier' => $supplier,
            ]);
        }
    }

    /**
     * 删除已经上传的文件
     * @param $kid
     * @return array
     */
    public function actionDelete($id){

        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            if (Yii::$app->request->isAjax) {
                $model = LnCourseware::findOne($id);
                if(isset($model) && $model != null){
                    $model->delete();

                    $service = new ResourceDomainService();
                    $list = LnResourceDomain::findAll(['resource_id'=>$id, 'resource_type'=>LnResourceDomain::RESOURCE_TYPE_COURSEWARE],false);
                    foreach ($list as $val){
                        $service->StopRelationship($val);
                    }
                    return ['result' => 'success'];
                }else{
                    return ['result' => 'failure'];
                }
            } else {
                return ['result' => 'failure'];
            }
        } catch (Exception $ex) {
            return ['result' => 'failure'];
        }
    }

    public function actionEmpty(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['result' => ''];
    }

    /**
     * 删除课件目录
     * @param $tree_node_id
     * @return array
     */
    public function actionDeleteCategory($tree_node_id){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new CoursewareCategoryService();
        //$category_id = $service->getCourseCategoryIdByTreeNodeId($tree_node_id);
        $service->deleteRelateData($tree_node_id);
        return ['result' => 'success'];
    }

    public function actionGetVendor(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $keyword = Yii::$app->request->get('q');
        $companyId = Yii::$app->request->get('companyId');
        if (empty($companyId)) $companyId = Yii::$app->user->identity->company_id;
        $service = new CoursewareService();
        $result = $service->getCoursewareVendor($keyword, $companyId);
        $data = array();
        if (!empty($result)) {
            foreach ($result as $items) {
                $title = $items['vendor_name'].(!empty($items['vendor_code']) ? '('.$items['vendor_code'].')' : '');
                $data[] = array('kid' => $items['kid'], 'title' => $title);
            }
            $data = array('results' => $data);
        }else{
            $data = array('results' => '');
        }
        return $data;
    }

} 