<?php
use common\helpers\TStringHelper;
use yii\helpers\Html;

?>
<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>" id="<?=$v->kid?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-question-sign" title="<?= Yii::t('frontend', 'question_answer') ?>"></i>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <? if($v->is_stick): ?>
                <h2 title="<?=Html::encode($v->title) ?>"><a href="javascript:void(0);" onclick="cancelStickTimeline('<?=$v->kid?>','timeline2')" class="glyphicon glyphicon-pushpin " title="<?= Yii::t('frontend', 'untop') ?>" style="display: inline-block;"></a><span class="lessWord timelineTitle"><?= Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></span></h2>
            <? else: ?>
                <h2 title="<?=Html::encode($v->title) ?>"><span class="lessWord timelineTitle"><?= Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></span><a href="javascript:void(0);" onclick="stickTimeline('<?=$v->kid?>','timeline2')" class="glyphicon glyphicon-pushpin pinNode" title="<?= Yii::t('common', 'art_top') ?>"></a></h2>
            <? endif;?>
            <p title="<?=Html::encode($v->content)?>"><?= Html::encode(TStringHelper::utf8_substr($v->content, 0,70,'...')) ?></p>
            <p style="text-align:right"><?= Yii::t('frontend', 'posted_question_text') ?>:<?= Html::encode($v->sender) ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= date('Y-m-d H:i:s', $v->created_at) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v->object_id]) ?>" class="pull-right"><?= Yii::t('common', 'art_datail') ?></a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>