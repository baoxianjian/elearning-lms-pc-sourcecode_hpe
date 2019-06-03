<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <? if($v['type']=='q'): ?>
        <div class="timeline-item eventCate<?= $i ?>">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book" title="用户关注"></i>
            </div>
            <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
                <p>关注问题"<?= $v['title'] ?>"</p>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= date('Y-m-d H:i:s', $v['created_at']) ?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v['kid']]) ?>" class="pull-right">详细</a>
            </div>
        </div>
    <? elseif($v['type']=='u'): ?>
        <div class="timeline-item eventCate<?= $i ?>">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book" title="问题关注"></i>
            </div>
            <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
                <p>关注用户"<?= $v['title'] ?>"</p>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= date('Y-m-d H:i:s', $v['created_at']) ?></span>
            </div>
        </div>
     <? endif; ?>
    <? $i++; ?>
<? endforeach; ?>