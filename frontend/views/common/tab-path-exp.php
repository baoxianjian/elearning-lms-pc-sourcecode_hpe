<?php
use common\helpers\TTimeHelper;
use common\services\learning\RecordService;
use yii\helpers\Html;

?>
<? foreach ($data as $v): ?>
    <div class="timeline-item">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-education" title="<?= Yii::t('frontend', 'experience') ?>"></i>
        </div>
        <div class="timeline-content">
            <h2>
                <span class="lessWord timelineTitle"><?=str_replace(['{time}','{verb}','{category}','{acivity}'],[TTimeHelper::toDate($v['created_at'],'Y年m月d日'),$v['learning_verb'],$v['record_category'],Html::encode($v['learning_acivity'])],RecordService::RECORD_TEMPLATE_RECORD_1)?></span>
                <a class="btn pull-right noticeShare" href="javascript:void(0);" onclick="ShareRecord(this,'<?=$v['object_id']?>')"><?= Yii::t('frontend', 'share') ?></a>
            </h2>
            <table class="timeLine_pathBlock">
                <? if($v['attach_original_filename']):?>
                    <tr>
                        <td><strong><?= Yii::t('frontend', 'enclosure') ?>:</strong><a href="javascript:void(0)" onclick="submitDownload('<?=$v['object_id']?>','record')"><?=Html::encode($v['attach_original_filename'])?></a></td>
                    </tr>
                <? endif; ?>
                <tr>
                    <td><strong><?= Yii::t('frontend', 'question_content') ?>:</strong><?=Html::encode($v['content'])?></td>
                </tr>
            </table>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= Yii::t('frontend', 'record_time') ?>：<?= TTimeHelper::toDateTime($v['created_at'],'Y年m月d日 H:i') ?></span>
        </div>
    </div>
<? endforeach; ?>