<?php
/**
 * User: Liu Cheng
 * Date: 2015/12/25
 * Time: 13:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\models\learning\LnCourse;

$this->pageTitle = $courseModel->course_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('frontend','teacher_home'), 'url' => ['teacher/index']];
$this->params['breadcrumbs'][] =  Yii::t('frontend','teacher_course_detail');
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
                    <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('frontend', 'course_online')?>
                </div>
                <div class="panel-body">
                    <div class="courseTitle">
                        <div class="left"><img src="<?= $courseModel->theme_url ? $courseModel->getCourseCover() : '/static/frontend/images/course_theme_big.png'?>"></div>
                        <div class="right">
                            <h2><?=$courseModel->course_name?>
                                <a href="###" class="btn courseStatu"><?=Yii::t('frontend', 'complete_status_doing')?></a>
                            </h2>
                            <table>
                                <tr>
                                    <td width="50%"><span><strong><?=Yii::t('common','course_code')?>:</strong> <?=$courseModel->course_code?></span></td>
                                    <td><span><strong><?=Yii::t('common','category_id')?>:</strong> <?=$courseModel->getCourseCategoryText()?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_type')?>:</strong> <?=$courseModel->course_type==LnCourse::COURSE_TYPE_ONLINE? Yii::t('frontend', 'course_online'): Yii::t('frontend', 'course_face')?></span></td>
                                    <td>
                                        <span><strong><?=Yii::t('common','course_default_credit')?>:</strong> <?=$courseModel->default_credit?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_level')?>:</strong> <?=$courseModel->getDictionaryText('course_level',$courseModel->course_level)?></span></td>
                                    <td><span><strong><?=Yii::t('common','course_period')?>:</strong> <?=$courseModel->course_period?><?=$courseModel->getCoursePeriodUnits($courseModel->course_period_unit)?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_language')?>:</strong> <?=$courseModel->getDictionaryText('course_language',$courseModel->course_language)?></span></td>
                                    <td><span><strong><?=Yii::t('common','course_price')?>:</strong> <?=$courseModel->getPriceUnit($courseModel->currency)?> <?=$courseModel->course_price?></span></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><span><strong><?= Yii::t('common', 'time_validity') ?>:</strong>
                                            <?php
                                            $end_time = $model->end_time ? date("Y-m-d", $courseModel->end_time) : Yii::t('frontend', 'forever');
                                            ?>
                                            <?=date("Y-m-d", $courseModel->start_time)?><?=Yii::t('common', 'to2')?>  <?= $end_time ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><?=Yii::t('frontend', 'give_a_mark')?>:&nbsp;</strong><div id="rating" class="ui star rating" data-name="pingfen" data-rating="<?=floor($rating)?>" data-rating-full="<?=$rating?>" data-max-rating="5" data-person="<?=$rating_count ?>" title="<?=$rating?><?=Yii::t('frontend', 'point')?>"></div></i>
                                    </td>
                                    <td>
                                        <strong style="float:left; margin-right:10px;"><?=Yii::t('frontend', 'qr_code')?>:&nbsp;</strong>
                                        <div class="barCode pull-left">
                                            <span><img src="<?=Yii::$app->urlManager->createUrl('resource/course/qr-scan-code')?>?code=<?=$courseModel->short_code?>" height="128" width="128"></span>
                                        </div>
                                        <a href="#" onclick="bigcode()"><?=Yii::t('frontend', 'enlarge')?></a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="courseInfo">
                        <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                            <li role="presentation" class="active"><a href="#courseIntro" data-var="courseIntro" aria-controls="courseIntro" role="tab" data-toggle="tab" aria-expanded="true"><?=Yii::t('frontend', 'course_content')?></a></li>
                            <?php if($certsUrl){?>
                                <li role="presentation" class=""><a href="#courseAwardCert"  data-var="courseAwardCert" aria-controls="courseAwardCert" role="tab" data-toggle="tab"><?=Yii::t('common', 'serial')?></a></li>
                            <?php }?>
                            <li role="presentation" class=""><a href="#courseTeacher" data-var="courseTeacher" aria-controls="courseTeacher" role="tab" data-toggle="tab"><?=Yii::t('common', 'lecturer')?></a></li>
                            <li role="presentation" class=""><a href="#courseAnswer" data-var="courseAnswer" aria-controls="courseAnswer" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'question_answer')?></a></li>
                            <li role="presentation" class=""><a href="#courseRule" data-var="courseRule" aria-controls="courseRule" role="tab" data-toggle="tab"><?= Yii::t('common', 'complete_rule') ?></a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="courseIntro">
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseAwardCert">
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseTeacher">
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseAnswer">
                            </div>
                            <div role="tabpanel" class="tab-pane" id="courseRule">
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

<!-- 二维码的弹出窗口 -->
<div class="ui modal" id="bigCode">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'qr_code')?></h4>
    </div>
    <div class="content">
        <div class="panel-body">
            <img src="<?=Yii::$app->urlManager->createUrl('resource/course/qr-scan-code')?>?code=<?=$courseModel->short_code?>" style="width:100%; height:auto;text-align: center;">
        </div>
        <div class="c"></div>
    </div>
</div>

<!-- 课件完成情况弹出窗口 -->
<div class="ui modal" id="courseware"></div>
<div class="ui modal" id="questionairedetail"></div>
<div class="ui modal" id="questionairedetailone"></div>
<div class="ui modal" id="checksurvay"></div>
<div class="ui modal" id="examination"></div>
<div class="ui modal" id="examination_log"></div>
<div class="ui modal" id="homework"></div>
<script type="text/javascript">
    var courseIntroUrl = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-intro'])?>?id=<?=$courseModel->kid?>";
    var courseAward6Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-cert'])?>?id=<?=$courseModel->kid?>";
    var courseAward7Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-teacher'])?>?id=<?=$courseModel->kid?>";
    var courseAward8Url = "<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-answer'])?>?id=<?=$courseModel->kid?>";
    var courseAward9Url = "<?=Yii::$app->urlManager->createUrl(['/resource/course/get-course-config'])?>?id=<?=$courseModel->kid?>";

    $("document").ready(function () {
        app.disableRating("pingfen");

        FmodalLoad("courseIntro", courseIntroUrl);

        $("ul#myTab li a").bind('click', function () {
            var obj = $(this);
            var target = obj.attr('data-var');

            if (target == "courseIntro") {
                FmodalLoad(target, courseIntroUrl);
            } else if (target == "courseAwardCert") {
                FmodalLoad(target, courseAward6Url);
            } else if (target == "courseTeacher") {
                FmodalLoad(target, courseAward7Url);
            } else if (target == "courseAnswer") {
                FmodalLoad(target, courseAward8Url);
            }else if(target == "courseRule"){
                if ($("#config_list").html() == "") {
                    loadPage(courseAward9Url, 'config_list', true);
                }
            }

            return true;

        });

    });

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
</script>

