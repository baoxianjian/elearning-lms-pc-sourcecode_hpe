<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/12
 * Time: 11:16
 */
use common\helpers\TTimeHelper;
use common\services\learning\RecordService;
use yii\helpers\Html;

?>
<? foreach ($data as $v): ?>
<div class="timeline-item">
    <div class="timeline-icon">
        <i class="glyphicon glyphicon-modal-window" title="<?= Yii::t('common', 'serial') ?>"></i>
    </div>
    <div class="timeline-content">
        <h2>
            <?=str_replace(['{time}','{verb}','{category}','{acivity}'],[TTimeHelper::toDate($v['created_at'],'Y年m月d日'),$v['learning_verb'],$v['record_category'],Html::encode($v['learning_acivity'])],RecordService::RECORD_TEMPLATE_COURSE_1)?>
        </h2>
        <br/>
        <span><i class="glyphicon glyphicon-time"></i><?= $v['learning_verb'] ?><?= Yii::t('common', 'time') ?>：<?=TTimeHelper::toDateTime($v['created_at'],'Y年m月d日 H:i')?></span>
        <? if($v['learning_verb']!== Yii::t('frontend', 'page_info_good_cancel')): ?><a href="<?= Yii::$app->urlManager->createUrl(['student/certification-preview', 'id' => $v['object_id']]) ?>" class="pull-right" target="_blank"><?= Yii::t('common', 'view_button') ?></a><? endif; ?>
    </div>
</div>
<? endforeach; ?>