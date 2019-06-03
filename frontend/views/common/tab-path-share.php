<?php
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use common\models\social\SoShare;
use yii\helpers\Html;

$i = 1;
?><? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <i class="glyphicon glyphicon-book" title="<?= Yii::t('frontend', 'share') ?>"></i>
        </div>
        <div class="timeline-content">
            <? if($v->type === SoShare::SHARE_TYPE_COURSE) :?>
                <h2 title="<?=Html::encode($v->title)?>"><a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v->obj_id])?>" target="_blank"><?= Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></a></h2>
            <? else:?>
                <h2 title="<?=Html::encode($v->title)?>"><?= Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></h2>
            <?endif;?>
            <p><?= Html::encode($v->content) ?></p>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDateTime($v->created_at) ?></span>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>