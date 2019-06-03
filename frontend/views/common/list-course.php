<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/8
 * Time: 11:39
 */

use common\helpers\TStringHelper;
use common\models\learning\LnCourse;
use yii\helpers\Html;

?>
<style>
    .img-a a:hover, a:focus{
        text-decoration: none !important;
    }
</style>
<? for ($i = 0; $i < count($data); $i++): ?>
    <div class="col-md-4 col-sm-4">
        <div class="cover text-center img-a">
            <a title="<?= Yii::t('common', 'list_code') ?>:[<?=$data[$i]->course_code?>]" href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $data[$i]->kid]) ?>">
                <img src="<?= TStringHelper::Theme($data[$i]) ?>" />
            </a>
        </div>
        <h5><a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $data[$i]->kid]) ?>"><?= Html::encode($data[$i]->course_name) ?></a></h5>
        <p class="p" title="<?=str_replace(array(" ","\r\t","\r\n","\r","\n","\t"),'',strip_tags($data[$i]->course_desc_nohtml))?>"><?= TStringHelper::subStr(str_replace(array(" ","\r\t","\r\n","\r","\n","\t"),'',strip_tags($data[$i]->course_desc_nohtml)),26,'utf-8',0,'...')?></p>
        <? if($data[$i]->course_type == LnCourse::COURSE_TYPE_FACETOFACE): ?>
            <? if($data[$i]->open_status === LnCourse::COURSE_END): ?>
            <span class="tag_finished"><?= Yii::t('common', 'status_2') ?></span>
            <? else:?>
            <span class="tag_offline"><?= Yii::t('common', 'face_to_face') ?></span>
            <? endif; ?>
        <? endif; ?>
    </div>
    <? if (($i + 1) % 3 == 0): ?>
</div>
<div class="row">
    <? endif; ?>
<? endfor; ?>