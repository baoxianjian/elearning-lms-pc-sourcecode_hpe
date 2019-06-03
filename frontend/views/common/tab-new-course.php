<?php
use common\models\learning\LnCourse;
use yii\helpers\Html;

$i = 1;
foreach ($data as $v): 
?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="<?= Yii::t('common', 'course') ?>"></i>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2><?= Html::encode($v->course_name) ?><strong class="noticeOver"><?= $v->course_type == LnCourse::COURSE_TYPE_ONLINE ? Yii::t('common','online') : Yii::t('common','face_to_face')?></strong></h2>

            <p><?= Html::encode($v->course_desc) ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= Yii::t('common', 'action_end_at') ?>: <?= $v->end_time ? date('Y-m-d H:i:s', $v->end_time) : Yii::t('frontend', 'time_free') ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v->kid, 'from' => 'message']) ?>" class="pull-right"><?= Yii::t('frontend', 'to_complete') ?></a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>