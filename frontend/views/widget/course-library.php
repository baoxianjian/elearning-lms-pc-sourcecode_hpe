<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2016/3/23
 * Time: 23:41
 */
use yii\helpers\Html;

?>
<? if ($data !== null && count($data) > 0): ?>
<div class="panel panel-default finishLearn topBordered">
    <div class="panel-heading">
        <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('common','{value}_list',['value'=>Yii::t('common','course')])?>
        <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/index', 'from' => 'recommend-course']) ?>" class="lessonListLink"><?=Yii::t('frontend','more')?></a>
    </div>
    <div class="panel-body">
        <div class="courseResume">
            <div class="lessonList">
                <img src="/static/frontend/images/quickLinkList1.jpg">
                <ul>
                    <? foreach ($data as $index => $item) : ?>
                        <li><a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $item->kid, 'from' => 'recommend-course']) ?>" class="lessWord" title="<?=Html::encode($item->course_name)?>"><?=Html::encode($item->course_name)?></a></li>
                    <? endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<? endif; ?>