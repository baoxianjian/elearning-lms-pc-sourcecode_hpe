<?php
use common\models\social\SoCollect;
use common\helpers\TTimeHelper;
use yii\helpers\Html;

?>
<? foreach ($data as $v): ?>
    <? if ($v['type']==SoCollect::TYPE_QUESTION):?>
        <div class="timeline-item eventCate1">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-question-sign" title="<?=Yii::t('frontend','question_answer')?>"></i>
            </div>
            <div class="timeline-content">
                <h2><?= Html::encode($v['title']) ?></h2>
                <table class="timeLine_pathBlock">
                    <tr>
                        <td width="30%"><strong><?=Yii::t('common','category')?>:</strong> <?=Yii::t('frontend','question_answer')?></td>
                        <td width="30%"><strong><?=Yii::t('frontend','posted_question_text')?>:</strong> <?= Html::encode($v['real_name']) ?></td>
                        <td width="40%"><strong><?=Yii::t('frontend','posted_question_time')?>:</strong> <?= \common\helpers\TTimeHelper::toDateTime($v['start_time']) ?></td>
                    </tr>
                </table>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= \common\helpers\TTimeHelper::toDate($v['created_at']) ?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v['kid']]) ?>" class="pull-right"><?=Yii::t('common','view_button')?></a>
                <a href="javascript:void(0);" onclick="cancelCollect(this,'<?=$v['kid']?>','q')" class="pull-right mr10"><?=Yii::t('common','canel_collection')?></a>
            </div>
        </div>
    <? elseif($v['type']==SoCollect::TYPE_COURSE):?>
        <div class="timeline-item eventCate1">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book" title="<?=Yii::t('common','course')?>"></i>
            </div>
            <div class="timeline-content">
                <h2><?= Html::encode($v['title']) ?></h2>
                <table class="timeLine_pathBlock">
                    <tr>
                        <td width="30%"><strong><?=Yii::t('common','category')?>:</strong> <?=Yii::t('common','course')?></td>
                        <td width="30%"><strong><?=Yii::t('common','lecturer')?>:</strong> <?= $v['real_name'] ? Html::encode($v['real_name']) : Yii::t('frontend','temp_no') ?></td>
                        <td width="40%"><strong><?=Yii::t('common','time_validity')?>:</strong> <?= TTimeHelper::CourseValidity($v['start_time'],$v['end_time']) ?></td>
                    </tr>
                </table>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDate($v['created_at']) ?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['kid']]) ?>" class="pull-right"><?=Yii::t('common','view_button')?></a>
                <a href="javascript:void(0);" onclick="cancelCollect(this,'<?=$v['kid']?>','c')" class="pull-right mr10"><?=Yii::t('common','canel_collection')?></a>
            </div>
        </div>
    <? endif;?>
<? endforeach; ?>