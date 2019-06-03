<?php
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use common\models\learning\LnCourse;
use yii\helpers\Html;

?>
<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <? if($v->course_type===LnCourse::COURSE_TYPE_ONLINE): ?>
                <i class="glyphicon glyphicon-book" title="<?= Yii::t('frontend', 'tab_btn_news') ?>"></i>
            <? elseif($v->course_type===LnCourse::COURSE_TYPE_FACETOFACE): ?>
                <i class="glyphicon glyphicon-list-alt" title="<?= Yii::t('frontend', 'tab_btn_news') ?>"></i>
            <? endif; ?>
            </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <h2>[<?= TStringHelper::CourseType($v->course_type) ?>]<?= Html::encode($v->course_name) ?></h2>
            <? if($v->theme_url):?>
                <img src="<?=$v->theme_url?>" class="noticePic">
                <p class="noticePicDes" title="<?= Html::encode(trim($v->course_desc_nohtml)) ?>">
                    <?= Html::encode(TStringHelper::subStr($v->course_desc_nohtml, 34, 'utf-8', 0, '...')) ?>
                </p>
            <? else: ?>
                <p title="<?= Html::encode(trim($v->course_desc_nohtml)) ?>">
                    <?= Html::encode(TStringHelper::subStr($v->course_desc_nohtml, 37, 'utf-8', 0, '...')) ?>
                </p>
            <? endif; ?>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDate($v->release_at)?></span>
            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v->kid, 'from' => 'timeline']) ?>" class="pull-right"><?= Yii::t('common', 'view_button') ?></a>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>