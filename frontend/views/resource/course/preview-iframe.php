<?php
use common\helpers\TStringHelper;
use common\services\learning\CourseService;
use common\services\learning\ResourceCompleteService;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TModal;
use components\widgets\TBreadcrumbs;
use common\models\learning\LnCourse;

?>
<style>
    body {padding-top: 0;}
    .courseInfo {
        float: left;
    }
</style>
<div class="container previewContainer" style="padding-top: 0;">
    <div class="row">
        <!--<div style="height:80px"></div>-->
        <div class="panel-body">
            <div class="col-md-12">
                <div class="panel panel-default hotNews">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-dashboard"></i> <?= Yii::t('frontend', 'introduction_course') ?>
                    </div>
                    <div class="panel-body">
                        <div class="courseTitle">
                            <div class="left" <?=$isOnlineCourse ? '' : 'style="margin-top: 75px;"'?>>
                                <img width="452px" src="<?= $model->theme_url ? $model->theme_url : '/static/frontend/images/course_theme_big.png'?>"/>
                            </div>
                            <div class="right">
                                <h2><?=$model->course_name?></h2>
                                <table>
                                    <tr>
                                        <td><span><strong><?=Yii::t('common','course_code')?>:</strong> <?=$model->course_code?></span></td>
                                        <td><span><strong><?=Yii::t('common','category_id')?>:</strong> <?=$model->getCourseCategoryText()?></span></td>
                                    </tr>
                                    <tr>
                                        <td><span><strong><?=Yii::t('common','course_type')?>:</strong> <?=$model->course_type==LnCourse::COURSE_TYPE_ONLINE?Yii::t('frontend','course_online'): Yii::t('frontend','course_face')?></span></td>
                                        <td>
                                            <span><strong><?=Yii::t('common','course_default_credit')?>:</strong> <?=$model->default_credit?></span>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($isOnlineCourse) {
                                        ?>
                                        <tr>
                                            <td><span><strong><?= Yii::t('common', 'course_level') ?> :</strong> <?= $model->getDictionaryText('course_level', $model->course_level) ?></span></td>
                                            <td><span><strong><?=Yii::t('common','course_period')?>:</strong> <?=$model->course_period?><?=$model->getCoursePeriodUnits($model->course_period_unit)?></span></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td><span><strong><?=Yii::t('common','course_language')?>:</strong> <?=$model->getDictionaryText('course_language',$model->course_language)?></span></td>
                                        <td><span><strong><?=Yii::t('common','course_price')?>:</strong> <?=$model->getPriceUnit($model->currency)?> <?=$model->course_price?></span></td>
                                    </tr>
                                    <?php
                                    if ($isOnlineCourse) {
                                        ?>
                                        <tr>
                                            <td colspan="2"><span><strong><?= Yii::t('common', 'time_validity') ?>:</strong>
                                                    <?php
                                                    $end_time = $model->end_time ? date("Y-m-d", $model->end_time) : Yii::t('frontend', 'forever') ;
                                                    ?>
                                                    <?=date("Y-m-d", $model->start_time)?> <?=Yii::t('common', 'to2')?> <?= $end_time ?></span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= Yii::t('frontend', 'give_a_mark') ?>:&nbsp;</strong><div id="rating" class="ui star rating" data-name="pingfen" data-rating="<?=floor($rating)?>" data-rating-full="<?=$rating?>" data-max-rating="5" data-person="<?=$rating_count ?>" title="<?=$rating?><?= Yii::t('frontend', 'point') ?>"></div></i>
                                        </td>
                                        <td>
                                            <strong style="float:left; margin-right:10px;"><?= Yii::t('frontend', 'qr_code') ?>:&nbsp;</strong>
                                            <div class="barCode pull-left">
                                                <span><img src="<?=TStringHelper::genQRCode($model->short_code)?>" height="128" width="128"></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                    if (!$isOnlineCourse){
                                        ?>
										<tr>
                                            <td>
                                        <span>
                                            <strong><?= Yii::t('frontend', 'places') ?>:&nbsp;</strong>
                                            <?=$model->limit_number?> <?= Yii::t('frontend', 'people') ?>
                                        </span>
                                            </td>
											<td>
                                        <span>
                                            <strong><?= Yii::t('common', 'time') ?>:&nbsp;</strong>
                                            <?=ceil(($model->open_end_time-$model->open_start_time)/86400)?> <?= Yii::t('common', 'time_day') ?>
                                        </span>
                                            </td>
										</tr>
                                        <tr>
                                            <td>
                                        <span>
                                            <strong><?= Yii::t('frontend', 'enroll_time') ?>:&nbsp;</strong>
                                            <?=date('Y年m月d日', $model->enroll_start_time)?> ～ <?=date('m月d日', $model->enroll_end_time)?>
                                        </span>
                                            </td>
                                            <td><span><strong><?=Yii::t('common','course_period')?>:</strong> <?=$model->course_period?><?=$model->getCoursePeriodUnits($model->course_period_unit)?></span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                        <span>
                                            <strong><?= Yii::t('frontend', 'start_course_time') ?>:&nbsp;</strong>
                                            <?=date('Y年m月d日', $model->open_start_time)?> ～ <?=date('m月d日', $model->open_end_time)?>
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
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                        <div class="courseInfo">
                            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                                <li role="presentation" class="active"><a href="#courseIntro" aria-controls="courseIntro" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'course_content') ?></a></li>
                                <?php
                                if (!empty($certificationTemplatesUrl)) {
                                ?>
                                <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab"><?= Yii::t('common', 'serial') ?></a></li>
                                <?php
                                }
                                if (!empty($teacher)) {
                                ?>
                                <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab"><?= Yii::t('common', 'lecturer') ?></a></li>
                                <?php
                                }
                                ?>
                                <!-- <li role="presentation"><a href="#question" aria-controls="question" role="tab" data-toggle="tab">问答</a></li> -->
                            </ul>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="courseIntro">
                                    <div class="panel-default scoreList">
                                        <div class="panel-default scoreList pathBlock">
                                            <div role="tab" id="headingOne">
                                                <p><?= Yii::t('frontend', 'introduction_course') ?>:</p>
                                                <p><?=Html::decode($model['course_desc'])?></p>
                                            </div>
                                            <div class="clearfix"></div>
                                            <hr />
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
                                                                    <span class='stepTime pull-right'><?= Yii::t('frontend', 'study_hours') ?>：<?=$mod['time']?><?= Yii::t('frontend', 'time_minute') ?></span>
                                                                <? }?>

                                                                <? if (!empty($mod['mod_desc'])) { ?>
                                                                    <p><?= Yii::t('frontend', 'module_description') ?>：<?=TStringHelper::OutPutBr($mod['mod_desc'])?></p>
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
                                            <h3 style="text-align:center;"><?= Yii::t('frontend', 'tip_for_get_credentials') ?>.</h3>
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
                                        <div class="panel-body">
                                            <div class="row col-md-12">
                                            <?php
                                            $teacherCount = count($teacher);
                                            $i = 0;
                                            foreach ($teacher as $i => $vo) {
                                            ?>
                                            <div class="row <?=$teacherCount>1?'col-md-6':'col-md-12'?>">
                                                <div class="col-md-2">
                                                    <img src="<?= TStringHelper::Thumb($vo['teacher_thumb_url'],$vo['gender']) ?>" onerror="this.src='/static/common/images/man.jpeg';" style="margin-top:20px; width:100%; text-align:center;">
                                                </div>
                                                <div class="col-md-10">
                                                    <h3 style="text-align:left;"><?= $vo['teacher_name'] ?></h3>
                                                    <p>
                                                        <?php
                                                        if (!empty($vo['description'])){
                                                            echo $vo['description'];
                                                        }else{
                                                            ?>
                                                            <?= Yii::t('frontend', 'warning_for_teacher_detail') ?>
                                                            <?php
                                                        }
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <?php
                                            if ($i%2==1){
                                            ?>
                                            </div><div class="row col-md-12">
                                            <?php
                                            }
                                            }
                                            ?>
                                            </div>
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
        <div style="height:80px"></div>
    </div>
</div>
<script>
var questionTabUrl = '<?=Url::toRoute(['resource/course/get-tab-question','courseId'=>$model->kid, 'preview'=>1])?>';
function FmodalLoad(target, url)
{
    if(url){
        $('#'+target).empty();
        $.get(url,function(r){
            if (r){
                $('#'+target).html(r);
            }
        });
    }
}
//FmodalLoad('question-answer', questionTabUrl);
</script>