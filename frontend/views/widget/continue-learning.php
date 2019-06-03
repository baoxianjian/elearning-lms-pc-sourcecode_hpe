<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:03
 */
use common\helpers\TStringHelper;
use yii\helpers\Html;

?>
<div class="panel panel-default finishLearn topBordered">
    <div class="panel-heading">
        <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend','continue_learning')?>
    </div>
    <div class="panel-body">
        <div class="courseResume">
            <? if (isset($data)&&$data!=null):?>
                <a title="<?=Yii::t('frontend','continue_learning')?>" href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $data->kid, 'from' => 'continue-learning']) ?>" class="playerIcon"></a>
                <a><img class="introPic" src="<?= TStringHelper::Theme($data) ?>"></a>
                <h5 title="<?= Html::encode($data->course_name) ?>"><?= Html::encode(TStringHelper::subStr(trim($data->course_name), 6, 'utf-8', 0, '...')) ?><span class="pull-right"><?=Yii::t('frontend','last_study_time')?><?= $time ?></span></h5>
            <? else: ?>
                <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/index', 'from' => 'continue-learning']) ?>"><img class="introPic" src="/static/frontend/images/continue-learning-blank.png"></a>
                <h5></h5>
            <? endif; ?>
        </div>
    </div>
</div>