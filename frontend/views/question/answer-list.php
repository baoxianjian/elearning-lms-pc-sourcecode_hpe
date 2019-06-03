<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/22
 * Time: 13:35
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use components\widgets\TLinkPager;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;

$commentFormId = 1;

$uid = Yii::$app->user->getId();
?>

<? foreach ($answer_list as $v): ?>
    <div class="answerBlock<?=$v['qa_id'] ? ' TrueAnswerBlock':'' ?>">
        <? if ($canOperate && !$v['qa_id']): ?>
            <span class="selectAnswer"><a class="btn btn-success btn-xs" href="javascript:void(0);" onclick="setRightAnswer('<?= $v['kid'] ?>')"><?=Yii::t('frontend', 'set_result')?></a></span>
        <? elseif ($v['qa_id']):?>
            <span class="showAnswer"><a class="btn btn-success btn-xs" style="cursor: default"><?=Yii::t('frontend', 'result2')?></a></span>
        <? endif;?>

        <div class="answerUser">
            <a><img src="<?=TStringHelper::Thumb($v['thumb'],$v['gender']) ?>"></a>
            <span><?= Html::encode($v['real_name']) ?></span>
            <ul class="popPanel">
                <?if($uid != $v['user_id']):?>
                    <li><a href="javascript:void(0);" class="btn btn-xs" onclick="attentionUser(this,'<?=$v['user_id']?>')" data-id="<?=$v['user_id']?>"><?=in_array($v['user_id'],$attentionUser)? Yii::t('common', 'cancel_attention'): Yii::t('common', 'attention')?></a></li>
                    <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$v['user_id']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                <?endif;?>
            </ul>
        </div>

        <div class="answerDetail">
            <p><?= Html::encode($v['answer_content']) ?></p>
            <div class="answerInteract">
                <span><?=TTimeHelper::toDateTime($v['created_at']) ?>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="showAnswerComment(this)" class="answerComments"><?=Yii::t('frontend', 'comment')?>(<?= $v['comment_num'] ?>)</a></span>
<!--                <span>时间: <i>--><?//= TTimeHelper::toDateTime($v['created_at'])?><!--</i></span>-->
                <div class="commentInput subcomments hide">
                    <? $comments = $comment_list[$v['kid']]; ?>
                    <? foreach ($comments as $c): ?>
                        <div class="answerBlock ">
                            <div class="answerUser">
                                <a><img src="<?=TStringHelper::Thumb($v['thumb'],$v['gender']) ?>"></a>
                                <span><?= Html::encode($v['real_name']) ?></span>
                                <?if($uid != $v['user_id']):?>
                                    <ul class="popPanel">
                                        <li><a href="javascript:void(0);" class="btn btn-xs" onclick="attentionUser(this,'<?=$v['user_id']?>')" data-id="<?=$v['user_id']?>"><?=in_array($v['user_id'],$attentionUser)? Yii::t('common', 'cancel_attention'): Yii::t('common', 'attention')?></a></li>
                                        <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$v['user_id']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                                    </ul>
                                <?endif;?>
                            </div>

                            <div class="answerDetail">
                                <p><?= Html::encode($c->comment_content) ?></p>
                                <span><?=TTimeHelper::toDateTime($c['created_at']) ?></span>
                            </div>
                        </div>
                    <? endforeach;
                    unset($comments);
                    $commentModel = new \common\models\social\SoAnswerComment(); ?>
                    <?php $form = ActiveForm::begin([
                        'id' => 'commentForm-' . $commentFormId,
                        'method' => 'post',
                        'enableAjaxValidation' => false,
                        'enableClientValidation' => true,
                        'action' => Yii::$app->urlManager->createUrl('question/answer-comment'),
                    ]);?>
                    <?= $form->field($commentModel, 'answer_id')->hiddenInput(['value' => $v['kid']]) ?>
                    <?= $form->field($commentModel, 'comment_content')->textarea(['maxlength' => 5000]) ?>
                    <?=
                    Html::button(Yii::t('frontend', 'comment'),
                        ['id' => 'answerBtn', 'class' => 'btn btn-success pull-right', 'onclick' => 'submitFormAjax("commentForm-' . $commentFormId . '");'])
                    ?>
                    <?php ActiveForm::end(); ?>
                    <? $commentFormId++; ?>
                </div>
            </div>
        </div>
    </div>
<? endforeach; ?>
<div class="answerBlock">
    <div class="commentInput">
        <?php $form = ActiveForm::begin([
            'id' => 'answerForm',
            'method' => 'post',
            'enableAjaxValidation' => false,
            'enableClientValidation' => true,
            'action' => Yii::$app->urlManager->createUrl('question/answer'),
        ]); ?>
        <?= $form->field($answer, 'question_id')->hiddenInput(['value' => $question_id]) ?>
        <?= $form->field($answer, 'answer_content')->textarea(['maxlength' => 5000]) ?>
        <?=
        Html::button(Yii::t('frontend', 'reanswer'),
            ['id' => 'answerBtn', 'class' => 'btn btn-success pull-right', 'onclick' => 'submitFormAjax("answerForm");'])
        ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="pull-right">
    <nav>
        <?php
        echo TLinkPager::widget([
            'id' => 'page',
            'pagination' => $page,
        ]);
        ?>
    </nav>
</div>