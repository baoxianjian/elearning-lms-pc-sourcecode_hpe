<?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;
use common\models\learning\LnCourse;

//$this->pageTitle = $courseModel->course_name;// Yii::t('frontend', 'page_lesson_hot_title');
//$this->params['breadcrumbs'][] = '课程详情';
//$this->params['breadcrumbs'][] = $this->pageTitle;

$this->pageTitle = $courseModel->course_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('frontend','teacher_home'), 'url' => ['teacher/index']];
$this->params['breadcrumbs'][] = Yii::t('frontend', 'teacher_course_detail');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style type="text/css">
    .courseInfo{float:left;}
    .barCode{
        cursor: text !important;
    }
</style>
<div class="container">
    <div class="row">
        <ol class="breadcrumb">
            <?= TBreadcrumbs::widget([
                'tag' => 'ol',
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
        </ol>
        <div class="col-md-12">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i>
                    <?php
                    if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
                        ?>
                  <?=Yii::t('frontend', 'course_face')?>
                        <?php
                    }else{
                        ?>
                        <?=Yii::t('frontend', 'course_online')?>
                        <?php
                    }
                    ?>
                </div>
                <div class="panel-body">
                    <div class="courseTitle">
                        <div class="left"><img style="<?=$courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE?'margin-top:75px':''?>" src="<?= $courseModel->theme_url ? $courseModel->getCourseCover() : '/static/frontend/images/course_theme_big.png'?>"></div>
                        <div class="right">
                            <h2><?=$courseModel->course_name?>
                                <?php
                                if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
                                    ?>
                  <a href="###" class="btn courseStatu <?if($courseModel->open_status ==0):?>hide<?endif;?>"><?if($courseModel->open_status ==1):?><?=Yii::t('frontend', 'complete_status_doing')?><?elseif($courseModel->open_status ==2):?><?=Yii::t('frontend', 'complete_status_done')?><?endif;?></a>
                  <?if($courseModel->open_status ==0):?>
                          <a href="###" class="btn courseControlBtn btn-success btn-sm pull-right" date-var="<?=$courseModel->kid?>" data-status="<?=$courseModel->open_status?>"> <?=Yii::t('frontend', 'start_course')?></a>
                      <?elseif($courseModel->open_status ==1):?>
                          <a href="###" class="btn courseControlBtn btn-success btn-sm pull-right" date-var="<?=$courseModel->kid?>" data-status="<?=$courseModel->open_status?>"><?=Yii::t('frontend', 'end_course')?></a>
                                    <?endif;?>
                                    <?php
                                }
                                ?>
                            </h2>
                            <table>
                                <tr>
                                    <td width="50%"><span><strong><?=Yii::t('common','course_code')?>:</strong> <?=$courseModel->course_code?></span></td>
                                    <td><span><strong><?=Yii::t('common','category_id')?>:</strong> <?=$courseModel->getCourseCategoryText()?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_type')?>:</strong> <?=$courseModel->course_type==LnCourse::COURSE_TYPE_ONLINE?Yii::t('frontend', 'course_online'): Yii::t('frontend', 'course_face')?></span></td>
                                    <td>
                                        <span><strong><?=Yii::t('common','course_default_credit')?>:</strong> <?=$courseModel->default_credit?></span>
                                    </td>
                                </tr>
                                <?php
                                if ($isCourseOnline) {
                                    ?>
                                    <tr>
                                        <td><span><strong><?= Yii::t('common', 'course_level') ?> :</strong> <?= $courseModel->getDictionaryText('course_level', $courseModel->course_level) ?></span></td>
                                        <td><span><strong><?=Yii::t('common','course_period')?>:</strong> <?=$courseModel->course_period?><?=$courseModel->getCoursePeriodUnits($courseModel->course_period_unit)?></span></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_language')?>:</strong> <?=$courseModel->getDictionaryText('course_language',$courseModel->course_language)?></span></td>
                                    <td><span><strong><?=Yii::t('common','course_price')?>:</strong> <?=$courseModel->getPriceUnit($courseModel->currency)?> <?=$courseModel->course_price?></span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?=Yii::t('frontend', 'give_a_mark')?>:&nbsp;</strong><div id="rating" class="ui star rating" data-name="pingfen" data-rating="<?=floor($rating)?>" data-rating-full="<?=$rating?>" data-max-rating="5" data-person="<?=$rating_count ?>" title="<?=$rating?><?=Yii::t('frontend', 'point')?>"></div></i>
                                    </td>
                                    <td>
                                        <strong style="float:left; margin-right:10px;"><?=Yii::t('frontend', 'qr_code')?>:&nbsp;</strong>
                                        <div class="barCode pull-left">
                                            <span><img src="<?=TStringHelper::genQRCode($courseModel->short_code)?>" height="128" width="128"></span>
                                        </div>
                                        <a href="#" onclick="bigcode()"><?=Yii::t('frontend', 'enlarge')?></a>
                                    </td>
                                </tr>
                                <?php
                                if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE){
                                    $remaining = $courseModel->limit_number - $enrollRegNumber;/*剩余*/
                                    $remaining = ($remaining > 0) ? $remaining : 0;
                                    ?>
                                    <tr>
                                        <td>
                            <span>
                                <strong><?=Yii::t('frontend', 'places')?>:&nbsp;</strong>
                                <?=$courseModel->limit_number?>(<?=Yii::t('frontend', 'surplus')?><?=$remaining?><?=Yii::t('frontend', 'people')?>)
                            </span>
                        </td>
                        <td>
                            <span>
                                <strong><?=Yii::t('common', 'time')?>:&nbsp;</strong>
                                <?=ceil(($courseModel->open_end_time-$courseModel->open_start_time)/86400)?> <?=Yii::t('frontend', 'day')?>
                            </span>
                        </td>
                        </tr>
                    <tr>
                        <td>
                            <span>
                                <strong><?=Yii::t('frontend', 'enroll_time')?>:&nbsp;</strong>
                                <?=date('Y年m月d日', $courseModel->enroll_start_time)?> ～ <?=date('m月d日', $courseModel->enroll_end_time)?>
                                <? if (!empty($courseModel->enroll_start_time)) { ?>
                                    <a href="<?=Yii::$app->urlManager->createUrl(["/resource/course/download-calendar","courseId"=>$courseModel->kid, "type"=>"enroll"])?>" title="<?=Yii::t('frontend', 'save_calendar')?>" target="_blank" style="display: none;"><img src="/static/frontend/images/calendar.gif" width="32px" height="32px"></a>
                                <? } ?>
                            </span>
                        </td>
                        <td><span><strong><?=Yii::t('common','course_period')?>:</strong> <?=$courseModel->course_period?><?=$courseModel->getCoursePeriodUnits($courseModel->course_period_unit)?></span></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span>
                                <strong><?=Yii::t('frontend', 'start_course_time')?>:&nbsp;</strong>
                                <?=date('Y年m月d日', $courseModel->open_start_time)?> ～ <?=date('m月d日', $courseModel->open_end_time)?>
                                <?php
                                if (!empty($courseModel->open_start_time)) {
                                ?>
                                <a href="<?=Yii::$app->urlManager->createUrl(["/resource/course/download-calendar","courseId"=>$courseModel->kid, "type"=>"open"])?>" title="<?=Yii::t('frontend', 'save_calendar')?>" target="_blank" style="display: none;"><img src="/static/frontend/images/calendar.gif" width="32px" height="32px"></a>
                                <?php
                                }
                                ?>
                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                            <span>
                                <strong><?=Yii::t('frontend', 'place')?>:&nbsp;</strong>
                                <?=$courseModel->training_address?>
                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                }else{
                                    ?>
                                    <tr>
                                        <td colspan="2"><span><strong><?= Yii::t('common', 'time_validity') ?>:</strong>
                                                <?php
                                                $end_time = $model->end_time ? date("Y-m-d", $courseModel->end_time) : Yii::t('frontend', 'forever');
                                                ?>
                                <?=date("Y-m-d", $courseModel->start_time)?> <?=Yii::t('common', 'to2')?> <?= $end_time ?></span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                    <div class="courseInfo">
                        <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                            <li class="active"><a href="#courseIntro" data-var="courseIntro" aria-controls="courseIntro"><?=Yii::t('frontend', 'course_content')?></a></li>
                            <?php
                            if($certsUrl){
                                ?>
                                <li><a href="#courseAwardCert"  data-var="courseAwardCert" aria-controls="courseAwardCert"><?=Yii::t('common', 'serial')?></a></li>
                                <?php
                            }
                            ?>
                            <li><a href="#courseTeacher" data-var="courseTeacher" aria-controls="courseTeacher"><?=Yii::t('common', 'lecturer')?></a></li>
                            <li><a href="#courseAnswer" data-var="courseAnswer" aria-controls="courseAnswer"><?=Yii::t('frontend', 'question_answer')?></a></li>
                            <?php
                            if ($courseModel->course_type == LnCourse::COURSE_TYPE_FACETOFACE) {
                                ?>
                                <li><a href="#courseAward" data-var="courseAward" aria-controls="courseAward"><?=Yii::t('frontend', 'enroll_student')?></a></li>
                                <li><a href="#courseAward3" data-var="courseAward3" aria-controls="courseAward"><?=Yii::t('frontend', 'signin_manage')?></a></li>
                                <?php
                            }
                            if($courseModel->open_status >0){
                                ?>
                                <li><a href="#courseAward4" data-var="courseAward4" aria-controls="courseAward"><?=Yii::t('frontend', 'study_record')?></a></li>
                                <li><a href="#courseAward6" data-var="courseAward6" aria-controls="courseAward"><?=Yii::t('frontend', 'summary')?></a></li>
                                <li><a href="#courseAward5" data-var="courseAward5" aria-controls="courseAward"><?=Yii::t('frontend', 'transcript_manage')?></a></li>
                                <?php
                            }
                            if ($isCourseOnline){
                                ?>
                                <li><a href="#courseAward9" data-var="courseAward9" aria-controls="courseAward" aria-expanded="true"><?= Yii::t('common', 'complete_rule') ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="courseIntro"></div>
                            <div class="tab-pane" id="courseAwardCert"></div>
                            <div class="tab-pane" id="courseTeacher"></div>
                            <div class="tab-pane" id="courseAnswer"></div>
                            <div class="tab-pane" id="courseAward"></div>
                            <div class="tab-pane" id="courseAward3"></div>
                            <div class="tab-pane" id="courseAward4"></div>
                            <div class="tab-pane" id="courseAward5"></div>
                            <div class="tab-pane" id="courseAward6">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body">
                                        <div class="panel-list" id="score_list" data-url="<?=Url::toRoute(['/resource/course/get-course-score','id'=>$courseModel->kid])?>"></div>
                                        <div id="list_loading" class="loadingWaiting hide" style="margin:100px auto;">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <p><?=Yii::t('frontend', 'loading')?>...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="courseAward9">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body">
                                        <div class="panel-list" id="config_list" data-url="<?=Url::toRoute(['/resource/course/get-course-config','id'=>$courseModel->kid])?>"></div>
                                        <div id="list_loading" class="loadingWaiting hide" style="margin:100px auto;">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                            <p><?=Yii::t('frontend', 'loading')?>...</p>
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

<!-- 新增成绩弹出窗口,并能打印输出 -->
<div class="modal ui printScore ipad" id="printScore" style="padding-left: 0px;">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
      <h4 class="modal-title" id="score_modal_title"><?=Yii::t('frontend', 'synthetic_test')?> <?=Yii::t('frontend', 'transcript')?></h4>
    </div>
    <div id="score_modal_content" class="content">
        <div class="panel-body">
            <div class="actionBar">
              <a class="btn btn-success pull-left" href="javascript:void(0);" onclick="exportScoreDetail();"><?=Yii::t('frontend', 'all_export')?></a>
                <form class="form-inline pull-right">
                    <div class="form-group">
                      <input id="score_key" type="text" class="form-control" placeholder="<?=Yii::t('frontend', 'input_name_email')?>">
                      <div class="form-group pull-left">
                          <select id="score_status" class="form-control">
                              <option value=""><?=Yii::t('common', 'status')?></option>
                              <option value="<?=\common\models\learning\LnResComplete::COMPLETE_STATUS_NOTSTART?>"><?=Yii::t('frontend', 'complete_status_nostart')?></option>
                              <option value="<?=\common\models\learning\LnResComplete::COMPLETE_STATUS_DOING?>"><?=Yii::t('frontend', 'complete_status_doing')?></option>
                              <option value="<?=\common\models\learning\LnResComplete::COMPLETE_STATUS_DONE?>"><?=Yii::t('frontend', 'complete_status_done')?></option>
                            </select>
                        </div>
                        <div class="form-group pull-left">
                            <select id="score_type" class="form-control">
                              <option value="*"><?=Yii::t('frontend', 'any_result')?></option>
                                <option value=">="><?= Yii::t('common', 'examination_score') ?> >=</option>
                                <option value="<="><?= Yii::t('common', 'examination_score') ?> <=</option>
                                <option value="="><?= Yii::t('common', 'examination_score') ?> =</option>
                            </select>
                        </div>
                      <input id="score_value" type="text" onkeyup="this.value=this.value.replace(/\D+/,'');" onblur="this.value=this.value.replace(/\D+/,'');" class="form-control" placeholder="><?=Yii::t('frontend', 'score')?>" style="width:60px;">
                      <button type="reset" class="btn btn-default pull-right"><?=Yii::t('frontend', 'reset')?></button>
                      <button onclick="searchScore();" type="button" class="btn btn-primary pull-right" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
                    </div>
                </form>
            </div>
            <div id="score_modal_list"></div>
        </div>
    </div>
</div>
<!-- /container -->

<!-- 成绩详情的弹出窗口 -->
<div class="ui modal ipad" id="scoreDetails" ></div>

<!-- 二维码的弹出窗口 -->
<div class="ui modal" id="bigCode">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'qr_code')?></h4>
    </div>
    <div class="content">
        <div class="panel-body">
            <img src="<?=TStringHelper::genQRCode($courseModel->short_code)?>" style="width:100%; height:auto;text-align: center;">
        </div>
        <div class="c"></div>
    </div>
</div>

<div id="startcourse" class="ui modal">
    <div class="header"><?=Yii::t('frontend', 'confirm_open_course')?>？</div>
    <div class="content">
        <p><?=Yii::t('frontend', 'warning_for_open_course')?></p>
        <div class="c"></div> <!--新增-->
    </div>
    <div class="actions">
        <div class="btn btn-primary ok"><?=Yii::t('frontend', 'be_sure')?></div>
        <div class="btn btn-default cancel"><?= Yii::t('frontend', 'page_info_good_cancel') ?></div>
    </div>
</div>

<div id="startcourse2" class="ui modal">
    <div class="header"><?=Yii::t('frontend', 'confirm_open_course')?>?</div>
    <div class="content">
        <p><?= Yii::t('frontend', 'start_course_confirm') ?></p>
        <div class="c"></div> <!--新增-->
    </div>
    <div class="actions">
        <div class="btn btn-primary ok"><?=Yii::t('frontend', 'be_sure')?></div>
        <div class="btn btn-default cancel"><?= Yii::t('frontend', 'page_info_good_cancel') ?></div>
    </div>
</div>

<div id="endcourse" class="ui modal">
    <div class="header"><button type="button" class="close"><span aria-hidden="true">×</span></button><?= Yii::t('frontend', 'end_course_confirm') ?>？</div>
    <div class="content">
        <p><?= Yii::t('frontend', 'end_course_confirm_warning') ?>？</p>
        <div class="c"></div> <!--新增-->
    </div>
    <div class="actions">
        <button type="button" class="btn btn-primary ok"><?=Yii::t('frontend', 'be_sure')?></button>
        <button type="button" class="btn btn-default cancel"><?= Yii::t('frontend', 'page_info_good_cancel') ?></button>
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
<div class="ui modal" id="examination_log-result"></div>
<div class="ui modal" id="homework"></div>
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
                                        <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'sign_in_date') ?></label>
                                        <div class="col-sm-9">
                                            <input type="hidden" id="edit_sign_type" />
                                            <input type="hidden" id="edit_sign_idx" />
                                            <input id="edit_sign_date" readonly="readonly" data-type="rili" class="form-control pull-left" type="text" style="width: 95%;" value="">
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
        <div class="c"></div>
    </div>
    <div class="c"></div>
</div>

<!-- 签到二维码的弹出窗口 -->
<div class="ui modal" id="div_sign_in_qr_code_big">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?=Yii::t('frontend', 'qr_code')?></h4>
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

        var addTime = '<div name="edit_sign_time_list" class="row timeRow"><div class="col-sm-12"><div class="form-group form-group-sm"><div class="col-sm-3 "><input name="edit_sign_time_title" type="text" placeholder="<?= Yii::t('common', 'audience_name') ?>" class="form-control" value=""></div><div class="col-sm-9"><input name="edit_sign_time_start" onblur="checkEditSignTimeStart(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="8:00" placeholder="8:00" class="form-control pull-left" style="width: 45%" value=""><input name="edit_sign_time_end" onblur="checkEditSignTimeEnd(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="10:00" placeholder="10:00" class="form-control pull-left" style="width: 45%" value=""><a href="###" class="btn btn-xs pull-right delTimeBtn" onclick="removeSignSettingFromDateBox(this)" style=" position: absolute; top: 5px;"><?=Yii::t('common', 'delete_button')?></a></div></div></div></div>';
        // $(this).parents('.row').before(addTime);
        $("#edit_sign_time").append(addTime);
        app.genCalendar();
    });


    function showSignInBigQrCodeImg(id) {
        $("#img_sign_in_qr_code_big").attr('src', $("#" + id).attr('src'));
        app.alert('#div_sign_in_qr_code_big');


    }
    function printSignInQrCodeImg(id) {
        var src = location.origin + $("#" + id).attr('src');
        app.printImage(src);
    }
    app.extend("alert");
    function bigcode() {
        app.alert('#bigCode');
    }
    $(function () {
        app.disableRating("pingfen");
    });
    var expandBtn = $(".answerComments");

    expandBtn.bind("click", function () {
        var b = $(this).parent();
        var c = $(b).nextAll(".commentInput");
        if (c.hasClass("hide")) {
            c.removeClass("hide")
        } else {
            c.addClass("hide")
        }
    })
    var courseIntroUrl = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-intro', 'id' => $courseModel->kid])?>";
    var courseAwardRead = false;
    var courseAwardUrl = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-enroll', 'id' => $courseModel->kid])?>";
    var courseAward3Url = "<?=Yii::$app->urlManager->createUrl(['sign-in/detail-sign', 'id' => $courseModel->kid])?>";
    var courseAward4Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-score', 'id' => $courseModel->kid, 'iframe' => 'teacher'])?>";
    var courseAward5Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-grade', 'id' => $courseModel->kid, 'iframe' => 'teacher'])?>";
    var courseAward6Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-cert', 'id' => $courseModel->kid])?>";
    var courseAward7Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-teacher', 'id' => $courseModel->kid])?>";
    var courseAward8Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-answer', 'id' => $courseModel->kid])?>";
    var courseAward9Url = "<?=Yii::$app->urlManager->createUrl(['/resource/course/get-course-config', 'id' => $courseModel->kid])?>";

    var
        selectBtn = $(".selectBtn"),
        selectPanel = $(".selectPanel"),
        btnComfirm = $(".btnComfirm");

    selectBtn.bind("click", function () {
        if (selectPanel.hasClass("hide")) {
            selectPanel.removeClass("hide");
        } else {
            selectPanel.addClass("hide");
        }
    });

    btnComfirm.bind("click", function () {
        if (selectPanel.hasClass("hide")) {
            selectPanel.removeClass("hide");
        } else {
            selectPanel.addClass("hide");
        }
    });

    $('.btnaddNewChoice').bind('click', function () {
        $('.addNewChoice').removeClass('hide');
    });

    $('.btnaddNewQuestion').bind('click', function () {
        $('.addNewQuestion').removeClass('hide');
    });

    $('.cancelBtn').bind('click', function () {
        $(this).parent().addClass('hide');
    });
    $("document").ready(function () {

        $('.controlBtns .btn-success').bind('click', function () {
            $(this).removeClass('btn-success').text('<?= Yii::t('frontend', 'passed') ?>')
            $(this).next('.btn-default').remove()
        });

        $('.controlBtns .btn-default').bind('click', function () {
            $(this).removeClass('btn-default').text('<?= Yii::t('frontend', 'refused') ?>')
            $(this).prev('.btn-success').remove()
        });

        $('.controlBtns2 .btn-success').bind('click', function () {
            $(this).removeClass('btn-success').text('<?= Yii::t('frontend', 'added') ?>')
        });

        $('.controlBtns4 .btn-success').bind('click', function () {
            $(this).removeClass('btn-success').text('<?= Yii::t('frontend', 'issued') ?>')
        });

        $('.courseControlBtn').bind('click', function () {
            var courseControlBtn = $(this);
            var cid = courseControlBtn.attr('date-var');

            if (courseControlBtn.attr('data-status') == '0') {
                <!--?php if(time()< $courseModel->open_start_time){ ?-->
                app.alert("#startcourse", {
                    ok: function () {
                        if (changestatus('start', cid)) {
                            $(this).text('<?=Yii::t('frontend', 'end_course')?>');
                            $('.courseStatu').text('<?=Yii::t('frontend', 'complete_status_doing')?>');
                            $('.courseStatu').removeClass('hide')
                        }
                    },
                    cancel: function () {

                        return true;
                    }
                });
                <!--?php } else { ?-->
                /* var res = changestatus('start',cid);
                 if (res) {
                 $(this).text('<?=Yii::t('frontend', 'end_course')?>');
                 $('.courseStatu').text('<?=Yii::t('frontend', 'complete_status_doing')?>');
                 $('.courseStatu').removeClass('hide');
                 }*/
                <!--?php }?-->
            } else {
                app.alert("#endcourse",
                    {
                        ok: function () {
                            var res = changestatus('end', cid);
                            if (res) {
                                $(this).remove();
                                $('.courseStatu').text('<?=Yii::t('frontend', 'complete_status_done')?>');
                            }
                            return true;
                        },
                        cancel: function () {
                            return true;
                        }
                    }
                );

                //

            }
        });

        FmodalLoad("courseIntro", courseIntroUrl);
        $("ul#myTab li a").bind('click', function (e) {
            e.preventDefault();
            var obj = $(this);
            if (obj.parent().hasClass('active')){
                return ;
            }
            $("ul#myTab li").removeClass('active');
            var target = obj.attr('data-var');
            obj.parent().addClass('active');
            $(".tab-content").find('.tab-pane').removeClass('active').hide();
            $("#"+target).addClass('active').show();
            if (target == "courseIntro") {
                FmodalLoad(target, courseIntroUrl);
            } else if (target == "courseAward" && !courseAwardRead) {
                FmodalLoad(target, courseAwardUrl);
                courseAwardRead = true;
            } else if (target == "courseAward3") {
                FmodalLoad(target, courseAward3Url);
            } else if (target == "courseAward4") {
                FmodalLoad(target, courseAward4Url);
            } else if (target == "courseAward5") {
                FmodalLoad(target, courseAward5Url);
            } else if (target == "courseAwardCert") {
                FmodalLoad(target, courseAward6Url);
            } else if (target == "courseTeacher") {
                FmodalLoad(target, courseAward7Url);
            } else if (target == "courseAnswer") {
                FmodalLoad(target, courseAward8Url);
            } else if (target == "courseAward6") {
                if ($("#score_list").html() == "") {
                    loadPage(score_url, 'score_list', true);
                }
            } else if (target == "courseAward9") {
                if ($("#config_list").html() == "") {
                    loadPage(courseAward9Url, 'config_list', true);
                }
            }
            return true;
        });
    });

    // 修改课程状态课程
    function changestatus(status, cid) {
        var url = "<?=Url::toRoute(['teacher/cstatus'])?>";
        $.post(url, {status: status, cid: cid}, function (data) {
            var result = data.result;
            if (result === 'fail') {
                app.showMsg(data.msg, 1000);
                return false;
            }
            else if (result === 'success') {
                app.showMsg(data.msg, 1000);
                setTimeout('window.location.reload()', 1500);
                return true;
            }
        }, "json");
        return false;
    }

    function FmodalLoad(target, url) {
        if (url) {
            $('#' + target).empty();
            $('#' + target).load(url);
        }
    }

    function reloadcourse(Id) {
        if (Id == 'courseIntro') {
            FmodalLoad(startId, courseStart);
        }
        else if (Id == 'courseAward') {
            FmodalLoad(beforeId, courseBefore);
        }
        else if (Id == 'courseTeacher') {
            FmodalLoad(endId, courseEnd);
        }

    }

    var score_url = "<?=Url::toRoute(['/resource/course/get-course-score', 'id' => $courseModel->kid])?>";

    function loadPage(ajaxUrl, container, is_clear) {
        if (is_clear) {
            $("#" + container).empty();
            $("#list_loading").removeClass("hide");
        }
        app.get(ajaxUrl, function (data) {
            if (is_clear) {
                $("#list_loading").addClass('hide');
            }
            $("#" + container).html(data);
            $("#" + container + ' .pagination a').bind('click', function () {
                var url = $(this).attr('href');
                loadPage(url, container, is_clear);
                return false;
            });
        });
    }
    var scoreDetailUrl = "<?=Url::toRoute(['/resource/course/get-score-detail'])?>";
    var exportScoreDetailUrl = "<?=Url::toRoute(['/resource/course/export-score-detail'])?>";
    var viewModId = null;
    function exportScoreDetail() {
        if (viewModId) {
            window.location.href = exportScoreDetailUrl + "?id=" + viewModId;
        }
    }

    /*加载成绩查看列表*/
    function LoadCompleteInfo(obj, courseId, modResId, itemId, itemName, componentCode, userId) {
        var url = '<?=Url::toRoute(['/teacher/common-result'])?>';
        if (componentCode != 'scorm' && componentCode != 'aicc' && componentCode != 'examination' && componentCode != 'investigation' && componentCode != 'homework') {
            componentCode = 'other';
        }
        if (typeof userId != 'undefined') {
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
        if (componentCode == 'examination' && typeof $(obj).attr('data-log') != 'undefined'){
            var activityId = $(obj).attr('data-activity-id');
            var modId = $(obj).attr('data-mod-id');
            url = '<?=Url::toRoute(['/exam-manage-main/view-log'])?>?id='+itemId+'&courseId='+courseId+'&modId='+modId+'&modResId='+modResId+'&courseactivityId='+activityId+'&userId='+userId+'&companyId=';
            componentCode += '_log';
        }

        $("#" + componentCode + "-result").empty();
        $.get(url, function (r) {
            if (r) {
                $("#" + componentCode + "-result").html(r);
                app.alertWideAgain($("#" + componentCode + "-result"));
                app.scrollAlertToTop();
            } else {
                app.showMsg(app.msg.NETWORKERROR);
            }
        });
    }
    //查看某个人的调查投票结果
    var detail_free = true;
    function detail(userId, courseId, modResId, itemId) {
        var modalId = "questionairedetail";
        var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('teacher/questionaire-result')?>";
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'itemId', itemId);
        ajaxUrl = urlreplace(ajaxUrl, 'userId', userId);
        if (detail_free) {
            detail_free = false;
            app.get(ajaxUrl, function (r) {
                if (r) {
                    app.alertWideAgain($("#" + modalId).html(r));
                }
                detail_free = true;
            });
        }
    }
    function detailhomework(userId, courseId, modResId, itemId) {
        var modalId = "courseware";
        var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('teacher/homework-player')?>";
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'itemId', itemId);
        ajaxUrl = urlreplace(ajaxUrl, 'userId', userId);
        if (detail_free) {
            detail_free = false;
            app.get(ajaxUrl, function (r) {
                if (r) {
                    app.alertWideAgain($("#" + modalId).html(r));
                }
                detail_free = true;
            });
        }
    }
    function backToStudentSignList()
    {
        FmodalLoad('courseAward3',courseAward3Url);
    }
</script>