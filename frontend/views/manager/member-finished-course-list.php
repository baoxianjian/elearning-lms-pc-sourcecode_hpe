<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/12/2
 * Time: 15:07
 */
use common\models\learning\LnCourse;
use common\models\learning\LnCourseComplete;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use yii\helpers\Html;

?>
<? if ($data): foreach($data as $v):?>
    <? if(empty($v->lnCourse)) continue; ?>
    <div class="col-md-12 myLessonList">
        <img src="<?=TStringHelper::Theme($v->lnCourse) ?>">
        <h5 class="lessWord"><a href="<?=Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v->lnCourse->kid])?>"><?=Html::encode($v->lnCourse->course_name)?></a></h5>
        <p><a href="javascript:void(0);" class="score" data-cid="<?=$v->course_id?>"><?=Yii::t('frontend', 'progress')?>: <?echo $exts[$v->kid]['complete'],'/',$exts[$v->kid]['count']; ?></a> &nbsp;&nbsp; <?=Yii::t('common', 'courseware_time')?>: <?=$v->lnCourse->course_period?><?=$v->lnCourse->getCoursePeriodUnits($v->lnCourse->course_period_unit)?><? $score=$v->getCompleteScore(); if($score!==null): ?>&nbsp;&nbsp; <?= Yii::t('common', 'examination_score') ?>:<?=$score ?><?=Yii::t('frontend', 'point')?><? endif; ?></p>
        <p><?=Yii::t('frontend', 'course_time')?>: <?=TStringHelper::GetCourseTime($v->lnCourse) ?> &nbsp;&nbsp;  <?=Yii::t('frontend', 'last_visit')?>: <?=$v->last_record_at?TTimeHelper::toDateTime($v->last_record_at,'Y年m月d日'):'-'?></p>
        <? if($v->lnCourse->course_type===LnCourse::COURSE_TYPE_FACETOFACE): ?><span class="tag_offline"><?=Yii::t('common', 'face_to_face')?></span><? endif; ?>
    </div>
<? endforeach; endif; ?>