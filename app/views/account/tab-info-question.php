<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="问答"></i>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2><?= $v['title'] ?></h2>
            <p><?= $v['question_content'] ?></p>
            <p style="text-align:right">提问人:<?= $v['real_name'] ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= date('Y-m-d H:i:s', $v['created_at']) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v['kid']]) ?>" class="pull-right">详细</a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>