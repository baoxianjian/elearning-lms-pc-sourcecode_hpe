<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/5/20
 * Time: 16:56
 */

use common\models\learning\LnCourse;
use common\services\learning\CourseService;
use common\services\learning\ResourceCompleteService;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TModal;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;

$this->title = $model->course_name;
$this->params['breadcrumbs'][] = ['url' => Yii::$app->urlManager->createUrl('resource/course/index'), 'label' => Yii::t('frontend','page_learn_path_tab_1')];
$this->params['breadcrumbs'][] = Yii::t('frontend','course_detail');
$this->params['breadcrumbs'][] = $model->course_name;
?>
<style>
    *{word-break: break-all;}
    .course_desc span {color: inherit!important; margin: auto!important;}
    #newTask .myGroupList_mini .thumbList li {float: left; width: 50%;}
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
                    <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('frontend', 'introduction_course')?>
                </div>
                <div class="panel-body">
                    <div class="courseTitle">
                        <div class="left">
                            <img width="452px" src="<?= $model->theme_url ? $model->getCourseCover() : '/static/frontend/images/course_theme_big.png'?>"/>
                        </div>
                        <div class="right">
                            <h2><?=$model->course_name?></h2>
                            <table>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_code')?>:</strong> <?=$model->course_code?></span></td>
                                    <td><span><strong><?=Yii::t('common','course_level')?>:</strong> <?=$model->getDictionaryText('course_level',$model->course_level)?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_type')?>:</strong> <?=$model->course_type==LnCourse::COURSE_TYPE_ONLINE?Yii::t('frontend', 'course_online'): Yii::t('frontend', 'course_face')?></span></td>
                                    <td><span><strong><?=Yii::t('common','course_language')?>:</strong> <?=$model->getDictionaryText('course_language',$model->course_language)?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_period')?>:</strong> <?=$model->course_period?><?=$model->getCoursePeriodUnits($model->course_period_unit)?></span></td>
                                    <td><span><strong><?=Yii::t('common','currency')?>:</strong> <?=$model->getDictionaryText('currency',$model->currency)?></span></td>
                                </tr>
                                <tr>
                                    <td><span><strong><?=Yii::t('common','course_price')?>:</strong> <?=$model->course_price?></span></td>
                                    <td><span><strong><?=Yii::t('common','category_id')?>:</strong> <?=$model->getCourseCategoryText()?></span></td>
                                </tr>
                                <?php
                                if ($isOnlineCourse) {
                                    ?>
                                    <tr>
                                        <td colspan="2"><span><strong><?= Yii::t('common', 'time_validity') ?>:</strong>
                                                <?php
                                                $end_time = $model->end_time ? date("Y-m-d", $model->end_time) : Yii::t('frontend', 'forever');
                                                ?>
                                                <?=date("Y-m-d", $model->start_time)?> <?=Yii::t('common', 'to2')?> <?= $end_time ?></span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td colspan="2">
                                        <strong><?=Yii::t('frontend', 'give_a_mark')?>:&nbsp;</strong><div id="rating" class="ui star rating" data-name="pingfen" data-rating="<?=round($rating)?>" data-rating-full="<?=$rating?>" data-max-rating="5" data-person="<?=$rating_count ?>" title="<?=$rating?><?=Yii::t('frontend', 'point')?>"></div></i>
                                    </td>
                            </table>
                        </div>
                    </div>
                    <div class="courseInfo">
                        <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                            <li role="presentation" class="active"><a href="#courseIntro" aria-controls="courseIntro" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'course_content')?></a></li>
                            <?php
                            if (!empty($certificationTemplatesUrl)) {
                                ?>
                                <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab"><?=Yii::t('common', 'serial')?></a></li>
                                <?php
                            }
                            if (!empty($teacher)) {
                                ?>
                                <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab"><?=Yii::t('common', 'lecturer')?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="courseIntro">
                                <div class="panel-default scoreList">
                                    <div class="panel-default scoreList pathBlock">
                                        <div role="tab" id="headingOne">
                                            <p><?=Yii::t('frontend', 'introduction_course')?>:</p>
                                            <div class="course_desc">
                                                <?=Html::decode($model['course_desc'])?>
                                            </div>
                                            <hr />
                                        </div>
                                        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                            <div class="panel-body">
                                                <ul class='panel-collapse collapse in' role='tabpanel' aria-labelledby='headingOne' id='collapseExample'><h4>
                                                        <?=Yii::t('common', 'scorm_course_unit')?>  </h4>

                                                    <? foreach ($catalogMenu as $modNum => $mod) {?>
                                                        <li class='pathStep'>
                                                        <span class='step lessWord' style='width: 68%; margin-right: 0;'>
                                                            <?=$mod['mod_name']?>
                                                        </span>

                                                            <? if ($mod['time'] != 0) { ?>
                                                                <span class='stepTime pull-right'><?=Yii::t('frontend', 'study_hours')?>：<?=$mod['time']?><?=Yii::t('frontend', 'time_minute')?></span>
                                                            <? }?>

                                                            <? if (!empty($mod['mod_desc'])) { ?>
                                                                <p><?=Yii::t('frontend', 'module_description')?>：<?=TStringHelper::OutPutBr($mod['mod_desc'])?></p>
                                                            <? }?>

                                                            <ul class='pathTask'>
                                                                <?
                                                                if (!empty($mod['courseitems'])) {
                                                                    foreach ($mod['courseitems'] as $num => $resource) {

                                                                        $displayItem = $resource['displayItem'];

                                                                        if ($displayItem) {
                                                                            $learnStatus = $resource['learning_status'];
                                                                            if ($learnStatus == "learned") {
                                                                                echo "<li class='learned'>";
                                                                            } else if ($learnStatus == "learning") {
                                                                                echo "<li class='learning'>";
                                                                            } else {
                                                                                echo "<li class=''>";
                                                                            }
                                                                            $canRun = $resource['canRun'];
                                                                            $mode = $resource['mode'];
                                                                            $itemName = $resource['itemName'];
                                                                            $componentName = $resource['componentName'];
                                                                            $componentCode = $resource['componentCode'];
                                                                            $componentIcon = $resource['componentIcon'];
                                                                            $modResId = $resource['modResId'];
                                                                            if ($mode == CourseService::PLAY_MODE_NORMAL) {
                                                                                if ($canRun) {
                                                                                    echo "<span class='taskName'><i class='unLearn'></i>" .
                                                                                        $componentIcon .
                                                                                        "&nbsp;<a href='" .
                                                                                        Yii::$app->urlManager->createUrl(['resource/course/play', 'modResId' => $modResId]) .
                                                                                        "' title='" . Yii::t('common', 'learn_button') . "'>" .
                                                                                        $itemName . "</a></span>";
                                                                                }
                                                                                else {
                                                                                    echo "<span class='taskName'><i class='unLearn'></i>" .
                                                                                        $componentIcon .
                                                                                        "&nbsp;" .
                                                                                        $itemName . "</span>";
                                                                                }
                                                                            } else {
                                                                                echo "<span class='taskName'><i class='unLearn'></i>" .
                                                                                    $componentIcon .
                                                                                    "&nbsp;<a href='" .
                                                                                    Yii::$app->urlManager->createUrl(['resource/course/play-preview', 'modResId' => $modResId]) .
                                                                                    "' title='" . Yii::t('common', 'learn_button') . "'>" .
                                                                                    $itemName . "</a></span>";
                                                                            }

                                                                            echo "<span class='taskTime pull-right'>" . $componentName . "</span>";

                                                                            echo "</li>";
                                                                        }
                                                                    }
                                                                }
                                                                ?>

                                                            </ul>
                                                        </li>
                                                    <? } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (!empty($certificationTemplatesUrl)) {
                                ?>
                                <div role="tabpanel" class="tab-pane" id="profile">
                                    <div class=" panel-default scoreList">
                                        <div class="panel-body">
                                            <h3 style="text-align:center;"><?=Yii::t('frontend', 'tip_for_get_credentialstip_for_get_credentials')?>.</h3>
                                            <img src="<?=$certificationTemplatesUrl?>" style="margin-top:4%; width:100%; box-shadow:0 0 1px 2px #ccc;">
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            if (!empty($teacher)) {
                                ?>
                                <div role="tabpanel" class="tab-pane" id="messages">
                                    <div class=" panel-default scoreList">
                                        <div class="panel-body" style="min-height:400px;">
                                            <?php
                                            foreach ($teacher as $vo) {
                                                ?>
                                                <div class="row clear">
                                                    <div class="col-md-3">
                                                        <img src="<?=$vo['teacher_thumb_url']?>" onerror="this.src='/static/common/images/thumb.jpg';" style="margin-top:8%; width:100%; text-align:center;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h3 style="text-align:left; margin-top:50px;"><?= Yii::t('frontend', 'lecturer_detail') ?>: <?= $vo['teacher_name'] ?></h3>
                                                        <p><?= $vo['description'] ?></p>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <div role="tabpanel" class="tab-pane" id="question">
                                <div class=" panel-default scoreList">
                                    <div class="panel-body" id="question-answer"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="dist/js/rating.js"></script>

<script>
    $(document).ready(function(){
        app.disableRating("pingfen");

    });
    $('#headingOne a').removeAttr('href');
    $('#collapseExample a').removeAttr('href');
    $('.btn-start').empty().html('<?= Yii::t('frontend', 'study_ok') ?>');
    var questionTabUrl = '<?=Url::toRoute(['resource/course/get-tab-scan-question','courseId'=>$model->kid])?>';
     app.extend('alert');
    function FmodalLoad(target, url)
    {
        if(url){
            $('#'+target).empty();
            var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?= Yii::t('frontend', 'loading') ?>...</p></div></div>';
            $('#'+target).html(loadingDiv); // 设置页面加载时的loading图片
            $.get(url,function(r){
                if (r){
                    $('#'+target).html(r);
                }
            });
        }
    }
    FmodalLoad('question-answer', questionTabUrl);
 </script>
