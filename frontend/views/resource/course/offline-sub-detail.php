<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/9/7
 * Time: 15:21
 */
use common\helpers\TStringHelper;
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseEnroll;
use common\models\learning\LnComponent;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnModRes;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common', 'face_to_face') . Yii::t('common','course') . Yii::t('common','management'),
    'url'=>['resource/course/manage-face']];
$this->params['breadcrumbs'][] = Yii::t('frontend','manage_course');
$this->params['breadcrumbs'][] = $model->course_name;
?>
<style type="text/css">
    .courseInfo{
        float: left;
    }
    .-query-list{
        display: inline-block;
        width: 160px;
    }
</style>

<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('common', 'face_to_face')?><?=Yii::t('common','course')?>
                </div>
                <div class="panel-body">
                    <div class="courseTitle">
                        <div class="left">
                            <img src="<?= $model->theme_url ? $model->getCourseCover() : '/static/frontend/images/course_theme_big.png'?>"/>
                        </div>
                        <div class="right">
                            <h2><?=$model->course_name?></h2>
                            <table>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_code')?>:</strong> <?=$model->course_code?></span></td>
                                    <td><span><strong><?=Yii::t('common','category_id')?>:</strong> <?=$model->getCourseCategoryText()?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_type')?>:</strong> <?=$model->course_type==LnCourse::COURSE_TYPE_ONLINE? Yii::t('frontend','course_online'): Yii::t('frontend','course_face')?></span></td>
                                    <td>
                                        <span><strong><?=Yii::t('common','course_default_credit')?>:</strong> <?=$model->default_credit?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_level')?>:</strong> <?=$model->getDictionaryText('course_level',$model->course_level)?></span></td>
                                    <td><span><strong><?=Yii::t('common','course_period')?>:</strong> <?=$model->course_period?><?=$model->getCoursePeriodUnits($model->course_period_unit)?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_language')?>:</strong> <?=$model->getDictionaryText('course_language',$model->course_language)?></span></td>
                                    <td><span><strong><?=Yii::t('common','course_price')?>:</strong> <?=$model->currency=='CNY'?'&yen;':'$'?> <?=$model->course_price?></span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?= Yii::t('frontend', 'give_a_mark') ?>:&nbsp;</strong><div id="rating" class="ui star rating" data-name="pingfen" data-rating="<?=floor($rating)?>" data-rating-full="<?=$rating?>" data-max-rating="5" data-person="<?=$rating_count ?>" title="<?=$rating?><?=Yii::t('frontend', 'point')?>"></div>
                                    </td>
                                    <td>
                                        <strong style="float:left; margin-right:10px;"><?= Yii::t('frontend', 'qr_code') ?>:&nbsp;</strong>
                                        <div class="barCode pull-left">
                                            <span><img src="<?=TStringHelper::genQRCode($model->short_code)?>" height="128" width="128"></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <span>
                                                <strong><?= Yii::t('frontend', 'enroll_time') ?>:&nbsp;</strong>
                                                <?=date('Y年m月d日', $model->enroll_start_time)?> ～ <?=date('m月d日', $model->enroll_end_time)?>
                                            </span>
                                    </td>
                                    <td>
                                            <span>
                                                <strong><?= Yii::t('frontend', 'places') ?>:&nbsp;</strong>
                                                <?php
                                                if ($model->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
                                                    $remaining = $model->limit_number - $enrollRegNumber;/*剩余*/
                                                    $remaining = ($remaining > 0) ? $remaining : 0;
                                                ?>
                                                    <?=$model->limit_number?>(<?=Yii::t('frontend', 'surplus')?><?=$remaining?><?=Yii::t('frontend', 'people')?>)
                                                <?php
                                                }else{
                                                ?>
                                                <?=$model->limit_number?> <?= Yii::t('frontend', 'people') ?>
                                                <?php
                                                }
                                                ?>
                                            </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                            <span>
                                                <strong><?= Yii::t('frontend', 'start_course_time') ?>:&nbsp;</strong>
                                                <?=date('Y年m月d日', $model->open_start_time)?> ～ <?=date('m月d日', $model->open_end_time)?>
                                            </span>
                                    </td>
                                    <td>
                                            <span>
                                                <strong><?= Yii::t('common', 'time') ?>:&nbsp;</strong>
                                                <?=ceil(($model->open_end_time-$model->open_start_time)/86400)?> <?= Yii::t('frontend', 'day') ?>
                                            </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <span>
                                            <strong><?= Yii::t('frontend', 'place') ?>:&nbsp;</strong>
                                            <?=$model->training_address?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="courseInfo">
                        <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                            <li role="presentation" class="active"><a href="#courseIntro" aria-controls="courseIntro" role="tab" data-toggle="tab" aria-expanded="true"><?= Yii::t('frontend', 'course_content') ?></a></li>
                            <li role="presentation" class=""><a href="#courseAward" id="btn_courseAward" aria-controls="courseAward" role="tab" data-toggle="tab" aria-expanded="false"><?= Yii::t('frontend', 'enroll_student') ?></a></li>
                            <li role="presentation" class=""><a href="#courseAward2" id="btn_courseAward2" aria-controls="courseAward" role="tab" data-toggle="tab" aria-expanded="false"><?= Yii::t('frontend', 'waiting_student') ?></a></li>
                            <li role="presentation" class=""><a href="#courseAward7" id="btn_courseAward7" aria-controls="courseAward" role="tab" data-toggle="tab" aria-expanded="false"><?= Yii::t('frontend', 'signin_manage') ?></a></li>
                            <li role="presentation" class=""><a href="#courseAward4" id="btn_courseAward4" aria-controls="courseAward" role="tab" data-toggle="tab" aria-expanded="false"><?= Yii::t('frontend', 'study_record') ?></a></li>
                            <li role="presentation" class=""><a href="#courseAward5" id="btn_courseAward5" aria-controls="courseAward" role="tab" data-toggle="tab" aria-expanded="false"><?= Yii::t('frontend', 'summary') ?></a></li>
                            <!--<li role="presentation" class=""><a href="#courseAward6" id="btn_courseAward6" aria-controls="courseAward" role="tab" data-toggle="tab" aria-expanded="false"><?= Yii::t('common', 'complete_rule') ?></a></li>-->
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="courseIntro">
                                <div class="panel-default scoreList">
                                    <div class="panel-default scoreList pathBlock offlineCourse">
                                        <div role="tab">
                                            <p><?= Yii::t('frontend', 'introduction_course') ?>:</p>
                                            <p>
                                                <?=Html::decode($model->course_desc)?>
                                            </p>
                                            <hr>
                                        </div>
                                        <div role="tab" id="headingOne">
                                            <ul class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" id="collapseExample">
                                                <?if($courseMods): foreach ($courseMods as $mod):?>
                                                    <?
                                                    $time = 0;
                                                    if (!empty($mod['courseitems'])) {
                                                        foreach ($mod['courseitems'] as $num => $resource) {
                                                            $itemId = $resource['itemId'];
                                                            $modResId = $resource['modResId'];
                                                            $componentId = $resource['componentId'];
                                                            $isCourseware = $resource['isCourseware'];
                                                            if (!empty($resource['item'])) {
                                                                if ($isCourseware) {
                                                                    $time += $resource['item']->courseware_time;
                                                                }
                                                                else{
                                                                    $modResModel = LnModRes::findOne($modResId);
                                                                    if (!empty($modResModel)) {
                                                                        $time += LnCourseactivity::findOne($modResModel->courseactivity_id)->default_time;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <li class="pathStep">
                                                        <span class="step "><?= $mod['mod_name']?></span>
                                                        <span class='stepTime pull-right'><?= Yii::t('frontend', 'study_hours') ?>：<?= $time?><?= Yii::t('common', 'time_minute') ?></span>
                                                        <? if (!empty($mod['mod_desc'])) { ?>
                                                            <p><?= Yii::t('frontend', 'module_description') ?>：<?=TStringHelper::OutPutBr($mod['mod_desc'])?></p>
                                                        <? }?>
                                                        <div class="pathTask">
                                                            <table>
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <ul class="attach">
                                                                            <?if($mod['courseitems']): foreach ($mod['courseitems'] as $num => $resource):?>
                                                                                <?
                                                                                $itemId = $resource['itemId'];
                                                                                $modResId = $resource['modResId'];
                                                                                $componentId = $resource['componentId'];
                                                                                $isCourseware = $resource['isCourseware'];
                                                                                $modRes = $resource['modRes'];
                                                                                $item = $resource['item'];

                                                                                if ($isCourseware) {
                                                                                    $itemName = $item->courseware_name;
                                                                                }
                                                                                else {
                                                                                    $modResModel = LnModRes::findOne($modResId);
                                                                                    if (!empty($modResModel)) {
                                                                                        $itemName = LnCourseactivity::findOne($modResModel->courseactivity_id)->activity_name;
                                                                                    }
                                                                                }

                                                                                $componentModel = LnComponent::findOne($componentId);
                                                                                ?>
                                                                                <li>
                                                                                    <?=$componentModel->icon?> <a href="javascript:;"><?=$itemName ?></a>
                                                                                </li>
                                                                            <?endforeach; endif;?>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </li>
                                                <?endforeach; endif;?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseAward">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body">
                                        <div class="panel-list" id="enroll_success" data-url="<?=Url::toRoute(['/resource/course/get-course-enroll','id'=>$model->kid,'enroll_type'=>0])?>"></div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseAward2">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body">
                                        <div class="panel-list" id="enroll_standby" data-url="<?=Url::toRoute(['/resource/course/get-course-standby','id'=>$model->kid,'enroll_type'=>2])?>"></div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseAward4" data-url="<?=Url::toRoute(['/teacher/detail-score','id'=>$model->kid])?>"></div>
                            <div role="tabpanel" class="tab-pane" id="courseAward5">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body">
                                        <div class="panel-list" id="score_list" data-url="<?=Url::toRoute(['/resource/course/get-course-score','id'=>$model->kid])?>"></div>
                                        <div id="list_loading" class="loadingWaiting hide" style="margin:100px auto;">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>

                                            <p><?= Yii::t('frontend', 'loading') ?>...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseAward6">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body">
                                        <div class="panel-list" id="config_list" data-url="<?=Url::toRoute(['/resource/course/get-course-config','id'=>$model->kid])?>"></div>
                                        <div id="list_loading" class="loadingWaiting hide" style="margin:100px auto;">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>

                                            <p><?= Yii::t('frontend', 'loading') ?>...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseAward7">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body">
                                        <div class="panel-list" id="detail_sign" data-url="<?=Url::toRoute(['/sign-in/detail-sign','id'=>$model->kid])?>"></div>
                                        <div id="list_loading" class="loadingWaiting hide" style="margin:100px auto;">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>

                                            <p><?= Yii::t('frontend', 'loading') ?>...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 课件完成情况弹出窗口 -->
<div class="ui modal" id="scorm-result"></div>
<div class="ui modal" id="aicc-result"></div>
<div class="ui modal" id="examination-result"></div>
<div class="ui modal" id="homework-result"></div>
<div class="ui modal" id="investigation-result"></div>
<div class="ui modal" id="other-result"></div>
<!-- 学员记录 -->
<div class="ui modal" id="questionaire-result"></div>

<div class="ui modal" id="courseware"></div>
<div class="ui modal" id="questionairedetail"></div>
<div class="ui modal" id="questionairedetailone"></div>
<div class="ui modal" id="checksurvay"></div>
<div class="ui modal" id="examination"></div>
<div class="ui modal" id="examination_log"></div>
<div class="ui modal" id="homework"></div>
<div class="ui modal" id="scoreDetails"></div>
<div class="ui modal" id="scoreDetailsPerson"></div>

<!--签到样式-->
<style>
    #editDate .form-control[readonly] { background-color:white; }
    #editDate .form-control[disabled]{ background-color:#eee; }
</style>
<!-- 签到配置增加日期弹出窗口 -->
<div id="editDate"  class="ui modal">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'add_and_modify_time') ?></h4>
    </div>
    <div class="content">
        <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                    <div class="panel-body">
                        <div class="infoBlock">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group form-group-sm">
                                        <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'sign_in_time') ?></label>
                                        <div class="col-sm-9">
                                            <input type="hidden" id="edit_sign_type" />
                                            <input type="hidden" id="edit_sign_idx" />
                                            <input id="edit_sign_date" data-type="rili" class="form-control pull-left" type="text" style="width: 95%;" value="2016年3月10日">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="edit_sign_time">
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12 centerBtnArea">
                                    <a href="###" class="btn btn-default btn-sm addSignTimeInner" style="width:30%"><?= Yii::t('frontend', 'add_signin_time') ?></a>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 col-sm-12 centerBtnArea">
                                    <a href="javascript:void(0);" onclick="closeEditSignDateBox()" class="btn btn-success btn-md" style="width:30%"><?= Yii::t('common', 'save') ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="c"></div> <!--新增-->
    </div>
    <div class="c"></div> <!--新增-->
</div>


<div class="ui modal" id="div_sign_in_qr_code_big">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= Yii::t('frontend', 'qr_code') ?></h4>
    </div>
    <div class="content">
        <div class="panel-body">
            <img id="img_sign_in_qr_code_big" onload="app.refreshAlert('#div_sign_in_qr_code_big');" src="" style="width:100%; height:auto;" />
        </div>
        <div class="c"></div>
    </div>
</div>

<script type="text/javascript">
    // 添加签到时间2
    $('.addSignTimeInner').bind('click', function () {

        var addTime = '<div name="edit_sign_time_list" class="row timeRow"><div class="col-sm-12"><div class="form-group form-group-sm"><div class="col-sm-3 "><input name="edit_sign_time_title" type="text" placeholder="<?= Yii::t('common', 'audience_name') ?>" class="form-control" value=""></div><div class="col-sm-9"><input name="edit_sign_time_start" onblur="checkEditSignTimeStart(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="8:00" placeholder="8:00" class="form-control pull-left" style="width: 45%" value=""><input name="edit_sign_time_end" onblur="checkEditSignTimeEnd(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="10:00" placeholder="10:00" class="form-control pull-left" style="width: 45%" value=""><a href="###" class="btn btn-xs pull-right delTimeBtn" onclick="removeSignSettingFromDateBox(this)" style=" position: absolute; top: 5px;"><?= Yii::t('common', 'delete_button') ?></a></div></div></div></div>';
        // $(this).parents('.row').before(addTime);
        $("#edit_sign_time").append(addTime);
        app.genCalendar();
    });


    function showSignInBigQrCodeImg(id)
    {
        $("#img_sign_in_qr_code_big").attr('src',$("#"+id).attr('src'));
        app.alert('#div_sign_in_qr_code_big');
    }
    function printSignInQrCodeImg(id)
    {
        var src=location.origin + $("#"+id).attr('src');
        app.printImage(src);
    }

</script>


<script type="text/javascript">
    $(function(){
        app.disableRating("pingfen");
    });
    var enroll_url = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_REG])?>";
    var standby_url = "<?=Url::toRoute(['/resource/course/get-course-standby', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_ALTERNATE])?>";
    var score_url = "<?=Url::toRoute(['/resource/course/get-course-score', 'id'=>$model->kid])?>";
    var study_url = "<?=Url::toRoute(['/teacher/detail-score', 'id'=>$model->kid])?>";
    var config_url = "<?=Url::toRoute(['/resource/course/get-course-config', 'id' => $model->kid, 'pageSize' => 10])?>";
    var sign_in_url = "<?=Url::toRoute(['/sign-in/detail-sign', 'id' => $model->kid, 'pageSize' => 10])?>";
    
    function getData(url, ele){
        app.get(url,function(r){
            $("#"+ele).html(r);
        });
    }
    function loadPage(ajaxUrl, container, is_clear) {
        if(is_clear){
            $("#" + container).empty();
            $("#list_loading").removeClass("hide");
        }
        app.get(ajaxUrl,function(data){
            if(is_clear) {
                $("#list_loading").addClass('hide');
            }
            $("#"+container).html(data);
            $("#" + container + ' .pagination a').bind('click', function () {
                var url = $(this).attr('href');
                loadPage(url, container, is_clear);
                return false;
            });
        });
    }
    $("#btn_courseAward").click(function(){
        if ($("#enroll_success").html() == "") {
            getData(enroll_url, 'enroll_success');
        }
    });
    $("#btn_courseAward2").click(function(){
        if ($("#enroll_standby").html() == "") {
            getData(standby_url, 'enroll_standby');
        }
    });
    $("#btn_courseAward4").click(function(){
        if ($("#courseAward4").html() == "") {
            getData(study_url, 'courseAward4');
        }
    });
    $("#btn_courseAward5").click(function(){
        if ($("#score_list").html() == "") {
            loadPage(score_url, 'score_list', true);
        }
    });
    $("#btn_courseAward6").click(function(){
        if ($("#config_list").html() == "") {
            loadPage(config_url, 'config_list', true);
        }
    });
    $("#btn_courseAward7").click(function(){
            loadPage(sign_in_url, 'detail_sign', true);
    });
    
    $(".courseInfo .pagination").on('click', 'a', function (e) {
        e.preventDefault();
        var parent = $(this).parents('panel-list').attr('id');
        alert(parent);
        getData($(this).attr('href'), parent);
    });
    $(".tab-pane").on('keydown', '.search_people', function(e){
        if (e.keyCode == 13){
            $(this).parent().find(".searchBtn").trigger('click');
            return false;
        }
    });
    $(".tab-pane").on('click', '.searchBtn', function (e) {
        e.preventDefault();
        var parent = $(this).parents('.panel-list').attr('id');
        $.ajax({
            url: $("#"+parent).attr('data-url'),
            data: {
                sort: $("#"+parent).find("select[name='sort']").val(),
                filter: $("#"+parent).find("select[name='filter']").val(),
                keyword: $("#"+parent).find("input[name='keyword']").val().trim()
            },
            type: 'GET',
            success: function (r) {
                $("#" + parent).html(r);;
            }
        })
    });

    var scoreDetailUrl = "<?=Url::toRoute(['/resource/course/get-score-detail'])?>";
    var exportScoreDetailUrl = "<?=Url::toRoute(['/resource/course/export-score-detail'])?>";
    var viewModId=null;
    function showScoreDetail(modId, title, component_code) {
    	if(component_code=="investigation"){
       	 $.get("<?=Yii::$app->urlManager->createUrl(['investigation-result/online-course-investigation-detail'])?>"+"?modId="+modId,function(data){
            	   if(data.result.type=="0"){
                	   //alert("问卷");
            		   window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/course-survey-manage-result-survey'])?>"+"?id="+data.result.id+"&&course_id="+data.result.course_id+"&&course_type=1";
               		}else{
               		 window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/course-survey-manage-result-vote'])?>"+"?id="+data.result.id+"&&course_id="+data.result.course_id+"&&course_type=1";
                   }
                });
       }else{
    	   viewModId=modId;
           var url = scoreDetailUrl + '?id=' + modId;
           $("#score_modal_title").text(title + " <?= Yii::t('frontend', 'transcript') ?>");
           loadPage(url, 'score_modal_list', false);
           app.alertWide('#printScore');
       }
    }
    function exportScoreDetail()
    {
        if(viewModId)
        {
            window.location.href=exportScoreDetailUrl+"?id="+viewModId;
        }
    }

    /*加载成绩查看列表*/
    function LoadCompleteInfo(obj, courseId, modResId, itemId, itemName, componentCode, userId)
    {
        var url = '<?=Url::toRoute(['/teacher/common-result'])?>';
        if (componentCode != 'scorm' && componentCode != 'aicc' && componentCode != 'examination' && componentCode != 'investigation' && componentCode != 'homework'){
            componentCode = 'other';
        }
        if (typeof userId != 'undefined'){
            url = urlreplace(url, 'userId', userId);
            if (componentCode == 'investigation') {
                componentCode = 'questionaire';
            }
        }
        url = url.replace('common', componentCode);
        url = urlreplace(url, 'courseId', courseId);
        url = urlreplace(url, 'modResId', modResId);
        url = urlreplace(url, 'itemId', itemId);
        url = urlreplace(url, 'componentCode', componentCode);
        $.get(url, function (r){
            if (r){
                $("#"+componentCode+"-result").html(r);
                app.alertWideAgain($("#"+componentCode+"-result"));
            }else{
                app.showMsg(app.msg.NETWORKERROR);
            }
        });
    }

    function detail(userId,courseId,modResId,itemId){
        var modalId = "questionairedetailone";
        var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('/teacher/questionaire-result')?>";
        ajaxUrl = urlreplace(ajaxUrl, 'courseid', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'modresid', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'itemId', itemId);
        ajaxUrl = urlreplace(ajaxUrl, 'userId', userId);
        modalTotalClear(modalId);
        app.alertWideAgain('#'+modalId);
        loadMessage(ajaxUrl, modalId);
    }

    function detailhomework(userId,courseId,modResId,itemId){
        var modalId = "courseware";
        var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('teacher/homework-player')?>";
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'itemId', itemId);
        ajaxUrl = urlreplace(ajaxUrl, 'userId',userId);
        app.get(ajaxUrl, function (r)
        {
            if(r)
            {
                app.alertWideAgain($("#" + modalId).html(r));
            }
        });
    }
    function FmodalLoad(target, url) {
        if (url) {
            $('#' + target).empty();
            $('#' + target).load(url);
        }
    }
    function backToStudentSignList()
    {
        loadPage(sign_in_url, 'detail_sign', true);
    }
    backToStudentSignList();
</script>
