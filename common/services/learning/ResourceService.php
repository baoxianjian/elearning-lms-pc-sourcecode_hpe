<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/3/25
 * Time: 15:00
 */

namespace common\services\learning;

use common\base\BaseService;
use common\models\framework\FwUser;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseware;
use common\models\learning\LnCoursewareBook;
use common\models\learning\LnCoursewareCategory;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnCoursewareScormRelate;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationCategory;
use common\models\learning\LnExaminationPaper;
use common\models\learning\LnExaminationPaperCopy;
use common\models\learning\LnExamPaperQuestion;
use common\models\learning\LnHomework;
use common\models\learning\LnHomeworkFile;
use common\models\learning\LnInvestigationOption;
use common\models\learning\LnInvestigationQuestion;
use common\models\learning\LnResDanmu;
use common\models\learning\LnResourceDomain;
use common\models\learning\LnCertification;
use common\models\treemanager\FwTreeNode;
use common\services\framework\CompanyMenuService;
use common\services\framework\TreeNodeService;
use common\services\framework\UserDomainService;
use common\services\social\AudienceManageService;
use common\helpers\TStringHelper;
use Yii;
use yii\db\Query;
use common\models\learning\LnCourse;
use common\models\learning\LnFiles;
use common\models\learning\LnModRes;
use common\models\learning\LnCourseMods;
use common\models\learning\LnComponent;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\User;
use common\models\learning\LnInvestigation;
use common\models\learning\LnCourseCertification;
use common\models\framework\FwTag;
use common\models\learning\LnTeacher;
use common\models\learning\LnCourseReg;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnResComplete;
use components\widgets\TPagination;
use common\services\learning\ComponentService;
use common\services\framework\PointRuleService;

class ResourceService extends BaseService
{
    /**
     * 资源公共统计信息
     * @return \stdClass
     */
    public function getResourceStatus()
    {
        /*当前用户要查询的域*/
        $userDomainService = new UserDomainService();
        $uid = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;
        $domain_list = $userDomainService->getManagedListByUserId($uid);
        $user_domain = array();
        if ($domain_list) {
            $user_domain = ArrayHelper::map($domain_list, 'kid', 'kid');
            $user_domain = array_keys($user_domain);
        }
        $resource = [];

        /*课程*/
        $lnOnlineCourses = LnCourse::find(false);
        $lnF2FCourses = LnCourse::find(false);
        if ($user_domain) {
//            $resourceDomainService = new LnResourceDomain();
//            $resourceResultSql = $resourceDomainService->find(false)
//                ->andFilterWhere(['in', 'domain_id', $user_domain])
//                ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSE])
//                ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
//                ->select("resource_id")
//                ->distinct()
//                ->createCommand()->getRawSql();
//            $lnOnlineCourses->andWhere('kid in ('.$resourceResultSql.')');
//
//            $lnF2FCourses->andWhere('kid in ('.$resourceResultSql.')');


            $lnOnlineCourses->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in', LnResourceDomain::tableName() . '.domain_id', $user_domain])
                ->distinct();

            $lnF2FCourses->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in', LnResourceDomain::tableName() . '.domain_id', $user_domain])
                ->distinct();
        } else {
            $lnOnlineCourses->andWhere('kid is null');

            $lnF2FCourses->andWhere('kid is null');
        }
        $resource['course_online'] = $lnOnlineCourses->andFilterWhere(['=', 'course_type', LnCourse::COURSE_TYPE_ONLINE])->count();
        $resource['course_face'] = $lnF2FCourses->andFilterWhere(['=', 'course_type', LnCourse::COURSE_TYPE_FACETOFACE])->count();


        /*课件*/
        $lnCoursesware = LnCourseware::find(false);
        if ($user_domain) {
//            $resourceDomainService = new LnResourceDomain();
//            $resourceResultSql = $resourceDomainService->find(false)
//                ->andFilterWhere(['in', 'domain_id', $user_domain])
//                ->andFilterWhere(['=', 'resource_type', LnResourceDomain::RESOURCE_TYPE_COURSEWARE])
//                ->andFilterWhere(['=', 'status', LnResourceDomain::STATUS_FLAG_NORMAL])
//                ->select("resource_id")
//                ->distinct()
//                ->createCommand()->getRawSql();
//            $lnCoursesware->andWhere('kid in ('.$resourceResultSql.')');

            $lnCoursesware->innerJoinWith('lnResourceDomains')
                ->andFilterWhere(['in', LnResourceDomain::tableName() . '.domain_id', $user_domain])
                ->distinct();
        } else {
            $lnCoursesware->andWhere('kid is null');
        }

        $coursewareCount = $lnCoursesware->count();
        $resource['courseware'] = $coursewareCount;

        /*调查*/
        $lnInvestigationCount = LnInvestigation::find(false)
            ->andFilterWhere(["=", "company_id", $companyId])
            ->count();
        $resource['investigationCount'] = $lnInvestigationCount;

        /*证书*/
        $lnCertificationCount = LnCertification::find(false)
            ->andFilterWhere(["=", "company_id", $companyId])
            ->count();
        $resource['lnCertificationCount'] = $lnCertificationCount;

        /*讲师*/
        $lnTeacherCount = LnTeacher::find(false)
            ->andFilterWhere(["=", "company_id", $companyId])
            ->count();
        $resource['lnTeacherCount'] = $lnTeacherCount;

        /*报表*/
        $companyMenuService = new CompanyMenuService();
        $reportMenuModels = $companyMenuService->getCompanyMenuByType($companyId,"report_new");
        $lnReportCount = count($reportMenuModels);
        $resource['lnReportCount'] = $lnReportCount;

        /*标签*/
        $TagCount = FwTag::find(false)
            ->andFilterWhere(["=", "company_id", $companyId])
            ->count();
        $resource['TagCount'] = $TagCount;

        /*考试*/
        $lnExaminationCount = LnExamination::find(false)
            ->andFilterWhere(["=", "company_id", $companyId])
            ->count();
        $resource['lnExaminationCount'] = $lnExaminationCount;

        /*场地*/
        $trainingAddressService = new TrainingAddressService();
        $lnTrainingAddressCount = $trainingAddressService->countData(null, $companyId);
        $resource['lnTrainingAddressCount'] = $lnTrainingAddressCount;

        /*供应商*/
        $vendorService = new VendorService();
        $lnVendorCount = $vendorService->countData(null, $companyId);
        $resource['lnVendorCount'] = $lnVendorCount;

        /*受众*/
        $audienceService = new AudienceManageService();
        $soAudienceCount = $audienceService->getAudienceCount($companyId, $uid);
        $resource['soAudienceCount'] = $soAudienceCount;
        
        /*积分规则数量*/
        $pointRuleService = new PointRuleService();
        $resource['pointRuleCount'] = $pointRuleService->getPointRuleCount($companyId);
        
        return $resource;
    }


    /**
     * 获取所有已经上传的Scorm课件
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getScorms()
    {
        $query = new Query();
        $Scorms = $query->select('lc.*,lf.file_dir,lf.file_name')
            ->from('{{%ln_Scorm}} lc')
            ->leftJoin("{{%ln_files}} lf", "lc.file_id = lf.kid AND lf.is_deleted = '0'")
            ->where("lc.is_deleted = '0'")
            ->all();
        return $Scorms;
    }


    /**
     * 获取当前用户正在上传的文件，临时状态
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTempFiles($code)
    {
        $files = json_decode(Yii::$app->session->get($code), true);
        return $files;
    }

    /**
     * 获取单个课程的单元模块，及模块组件
     * @param $course_id
     * @return array
     */
    public function getCourseMods($course_id, $returnHtml = false, $domain=null, $isCourseType = LnCourse::COURSE_TYPE_ONLINE, $isCopy = false)
    {   
        $courseMods = LnCourseMods::find(false)->andWhere(['course_id' => $course_id])->orderBy('mod_num')->all();
        $newMods = [];
        $courseService = new CourseService();
        foreach ($courseMods as $courseMod) {
            $newMods[$courseMod['mod_num']] = $courseMod->attributes;
            $modRes = LnModRes::find(false)->andWhere(['mod_id' => $courseMod['kid']])->orderBy('sequence_number')->all();
            $time = 0;
            foreach ($modRes as $resMode) {
                $modResId = $resMode['kid'];
                $componentId = $resMode['component_id'];
                $componentModel = LnComponent::findOne($componentId);
                $componentCode = $componentModel->component_code;
                $componentIcon = $componentModel->icon;
                $componentName = $componentModel->title;
                if (!empty($resMode['courseware_id'])) {
                    $coursewareId = $resMode['courseware_id'];
                    if ($isCopy && $componentCode == LnComponent::COMPONENT_CODE_BOOK){
                        $coursewareId = $courseService->copyBook($coursewareId);
                    }elseif ($isCopy && $componentCode == LnComponent::COMPONENT_CODE_HTML){
                        $coursewareId = $courseService->copyHtml($coursewareId);
                    }

                    if ($item = LnCourseware::findOne($coursewareId)) {
                        $item->modResId = $modResId;

                        if($resMode->res_time)
                        {
                            $time += intval($resMode->res_time);
                        }
                        else
                        {
                            $time += intval($item->courseware_time);
                        }

                        $newItem = [
                            'itemId' => $item->kid,
                            'itemName' => $item->courseware_name,
                            'componentId' => $componentId,
                            'componentName' => $componentName,
                            'modResId' => $modResId,
                            'isCourseware' => true,
                            'modRes' => $resMode,
                            'item' => $item,
                            'mod_num' => $courseMod['mod_num'],
                            'componentCode' => $componentCode,
                            'componentIcon' => $componentIcon,
                            'sequence_number' => $resMode->sequence_number
                        ];

                        $newMods[$courseMod['mod_num']]['courseitems'][] = $newItem;
                    }
                } elseif (!empty($resMode['courseactivity_id'])) {
                    $courseActivityModel = LnCourseactivity::findOne($resMode['courseactivity_id']);
                    if (!empty($courseActivityModel->kid)) {
                        $time += intval($resMode->res_time);

                        if ($componentCode == 'investigation'){
                            $item = LnInvestigation::findOne($courseActivityModel->object_id);
                        }else if ($componentCode == 'examination')
                        {
                            $item = LnExamination::findOne($courseActivityModel->object_id);
                            
                            if(!$resMode->res_time)
                            {
                                $time += intval($item->limit_time);
                            }
                            
                        }else if ($componentCode == 'homework'){
                            if ($isCopy) {
                                $courseActivityModel->object_id = $courseService->copyHomework($courseActivityModel->object_id);
                            }
                            $item = LnHomework::findOne($courseActivityModel->object_id);
                        }

                        if (!empty($item)) {
                            $item->modResId = $modResId;
                            $newItem = [
                                'itemId' => $item->kid,
                                'itemName' => $item->title,
                                'componentId' => $componentId,
                                'componentName' => $componentName,
                                'modResId' => $modResId,
                                'isCourseware' => false,
                                'modRes' => $resMode,
                                'item' => $item,
                                'mod_num' => $courseMod['mod_num'],
                                'componentCode' => $componentCode,
                                'componentIcon' => $componentIcon,
                                'sequence_number' => $resMode->sequence_number
                            ];
                            $newMods[$courseMod['mod_num']]['courseitems'][] = $newItem;
                        }else{

                        }
                    }
                }
            }
            $resource = $newMods[$courseMod['mod_num']]['courseitems'];
            if ($returnHtml) {
                $newMods[$courseMod['mod_num']]['resource'] = $this->getCourseComponent($resource, $domain, $isCourseType);
            }
            $newMods[$courseMod['mod_num']]['time'] = $time;
        }
        return $newMods;
    }

    /**
     * @param $resoource
     * @param $domain_id
     */
    public function GetPostResource($resource, $domain_id, $isCourseType = LnCourse::COURSE_TYPE_ONLINE){
        if (!empty($resource)){
            foreach ($resource as $mod_num => $items){
                $mod_items = [];
                if (!empty($items['coursewares'])){
                    foreach ($items['coursewares'] as $i => $v_1) {
                        $componentName = LnComponent::findOne($items->component_id)->title;

                        foreach ($v_1 as $k => $val){
                            if ($item = LnCourseware::findOne($val)) {
                                $item = [
                                    'itemId'=>$item->kid,
                                    'isCourseware'=>true,
                                    'item'=>$item,
                                    'componentId' => $item->component_id,
                                    'componentName' => $componentName,
                                    'mod_num' => $items['mod_num'],
                                    'component_code' => $i,
                                    'sequence_number' => $k,
                                ];
                                $mod_items[$k] = $item;
                            }
                        }
                    }
                }
                if (!empty($items['activity'])){
                    foreach ($items['activity'] as $i => $v_2) {
                        $componentId = LnComponent::findOne(['component_code'=> $i]);
                        foreach ($v_2 as $tt => $val){
                            if ($i == 'examination'){
                                $val_model = new LnExamination();
                            }else if ($i == 'investigation'){
                                $val_model = new LnInvestigation();
                            }else if ($i == 'homework'){
                                $val_model = new LnHomework();
                            }
                            if ($item = $val_model->findOne($val)) {
                                $item = [
                                    'itemId' => $item->kid,
                                    'isCourseware'=> false,
                                    'item'=> $item,
                                    'componentId' => $componentId->kid,
                                    'componentName' => $componentId->title,
                                    'mod_num' => $items['mod_num'],
                                    'component_code' => $i,
                                    'sequence_number' => $tt,
                                ];
                                $mod_items[$tt] = $item;
                            }else{
                                //var_dump($item);
                            }
                        }
                    }
                }
                ksort($mod_items);
                $resource[$mod_num]['resource'] = $this->getCourseComponent($mod_items, $domain_id, $isCourseType);
            }
        }
        return $resource;
        //return
    }
    /**
     * @param $resource
     * @param $domain_id
     * @return string|void
     */
    public function getCourseComponent($resource, $domain, $isCourseType = LnCourse::COURSE_TYPE_ONLINE){
        if (empty($resource)) return null;
        $html = "";
        $componentService = new ComponentService();
        $is_setting_component = $componentService->getRecordScore();
        foreach ($resource as $item){
            $component = $componentService->getCompoentByComponentKid($item['componentId']);
            $action_url = !empty($component->action_url) ? Yii::$app->urlManager->createUrl([$component->action_url]) : '';
            $icon = !empty($component->icon) ? $component->icon : '';
            $html .= '<li id="ware_'.$item['itemId'].'" class="component componentSelected" data-component="'.$component->component_code.'">';
            //资源
            if ($item['isCourseware']) {
                $resource_type = 'coursewares';
                $typeno = '';
                if ($component->component_code == 'html') {
                    if ($item['item']->entry_mode == LnCourseware::ENTRY_MODE_UPLOAD){
                        $typeno = 3;
                    }
                }else if ($component->component_code == 'book') {

                } else {

                }
                $action_url = empty($action_url) ? Yii::$app->urlManager->createUrl(['resource/courseware']) : $action_url;
                $html .= '<a href="javascript:;" class="pull-left component-tbody" onclick="loadModalFormData(\'addModal\',\'' . $action_url . '?component_id=' . $item['componentId'] . '&sequence_number=' . $item['mod_num'] . '&domain_id=' . $domain . '&component_code='.$component->component_code.'&typeno='.$typeno.'&id=' . $item['itemId'] . '\',this,\''.$resource_type.'\',\''.$component->component_code.'\',\''.$component->window_mode.'\');"> ' . $icon . '&nbsp;' . TStringHelper::clean_xss($item['item']->courseware_name) . '</a>';
            }else{/*活动*/
                $resource_type = 'activity';
                $action_url = empty($action_url) ? Yii::$app->urlManager->createUrl(['resource/activity']) : $action_url;
                $user = FwUser::findIdentity($item['item']->created_by);
                if ($component->component_code == 'examination'){
                    $paperCopy = LnExaminationPaperCopy::findOne($item['item']->examination_paper_copy_id);
                    $html .= '<a href="javascript:;" class="pull-left component-tbody" onclick="loadModalFormData(\'addModal\',\'' . $action_url . '?component_id=' . $item['componentId'] . '&sequence_number=' . $item['mod_num'] . '&domain_id=' . $domain . '&component_code='.$component->component_code.'&id=' . $item['itemId'] . '\',this,\''.$resource_type.'\',\''.$component->component_code.'\',\''.$component->window_mode.'\');"> <font>' . $icon . '&nbsp;' . TStringHelper::clean_xss($item['item']->title) . '</font>&nbsp;<font>试题数目：'.$paperCopy->examination_question_number.'</font></a>';
                }else if ($component->component_code == 'investigation'){
                    $html .= '<a href="javascript:;" class="pull-left component-tbody" onclick="loadModalFormData(\'addModal\',\'' . $action_url . '?component_id=' . $item['componentId'] . '&sequence_number=' . $item['mod_num'] . '&domain_id=' . $domain . '&component_code='.$component->component_code.'&id=' . $item['itemId'] . '\',this,\''.$resource_type.'\',\''.$component->component_code.'\',\''.$component->window_mode.'\');"><font> ' . $icon . '&nbsp;' . TStringHelper::clean_xss($item['item']->title) . '</font>&nbsp;<font>'.($item['item']->investigation_type == LnInvestigation::INVESTIGATION_TYPE_SURVEY ? Yii::t('frontend', 'inv_result_diaocha') : Yii::t('frontend', 'vote')).'</font>&nbsp;<font>'.$user->real_name .'('.$user->email.')</font></a>';
                }else{
                    $html .= '<a href="javascript:;" class="pull-left component-tbody" onclick="loadModalFormData(\'addModal\',\'' . $action_url . '?component_id=' . $item['componentId'] . '&sequence_number=' . $item['mod_num'] . '&domain_id=' . $domain . '&component_code='.$component->component_code.'&id=' . $item['itemId'] . '\',this,\''.$resource_type.'\',\''.$component->component_code.'\',\''.$component->window_mode.'\');"> ' . $icon . '&nbsp;' . TStringHelper::clean_xss($item['item']->title) . '</a>';
                }

            }
            if($item['isCourseware']){
                 $html .= '<input type="hidden" data-modnum="'.$item['mod_num'].'" data-restitle="'.$item['item']->courseware_name.'" data-compnenttitle="'.$component->title.'" data-isscore="'.$component->is_record_score.'" data-completerule="'.$component->complete_rule.'" class="componentid" name="resource['. $item['mod_num'] .']['.$resource_type.']['.$component->component_code.']['.$item['sequence_number'].']" value="' . $item['itemId'] . '"/>';
                 $html .= '<div class="addAction pull-right"><a href="javascript:;" class="glyphicon glyphicon-remove del" title="'.Yii::t('frontend', 'delete_button').'"></a>';
                 //if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE && in_array($component->component_code, $is_setting_component)){
                 if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){
                 	$html .= '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id='.$item['componentId'].'&sequence_number='. $item['mod_num'].'&domain_id='.$domain.'&component_code='.$component->component_code.'&id='.$item['itemId'].'&isCourseType='.$isCourseType.'&title='.urlencode($item['item']->courseware_name).'\',this,\''."$resource_type".'\',\''."$component->component_code".'\',\''."0".'\');">'.Yii::t('frontend', 'configuration').'</a>';
                 }else if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                 	$html .= '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id='.$item['componentId'].'&sequence_number='. $item['mod_num'].'&domain_id='.$domain.'&component_code='.$component->component_code.'&id='.$item['itemId'].'&isCourseType='.$isCourseType.'&title='.urlencode($item['item']->courseware_name).'\',this,\''."$resource_type".'\',\''."$component->component_code".'\',\''."0".'\');">'.Yii::t('frontend', 'configuration').'</a>';
                 }

           }else{
                $html .= '<input type="hidden" data-modnum="'.$item['mod_num'].'" data-restitle="'.$item['item']->title.'" data-compnenttitle="'.$component->title.'" data-isscore="'.$component->is_record_score.'" data-completerule="'.$component->complete_rule.'" class="componentid" name="resource['. $item['mod_num'] .']['.$resource_type.']['.$component->component_code.']['.$item['sequence_number'].']" value="' . $item['itemId'] . '"/>';
                $html .= '<div class="addAction pull-right"><a href="javascript:;" class="glyphicon glyphicon-remove del" title="'.Yii::t('frontend', 'delete_button').'"></a>';
                //if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE && in_array($component->component_code, $is_setting_component)){
                if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){
                	$html .= '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id='.$item['componentId'].'&sequence_number='. $item['mod_num'].'&domain_id='.$domain.'&component_code='.$component->component_code.'&id='.$item['itemId'].'&isCourseType='.$isCourseType.'&title='.urlencode($item['item']->title) .'\',this,\''."$resource_type".'\',\''."$component->component_code".'\',\''."0".'\');">'.Yii::t('frontend', 'configuration').'</a>';
                }else if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                	$html .= '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id='.$item['componentId'].'&sequence_number='. $item['mod_num'].'&domain_id='.$domain.'&component_code='.$component->component_code.'&id='.$item['itemId'].'&isCourseType='.$isCourseType.'&title='.urlencode($item['item']->title) .'\',this,\''."$resource_type".'\',\''."$component->component_code".'\',\''."0".'\');">'.Yii::t('frontend', 'configuration').'</a>';
                }
           }
            $html .= '</li>';
        }

        return $html;
    }

    /**
     * 构造资源表单
     * @param $resource
     * @return string
     */
    public function GetResourceInput($resource){
        $input = '';
        if (!empty($resource)){
            foreach($resource as $item){
                $mod_num = $item['mod_num'];
                $input .= '<input type="hidden" name="resource['.$mod_num.'][kid]" value="'.$item['kid'].'" />'."\r\n";
                $input .= '<input type="hidden" name="resource['.$mod_num.'][mod_num]" value="'.$item['mod_num'].'" />'."\r\n";
                $input .= '<input type="hidden" name="resource['.$mod_num.'][mod_name]" value="'.$item['mod_name'].'" />'."\r\n";
                $input .= '<textarea style="display: none;" name="resource['.$mod_num.'][mod_desc]">'.$item['mod_desc'].'</textarea>'."\r\n";
                if (!empty($item['coursewares'])){
                    foreach ($item['coursewares'] as $k => $v){
                        if (is_array($v)){
                            foreach ($v as $i=>$t){
                                $input .= '<input type="hidden" class="componentid" name="resource['. $mod_num .'][coursewares]['.$k.']['.$i.']" value="' . $t . '"/>'."\r\n";
                            }
                        }else{
                            $input .= '<input type="hidden" class="componentid" name="resource['. $mod_num .'][coursewares]['.$k.']" value="' . $v . '"/>'."\r\n";//理论上不存在
                        }
                    }
                }
                if (!empty($item['activity'])){
                    foreach ($item['activity'] as $key => $v){
                        if (is_array($v)){
                            foreach ($v as $i=>$t){
                                $input .= '<input type="hidden" class="componentid" name="resource['. $mod_num .'][activity]['.$key.']['.$i.']" value="' . $t . '"/>'."\r\n";
                            }
                        }else{
                            $input .= '<input type="hidden" class="componentid" name="resource['. $mod_num .'][activity]['.$key.']" value="' . $v . '"/>'."\r\n";//理论上不存在
                        }
                    }
                }
                if (!empty($item['config'])){
                	foreach ($item['config'] as $sk => $ssval){
                		$json_config = json_decode($ssval, true);
                		$input .= '<input type="hidden" value="'.htmlspecialchars($ssval).'" id="con_'. $mod_num .'_'.$json_config['kid'].'" name="resource['. $mod_num .'][config]['.$json_config['kid'].']" data-name="config" data-title="'.htmlspecialchars($json_config['title']).'" data-isfinish="'.$json_config['isfinish'].'" data-score="'.$json_config['score'].'" data-componet="'.$json_config['componet'].'" data-iscore="'.$json_config['isscore'].'" data-kid="'.$json_config['kid'].'">';
                	}
                }
                if (!empty($item['rescore'])){
                	foreach ($item['rescore'] as $kk => $sval){
                		$json_decode = json_decode($sval, true);
                		$input .= '<input id="socl_'.$json_decode['modnum'].'_'.$json_decode['id'].'" data-score="'.$json_decode['score'].'" data-id="'.$json_decode['id'].'" data-modnum="'.$json_decode['modnum'].'" name="resource['.$json_decode['modnum'].'][rescore]['.$json_decode['id'].']" type="hidden" value="'.htmlspecialchars($sval).'">';
                	}
                }
            }
        }
        return $input;
    }

    /**
     * 重新加载数据
     * @param $model
     * @param $attributes
     * @return bool]
     */
    public function serviceLoadModel(&$model, $attributes){
        if (empty($attributes)) return ;
        foreach ($attributes as $key => $val){
            $model->$key = $val;
        }
    }

    /**
     * 复制课件
     * @param $kid 课件ID
     * @param $companyId 目标企业
     * @return bool|null
     */
    public function copyCourseware($kid, $companyId, $domain = null)
    {
        $loginUserCompanyId = Yii::$app->user->identity->company_id;
        if ($loginUserCompanyId == $companyId) {
            return $kid;
        }

        $courseware = LnCourseware::findOne($kid);
        if (empty($courseware)) {
            return $kid;
        }
        /*判断是否存在相同的文件*/
        /*$getOwnerInfo = LnCourseware::find(false)->andFilterWhere(['company_id' => $companyId,'courseware_name' => $courseware->courseware_name])->one();
        if (!empty($getOwnerInfo)){
            return $getOwnerInfo->kid;
        }*/
        /*判断是否存在临时目录*/
        $coursewareCategoryTemp = LnCoursewareCategory::find(false)->andFilterWhere(['company_id' => $companyId, 'category_name' => Yii::t('common', 'temp_category')])->one();
        if (empty($coursewareCategoryTemp)) {
            $treeNodeService = new TreeNodeService();
            $tree_node_id = $treeNodeService->addTreeNode('courseware-category', Yii::t('common', 'temp_category'), "");
            $treeNode = FwTreeNode::findOne($tree_node_id);
            $coursewareCategory = new LnCoursewareCategory();
            $coursewareCategory->tree_node_id = $tree_node_id;
            $coursewareCategory->parent_category_id = null;
            $coursewareCategory->company_id = $companyId;
            $coursewareCategory->category_code = $treeNode->tree_node_code;
            $coursewareCategory->category_name = Yii::t('common', 'temp_category');
            $coursewareCategory->status = LnCoursewareCategory::STATUS_FLAG_NORMAL;
            $coursewareCategory->needReturnKey = true;
            $coursewareCategory->save();
            $coursewareCategoryId = $coursewareCategory->kid;
        } else {
            $coursewareCategoryId = $coursewareCategoryTemp->kid;
        }
        $lncomponent = LnComponent::findOne($courseware->component_id);
        $LnCoursewareModel = new LnCourseware();
        $attributes = $courseware->attributes;
        unset($attributes['kid']);
        unset($attributes['course_code']);
        unset($attributes['version']);
        unset($attributes['created_by']);
        unset($attributes['updated_by']);
        unset($attributes['updated_at']);
        $this->serviceLoadModel($LnCoursewareModel, $attributes);
        $LnCoursewareModel->courseware_category_id = $coursewareCategoryId;
        $LnCoursewareModel->courseware_code = $LnCoursewareModel->setCoursewareCode();
        $LnCoursewareModel->company_id = $companyId;
        $LnCoursewareModel->needReturnKey = true;
        if ($LnCoursewareModel->save() === false) {
            return $kid;
        }
        /*scorm aicc*/
        if ($lncomponent->component_code == LnComponent::COMPONENT_CODE_AICC || $lncomponent->component_code == LnComponent::COMPONENT_CODE_SCORM){
            $scormRelate = LnCoursewareScormRelate::findOne(['courseware_id' => $kid]);
            if (!empty($scormRelate)) {
                $scormRelateAttribute = $scormRelate->attributes;
                unset($scormRelateAttribute['kid']);
                unset($scormRelateAttribute['courseware_id']);
                $scormRelateModel = new LnCoursewareScormRelate();
                $this->serviceLoadModel($scormRelateModel, $scormRelateAttribute);
                $scormRelateModel->courseware_id = $LnCoursewareModel->kid;
                $scormRelateModel->save();
            }
        }elseif ($lncomponent->component_code == LnComponent::COMPONENT_CODE_BOOK){
            /*图书复制*/
            $coursewarebook = LnCoursewareBook::findOne($kid);
            if (!empty($coursewarebook)){
                $coursewarebookModel = new LnCoursewareBook();
                $bookAttribute = $coursewarebook->attributes;
                unset($bookAttribute['kid']);
                unset($bookAttribute['courware_id']);
                $this->serviceLoadModel($coursewarebookModel, $bookAttribute);
                $coursewarebookModel->courware_id = $LnCoursewareModel->kid;
                $coursewarebookModel->save();
            }
        }
        /*添加资源与域关系*/
        if (!empty($domain)){
            foreach ($domain as $i){
                $resourceDomain = new LnResourceDomain();
                $resourceDomain->resource_id = $LnCoursewareModel->kid;
                $resourceDomain->domain_id = $i;
                $resourceDomain->company_id = $companyId;
                $resourceDomain->resource_type = LnResourceDomain::RESOURCE_TYPE_COURSEWARE;
                $resourceDomain->start_at = time();
                $resourceDomain->status = LnResourceDomain::STATUS_FLAG_NORMAL;
                $resourceDomain->save();
            }
        }
        return $LnCoursewareModel->kid;
    }

    /**
     * 复制活动组课件：考试、作业、调查
     * @param $kid
     * @param $companyId
     * @return mixed
     */
    public function copyCourseactivity($componentCode, $kid, $companyId){
        $loginUserCompanyId = Yii::$app->user->identity->company_id;
        if ($loginUserCompanyId == $companyId && $componentCode != LnComponent::COMPONENT_CODE_HOMEWORK) {
            return $kid;
        }

        if ($componentCode == LnComponent::COMPONENT_CODE_EXAMINATION){
            $exam = LnExamination::findOne($kid);
            if (empty($exam)) return $kid;
            /*判断数据是否存在于同一企业下*/
            if ($exam->company_id == $companyId){
                return $kid;
            }
            $examService = new ExaminationService();
            $result = $examService->copyAllExamination($exam, $companyId);
            return $result['examination_id'];
        } elseif ($componentCode == LnComponent::COMPONENT_CODE_HOMEWORK){
            $service = new CourseService();
            return $service->copyHomework($kid, $companyId);
        } elseif ($componentCode == LnComponent::COMPONENT_CODE_INVESTIGATION){
            $investigation = LnInvestigation::findOne($kid);
            if (empty($investigation)) return $kid;
            if ($investigation->company_id == $companyId){
                return $kid;
            }
            $attribute = $investigation->attributes;
            $model = new LnInvestigation();
            unset($attribute['kid']);
            unset($attribute['company_id']);
            unset($attribute['version']);
            unset($attribute['created_at']);
            unset($attribute['created_by']);
            unset($attribute['updated_by']);
            unset($attribute['updated_at']);
            $this->serviceLoadModel($model, $attribute);
            $model->company_id = $companyId;
            $model->needReturnKey = true;
            if ($model->save() === false){
                return $kid;
            }
            /*question*/
            $investigationQuestion = LnInvestigationQuestion::findAll(['investigation_id' => $kid]);
            if (empty($investigationQuestion)) return $kid;
            foreach ($investigationQuestion as $val){
                $investigationQuestionOption = LnInvestigationOption::findAll(['investigation_id' => $kid, 'investigation_question_id' => $val->kid]);;
                $item = $val->attributes;
                unset($item['kid']);
                unset($item['investigation_id']);
                unset($item['version']);
                unset($item['created_at']);
                unset($item['created_by']);
                unset($item['updated_by']);
                unset($item['updated_at']);
                $investigationQuestionModel = new LnInvestigationQuestion();
                $this->serviceLoadModel($investigationQuestionModel, $item);
                $investigationQuestionModel->investigation_id = $model->kid;
                $investigationQuestionModel->needReturnKey = true;
                if ($investigationQuestionModel->save() === false){
                    continue ;
                }
                if (empty($investigationQuestionOption)){
                    continue ;
                }
                foreach ($investigationQuestionOption as $t){
                    $tt = $t->attributes;
                    unset($tt['kid']);
                    unset($tt['investigation_id']);
                    unset($tt['investigation_question_id']);
                    unset($tt['version']);
                    unset($tt['created_at']);
                    unset($tt['created_by']);
                    unset($tt['updated_by']);
                    unset($tt['updated_at']);
                    $investigationQuestionOptionModel = new LnInvestigationOption();
                    $this->serviceLoadModel($investigationQuestionOptionModel, $tt);
                    $investigationQuestionOptionModel->investigation_id = $model->kid;
                    $investigationQuestionOptionModel->investigation_question_id = $investigationQuestionModel->kid;
                    $investigationQuestionOptionModel->save();
                }
            }
            return $model->kid;
        } else {
            return $kid;
        }
        return $kid;
    }

    /**
     * 保存模块资源数据
     * @param $resource
     * @param $courseId
     * @throws \Exception
     */
    public function SetCourseResource($resource, $courseId, $is_copy = LnCourse::IS_COPY_NO, $domain = null){
        $model = LnCourse::findOne($courseId);
        $courseModKid = array();
        $modResKid = array();
        $courseactivityModKid = array();
        $componentService = new ComponentService();
        $is_record_component = $componentService->getRecordScore();
        $loginUserCompanyId = Yii::$app->user->identity->company_id;
        $courseCompanyId = $model->company_id;

        foreach ($resource as $val) {
            $mod_id = $val['kid'];
            if ($is_copy == LnCourse::IS_COPY_YES){
                $mod_id = null;
                $courseMod = new LnCourseMods();
            } else {
                $courseMod = !empty($mod_id) ? LnCourseMods::findOne($mod_id) : new LnCourseMods();
            }
            if (!empty($mod_id) && empty($courseMod->kid)) {
                $mod_id = null;
                $courseMod = new LnCourseMods();
            }
            $courseMod->course_id = $courseId;
            $courseMod->mod_num = (int)$val['mod_num'];
            $courseMod->mod_name = TStringHelper::clean_xss($val['mod_name']);
            $courseMod->mod_desc = TStringHelper::clean_xss($val['mod_desc']);
            $courseMod->is_deleted = LnCourseMods::DELETE_FLAG_NO;
            $courseMod->needReturnKey = true;
            $result = !empty($mod_id) ? $courseMod->update() : $courseMod->save();
            if ($result!==false) {
                $courseModKid[] = $courseMod->kid;
                if (!empty($val['coursewares'])) {
                    foreach ($val['coursewares'] as $k => $item) {
                        $coursewareInfo = LnComponent::findOne(['component_code' => $k]);
                        if (is_array($item)) {
                            foreach ($item as $key => $value) {
                                /*复制课件*/
                                if ($is_copy == LnCourse::IS_COPY_YES){
                                    /*图书与HTML插件已经在添加组件页面提前复制*/
                                    if ($loginUserCompanyId != $courseCompanyId && $k != LnComponent::COMPONENT_CODE_BOOK && $k != LnComponent::COMPONENT_CODE_HTML) {
                                        $copyId = $this->copyCourseware($value, $courseCompanyId, $domain);
                                        if (!empty($copyId)) $value = $copyId;
                                    }
                                }
                                $find = LnModRes::findOne(['mod_id' => $courseMod->kid, 'course_id' => $courseId, 'courseware_id' => $value]);
                                if(isset($val['config'][$value])){
                                    $config = json_decode($val['config'][$value],true);
                                }else{
                                    $config = '';
                                }
                                if(isset($val['rescore'][$value])){
                                    $rescore = json_decode($val['rescore'][$value],true);
                                }else{
                                    $rescore = '';
                                }

                                $modRes = !empty($find) ? $find : new LnModRes();
                                if (in_array($k, $is_record_component)){
                                	/*  */
                                }
                                $modRes->mod_id = $courseMod->kid;
                                $modRes->component_id = $coursewareInfo->kid;
                                $modRes->courseware_id = $value;
                                $modRes->course_id = $courseId;
                                $modRes->res_type = $coursewareInfo->component_type;
                                if(!empty($rescore)){
                                    $modRes->score_scale = $rescore['score'];
                                    $modRes->complete_rule = $rescore['comrul'];
                                }else{
                                    $modRes->score_scale = "";
                                }
                                if(!empty($config)){
                                    if($config['score'] != undefined && $config['score'] != ''){
                                        $modRes->pass_grade = $config['score'];
                                    }else{
                                        $modRes->pass_grade = "";
                                    }
                                    $modRes->res_time=intval($config['res_time']);

                                    $modRes->direct_complete_course = "$config[isfinish]";
                                }else{
                                   // $modRes->pass_grade = "";
                                    $modRes->direct_complete_course = "0";
                                }

                                $modRes->is_deleted = LnModRes::DELETE_FLAG_NO;
                                $modRes->sequence_number = $key ? $key : 1;
                                /*20160116添加积分规则*/
                                $modRes->is_record_score = $coursewareInfo->is_record_score;
                                $modRes->complete_rule = $coursewareInfo->complete_rule;
                                $modRes->needReturnKey = true;
                                if (empty($find)) {
                                    $modRes->publish_status = $model->course_type == LnCourse::COURSE_TYPE_ONLINE ? LnModRes::PUBLIC_STATUS_YES : LnModRes::PUBLIC_STATUS_NO;/*在线直接为发布状态*/
                                    //$modRes->sequence_number = $modRes->getSequenceNumber($courseMod->kid);
                                    $modResult = $modRes->save();
                                } else {
                                    $modRes->publish_status = $find->publish_status;
                                    $modResult = $modRes->update();

                                    LnModRes::removeFromCacheByKid($modRes->kid);
                                }
                                if ($modResult === false){
                                    return ['result' => 'fail', 'errmsg' => $modRes->getErrors()];
                                }
                                $modResKid[] = $modRes->kid;
                            }
                        }else{
                            //
                        }
                    }
                }else{
                    /*清空资源组件*/
                }
                if (!empty($val['activity'])) {

                    foreach ($val['activity'] as $k => $item) {
                        $coursewareInfo = LnComponent::findOne(['component_code' => $k]);
                        if (is_array($item)) {
                            foreach ($item as $ii => $t) {
                                if ($is_copy == LnCourse::IS_COPY_YES ){
                                    /*作业只要是复制课程都重新复制*/
                                    if ($k != LnComponent::COMPONENT_CODE_HOMEWORK){
                                        if ($loginUserCompanyId != $courseCompanyId){
                                            $copyId = $this->copyCourseactivity($k, $t, $courseCompanyId);
                                            if (!empty($copyId)) $t = $copyId;
                                        }
                                    }else{
                                        /*$copyId = $this->copyCourseactivity($k, $t, $courseCompanyId);
                                        if (!empty($copyId)) $t = $copyId;*/
                                    }
                                }
                                $activityModel = LnCourseactivity::findOne(['object_id' => $t, 'course_id' => $courseId, 'mod_id' => $courseMod->kid, 'object_type' => $k]);
                                if (!empty($activityModel)) {
                                    $find = LnModRes::findOne(['mod_id' => $courseMod->kid, 'course_id' => $courseId, 'courseactivity_id' => $activityModel->kid]);
                                } else {
                                    $find = null;
                                    $activityModel = new LnCourseactivity();
                                }
                                $default_time = $coursewareInfo->default_time;
                                $modRes = !empty($find) ? $find : new LnModRes();
                                $modRes->mod_id = $courseMod->kid;
                                $modRes->component_id = $coursewareInfo->kid;
                                $default_credit = $coursewareInfo->default_credit;

                                //add by baoxianjian 20:46 2016/6/2 
                                if(isset($val['config'][$t])){
                                    $config = json_decode($val['config'][$t],true);
                                    $modRes->res_time=intval($config['res_time']);
                                }
                                
                                if ($k == 'investigation') {
                                    $threeModel = LnInvestigation::findOne($t);
                                } else if ($k == 'examination') {
                                    $threeModel = LnExamination::findOne($t);
                                    //$paperCopyModel = LnExaminationPaperCopy::findOne($threeModel->examination_paper_copy_id);
                                    $modRes->pass_grade = $threeModel->pass_grade;
                                    if ($threeModel->examination_mode == LnExamination::EXAMINATION_MODE_EXERCISE){
                                        $default_credit = 100;
                                        $modRes->transfer_total_score = 100;
                                    }else{
                                        $default_total_score = 100;
                                        $modRes->transfer_total_score = $default_total_score;
                                        $default_credit = $default_total_score;
                                    }
                                } else if ($k == 'homework') {
                                    $threeModel = LnHomework::findOne($t);
                                }
                                if (!empty($activityModel->kid)) {
                                    $activityModel->course_id = $courseId;
                                    $activityModel->object_id = $t;
                                    $activityModel->mod_id = $courseMod->kid;
                                    $activityModel->mod_res_id = !empty($find) ? $find->kid : '';//后面需更新
                                    $activityModel->activity_name = $threeModel->title;
                                    $activityModel->component_id = $coursewareInfo->kid;
                                    $activityModel->object_type = $k;
                                    $activityModel->start_at = $model->start_time;
                                    $activityModel->end_at = $model->end_time;
                                    $activityModel->is_display_pc = $coursewareInfo->is_display_pc;
                                    $activityModel->is_display_mobile = $coursewareInfo->is_display_mobile;
                                    $activityModel->is_allow_download = $coursewareInfo->is_allow_download;
                                    $activityModel->default_credit = $default_credit;
                                    $activityModel->default_time = !empty($coursewareInfo->default_time) ? $coursewareInfo->default_time : $default_time;
                                    $activityModel->resource_version = LnCourseactivity::getResourceVersion($activityModel->kid);
                                    $activityModel->is_deleted = LnCourseactivity::DELETE_FLAG_NO;
                                    $resultActivity = $activityModel->update();
                                } else {
                                    $activityModel = new LnCourseactivity();
                                    $activityModel->course_id = $courseId;
                                    $activityModel->object_id = $t;
                                    $activityModel->mod_id = $courseMod->kid;
                                    $activityModel->mod_res_id = !empty($find) ? $find->kid : '';//后面需更新
                                    $activityModel->activity_name = $threeModel->title;
                                    $activityModel->component_id = $coursewareInfo->kid;
                                    $activityModel->object_type = $k;
                                    $activityModel->start_at = $model->start_time;
                                    $activityModel->end_at = $model->end_time;
                                    $activityModel->is_display_pc = $coursewareInfo->is_display_pc;
                                    $activityModel->is_display_mobile = $coursewareInfo->is_display_mobile;
                                    $activityModel->is_allow_download = $coursewareInfo->is_allow_download;
                                    $activityModel->default_credit = $default_credit;
                                    $activityModel->default_time = !empty($coursewareInfo->default_time) ? $coursewareInfo->default_time : $default_time;
                                    $activityModel->resource_version = LnCourseactivity::getResourceVersion();
                                    $activityModel->is_deleted = LnCourseactivity::DELETE_FLAG_NO;
                                    $activityModel->needReturnKey = true;
                                    $resultActivity = $activityModel->save();
                                }
                                if ($resultActivity === false){
                                    return ['result' => 'fail', 'errmsg' => $activityModel->getErrors()];
                                }
                                $courseactivityModKid[] = $activityModel->kid;
                                if(isset($val['config'][$t])){
                                    $config = json_decode($val['config'][$t],true);
                                }else{
                                    $config = '';
                                }
                                if(isset($val['rescore'][$t])){
                                    $rescore = json_decode($val['rescore'][$t],true);
                                }else{
                                    $rescore = '';
                                }

                                $modRes->courseactivity_id = $activityModel->kid;
                                $modRes->course_id = $courseId;
                                $modRes->res_type = $coursewareInfo->component_type;
                                if(!empty($rescore)){
                                    $modRes->score_scale = $rescore['score'];
                                }else{
                                    $modRes->score_scale = "";
                                }

                                if(!empty($config)){
                                    if($config['score'] != undefined && $config['score'] != ''){
                                        $modRes->pass_grade = $config['score'];
                                    }else{
                                        $modRes->pass_grade = '';
                                    }
                                    $modRes->direct_complete_course = "$config[isfinish]";
                                }else{
                                   // $modRes->pass_grade = "";
                                    $modRes->direct_complete_course = "0";                                    
                                }

                                $modRes->is_deleted = LnModRes::DELETE_FLAG_NO;
                                $modRes->sequence_number = $ii ? $ii : 1;
                                /*20160116添加积分规则*/
                                $modRes->is_record_score = $coursewareInfo->is_record_score;
                                $modRes->complete_rule = $coursewareInfo->complete_rule;
                                $modRes->needReturnKey = true;
                                if (empty($find)) {
                                    $modRes->publish_status = $model->course_type == LnCourse::COURSE_TYPE_ONLINE ? LnModRes::PUBLIC_STATUS_YES : LnModRes::PUBLIC_STATUS_NO;/*在线直接为发布状态*/
                                    //$modRes->sequence_number = $modRes->getSequenceNumber($courseMod->kid);
                                    $modResult = $modRes->save();
                                } else {
                                    //$modRes->publish_status = $find->publish_status;
                                    $modResult = $modRes->update();
                                    LnModRes::removeFromCacheByKid($modRes->kid);
                                }
                                if ($modResult === false){
                                    return ['result' => 'fail', 'errmsg' => $modRes->getErrors()];
                                }
                                $modResKid[] = $modRes->kid;
                                LnCourseactivity::updateAll(['mod_res_id' => $modRes->kid],"`kid`=:kid", [':kid'=>$activityModel->kid]);
								LnCourseactivity::removeFromCacheByKid($activityModel->kid);/*清除缓存*/
                            }
                        } else {

                        }
                    }
                }else{
                    /*清空活动组件*/
                }
            }else{
                return ['result' => 'fail', 'errmsg' => $courseMod->getErrors()];
            }
        }
        /*删除不存在的mod_id*/
        if (!empty($courseModKid)){
            $mod_kids = "'".join("','", $courseModKid)."'";
            LnCourseMods::deleteAll("course_id='{$courseId}' and kid not in ({$mod_kids})");
        }
        /*删除不存在的activity_id*/
        if (!empty($courseactivityModKid)){
            $courseactivity_kids = "'".join("','", $courseactivityModKid)."'";
            LnCourseactivity::deleteAll("course_id='{$courseId}' and kid not in ({$courseactivity_kids})");
        }else{
            LnCourseactivity::deleteAll("course_id='{$courseId}'");
        }
        /*删除不存在的mod_res_id*/
        if (!empty($modResKid)){
            $mod_res_kids = "'".join("','", $modResKid)."'";
            LnModRes::deleteAll("course_id='{$courseId}' and kid not in ({$mod_res_kids})");
        }else{
            LnModRes::deleteAll("course_id='{$courseId}'");
        }
    }

//    /**
//     * 讲师获取单个课程的单元模块，及模块组件
//     * @param $course_id
//     * @return array
//     */
//    public function getCourseModsTeacher($course_id)
//    {
//
//    	$courseMods = LnCourseMods::find(false)->andWhere(['course_id' => $course_id])->orderBy('mod_num')->all();
//
//    	$newMods = [];
//    	foreach ($courseMods as $courseMod) {
//    		$newMods[$courseMod['mod_num']] = $courseMod->attributes;
//    		$modRes = LnModRes::find(false)->andWhere(['mod_id' => $courseMod['kid']])->orderBy('sequence_number')->all();
//    		foreach ($modRes as $k=> $resMode) {
//    			if (!empty($resMode['courseware_id'])){
//    				if ($item = LnCourseware::findOne($resMode['courseware_id'], false)) {
//    					$newMods[$courseMod['mod_num']]['coursewares'][$k]['ware'] = $item;
//    					$newMods[$courseMod['mod_num']]['coursewares'][$k]['res'] = $resMode;
//    				}
//    			}
//    		}
//    	}
//    	return $newMods;
//    }


    /**
     * 获取指定类型的组件
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getComponents($component_type = null, $includeSource = true)
    {
        $components = LnComponent::find(false)
            ->andFilterWhere(['=', 'component_type', $component_type])
            ->orderBy('sequence_number')
            ->all();

        if ($includeSource) {
            foreach ($components as $k => $component) {
                $components[$k]['courseware'] = $this->getCourseware($component['kid']);
            }
        }

        return $components;
    }

    /**
     * 根据组件ID，获取属于这个组件的资源或活动
     * @param $component_code
     * @return mixed
     */
    public function getCourseware($component_id)
    {
        $coursewares = LnCourseware::find(false)
            ->andFilterWhere(['component_id' => $component_id])
            ->all();
        return $coursewares;
    }

    /**
     * 获取所有课程
     * @param string $userId 用户id
     * @param string $companyId 企业id
     * @param array $ids 分类ids
     * @param string $type 课程类型
     * @param int $limit
     * @param int $offset
     * @param string $order 排序
     * @param bool $isMobile 是否移动端
     * @param int $current_time
     * @param string $status
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getResource($userId, $companyId, $ids, $type, $limit, $offset, $order = 'new', $isMobile = false, $current_time, $status = LnCourse::STATUS_FLAG_NORMAL, $withSession = true)
    {
        $currentTime = time();

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($userId, $status, $withSession);

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

        $tableName = LnCourse::tableName();
        // 按课程受众过滤
        $audienceFilterSql = "NOT EXISTS(SELECT kid FROM eln_ln_resource_audience WHERE `status`='1' and resource_type='1' and resource_id=$tableName.kid and company_id='$companyId') OR" .
            " EXISTS(SELECT sam.kid FROM eln_ln_resource_audience ra INNER JOIN eln_so_audience sa ON ra.`status`='1' and ra.resource_type='1' and ra.company_id='$companyId' and ra.audience_id=sa.kid " .
            "and ra.is_deleted='0' and sa.`status`='1' and sa.company_id='$companyId' and sa.is_deleted='0' LEFT JOIN eln_so_audience_member sam ON sa.kid=sam.audience_id and sa.is_deleted='0' and sam.is_deleted='0' " .
            "WHERE $tableName.kid=ra.resource_id AND sam.user_id='$userId')";

        $domainQuerySql = $domainQuery->createCommand()->rawSql;
        $courseQuery = LnCourse::find(false);
        $courseQuery
            ->andWhere($audienceFilterSql)
            ->andWhere('kid in (' . $domainQuerySql . ')')
            ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL])
//            ->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES])
            ->andFilterWhere(['or', ['>=', 'end_time', $currentTime], 'end_time is null'])
            ->andFilterWhere(['or', ['<=', 'start_time', $currentTime], 'start_time is null']);

        if ($ids) {
            $courseQuery->andFilterWhere(['in', 'category_id', $ids]);
        }

        if ($type != null && $type != 'all') {
            $courseQuery->andFilterWhere(['=', 'course_type', $type]);
        }

        if ($isMobile) {
            $courseQuery->andFilterWhere(['=', 'is_display_mobile', LnCourse::DISPLAY_MOBILE_YES]);
        } else {
            $courseQuery->andFilterWhere(['=', 'is_display_pc', LnCourse::DISPLAY_PC_YES]);
        }


        if ($order === 'new') {
            $courseQuery->orderBy('release_at desc');
        } elseif ($order === 'hot') {
            $courseQuery->orderBy('register_number desc');
        }

        // 防止有新数据时，重复读取数据
        $courseQuery->andFilterWhere(['<', LnCourse::tableName() . '.created_at', $current_time]);

        $result = $courseQuery
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $result;
    }

    /**
     * 获取所有多个课程
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getResourcecourse($ids, $type, $limit, $offset, $order = 'new')
    {
        $courseQuery = LnCourse::find(false);
        $courseQuery
            ->andFilterWhere(['=', 'status', LnCourse::STATUS_FLAG_NORMAL]);
        if ($ids) {
            $courseQuery->andFilterWhere(['in', 'kid', $ids]);
        }

        if ($type != null && $type != 'all') {
            $courseQuery->andFilterWhere(['=', 'course_type', $type]);
        }

        if ($order === 'new') {
            $courseQuery->orderBy('release_at desc');
        } elseif ($order === 'hot') {
            $courseQuery->orderBy('register_number desc');
        }

        $courseQuery
            ->limit($limit)
            ->offset($offset);

        $result = $courseQuery->all();
        return $result;
    }

    /**
     * 获取所有课程目录
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCategory()
    {
        $companyId = Yii::$app->user->identity->company_id;

        $query = new Query();

        $data = $query->select('cat.*,count(c.kid) as c_count')
            ->from('{{%ln_course_category}} cat')
            ->leftJoin("{{%ln_course}} c", "cat.kid = c.category_id AND c.is_deleted = '0'")
            ->groupBy('cat.kid')
            ->where("cat.is_deleted = '0' and cat.company_id='$companyId'")
            ->all();

        $result = array();
        $i = 0;
        foreach ($data as $val) {
            if ($val['parent_category_id'] == null || $val['parent_category_id'] == '') {
                $result[$i]['p'] = $val;
                $count = 0;
                foreach ($data as $v) {
                    if ($v['parent_category_id'] != null && $val['kid'] == $v['parent_category_id']) {
                        $count += $v['c_count'];
                        $result[$i]['c'][] = $v;
                    }
                }
                $result[$i]['count'] = $count;
                $i++;
            }
        }
        return $result;
    }


    /**
     * 获取相关课程的资源数
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetResourceCount($courseId)
    {
        $model = LnModRes::find(false);
        $courseTableName = LnCourseMods::tableName();
        $query = $model
            ->leftJoin($courseTableName, $courseTableName.".kid=".LnModRes::tableName().".mod_id")
            ->andFilterWhere(['=', LnModRes::tableName().'.course_id', $courseId])
            ->andFilterWhere(['=', LnModRes::tableName().'.publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->count(LnModRes::tableName().'.kid');

        return intval($query);
    }


    /**
     * 获取相关课程的资源数信息
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetResourceInfo($courseId)
    {
        $model = new LnModRes();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->all();

        return $query;
    }

    /**
     * 获取相关课程的资源数信息除去直通
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getResourceInfoNoDirectCount($courseId)
    {
        $model = new LnModRes();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_NO])
            ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_RECORD_SCORE_YES])
            ->count(1);

        return intval($query);
    }



    /**
     * 获取相关课程的资源数信息
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getNoneDirectCompleteResourceInfo($courseId)
    {
        $model = new LnModRes();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_NO])
            ->all();

        return $query;
    }

    public function GetDirectCompleteResourceInfo($courseId)
    {
        $model = new LnModRes();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->andFilterWhere(['=', 'direct_complete_course', LnModRes::DIRECT_COMPLETE_COURSE_YES])
            ->all();

        return $query;
    }

    public function GetResourceInfoCount($courseId)
    {
        $model = new LnModRes();
        $result = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->count('kid');

        return intval($result);
    }

    /**
     * 获取学员 课程下的组件及成绩(增加必须发布的课件能查看成绩)
     * @param $courseId
     * @return array
     */
    public function getCourseResDetail($courseId, $userId)
    {
        $resArr = array();

        $modRes = LnModRes::find(false)->andWhere(['course_id' => $courseId])
            ->andFilterWhere(['=','publish_status',LnModRes::PUBLIC_STATUS_YES])
            ->orderBy('sequence_number');
        $count = $modRes->count();
        $size = 10;
        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
        $modRes = $modRes->offset($pages->offset)->limit($pages->limit)->all();
        $examinationService = new ExaminationService();
        foreach ($modRes as $resMode) {
            $modResId = $resMode['kid'];
            $componentId = $resMode['component_id'];
            $componentModel = LnComponent::findOne($componentId);
            $componentCode = $componentModel->component_code;
            $direct_complete_course = $resMode->direct_complete_course;
            if (!empty($resMode['courseware_id'])) {
                if ($item = LnCourseware::findOne($resMode['courseware_id'])) {
                    $item->modResId = $modResId;
                    $resComplete = LnResComplete::find(false)
                        ->andFilterWhere(['=', 'course_id', $courseId])
                        ->andFilterWhere(['=', 'user_id', $userId])
                        ->andFilterWhere(['=', 'mod_res_id', $modResId])
                        ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
                        ->orderBy('created_at desc')
                        ->one();
                    if ($componentModel->is_record_score == LnComponent::IS_RECORD_YES){
                        $score = $resComplete->score_before;
                    }else{
                        $score = null;
                    }
                    $item = [
                        'itemId' => $item->kid,
                        'componentId' => $componentId,
                        'modResId' => $modResId,
                        'isCourseware' => true,
                        'modRes' => $resMode,
                        'item' => $item,
                        'componentCode' => 'courseware',
                        'score' => $score,
                        'isRecordScore' => $componentModel->is_record_score,
                        'direct_complete_course' => $direct_complete_course,
                        'resComplete' => $resComplete,
                    ];
                    $resArr[] = $item;
                }
            } elseif (!empty($resMode['courseactivity_id'])) {
                $courseActivityModel = LnCourseactivity::findOne($resMode['courseactivity_id']);
                if (!empty($courseActivityModel->kid)) {
                    if ($componentCode === LnComponent::COMPONENT_CODE_INVESTIGATION) {
                        if ($item = LnInvestigation::findOne($courseActivityModel->object_id)) {
                            $item->modResId = $modResId;
                            $invesResComplete = LnResComplete::find(false)->andFilterWhere(['course_id' => $courseId, 'user_id' => $userId, 'mod_id' => $courseActivityModel->mod_id, 'mod_res_id' => $courseActivityModel->mod_res_id, 'complete_type' => LnResComplete::COMPLETE_TYPE_FINAL])->orderBy('created_at desc')->one();
                            $item = [
                                'itemId' => $item->kid,
                                'componentId' => $componentId,
                                'modResId' => $modResId,
                                'isCourseware' => false,
                                'modRes' => $resMode,
                                'item' => $item,
                                'componentCode' => 'investigation',
                                'score' => !empty($invesResComplete) ? $invesResComplete->score_before : null,
                                'isRecordScore' => $componentModel->is_record_score,
                                'direct_complete_course' => $direct_complete_course,
                                'resComplete' => $invesResComplete,
                            ];
                            $resArr[] = $item;
                        }
                    } else if ($componentCode === LnComponent::COMPONENT_CODE_EXAMINATION) {
                        if ($item = LnExamination::findOne($courseActivityModel->object_id)) {
                            $item->modResId = $modResId;
                            $score = $examinationService->GetExaminationGrade($userId, $courseActivityModel->object_id, $courseId, $modResId, $courseActivityModel->mod_id);/*考试成绩*/
                            $item = [
                                'itemId' => $item->kid,
                                'componentId' => $componentId,
                                'modResId' => $modResId,
                                'isCourseware' => false,
                                'modRes' => $resMode,
                                'item' => $item,
                                'score' => $score,
                                'componentCode' => 'examination',
                                'isRecordScore' => $componentModel->is_record_score,
                                'direct_complete_course' => $direct_complete_course,
                            ];
                            $resArr[] = $item;
                        }
                    } else if ($componentCode == LnComponent::COMPONENT_CODE_HOMEWORK) {
                        if ($item = LnHomework::findOne($courseActivityModel->object_id)) {
                            $item->modResId = $modResId;
                            $resComplete = LnResComplete::find(false)
                                ->andFilterWhere(['=', 'course_id', $courseId])
                                ->andFilterWhere(['=', 'user_id', $userId])
                                ->andFilterWhere(['=', 'mod_res_id', $modResId])
                                ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
                                ->orderBy('created_at desc')
                                ->one();
                            $item = [
                                'itemId' => $item->kid,
                                'componentId' => $componentId,
                                'modResId' => $modResId,
                                'isCourseware' => false,
                                'modRes' => $resMode,
                                'item' => $item,
                                'componentCode' => 'homework',
                                'score' => $resComplete->score_before,
                                'isRecordScore' => $componentModel->is_record_score,
                                'direct_complete_course' => $direct_complete_course,
                                'resComplete' => $resComplete,
                            ];
                            $resArr[] = $item;
                        }
                    }
                }
            }
        }

        $courseReg = LnCourseReg::find(false)
            ->andWhere(['course_id' => $courseId, 'user_id' => $userId])
            ->one();

        $resComServer = new ResourceCompleteService();
        $courseCompleteService = new CourseCompleteService();
        $courseRegId = $courseReg->kid;

        if ($courseReg) {
            $courseCompleteModel = $courseCompleteService->getLastCourseCompleteInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
        }

        if (!empty($courseCompleteModel)) {
            $total_score = $courseCompleteModel->getCompleteScore();
        }

        foreach ($resArr as &$v) {
            if ($courseReg) {
                $v['is_retake'] = $courseCompleteModel->is_retake;
                if (!empty($courseCompleteModel)) {
                    $resCompelete = $resComServer->getLastResCompleteInfo($courseRegId, $v['modResId'], LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteModel->kid);
                    if ($courseCompleteModel->is_direct_completed == LnCourseComplete::IS_DIRECT_COMPLETED_YES && $courseCompleteModel->complete_status == LnCourseComplete::COMPLETE_STATUS_DONE) {
                        $v['status'] = Yii::t('frontend', 'complete_status_done');
                    }else{
                        if ($resCompelete) {
                            $v['status'] = $resCompelete->getCompleteStatusText();
                        } else {
                            $v['status'] = Yii::t('frontend', 'complete_status_nostart');
                        }
                        /*直通课件*/
                        if ($v['isRecordScore'] && $resCompelete->complete_status === LnResComplete::COMPLETE_STATUS_DONE && $v['direct_complete_course'] == LnModRes::DIRECT_COMPLETE_COURSE_YES) {
                            $v['status'] = Yii::t('frontend', 'complete_status_done');
                        }
                    }
                    $v['resstatus'] = $resComServer->isResCompleteOrRetake($courseCompleteModel->kid, $v['modResId']);
                    if ($v['isRecordScore']) {
                        if ($v['componentCode'] === LnComponent::COMPONENT_CODE_EXAMINATION) {
                            $v['resstatus'] = $examinationService->CheckIsPassExamination($userId, null, $v['itemId']);
                        } else {
                            /*if ($courseCompleteModel->complete_status === LnCourseComplete::COMPLETE_STATUS_DONE) {
                                $score = $resComServer->getLastResCompleteInfo($courseRegId, $v['modResId'], LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteModel->kid);
                            } elseif ($courseCompleteModel->is_retake === LnCourseComplete::IS_RETAKE_YES) {
                                $score = $resComServer->getLastResCompleteInfo($courseRegId, $v['modResId'], LnResComplete::COMPLETE_TYPE_BACKUP, $courseCompleteModel->kid);
                            } else {
                                $score = null;
                            }
                            $v['score'] = $score ? $score['score_before'] : '';*/
                            if ($resCompelete->complete_status === LnResComplete::COMPLETE_STATUS_DONE) {
                                $score = $resCompelete->complete_score;
                            } else {
                                $score = '--';
                            }
                            $v['score'] = $score;
                        }
                        $v['score'] = $v['score'] ? $v['score'] : $resCompelete->score_before;
                    }
                } else {
                    $v['resstatus'] = false;
                    $v['score'] = isset($v['score']) ? $v['score'] : '';
                }
            } else {
                $v['score'] = '';
                $v['resstatus'] = false;
            }
        }

        return ['data' => $resArr, 'pages' => $pages, 'total_score' => $total_score];
    }

    /**
     * 获取学员 课程下的组件及成绩
     * @param $courseId
     * @return array
     */
    public function getCourseResScoreDetail($courseId, $userId)
    {
        $resArr = array();

        $query = LnModRes::find(false);
        $query->leftJoin(LnCourseMods::tableName() . ' mod', LnModRes::tableName() . '.mod_id = mod.kid and mod.is_deleted= \'0\'')
            ->andWhere([LnModRes::tableName() . '.course_id' => $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->orderBy('mod_num,sequence_number');
        $count = $query->count();
        $size = 10;
        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
        $modResList = $query->offset($pages->offset)->limit($pages->limit)->all();
        $examinationService = new ExaminationService();
        foreach ($modResList as $modRes) {
            $modResId = $modRes['kid'];
            $componentId = $modRes['component_id'];
            $componentModel = LnComponent::findOne($componentId);
            $componentCode = $componentModel->component_code;
            if ($modRes->res_type === LnModRes::RES_TYPE_COURSEWARE) {
                if ($item = LnCourseware::findOne($modRes->courseware_id)) {
                    $item->modResId = $modResId;
                    $resComplete = LnResComplete::find(false)
                        ->andFilterWhere(['=', 'course_id', $courseId])
                        ->andFilterWhere(['=', 'user_id', $userId])
                        ->andFilterWhere(['=', 'mod_res_id', $modResId])
                        ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
                        ->orderBy('created_at desc')
                        ->one();
                    if ($componentModel->is_record_score == LnComponent::IS_RECORD_YES) {
                        $score = $resComplete->score_before;
                    } else {
                        $score = '--';
                    }
                    $item = [
                        'itemId' => $item->kid,
                        'componentId' => $componentId,
                        'modResId' => $modResId,
                        'isCourseware' => true,
                        'modRes' => $modRes,
                        'item' => $item,
                        'title' => $item->courseware_name,
                        'componentCode' => 'courseware',
                        'score' => $score,
                        'isRecordScore' => $componentModel->is_record_score,
                    ];
                    $resArr[] = $item;
                }
            } elseif ($modRes->res_type === LnModRes::RES_TYPE_COURSEACTIVITY) {
                if ($courseActivityModel = LnCourseactivity::findOne($modRes->courseactivity_id)) {
                    if ($componentCode === LnComponent::COMPONENT_CODE_INVESTIGATION) {
                        if ($item = LnInvestigation::findOne($courseActivityModel->object_id)) {
                            $item->modResId = $modResId;
                            $resComplete = LnResComplete::find(false)->andFilterWhere([
                                'course_id' => $courseId,
                                'user_id' => $userId,
                                'mod_id' => $courseActivityModel->mod_id,
                                'mod_res_id' => $courseActivityModel->mod_res_id,
                                'complete_type' => LnResComplete::COMPLETE_TYPE_FINAL,
                                // 'complete_status' => LnResComplete::COMPLETE_STATUS_DONE
                            ])->orderBy('created_at desc')->one();
                            $item = [
                                'itemId' => $item->kid,
                                'componentId' => $componentId,
                                'modResId' => $modResId,
                                'isCourseware' => false,
                                'modRes' => $modRes,
                                'item' => $item,
                                'componentCode' => $componentCode,
                                'title' => $item->title,
                                'score' => !empty($resComplete) ? $resComplete->score_before : '--',
                                'isRecordScore' => $componentModel->is_record_score,
                            ];
                            $resArr[] = $item;
                        }
                    } else if ($componentCode === LnComponent::COMPONENT_CODE_EXAMINATION) {
                        if ($item = LnExamination::findOne($courseActivityModel->object_id)) {
                            $item->modResId = $modResId;
                            $score = $examinationService->GetExaminationGrade($userId, $courseActivityModel->object_id, $courseId, $modResId, $courseActivityModel->mod_id);/*考试成绩*/
                            $item = [
                                'itemId' => $item->kid,
                                'componentId' => $componentId,
                                'modResId' => $modResId,
                                'isCourseware' => false,
                                'modRes' => $modRes,
                                'item' => $item,
                                'componentCode' => $componentCode,
                                'title' => $item->title,
                                'score' => $score,
                                'isRecordScore' => $componentModel->is_record_score,
                            ];
                            $resArr[] = $item;
                        }
                    } else if ($componentCode == LnComponent::COMPONENT_CODE_HOMEWORK) {
                        if ($item = LnHomework::findOne($courseActivityModel->object_id)) {
                            $item->modResId = $modResId;
                            $resComplete = LnResComplete::find(false)
                                ->andFilterWhere(['=', 'course_id', $courseId])
                                ->andFilterWhere(['=', 'user_id', $userId])
                                ->andFilterWhere(['=', 'mod_res_id', $modResId])
                                ->andFilterWhere(['=', 'complete_type', LnResComplete::COMPLETE_TYPE_FINAL])
                                ->orderBy('created_at desc')
                                ->one();
                            $item = [
                                'itemId' => $item->kid,
                                'componentId' => $componentId,
                                'modResId' => $modResId,
                                'isCourseware' => false,
                                'modRes' => $modRes,
                                'item' => $item,
                                'componentCode' => $componentCode,
                                'title' => $item->title,
                                'score' => $resComplete->score_before,
                                'isRecordScore' => $componentModel->is_record_score,
                            ];
                            $resArr[] = $item;
                        }
                    }
                }
            }
        }

        $courseReg = LnCourseReg::findOne([
            'course_id' => $courseId,
            'user_id' => $userId,
            'reg_state' => LnCourseReg::REG_STATE_APPROVED
        ]);
//            ->andWhere(['course_id' => $courseId, 'user_id' => $userId, 'reg_state' => LnCourseReg::REG_STATE_APPROVED])
//            ->one();

        $resCompleteService = new ResourceCompleteService();
        $courseCompleteService = new CourseCompleteService();
        $courseRegId = $courseReg->kid;

        if ($courseReg) {
            $courseCompleteModel = $courseCompleteService->getLastCourseCompleteInfo($courseRegId, LnCourseComplete::COMPLETE_TYPE_FINAL);
        }

        if (!empty($courseCompleteModel)) {
            $total_score = $courseCompleteModel->getCompleteScore();
        }

        foreach ($resArr as &$v) {
            if ($courseReg) {
                $v['is_retake'] = $courseCompleteModel->is_retake;
                if (!empty($courseCompleteModel)) {
                    $resCompelete = $resCompleteService->getLastResCompleteInfo($courseRegId, $v['modResId'], LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteModel->kid);
                    /*直通课件*/
                    if ($courseCompleteModel->is_direct_completed == LnCourseComplete::IS_DIRECT_COMPLETED_YES && $courseCompleteModel->complete_status == LnCourseComplete::COMPLETE_STATUS_DONE) {
                        $v['status'] = Yii::t('frontend', 'complete_status_done');
                    }else{
                        if ($resCompelete) {
                            $v['status'] = $resCompelete->getCompleteStatusText();
                        } else {
                            $v['status'] = Yii::t('frontend', 'complete_status_nostart');
                        }
                        if ($v['isRecordScore'] && $resCompelete->complete_status === LnResComplete::COMPLETE_STATUS_DONE && $v['direct_complete_course'] == LnModRes::DIRECT_COMPLETE_COURSE_YES) {
                            $v['status'] = Yii::t('frontend', 'complete_status_done');
                        }
                    }
                    $v['resstatus'] = $resCompleteService->isResCompleteOrRetake($courseCompleteModel->kid, $v['modResId']);
                    if ($v['isRecordScore']) {
                        if ($v['componentCode'] === LnComponent::COMPONENT_CODE_EXAMINATION) {
                            $v['resstatus'] = $examinationService->CheckIsPassExamination($userId, null, $v['itemId']);
                        } else {
                            if ($resCompelete->complete_status === LnResComplete::COMPLETE_STATUS_DONE) {
                                $score = $resCompelete->complete_score;
                            } else {
                                $score = '--';
                            }
//                            if ($courseCompleteModel->complete_status === LnCourseComplete::COMPLETE_STATUS_DONE) {
//                                $score = $resCompleteService->getLastResCompleteInfo($courseRegId, $v['modResId'], LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteModel->kid);
//                            } elseif ($courseCompleteModel->is_retake === LnCourseComplete::IS_RETAKE_YES) {
//                                $score = $resCompleteService->getLastResCompleteInfo($courseRegId, $v['modResId'], LnResComplete::COMPLETE_TYPE_BACKUP, $courseCompleteModel->kid);
//                            } else {
//                                $score = '--';
//                            }
//                            $score = $resCompleteService->getLastResCompleteInfo($courseRegId, $v['modResId'], LnResComplete::COMPLETE_TYPE_FINAL, $courseCompleteModel->kid);
//                            $v['score'] = $score ? $score['score_before'] : '--';
                            $v['score'] = $score;
                        }
//                        $v['score'] = $v['score'] ? $v['score'] : $resCompelete->score_before;
                    }
                } else {
                    $v['resstatus'] = false;
                    $v['score'] = isset($v['score']) ? $v['score'] : '--';
                }
            } else {
                $v['score'] = '--';
                $v['resstatus'] = false;
            }
        }

        return ['data' => $resArr, 'pages' => $pages, 'total_score' => $total_score];
    }

    /**
     * 获取相关课程的资源数信息
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseResource($courseId, $size, $offset)
    {
        $model = LnModRes::find(false);
        $courseTableName = LnCourseMods::tableName();
        $query = $model
            ->leftJoin($courseTableName, $courseTableName.".kid=".LnModRes::tableName().".mod_id")
            ->andFilterWhere(['=', LnModRes::tableName().'.course_id', $courseId])
            ->andFilterWhere(['=', LnModRes::tableName().'.publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->limit($size)
            ->offset($offset)
            ->orderBy($courseTableName.".mod_num,".LnModRes::tableName().".sequence_number")
            ->all();

        return $query;
    }

    /**
     * 获取相关课程的资源数信息
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseResourceConfigAll($courseId)
    {
        $model = new LnModRes();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->all();

        return $query;
    }
    /**
     * 获取相关课程的资源数信息(计分课件)
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseResourceConfig($courseId)
    {
        $model = new LnModRes();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_RECORD_SCORE_YES])
            ->all();

        return $query;
    }
    /**
     * 获取相关课程的已发布资源计分课件总数
     * @param $courseId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseScoreCount($courseId){
        $query = LnModRes::find(false);
        $count = $query->andFilterWhere(['=', 'course_id', $courseId])
            ->andFilterWhere(['=', 'publish_status', LnModRes::PUBLIC_STATUS_YES])
            ->andFilterWhere(['=', 'is_record_score', LnModRes::IS_RECORD_SCORE_YES])
            ->count('kid');
        return $count;
    }
    /**
     * 获取相关课程的资源信息（课件详情）(计分课件)
     * @param $ids
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseResourceCoursewareDetail($ids){
        $model = new LnCourseware();
        $query = $model->find(false)
            ->andFilterWhere(['in', 'kid', $ids])
            ->select("courseware_name,kid")
            ->all();
          //  ->createCommand()->getRawSql();

        return $query;
    }

    /**
     * 获取相关课程的资源信息（课件详情）(计分课件)
     * @param $ids
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseResourceCourseActivityDetail($ids){
        $model = new LnCourseactivity();
        $query = $model->find(false)
            ->andFilterWhere(['in', 'kid', $ids])
            ->select("activity_name,kid")
            ->all();
        return $query;
    }
    /**
     * 获取相关课程的课件类型名称
     * @param $ids
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseResourceComponentNames($ids){
        $model = new LnComponent();
        $query = $model->find(false)
            ->andFilterWhere(['in', 'kid', $ids])
            ->select("title,kid")
            ->all();
        return $query;
    }
    /**
     * 获取相关课程的类型
     * @param $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public function GetCourseType($id){
        $model = new LnCourse();
        $query = $model->find(false)
            ->andFilterWhere(['=', 'kid', $id])
            ->select("course_type")
            ->one();
        return $query;
    }
    /**
     *上传文件附归属
     *@param $id
    */
    public function HomeworkFileAddId($id,$hwkid){
        if(!$id){return false;}
        if($hwkid){return false;}
         /*
        $model = LnHomeworkFile::findOne($id);
        $model->homework_id = $hwkid;
        $model->update();
        */

        $model = new LnHomeworkFile();
        $data = $model->find(false)
            ->andFilterWhere(['=', 'kid', $id])
            ->one();
        if($data) {
            $data->homework_id = $hwkid;
           return $data->update();
        }
        return false;
    }

    /**
     * 添加分类递归
     * @param $data
     * @param null $parentCategoryId
     * @return string
     */
    public function getCategoryTree($data, $editKid = null, $parentCategoryId = null)
    {
        $html = "";
        if (!empty($data['parent'])){
            foreach ($data['parent'] as $val){
                if (!empty($editKid) && $editKid == $val['kid']){
                    continue;
                }
                $html .= '<option value="'.$val['kid'].'" data-id="'.$val['kid'].'" '.($val['kid']==$parentCategoryId?'selected':'').'>'.$val['category_name'].'</option>';
                if (!empty($data['sub'][$val['kid']])){
                    $html .= $this->getCategorySub($data, $editKid, $val['kid'], $parentCategoryId);
                }
            }
        }
        return $html;
    }


    public function getDanmuInfo($modResId)
    {
        $model = new LnResDanmu();
        $result = $model->find(false)
            ->andFilterWhere(['=','mod_res_id',$modResId])
            ->all();
        return $result;
    }

    public function getCategorySub($data, $editKid = null, $parentKid, $parentCategoryId = null, $i = 1){
        $html = "";
        if (!empty($data['sub'][$parentKid])){
            $nbsp = "";
            for($j = 1; $j <= $i; $j ++){
                $nbsp .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            foreach ($data['sub'][$parentKid] as $vo){
                if (!empty($editKid) && $editKid == $vo['kid']){
                    continue ;
                }
                $html .= '<option value="'.$vo['kid'].'" data-id="'.$vo['kid'].'" '.($vo['kid']==$parentCategoryId?'selected':'').'>'.$nbsp.$vo['category_name'].'</option>';
                if (!empty($data['sub'][$vo['kid']])){
                    $html .= $this->getCategorySub($data, $editKid, $vo['kid'], $parentCategoryId, $i+1);
                }
            }
        }
        return $html;
    }

}