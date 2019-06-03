<?php
use common\helpers\TTimeHelper;
use common\services\learning\RecordService;
use yii\helpers\Html;

?>
<? foreach ($data as $v): ?>
    <div class="timeline-item">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-question-sign" title="<?= Yii::t('frontend', 'question_answer') ?>"></i>
        </div>
        <div class="timeline-content">
            <h2>
                <span class="lessWord timelineTitle"><? if ($v->record_format==='1'):?>
                    <?=str_replace(['{time}','{verb}','{acivity}'],[TTimeHelper::toDate($v['created_at'],'Y年m月d日'),$v['learning_verb'],Html::encode($v['learning_acivity'])],RecordService::RECORD_TEMPLATE_QUESTION_1)?>
                <? elseif($v->record_format==='2'): ?>
                    <?=str_replace(['{time}','{verb}','{acivity}','{result}'],[TTimeHelper::toDate($v['created_at'],'Y年m月d日'), $v['learning_verb'], Html::encode($v['learning_acivity']), $v['learning_result']],RecordService::RECORD_TEMPLATE_QUESTION_2)?>
                <? endif;?>
                    </span>
                <a class="btn pull-right noticeShare" href="javascript:void(0);" onclick="ShowShare('<?=$v['object_id']?>','<?=Html::encode($v['learning_acivity'])?>',3)"><?= Yii::t('frontend', 'share') ?></a>
            </h2>
            <br/>
            <span><i class="glyphicon glyphicon-time"></i><?= Yii::t('frontend', 'page_info_publish') ?>：<?= TTimeHelper::toDateTime($v['created_at'],'Y年m月d日 H:i') ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v['object_id']]) ?>" class="pull-right"><?= Yii::t('common', 'view_button') ?></a>
        </div>
    </div>
<? endforeach; ?>