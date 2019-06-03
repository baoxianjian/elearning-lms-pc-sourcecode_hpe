<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/12
 * Time: 11:16
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use common\models\learning\LnCourseComplete;

?>
<? foreach ($data as $v): ?>
    <? if($v['object_type']==='course'): ?>
    <div class="timeline-item">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="<?=Yii::t('common', 'course')?>"></i>
        </div>
        <div class="timeline-content">
            <h2><?=Yii::t('frontend', 'i_am_here_{value}',['value'=>TTimeHelper::toDateTime($v['start_at']).$v['learning_verb']])?><?= $v['record_category'] ?>《<?= Html::encode($v['learning_acivity']) ?>》<a class="btn pull-right noticeShare" href="#"><?=Yii::t('frontend', 'share')?></a></h2>
            <table class="timeLine_pathBlock">
                <tr>
                    <td><strong><?=Yii::t('common', 'course_type')?>:</strong><?=Yii::t('common', 'course')?></td>
                    <td><strong><?=Yii::t('common', 'complete_grade')?>:</strong><?=$v['learning_result']?></td>
                </tr>
                <tr>
                    <td><strong><?=Yii::t('frontend', 'signup_time')?>:</strong><?= TTimeHelper::toDateTime($v['start_at']) ?></td>
                    <td><strong><?=Yii::t('common', 'complete_end_at')?>:</strong><?= TTimeHelper::toDateTime($v['end_at']) ?></td>
                </tr>
            </table>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDateTime($v['created_at']) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['object_id']]) ?>" class="pull-right"><?=Yii::t('frontend', 'review')?></a>
        </div>
    </div>
        <? elseif($v['object_type']==='question'): ?>
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="glyphicon glyphicon-book" title="<?=Yii::t('common', 'course')?>"></i>
                </div>
                <div class="timeline-content">
                    <h2><?=Yii::t('frontend', 'i_am_here_{value}',['value'=>TTimeHelper::toDateTime($v['start_at']).$v['learning_verb']])?><?= $v['record_category'] ?><?= $v['record_category'] ?>《<?= Html::encode($v['learning_acivity']) ?>》<a class="btn pull-right noticeShare" href="#"><?=Yii::t('frontend', 'share')?></a></h2>
                    <table class="timeLine_pathBlock">
                        <tr>
                            <td><strong><?=Yii::t('common', 'course_type')?>:</strong><?=Yii::t('common', 'course')?></td>
                            <td><strong><?=Yii::t('common', 'complete_grade')?>:</strong><?=$v['learning_result']?></td>
                        </tr>
                        <tr>
                            <td><strong><?=Yii::t('frontend', 'signup_time')?>:</strong><?= TTimeHelper::toDateTime($v['start_at']) ?></td>
                            <td><strong><?=Yii::t('common', 'complete_end_at')?>:</strong><?= TTimeHelper::toDateTime($v['end_at']) ?></td>
                        </tr>
                    </table>
                    <hr/>
                    <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDateTime($v['created_at']) ?></span>
                    <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['object_id']]) ?>" class="pull-right"><?=Yii::t('frontend', 'review')?></a>
                </div>
            </div>
        <? elseif(1==2): ?>
<div class="timeline-item eventCate1">
    <div class="timeline-icon">
        <i class="glyphicon glyphicon-book"></i>
    </div>
    <div class="timeline-content">
        <h2><?= Html::encode(TStringHelper::subStr($v['course_name'], 16, 'utf-8', 0, '...')) ?><?if($v['complete_status']==LnCourseComplete::COMPLETE_STATUS_DONE):?><strong class="noticeOver"><?=Yii::t('frontend', 'complete_status_done')?></strong><?endif;?></h2>
        <p>
            <?= Html::encode(TStringHelper::subStr($v['course_desc_nohtml'], 54, 'utf-8', 0, '...')) ?>
        </p>
        <hr/>
        <? if ($v['complete_status'] == LnCourseComplete::COMPLETE_STATUS_DONE): ?>
            <span><i class="glyphicon glyphicon-time"></i><?=Yii::t('frontend', 'signup_time')?>: <?= TTimeHelper::toDateTime($v['reg_time']) ?></span>
            <span><i class="glyphicon glyphicon-time"></i><?=Yii::t('common', 'complete_end_at')?>: <?= TTimeHelper::toDateTime($v['end_at']) ?></span>
            <span><?= Yii::t('frontend', 'credit') ?>：<?=$v['complete_grade']?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['course_id']]) ?>" class="pull-right"><?=Yii::t('frontend', 'review_course')?></a>
        <? else: ?>
            <span><i class="glyphicon glyphicon-time"></i><?=Yii::t('frontend', 'signup_time')?>: <?= TTimeHelper::toDateTime($v['reg_time']) ?></span>
            <span><i class="glyphicon glyphicon-time"></i><?=Yii::t('frontend','end_time')?>: <?= TTimeHelper::getCourseEndtime($v['end_time'])?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['course_id']]) ?>" class="pull-right"><?=Yii::t('frontend', 'to_complete')?></a>
        <? endif; ?>
    </div>
</div>
        <? endif; ?>
<? endforeach; ?>