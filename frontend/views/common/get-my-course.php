<?php
use yii\helpers\Html;
$i = 1; 
 
?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="<?= Yii::t('common', 'course') ?>"></i>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2><?= Html::encode($v['course_name']) ?><strong class="noticeOver"><?=$v['end_time']<time()? Yii::t('frontend', 'due_date'):(($v['end_time']-time())< 259200? Yii::t('frontend', 'fast_expired'):'');//            Yii::t('frontend',$v->msg_status)?></strong></h2>
            <p><?= Html::encode($v['course_desc']) ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= Yii::t('common', 'action_end_at') ?>: <?=\common\helpers\TTimeHelper::toDateTime($v['end_time']) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['course_id'], 'from' => 'myinfo']) ?>" class="pull-right"><?= Yii::t('frontend', 'to_complete') ?></a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>