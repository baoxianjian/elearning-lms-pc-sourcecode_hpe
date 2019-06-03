<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/18
 * Time: 17:51
 */
use common\helpers\TTimeHelper;
use common\models\social\SoShare;
use common\models\social\SoRecord;

?>
<? foreach($data as $val): ?>
    <? if ($val['type']===SoShare::SHARE_TYPE_COURSE):?>
        <div class="timeline-item">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book" title="<?=Yii::t('common','course')?>"></i>
            </div>
            <div class="timeline-content">
                <table class="timeLine_pathBlock">
                    <tbody>
                    <tr>
                        <td><strong><?=Yii::t('common','course_name')?>: </strong><?=$val['title']?></td>
                    </tr>
                    <tr>
                        <td><strong><?=Yii::t('frontend','question_content')?>: </strong><?=$val['content']?></td>
                    </tr>
                    </tbody>
                </table>
                <hr>
                <span><i class="glyphicon glyphicon-time"></i><?=TTimeHelper::toDateTime($val['created_at'],'Y年m月d日 H:i') ?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val['obj_id']]) ?>" target="_blank" class="pull-right"><?=Yii::t('common','view_button')?></a>
            </div>
        </div>
    <? elseif($val['type']===SoShare::SHARE_TYPE_QUESTION): ?>
        <div class="timeline-item">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-question-sign" title="<?=Yii::t('frontend','question_answer')?>"></i>
            </div>
            <div class="timeline-content">
                <table class="timeLine_pathBlock">
                    <tbody>
                    <tr>
                        <td><strong><?=Yii::t('frontend','question')?>: </strong><?=$val['title']?></td>
                    </tr>
                    <tr>
                        <td><strong><?=Yii::t('frontend','question_content')?>: </strong><?=$val['content']?></td>
                    </tr>
                    </tbody>
                </table>
                <hr>
                <span><i class="glyphicon glyphicon-time"></i><?=TTimeHelper::toDateTime($val['created_at'],'Y年m月d日 H:i') ?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $val['obj_id']]) ?>" target="_blank" class="pull-right"><?=Yii::t('common','view_button')?></a>
            </div>
        </div>
    <? elseif($val['type']===SoShare::SHARE_TYPE_RECORD): ?>
        <? if($val['record_type'] === SoRecord::RECORD_TYPE_WEB): ?>
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="glyphicon glyphicon-globe" title="<?=Yii::t('frontend','web_page')?>"></i>
                </div>
                <div class="timeline-content">
                    <table class="timeLine_pathBlock">
                        <tbody>
                        <tr>
                            <td colspan="2"><strong><?=Yii::t('frontend','question_title')?>: </strong><?=$val['title']?></td>
                        </tr>
                        <? if($val['url']): ?>
                            <tr>
<!--                                <td colspan="2"><strong>URL: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $val['url'], 'objId' => $val['obj_id'], 'type' => SoRecord::RECORD_TYPE_WEB]) ?><!--" target="_blank">--><?//=$val['url']?><!--</a></td>-->
                                <td colspan="2"><strong>URL: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$val['url']?>','<?=$val['obj_id']?>','<?=SoRecord::RECORD_TYPE_WEB?>')"><?= $val['url'] ?></a></td>
                            </tr>
                        <? endif; ?>
                        <? if($val['duration'] || $val['attach_url']):?>
                            <tr>
                                <? if($val['duration']): ?>
                                    <td <?=$val['attach_url'] ? '':'colspan="2"'?>><strong><?=Yii::t('frontend','duration_time')?>: </strong><?=TTimeHelper::timeConvert($val['duration'])?></td>
                                <? endif; ?>
                                <? if($val['attach_url']): ?>
                                    <td <?=$val['duration'] ? '':'colspan="2"'?>><strong><?=Yii::t('frontend','enclosure')?>:</strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$val['obj_id']?>','record')"><?=$val['attach_original_filename']?></a></td>
                                <? endif; ?>
                            </tr>
                        <? endif; ?>
                        <tr>
                            <td colspan="2"><div class="moreContent"><strong><?=Yii::t('frontend','question_content')?>: </strong><?=$val['content']?></div></td>
                        </tr>
                        </tbody>
                    </table>
                    <hr>
                    <span><i class="glyphicon glyphicon-time"></i><?=TTimeHelper::toDateTime($val['created_at'],'Y年m月d日 H:i') ?></span>
                    <a href="javascript:void(0);" class="moreBtn pull-right"><?=Yii::t('common','menu_collapse')?></a>
                </div>
            </div>
        <? elseif($val['record_type'] === SoRecord::RECORD_TYPE_EVENT): ?>
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="glyphicon glyphicon-calendar" title="<?=Yii::t('frontend','event')?>"></i>
                </div>
                <div class="timeline-content">
                    <table class="timeLine_pathBlock">
                        <tbody>
                        <tr>
                            <td><strong><?=Yii::t('frontend','question_title')?>: </strong><?=$val['title']?></td>
                        </tr>
                        <tr>
                            <td><strong><?=Yii::t('common','start_time')?>: </strong><?=TTimeHelper::toDateTime(strtotime($val['start_at']),'Y年m月d日 H:i')?><? if ($val['duration']): ?>&nbsp;&nbsp;&nbsp;&nbsp;<?=Yii::t('frontend','continued')?><?= TTimeHelper::timeConvert($val['duration']) ?><? endif; ?></td>
                        </tr>
                        <? if ($val['url']): ?>
                            <tr>
<!--                                <td><strong>URL: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $val['url'], 'objId' => $val['obj_id'], 'type' => SoRecord::RECORD_TYPE_EVENT]) ?><!--" target="_blank">--><?//=$val['url']?><!--</a></td>-->
                                <td><strong>URL: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$val['url']?>','<?=$val['obj_id']?>','<?=SoRecord::RECORD_TYPE_EVENT?>')"><?= $val['url'] ?></a></td>
                            </tr>
                        <? endif; ?>
                        <? if($val['attach_url']):?>
                            <tr>
                                <td><strong><?=Yii::t('frontend','enclosure')?>:</strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$val['obj_id']?>','record')"><?=$val['attach_original_filename']?></a></td>
                            </tr>
                        <? endif; ?>
                        <tr>
                            <td><div class="moreContent"><strong><?=Yii::t('frontend','question_content')?>: </strong><?=$val['content']?></div></td>
                        </tr>
                        </tbody>
                    </table>
                    <hr>
                    <span><i class="glyphicon glyphicon-time"></i><?=TTimeHelper::toDateTime($val['created_at'],'Y年m月d日 H:i') ?></span>
                    <a href="javascript:void(0);" class="moreBtn pull-right"><?=Yii::t('common','menu_collapse')?></a>
                </div>
            </div>
        <? elseif($val['record_type'] === SoRecord::RECORD_TYPE_BOOK): ?>
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="glyphicon glyphicon-text-background" title="<?=Yii::t('frontend','book')?>"></i>
                </div>
                <div class="timeline-content">
                    <table class="timeLine_pathBlock">
                        <tbody>
                        <tr>
                            <td colspan="2"><strong><?=Yii::t('frontend','question_title')?>: </strong><?=$val['title']?></td>
                        </tr>
                        <? if($val['url']): ?>
                            <tr>
<!--                                <td colspan="2"><strong>URL: </strong><a href="--><?//= Yii::$app->urlManager->createUrl(['common/jump-url', 'url' => $val['url'], 'objId' => $val['obj_id'], 'type' => SoRecord::RECORD_TYPE_BOOK]) ?><!--" target="_blank">--><?//=$val['url']?><!--</a></td>-->
                                <td colspan="2"><strong>URL: </strong><a href="javascript:void(0);" onclick="openUrl('<?=$val['url']?>','<?=$val['obj_id']?>','<?=SoRecord::RECORD_TYPE_BOOK?>')"><?= $val['url'] ?></a></td>
                            </tr>
                        <? endif; ?>
                        <? if($val['duration'] || $val['attach_url']):?>
                            <tr>
                                <? if($val['duration']): ?>
                                    <td <?=$val['attach_url'] ? '':'colspan="2"'?>><strong><?=Yii::t('frontend','duration_time')?>: </strong><?=TTimeHelper::timeConvert($val['duration'])?></td>
                                <? endif; ?>
                                <? if($val['attach_url']): ?>
                                    <td <?=$val['duration'] ? '':'colspan="2"'?>><strong><?=Yii::t('frontend','enclosure')?>:</strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$val['obj_id']?>','record')"><?=$val['attach_original_filename']?></a></td>
                                <? endif; ?>
                            </tr>
                        <? endif; ?>
                        <tr>
                            <td colspan="2"><div class="moreContent"><strong><?=Yii::t('frontend','question_content')?>: </strong><?=$val['content']?></div></td>
                        </tr>
                        </tbody>
                    </table>
                    <hr>
                    <span><i class="glyphicon glyphicon-time"></i><?=TTimeHelper::toDateTime($val['created_at'],'Y年m月d日 H:i') ?></span>
                    <a href="javascript:void(0);" class="moreBtn pull-right"><?=Yii::t('common','menu_collapse')?></a>
                </div>
            </div>
        <? elseif($val['record_type'] === SoRecord::RECORD_TYPE_EXP): ?>
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="glyphicon glyphicon-education" title="<?=Yii::t('frontend','experience')?>"></i>
                </div>
                <div class="timeline-content">
                    <table class="timeLine_pathBlock">
                        <tbody>
                        <tr>
                            <td><strong><?=Yii::t('frontend','question_title')?>: </strong><?=$val['title']?></td>
                        </tr>
                        <? if ($val['attach_url']): ?>
                            <tr>
                                <td><strong><?=Yii::t('frontend','enclosure')?>:</strong><a href="javascript:void(0)" onclick="openDownloadUrl('<?=$val['obj_id']?>','record')"><?=$val['attach_original_filename']?></a></td>
                            </tr>
                        <? endif; ?>
                        <tr>
                            <td><div class="moreContent"><strong><?=Yii::t('frontend','question_content')?>: </strong><?=$val['content']?></div></td>
                        </tr>
                        </tbody>
                    </table>
                    <hr>
                    <span><i class="glyphicon glyphicon-time"></i><?=TTimeHelper::toDateTime($val['created_at'],'Y年m月d日 H:i') ?></span>
                    <a href="javascript:void(0);" class="moreBtn pull-right"><?=Yii::t('common','menu_collapse')?></a>
                </div>
            </div>
        <? endif;?>
    <? endif;?>
<? endforeach; ?>
<? if (!empty($data) && count($data) > 0): ?>
<script type="text/javascript">
    $('.moreBtn').bind('click', function() {
        var thisBtn = $(this)
        if (thisBtn.text() == '<?=Yii::t('common','menu_collapse')?>') {
            thisBtn.parent().find('.moreContent').css('height', 'auto')
            thisBtn.text('<?=Yii::t('frontend','page_myteam_noshow')?>')
        } else {
            thisBtn.parent().find('.moreContent').css('height', '50px')
            thisBtn.text('<?=Yii::t('common','menu_collapse')?>')
        }
    })
</script>
<? endif;?>