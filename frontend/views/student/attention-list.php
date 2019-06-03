<?php
use common\helpers\TTimeHelper;
use yii\helpers\Html;

?>
<? foreach ($data as $v): ?>
    <? if($v['type']=='q'): ?>
        <div class="timeline-item eventCate1">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-question-sign" title="<?=Yii::t('frontend','care_question_answer')?>"></i>
            </div>
            <div class="timeline-content">
                <h2><?= Html::encode($v['title']) ?></h2>
                <table class="timeLine_pathBlock">
                    <tr>
                        <td><strong><?=Yii::t('frontend','posted_question_text')?>:</strong> <?= Html::encode($v['sender']) ?></td>
                        <td><strong><?=Yii::t('frontend','posted_question_time')?>:</strong> <?= TTimeHelper::toDateTime($v['a_created_at']) ?></td>
                    </tr>
                </table>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDate($v['created_at']) ?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v['kid']]) ?>" class="pull-right"<?=Yii::t('common','view_button')?>></a>
                <a href="javascript:void(0);" onclick="cancelCare(this,'<?=$v['kid']?>','q')" class="pull-right mr10"><?=Yii::t('common','cancel_attention')?></a>
            </div>
        </div>
    <? elseif($v['type']=='u'): ?>
        <div class="timeline-item eventCate1">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-user" title="<?=Yii::t('frontend','care_man')?>"></i>
            </div>
            <div class="timeline-content">
                <h2><?=Yii::t('frontend','care_user')?>: <?= Html::encode($v['title']) ?></h2>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDate($v['created_at']) ?></span>
                <a href="javascript:void(0);" onclick="cancelCare(this,'<?=$v['kid']?>','u')" class="pull-right"><?=Yii::t('common','cancel_attention')?></a>
            </div>
        </div>
     <? endif; ?>
<? endforeach; ?>