<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/26
 * Time: 22:51
 */
?>
<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
    <?= Yii::t('frontend', 'top_message_text') ?><span class="caret"></span>
</a>
<ul class="dropdown-menu dropdown-menu-right" role="menu">
    <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-course') ?>')"  class="pull-left"><?= Yii::t('frontend', 'task_to_do') ?></a><span class="badge pull-right"><?=$courseMessageCount ?></span></li>
    <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-question') ?>')" class="pull-left"><?= Yii::t('frontend', 'tab_btn_qa') ?></a><span class="badge pull-right"><?=$qaMessageCount ?></span></li>
    <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-news') ?>')" class="pull-left"><?= Yii::t('frontend', 'tab_btn_news') ?></a><span class="badge pull-right"><?=$newsMessageCount ?></span></li>
    <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-social') ?>')" class="pull-left"><?= Yii::t('frontend', 'tab_btn_social') ?></a><span class="badge pull-right"><?=$socialMessageCount ?></span></li>
<!--    <li><a href="javascript:void(0);" onclick="" class="pull-left">系统消息</a><span class="badge pull-right">0</span></li>-->
    <li><a href="javascript:void(0);" class="system" onclick="app.alert('#systemSetting');"><?= Yii::t('frontend', 'system_config') ?></a></li>
</ul>
<? if ($count > 0): ?><span class="newMsg"></span><? endif; ?>