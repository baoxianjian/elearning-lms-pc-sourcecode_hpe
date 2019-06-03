<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="记录"></i>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2><?= $v->title ?></h2>

            <p>
                <?= $v->content ?>
            </p>
            <hr/>
            相关链接:<a href="<?= $v->url ?>"><?= $v->url ?></a>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= $v->start_at ?>~<?= $v->end_at ?></span>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>