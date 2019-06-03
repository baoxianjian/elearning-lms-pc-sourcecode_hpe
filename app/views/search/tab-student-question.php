<div class="panel-body">
<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="search_result">
        <h3> <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v->object_id]) ?>"><?= $v->title ?></a> </h3>
        <span>发布时间: <?= date('Y-m-d H:i:s', $v->created_at) ?></span>
        <p><?= $v->content ?> </p>
    </div>
    <hr />
    <? $i++; ?>
<? endforeach; ?>
</div>

