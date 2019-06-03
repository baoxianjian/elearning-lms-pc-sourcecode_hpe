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
        <i class="glyphicon glyphicon-flag"></i><?=Yii::t('frontend','question_area')?>
        <a href="<?= Yii::$app->urlManager->createUrl(['question/index', 'from' => 'question-area']) ?>" class="lessonListLink"><?=Yii::t('frontend','more')?></a>
    </div>
    <div class="panel-body">
        <div class="courseResume">
            <div class="lessonList">
                <img src="/static/frontend/images/quickLinkList2.jpg">
                <ul>
                    <? foreach ($data as $index => $item) : ?>
                        <li><a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $item->kid, 'from' => 'question-area']) ?>" class="lessWord" title="<?=Html::encode($item->title)?>"><?=Html::encode($item->title)?></a></li>
                    <? endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<? endif; ?>