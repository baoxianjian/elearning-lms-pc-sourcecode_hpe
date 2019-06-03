<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="分享"></i>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2><?= $v->title ?></h2>
            <p><?= $v->content ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= date('Y-m-d H:i', $v->created_at) ?></span>
            <span class="pull-right" style="margin: 0;">来自：（<?=$v->fwUser->nick_name?>）</span>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>