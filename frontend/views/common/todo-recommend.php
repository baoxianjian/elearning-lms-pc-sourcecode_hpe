<?php
/**
 * Created by PhpStorm.
 * User: Alex Liu
 * Date: 2016/4/8
 * Time: 18:31
 */
use common\models\learning\LnCourse;
use common\helpers\TStringHelper;
use yii\helpers\Html;

?>
<style>
    .img-a a:hover, a:focus{
        text-decoration: none !important;
    }
</style>
<div class="centerBtnArea noData" style="padding: 0; position: relative; top:-40px; z-index: 99">
    <p><?= Yii::t('frontend', 'you_can_come_in_{value}_to_learn', ['value' => '<a href="' . Yii::$app->urlManager->createUrl(['resource/course/index']) . '">' . Yii::t('common', '{value}_list', ['value' => Yii::t('common', 'course')]) . '</a>']) ?></p>
</div>
<div class="panel-body" style="position: relative; top:-30px;">
    <div class="row">
        <? foreach ($data as $index => $item) : ?>
        <div class="col-md-4 col-sm-4">
            <div class="cover text-center img-a">
                <a title="<?= Yii::t('common', 'list_code') ?>:[<?=$item->course_code?>]" href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $item->kid]) ?>">
                    <img src="<?= TStringHelper::Theme($item) ?>" />
                </a>
            </div>
            <h5><a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $item->kid]) ?>"><?= Html::encode($item->course_name) ?></a></h5>
            <p class="p" title="<?=str_replace(array(" ","\r\t","\r\n","\r","\n","\t"),'',strip_tags($item->course_desc_nohtml))?>"><?= TStringHelper::subStr(str_replace(array(" ","\r\t","\r\n","\r","\n","\t"),'',strip_tags($item->course_desc_nohtml)),26,'utf-8',0,'...')?></p>
            <? if($item->course_type == LnCourse::COURSE_TYPE_FACETOFACE): ?>
                <span class="tag_offline"><?= Yii::t('common', 'face_to_face') ?></span>
            <? endif; ?>
        </div>
        <? if (($index + 1) % 3 == 0): ?>
    </div>
    <div class="row">
        <? endif; ?>
        <? endforeach; ?>
</div>