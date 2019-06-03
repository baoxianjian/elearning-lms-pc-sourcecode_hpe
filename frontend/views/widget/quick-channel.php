<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:31
 */
?>
<div class="panel panel-default finishLearn topBordered">
    <div class="panel-heading">
        <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend','quick_channel')?>
    </div>
    <div class="panel-body">
        <div class="courseResume">
            <a title="<?=Yii::t('common','{value}_list',['value'=>Yii::t('common','course')])?>" href="<?= Yii::$app->urlManager->createUrl('resource/course/index') ?>"><img class="introPic" src="/static/frontend/images/quicklink1.jpg" style="width:100%"></a>
            <a title="<?=Yii::t('frontend','question_area')?>" href="<?= Yii::$app->urlManager->createUrl('question/index') ?>"><img class="introPic" src="/static/frontend/images/quicklink2.jpg" style="width:100%"></a>
        </div>
    </div>
</div>