<?php
use common\models\message\MsTimeline;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use common\models\learning\LnCourse;
use yii\helpers\Html;

?>
<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>" id="<?=$v->kid?>">
        <div class="timeline-icon">
            <? if ($v->object_type === MsTimeline::OBJECT_TYPE_EXAM): ?>
                <i class="glyphicon glyphicon-headphones" title="<?= Yii::t('frontend', 'exam') ?>"></i>
            <? elseif ($v->object_type === MsTimeline::OBJECT_TYPE_SURVEY): ?>
                <i class="glyphicon glyphicon-check" title="<?= Yii::t('common', 'investigation') ?>"></i>
            <? elseif ($v->course_type === LnCourse::COURSE_TYPE_ONLINE): ?>
                <i class="glyphicon glyphicon-book" title="<?= Yii::t('common', 'online') ?><?= Yii::t('common', 'course') ?>"></i>
            <? elseif ($v->course_type === LnCourse::COURSE_TYPE_FACETOFACE): ?>
                <i class="glyphicon glyphicon-list-alt" title="<?= Yii::t('common', 'face_to_face') ?><?= Yii::t('common', 'course') ?>"></i>
            <? endif; ?>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <? if($v->is_stick): ?>
                <h2 title="<?=Html::encode($v->title)?>"><a href="javascript:void(0);" onclick="cancelStickTimeline('<?=$v->kid?>','timeline1')" class="glyphicon glyphicon-pushpin " title="<?= Yii::t('frontend', 'untop') ?>" style="display: inline-block;"></a><span class="lessWord timelineTitle"><?= TStringHelper::GetObjecTypeText($v->object_type) . Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></span><strong class="noticeOver"><?= TTimeHelper::getCourseExpiredTag($v->end_at)?></strong><a href="javascript:void(0);" onclick="deleteTimeline('<?=$v->kid?>')"; class="glyphicon glyphicon-trash removeNode" title="<?= Yii::t('common', 'delete_button') ?>"></a></h2>
            <? else: ?>
                <h2 title="<?=Html::encode($v->title)?>"><span class="lessWord timelineTitle"><?= TStringHelper::GetObjecTypeText($v->object_type) . Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></span><strong class="noticeOver"><?= TTimeHelper::getCourseExpiredTag($v->end_at)?></strong><a href="javascript:void(0);" onclick="deleteTimeline('<?=$v->kid?>')"; class="glyphicon glyphicon-trash removeNode" title="<?= Yii::t('common', 'delete_button') ?>"></a><a href="javascript:void(0);" onclick="stickTimeline('<?=$v->kid?>','timeline1')" class="glyphicon glyphicon-pushpin pinNode" title="<?= Yii::t('common', 'art_top') ?>"></a></h2>
            <? endif;?>
            <? if($v->image_url):?>
                <img src="<?=$v->image_url?>" class="noticePic">
                <p class="noticePicDes" title="<?= trim($v->content) ? Html::encode(trim($v->content)) : Yii::t('frontend', 'no_introduced') ?>">
                    <?= trim($v->content) ? Html::encode(TStringHelper::subStr($v->content, 34, 'utf-8', 0, '...')) : Yii::t('frontend', 'no_introduced') ?>
                </p>
            <? else: ?>
                <p title="<?= trim($v->content) ? Html::encode(trim($v->content)) : Yii::t('frontend', 'no_introduced') ?>">
                    <?= trim($v->content) ? Html::encode(TStringHelper::subStr($v->content, 37, 'utf-8', 0, '...')) : Yii::t('frontend', 'no_introduced') ?>
                </p>
            <? endif; ?>
            <hr/>
            <span class="lessWord" style="width:70%"><i class="glyphicon glyphicon-time"></i><?=Yii::t('frontend','end_time')?>: <?= TTimeHelper::getCourseEndtime($v->end_at) ?></span>
            <? if($v->course_end_at && $v->course_end_at < time()):?>
                <a class="btn pull-right" disabled="disabled"><?= Yii::t('frontend', 'under_shelf') ?></a>
            <? else: ?>
                <? if ($v->object_type === MsTimeline::OBJECT_TYPE_EXAM): ?>
                    <a href="<?= Yii::$app->urlManager->createUrl(['exam/view', 'id' => $v->object_id, 'from' => 'timeline']) ?>" class="pull-right"><?=$v->getButtonText()?></a>
                <? elseif ($v->object_type === MsTimeline::OBJECT_TYPE_SURVEY): ?>
                    <a href="<?= Yii::$app->urlManager->createUrl(['investigation/play', 'id' => $v->object_id, 'from' => 'timeline']) ?>" class="pull-right"><?=$v->getButtonText()?></a>
                <? elseif ($v->object_type === MsTimeline::OBJECT_TYPE_COURSE): ?>
                    <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v->object_id, 'from' => 'timeline']) ?>" class="pull-right"><?=$v->getButtonText()?></a>
                <? endif; ?>
            <? endif; ?>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>