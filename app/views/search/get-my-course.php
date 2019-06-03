<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="课程"></i>
        </div>
        <div class="timeline-content
        <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2><?= $v['course_name'] ?><strong class="noticeOver"><?=$v['end_time']<time()?'已过期':(($v['end_time']-time())< 259200?'快过期':'');//            Yii::t('frontend',$v->msg_status)?></strong></h2>
            <p><?= $v['course_desc'] ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i>结束时间: <?=\common\helpers\TTimeHelper::toDateTime($v['end_time']) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['course_id'], 'from' => 'myinfo']) ?>" class="pull-right">去完成</a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>

