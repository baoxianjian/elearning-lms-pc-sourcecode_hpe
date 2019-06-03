<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="课程"></i>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2><?= $v->title ?><strong class="noticeOver"><?=$v->end_time<time()?'已过期':(($v->end_time-time())< 259200?'快过期':'');//            Yii::t('frontend',$v->msg_status)?></strong></h2>
            <p><?= $v->content ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i>结束时间: <?= date('Y-m-d H:i:s', $v->end_time) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v->object_id, 'from' => 'message']) ?>" class="pull-right">去完成</a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>



<!--<div class="timeline-item eventCate1">-->
<!--    <div class="timeline-icon">-->
<!--        <i class="glyphicon glyphicon-book" title="课程"></i>-->
<!--    </div>-->
<!--    <div class="timeline-content">-->
<!--        <h2>项目管理PMP</h2>-->
<!--        <p>PMP是项目管理界含金量最高的证书，在全球185个国家和地区得到了高度认可.</p>-->
<!--        <hr/>-->
<!--        <span><i class="glyphicon glyphicon-time"></i>结束时间: 2015-5-1</span>-->
<!--        <a href="new_courseDetail1.html" class="pull-right">去完成</a>-->
<!--    </div>-->
<!--</div>-->