<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2016/3/23
 * Time: 21:29
 */
use common\helpers\TStringHelper;
use yii\helpers\Html;

?>
<? if ($data !== null && count($data) > 0): ?>
<div class="panel panel-default finishLearn topBordered">
    <div class="panel-heading">
        <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend','suggested_course')?>
    </div>
    <div class="panel-body">
        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
            <!-- Wrapper for slides -->
            <div class="carousel-inner suggestLesson" role="listbox">
                <? foreach ($data as $index => $item) : ?>
                    <div class="item <?= $index === 0 ? 'active' : '' ?>">
                        <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $item->kid, 'from' => 'recommend-course']) ?>"><img src="<?= TStringHelper::Theme($item) ?>" alt="<?=Html::encode($item->course_name)?>" /></a>
                        <div class="carousel-caption">
                            <a class="lessWord" style="width: 100%;text-indent: 10px" href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $item->kid, 'from' => 'recommend-course']) ?>"><?=Html::encode($item->course_name)?></a>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
            <!-- Controls -->
            <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only"><?=Yii::t('frontend','last_one')?></span>
            </a>
            <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only"><?=Yii::t('frontend','next_one')?></span>
            </a>
        </div>
    </div>
</div>
<? endif; ?>