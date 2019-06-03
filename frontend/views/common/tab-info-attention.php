<? use yii\helpers\Html;

$i = 1; ?>
<? foreach ($data as $v): ?>
    <? if($v['type']=='q'): ?>
        <div class="timeline-item eventCate<?= $i ?>">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book" title="<?= Yii::t('common', 'user') ?><?= Yii::t('common', 'attention') ?>"></i>
            </div>
            <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
                <p><?= Yii::t('common', 'attention') ?><?= Yii::t('frontend', 'question') ?>"<a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v['kid']]) ?>"><?= Html::encode($v['title']) ?></a>"</p>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= date('Y-m-d H:i:s', $v['created_at']) ?></span>
                <a href="javascript:void(0);" onclick="cancelCare('<?=$v['kid']?>','q')" class="pull-right"><?= Yii::t('common', 'cancel_attention') ?></a>
            </div>
        </div>
    <? elseif($v['type']=='u'): ?>
        <div class="timeline-item eventCate<?= $i ?>">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book" title="<?= Yii::t('frontend', 'question') ?><?= Yii::t('common', 'attention') ?>"></i>
            </div>
            <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
                <p><?= Yii::t('frontend', 'care_user') ?>"<?= Html::encode($v['title']) ?>"</p>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= date('Y-m-d H:i:s', $v['created_at']) ?></span>
                <a href="javascript:void(0);" onclick="cancelCare('<?=$v['kid']?>','u')" class="pull-right"><?= Yii::t('common', 'cancel_attention') ?></a>
            </div>
        </div>
     <? endif; ?>
    <? $i++; ?>
<? endforeach; ?>