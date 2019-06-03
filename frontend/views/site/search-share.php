<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/20
 * Time: 16:28
 */
use common\models\social\SoRecord;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>
<style>
.lessWord
{
    width:90%;
}
</style>
<div class="timeline miniLine" style="float:left;">
    <? foreach($data as $v):?>
        <div class="timeline-item">
            <div class="timeline-icon">
                <? if($v->record_type===SoRecord::RECORD_TYPE_WEB):?>
                    <i class="glyphicon glyphicon-globe" title="<?=Yii::t('frontend','web_page')?>"></i>
                <? elseif($v->record_type===SoRecord::RECORD_TYPE_EVENT):?>
                    <i class="glyphicon glyphicon-calendar" title="<?=Yii::t('frontend','event')?>"></i>
                <? elseif($v->record_type===SoRecord::RECORD_TYPE_BOOK):?>
                    <i class="glyphicon glyphicon-text-background" title="<?=Yii::t('frontend','book')?>"></i>
                <? elseif($v->record_type===SoRecord::RECORD_TYPE_EXP):?>
                    <i class="glyphicon glyphicon-education" title="<?=Yii::t('frontend','experience')?>"></i>
                <? endif; ?>
            </div>
            <div class="timeline-content">
                <h2><span class="lessWord"><?=$v->title;// TStringHelper::HighlightDecode(Html::encode(TStringHelper::subStr($v->title, 100, 'utf-8', 0, '...'))) ?></span><a class="btn pull-right noticeShare" href="javascript:void(0)" onclick="submitShare('<?= $v->kid ?>');"><?=Yii::t('frontend','share')?></a></h2>
                <table class="timeLine_pathBlock">
                    <tbody>
                    <? if($v->record_type===SoRecord::RECORD_TYPE_WEB):?>
                        <? if($v->url):?>
                            <tr>
<!--                                <td colspan="2"><strong>URL: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $v->url, 'objId' => $v->kid, 'type' => SoRecord::RECORD_TYPE_WEB]) ?><!--">--><?//= $v->url ?><!--</a></td>-->
                                <td colspan="2"><strong>URL: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$v->url?>','<?=$v->kid?>','<?=SoRecord::RECORD_TYPE_WEB?>')"><?= $v->url ?></a></td>
                            </tr>
                        <? endif; ?>
                        <? if($v->duration):?>
                            <tr>
                                <td colspan="2"><strong><?=Yii::t('frontend','duration_time')?>: </strong><?=TTimeHelper::timeConvert($v->duration)?></td>
                            </tr>
                        <? endif; ?>
                        <? if($v->attach_url):?>
                            <tr>
                                <td colspan="2"><strong><?=Yii::t('frontend','enclosure')?>: </strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$v->kid?>','record')"><?= $v->attach_original_filename ?></a></td>
                            </tr>
                        <? endif;?>
                    <? elseif($v->record_type===SoRecord::RECORD_TYPE_EVENT):?>
                        <? if($v->url):?>
                            <tr>
<!--                                <td colspan="2"><strong>相关链接: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $v->url, 'objId' => $v->kid, 'type' => SoRecord::RECORD_TYPE_EVENT]) ?><!--">--><?//= $v->url ?><!--</a></td>-->
                                <td colspan="2"><strong><?=Yii::t('frontend','related_link')?>: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$v->url?>','<?=$v->kid?>','<?=SoRecord::RECORD_TYPE_EVENT?>')"><?= $v->url ?></a></td>
                            </tr>
                        <? endif; ?>
                        <? if($v->start_at):?>
                            <tr>
                                <td <?=$v->duration ? '':'colspan="2"'?>><strong><?=Yii::t('common','start_time')?>: </strong><?=TTimeHelper::toDateTime($v->start_at)?></td>
                                <? if($v->duration):?><td><strong><?=Yii::t('frontend','duration_time')?>: </strong><?=TTimeHelper::timeConvert($v->duration)?></td><? endif; ?>
                            </tr>
                        <? endif; ?>
                        <? if($v->attach_url):?>
                            <tr>
                                <td colspan="2"><strong><?=Yii::t('frontend','enclosure')?>: </strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$v->kid?>','record')"><?= $v->attach_original_filename ?></a></td>
                            </tr>
                        <? endif;?>
                    <? elseif($v->record_type===SoRecord::RECORD_TYPE_BOOK):?>
                        <? if($v->url):?>
                            <tr>
<!--                                <td colspan="2"><strong><?=Yii::t('frontend','related_link')?>: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $v->url, 'objId' => $v->kid, 'type' => SoRecord::RECORD_TYPE_BOOK]) ?><!--">--><?//= $v->url ?><!--</a></td>-->
                                <td colspan="2"><strong><?=Yii::t('frontend','related_link')?>: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$v->url?>','<?=$v->kid?>','<?=SoRecord::RECORD_TYPE_BOOK?>')"><?= $v->url ?></a></td>
                            </tr>
                        <? endif; ?>
                        <? if($v->duration):?>
                            <tr>
                                <td colspan="2"><strong><?=Yii::t('frontend','duration_time')?>: </strong><?=TTimeHelper::timeConvert($v->duration)?></td>
                            </tr>
                        <? endif; ?>
                        <? if($v->attach_url):?>
                            <tr>
                                <td colspan="2"><strong><?=Yii::t('frontend','enclosure')?>: </strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$v->kid?>','record')"><?= $v->attach_original_filename ?></a></td>
                            </tr>
                        <? endif;?>
                    <? elseif($v->record_type===SoRecord::RECORD_TYPE_EXP):?>
                        <? if($v->attach_url):?>
                            <tr>
                                <td colspan="2"><strong><?=Yii::t('frontend','enclosure')?>: </strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$v->kid?>','record')"><?= $v->attach_original_filename ?></a></td>
                            </tr>
                        <? endif;?>
                    <? endif; ?>
                    <tr>
                        <td colspan="2">
                            <div class="moreContent" style="height: 20px;"><strong><?=Yii::t('frontend','question_content')?>: </strong><?=TStringHelper::HighlightDecode(Html::encode($v->content)) ?></div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i><?= TTimeHelper::toDateTime($v->created_at) ?></span>
                <a href="javascript:void(0)" class="moreBtn pull-right" onclick="moreContent(this)"><?=Yii::t('common','menu_collapse')?></a>
            </div>
        </div>
    <? endforeach;?>
</div>
<nav>
    <?php
    echo TLinkPager::widget([
        'id' => 'page4',
        'pagination' => $pages,
        'options'=>['class'=>'pagination pagination-sm pull-right']
    ]);
    ?>
</nav>