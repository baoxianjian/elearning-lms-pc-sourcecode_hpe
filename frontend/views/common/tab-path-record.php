<?php
use common\helpers\TTimeHelper;
use common\services\learning\RecordService;
use yii\helpers\Html;

?>

<? foreach ($data as $v): ?>
    <div class="timeline-item">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="<?= Yii::t('frontend', 'record') ?>"></i>
        </div>
        <div class="timeline-content">
            <h2>
                <?=str_replace(['{time}','{verb}','{category}','{acivity}'],[TTimeHelper::toDate($v['created_at'],'Y年m月d日'),$v['learning_verb'],$v['record_category'],Html::encode($v['learning_acivity'])],RecordService::RECORD_TEMPLATE_RECORD_1)?>
            </h2>
            <hr/>
<!--            相关链接:<a href="--><?//= $v->url ?><!--">--><?//= $v->url ?><!--</a>-->
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= $v->start_at ?>~<?= $v->end_at ?></span>
        </div>
    </div>
<? endforeach; ?>