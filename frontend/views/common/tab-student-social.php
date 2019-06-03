<?php
use common\models\social\SoRecord;
use common\helpers\TStringHelper;
use common\models\message\MsTimeline;
use common\helpers\TTimeHelper;
use yii\helpers\Html;

?>
<? $i = 1; ?>
<? foreach ($data as $v): ?>
    <div class="timeline-item eventCate<?= $i ?>">
        <div class="timeline-icon">
            <? if($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_WEB):?>
                <i class="glyphicon glyphicon-globe" title="<?= Yii::t('frontend', 'web_page') ?>"></i>
            <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_EVENT):?>
                <i class="glyphicon glyphicon-calendar" title="<?= Yii::t('frontend', 'event') ?>"></i>
            <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_BOOK):?>
                <i class="glyphicon glyphicon-text-background" title="<?= Yii::t('frontend', 'book') ?>"></i>
            <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_EXP):?>
                <i class="glyphicon glyphicon-education" title="<?= Yii::t('frontend', 'experience') ?>"></i>
            <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_COURSE):?>
                <i class="glyphicon glyphicon-book" title="<?= Yii::t('common', 'course') ?>"></i>
            <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_QUESTION):?>
                <i class="glyphicon glyphicon-question-sign" title="<?= Yii::t('frontend', 'question_answer') ?>"></i>
            <? endif; ?>
        </div>
        <div class="timeline-content <?= $i % 2 == 0 ? 'right' : ''; ?>">
            <? if($v->object_type === MsTimeline::OBJECT_TYPE_COURSE): ?>
                <h2 title="<?= Html::encode($v->title) ?>"><span class="lessWord timelineTitle"><?= Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></span><a class="btn pull-right noticeShare" href="javascript:void(0)" onclick="showCourseShare('<?=$v['object_id']?>','<?=Html::encode($v->title)?>')"><?= Yii::t('frontend', 'share') ?></a></h2>
            <? elseif($v->object_type === MsTimeline::OBJECT_TYPE_QUESTION): ?>
                <h2 title="<?= Html::encode($v->title) ?>"><span class="lessWord timelineTitle"><?= Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></span><a class="btn pull-right noticeShare" href="javascript:void(0)" onclick="submitShare('<?= $v->kid ?>');"><?= Yii::t('frontend', 'share') ?></a></h2>
            <? else: ?>
                <h2 title="<?= Html::encode($v->title) ?>"><span class="lessWord timelineTitle"><?= Html::encode(TStringHelper::subStr($v->title, 16, 'utf-8', 0, '...')) ?></span><a class="btn pull-right noticeShare" href="javascript:void(0)" onclick="submitShare('<?= $v->kid ?>');"><?= Yii::t('frontend', 'share') ?></a></h2>
            <? endif; ?>
            <table class="timeLine_pathBlock">
                <tbody>
                <? if($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_WEB):?>
                    <? if($v->url):?>
                        <tr>
<!--                            <td colspan="2"><strong>URL: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $v->url, 'objId' => $v->object_id, 'type' => SoRecord::RECORD_TYPE_WEB]) ?><!--">--><?//= $v->url ?><!--</a></td>-->
                            <td colspan="2"><strong>URL: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$v->url?>','<?=$v->object_id?>','<?=SoRecord::RECORD_TYPE_WEB?>')"><?= $v->url ?></a></td>
                        </tr>
                    <? endif; ?>
                    <? if($v->duration || $v->attach_url):?>
                        <tr>
                            <? if($v->duration): ?>
                                <td <?=$v->attach_url ? '':'colspan="2"'?>><strong><?= Yii::t('frontend', 'duration_time') ?>: </strong><?=TTimeHelper::timeConvert($v->duration)?></td>
                            <? endif; ?>
                            <? if($v->attach_url): ?>
                                <td <?=$v->duration ? '':'colspan="2"'?>><strong><?= Yii::t('frontend', 'enclosure') ?>:</strong><a class="textout<?=$v->duration ? '-small':'-wide'?>" href="javascript:void(0)" onclick="openDownloadUrl('<?=$v['object_id']?>','record')" title="<?=Html::encode($v['attach_original_filename'])?>"><?=Html::encode($v['attach_original_filename'])?></a></td>
                            <? endif; ?>
                        </tr>
                    <? endif; ?>
                <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_EVENT):?>
                    <? if($v->url):?>
                        <tr>
<!--                            <td colspan="2"><strong>相关链接: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $v->url, 'objId' => $v->object_id, 'type' => SoRecord::RECORD_TYPE_EVENT]) ?><!--">--><?//= $v->url ?><!--</a></td>-->
                            <td colspan="2"><strong><?= Yii::t('frontend', 'related_link') ?>: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$v->url?>','<?=$v->object_id?>','<?=SoRecord::RECORD_TYPE_EVENT?>')"><?= $v->url ?></a></td>
                        </tr>
                    <? endif; ?>
                    <? if($v->start_at):?>
                        <tr>
                            <td <?=$v->duration ? '':'colspan="2"'?>><strong><?= Yii::t('common', 'start_time') ?>: </strong><?=TTimeHelper::toDate($v->start_at)?></td>
                            <? if($v->duration):?><td><strong><?= Yii::t('frontend', 'duration_time') ?>: </strong><?=TTimeHelper::timeConvert($v->duration)?></td><? endif; ?>
                        </tr>
                    <? endif; ?>
                    <? if($v->attach_url):?>
                        <tr>
                            <? if($v->attach_url): ?>
                                <td colspan="2"><strong><?= Yii::t('frontend', 'enclosure') ?>:</strong><a class="textout-wide" href="javascript:void(0)" onclick="openDownloadUrl('<?=$v['object_id']?>','record')" title="<?=Html::encode($v['attach_original_filename'])?>"><?=Html::encode($v['attach_original_filename'])?></a></td>
                            <? endif; ?>
                        </tr>
                    <? endif; ?>
                <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_BOOK):?>
                    <? if($v->url):?>
                        <tr>
<!--                            <td colspan="2"><strong>相关链接: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $v->url, 'objId' => $v->object_id, 'type' => SoRecord::RECORD_TYPE_BOOK]) ?><!--">--><?//= $v->url ?><!--</a></td>-->
                            <td colspan="2"><strong><?= Yii::t('frontend', 'related_link') ?>: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$v->url?>','<?=$v->object_id?>','<?=SoRecord::RECORD_TYPE_BOOK?>')"><?= $v->url ?></a></td>
                        </tr>
                    <? endif; ?>
                    <? if($v->duration || $v->attach_url):?>
                        <tr>
                            <? if($v->duration): ?>
                                <td <?=$v->attach_url ? '':'colspan="2"'?>><strong><?= Yii::t('frontend', 'duration_time') ?>: </strong><?=TTimeHelper::timeConvert($v->duration)?></td>
                            <? endif; ?>
                            <? if($v->attach_url): ?>
                                <td <?=$v->duration ? '':'colspan="2"'?>><strong><?= Yii::t('frontend', 'enclosure') ?>:</strong><a class="textout<?=$v->duration ? '-small':'-wide'?>" href="javascript:void(0)" onclick="openDownloadUrl('<?=$v['object_id']?>','record')" title="<?=Html::encode($v['attach_original_filename'])?>"><?=Html::encode($v['attach_original_filename'])?></a></td>
                            <? endif; ?>
                        </tr>
                    <? endif; ?>
                <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_RECORD_EXP):?>
                    <? if($v->attach_url):?>
                        <tr>
                            <td colspan="2"><strong><?= Yii::t('frontend', 'enclosure') ?>: </strong><a class="textout-wide" href="javascript:void(0)" onclick="openDownloadUrl('<?=$v->object_id?>','record')" title="<?=Html::encode($v['attach_original_filename'])?>"><?=Html::encode($v->attach_original_filename) ?></a></td>
                        </tr>
                    <? endif;?>
                <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_COURSE):?>
                    <tr>
                        <td><strong><?= Yii::t('common', 'investigation_type') ?>: </strong><?= $v->lnCourse->course_type=='0'? Yii::t('common', 'online').Yii::t('common', 'course'): Yii::t('common', 'face_to_face').Yii::t('common', 'course') ?></td>
                    </tr>
                <? elseif($v->object_type===MsTimeline::OBJECT_TYPE_QUESTION):?>
                    <tr>
                        <td><strong><?= Yii::t('frontend', 'posted_question_text') ?>: </strong><?= Html::encode($v->soQuestion->fwUser->real_name) ?></td>
                    </tr>
                <? endif; ?>
                <tr>
                    <td colspan="2">
                        <div class="moreContent"><strong><?= Yii::t('frontend', 'question_content') ?>: </strong><?=Html::encode($v->content) ?></div>
                    </td>
                </tr>
                </tbody>
            </table>
            <hr/>
            <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDate($v->created_at) ?></span>
            <? if($v->object_type === MsTimeline::OBJECT_TYPE_COURSE): ?>
                <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v->object_id])?>" class="moreBtn pull-right" target="_blank"><?= Yii::t('common', 'view_button') ?></a>
            <? elseif($v->object_type === MsTimeline::OBJECT_TYPE_QUESTION): ?>
                <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $v->object_id])?>" class="moreBtn pull-right" target="_blank"><?= Yii::t('common', 'view_button') ?></a>
            <? else: ?>
                <a href="javascript:void(0)" class="moreBtn pull-right" onclick="moreContent(this)"><?= Yii::t('common', 'menu_collapse') ?></a>
            <? endif; ?>
        </div>
    </div>
    <? $i++; ?>
<? endforeach; ?>