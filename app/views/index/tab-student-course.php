<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="课程"></i>
        </div>
        <div class="timeline-content">
            <h2><?= $v->title ?><strong class="noticeOver"><?=Yii::t('frontend',$v->msg_status)?></strong></h2>

            <p><?= $v->content ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i>结束时间: <?= date('Y-m-d H:i:s', $v->end_time) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['course/view', 'id' => $v->object_id, 'from' => 'message']) ?>" class="pull-right">去完成</a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>