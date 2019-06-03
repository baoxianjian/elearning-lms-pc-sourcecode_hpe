<?php
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use common\models\learning\LnCourse;
use yii\helpers\Html;

?>
<? if($data): ?>
    <div class="timeline-item" id="<?=$data->kid?>">
        <div class="timeline-icon">
            <? if($data->course_type===LnCourse::COURSE_TYPE_ONLINE): ?>
                <i class="glyphicon glyphicon-book" title="<?= Yii::t('common', 'online') ?><?= Yii::t('common', 'course') ?>"></i>
            <? elseif($data->course_type===LnCourse::COURSE_TYPE_FACETOFACE): ?>
                <i class="glyphicon glyphicon-list-alt" title="<?= Yii::t('common', 'face_to_face') ?><?= Yii::t('common', 'course') ?>"></i>
            <? endif; ?>
        </div>
        <div class="timeline-content right">
            <h2 title="<?=Html::encode($data->title)?>"><span class="lessWord timelineTitle"><?= Html::encode(TStringHelper::subStr($data->title, 16, 'utf-8', 0, '...')) ?></span><strong class="noticeOver"><?= TTimeHelper::getCourseExpiredTag($data->end_at)?></strong><a href="javascript:void(0);" onclick="deleteTimeline('<?=$data->kid?>')"; class="glyphicon glyphicon-trash removeNode" title="<?= Yii::t('frontend', 'delete_button') ?>"></a></h2>
            <? if($data->image_url):?>
                <img src="<?=$data->image_url?>" class="noticePic">
                <p class="noticePicDes" title="<?= Html::encode(trim($data->content)) ?>">
                    <?= Html::encode(TStringHelper::subStr($data->content, 37, 'utf-8', 0, '...')) ?>
                </p>
            <? else: ?>
                <p title="<?= Html::encode(trim($data->content)) ?>">
                    <?= Html::encode(TStringHelper::subStr($data->content, 37, 'utf-8', 0, '...')) ?>
                </p>
            <? endif; ?>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?=Yii::t('frontend','end_time')?>: <?= TTimeHelper::getCourseEndtime($data->end_at) ?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $data->object_id, 'from' => 'message']) ?>" class="pull-right"><?=$data->getButtonText()?></a>
        </div>
    </div>
<? endif; ?>