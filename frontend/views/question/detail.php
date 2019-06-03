<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/22
 * Time: 9:51
 */
use common\helpers\TTimeHelper;
use frontend\assets\AppAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TBreadcrumbs;
use common\helpers\TStringHelper;

/* @var $this yii\web\View */
$this->pageTitle = Yii::t('frontend', 'question_answer_detail');
$this->params['breadcrumbs'][] =  ['url' => Yii::$app->urlManager->createUrl('question/index'), 'label' => Yii::t('frontend', 'question_answer_home')];
$this->params['breadcrumbs'][] = $this->pageTitle;
$this->params['breadcrumbs'][] = TStringHelper::subStr($question->title,20,'utf-8',0,'...');

$uid = Yii::$app->user->getId();

?>
<style>
    .control-label {
        display: none;
    }
    .help-block {
        display: none;
    }
    .courseInfoInput input[type=text], .courseInfoInput select {
        width: 100% !important;
        padding: 0 12px;
    }
    .courseInfoInput input[type=radio] {
        width: auto !important;
        height: auto;
        padding: 0 12px;
    }
</style>

<?=$this->render('/common/point-trans')?>


<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-8">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?= Yii::t('frontend', 'detail')?>
<!--                    --><?// if($question->created_by !== $uid): ?>
                    <a href="javascript:void(0);" data-id="<?=$question->kid?>" onclick="return subCare(this);" class="btnTitle pull-right" title="<?= Yii::t('common', 'attention')?>"><?=$isCare ? Yii::t('common', 'cancel_attention') : Yii::t('common', 'attention')?></a>
<!--                    --><?// endif; ?>
                    <a href="javascript:void(0);" onclick="app.alert('#newShare');" class="btnTitle pull-right"><?=Yii::t('frontend', 'share')?></a>
<!--                    --><?// if($question->created_by !== $uid): ?>
                    <a href="javascript:void(0);" onclick="return subCollect(this);" class="btnTitle pull-right"><?=$isCollect ? Yii::t('common', 'canel_collection') :  Yii::t('common', 'collection')?></a>
<!--                    --><?// endif; ?>
                </div>
                <div class="panel-body">
                    <h2>
                        <?= Html::encode($question->title) ?>
                    </h2>
                    <span><?= Yii::t('frontend', 'from_text')?>：<?= $question->obj_id ? Html::encode($question->lnCourse->course_name) : Html::encode($question->fwUser->real_name) ?>&nbsp;&nbsp;<i><?= Yii::t('frontend', 'browse_num')?>：<?= $question->browse_num ?></i>&nbsp;&nbsp;<i><?= Yii::t('frontend', 'attention_num')?>：<?= $question->attention_num ?></i>&nbsp;&nbsp;<span><?= Yii::t('frontend', 'posted_question_text')?>：<?=Html::encode($question->fwUser->real_name)?><? if($uid!==$question->created_by): ?>&nbsp;<a href="javascript:void(0);" onclick="attentionUser(this,'<?=$question->created_by?>')" data-id="<?=$question->created_by?>"><?=in_array($question->created_by,$attentionUser)? Yii::t('common', 'cancel_attention'):Yii::t('common', 'attention')?></a><? endif; ?></span></span>
                    <div clas="questionBlock">
                        <p><?= Html::encode($question->question_content) ?></p>
                    </div>
                    <div class="labelArea">
                        <i style="float: left">
                            <?=TTimeHelper::toDateTime($question->created_at,'Y年m月d H:i:s') ?>
                        </i>
                        <? foreach ($tags as $tag): ?>
                            <span class="label label-info"><?= Html::encode($tag->tag_value) ?></span>
                        <? endforeach; ?>
                    </div>
                    <hr/>
                    <div class="loadingWaiting hide">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <p><?=Yii::t('frontend', 'loading')?>...</p>
                    </div>
                    <div id="answer">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default finishLearn">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend', 'related_problem')?>
                </div>
                <div class="panel-body newsDetailLeft">
                    <ul>
                        <? foreach ($relationship_question as $row): ?>
                            <li><i class="glyphicon glyphicon-link"></i><a
                                    href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $row->kid]) ?>"><?= Html::encode(mb_substr($row->title, 0, 14, 'utf-8')).(mb_strlen($row->title,'utf-8')>14?'...':'') ?></a><span
                                    class="pull-right"><?= date('Y-m-d', $row->created_at) ?></span></li>
                        <? endforeach; ?>
                    </ul>
                </div>
            </div>
            <? if ($relationship_course!=null && count($relationship_course) > 0):?>
            <div class="panel panel-default finishLearn">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend', 'related_course')?>
                </div>
                <div class="panel-body newsDetailLeft">
                    <ul>
                        <? foreach($relationship_course as $course):?>
                            <li><i class="glyphicon glyphicon-book"></i><a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $course->kid, 'from' => 'question']) ?>"><?=Html::encode($course->course_name)?></a></li>
                        <? endforeach;?>
                    </ul>
                </div>
            </div>
            <? endif;?>
            <div class="panel panel-default finishLearn"  id="care_user">
            </div>
        </div>
    </div>
</div>
<!-- 分享的弹出窗口 -->
<div class="modal ui" id="newShare">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'share')?></h4>
    </div>
    <div class="content">
        <h4><?=Yii::t('frontend', 'share_to_social_circle')?></h4>
        <?php $form = ActiveForm::begin([
            'id' => 'shareQuestionForm',
            'method' => 'post',
            'action' => Yii::$app->urlManager->createUrl('question/share'),
        ]); ?>
        <?= $form->field($shareModel, 'obj_id')->hiddenInput(['value' => $question->kid]) ?>
        <?= $form->field($shareModel, 'title')->hiddenInput(['value' => $question->title]) ?>
        <?= $form->field($shareModel, 'content')->textarea(['maxlength' => 1000,'style'=>'width:100%; height:80px;border:1px solid #eee;','placeholder'=> Yii::t('frontend', 'say_something')]) ?>
        <div id="content" style="width:100%;border:1px solid #eee; padding: 4px 8px" data-title="<?=Html::encode($question->title)?>">
            <?=Yii::t('frontend', 'question')?>：<?= Html::encode($question->title) ?>
        </div>
        <?=
        Html::button(Yii::t('frontend', 'share'),
            ['id' => 'shareQuestionBtn', 'class' => 'btn btn-success btn-sm pull-right', 'onclick' => 'submitModalForm("","shareQuestionForm","",true,false,null,null);'])
        ?>
        <?php ActiveForm::end(); ?>
        <div class="c"></div>
    </div>
</div>
<script>
    var answer_url = "<?=Url::toRoute(['question/answer-list','qid' => $question->kid])?>";

    $(document).ready(function () {
        loadTab(answer_url, 'answer');
        loadCare();
    });

    function loadCare()
    {
        var url="<?=Url::toRoute(['question/care-user-list','qid' => $question->kid])?>";
        $("#care_user").empty();
        $("#care_user").load(url);
    }

    function loadTab(ajaxUrl, container) {
        $("#" + container).empty();
        $(".loadingWaiting").removeClass('hide');
        ajaxGet(ajaxUrl, container, bind);
    }
    function bind(target, data) {
        $(".loadingWaiting").addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            $('html, body').animate({scrollTop:0}, 'fast');
            var url = $(this).attr('href');
            loadTab(url, target);
            return false;
        });
    }

    function showAnswerComment(obj)
    {
        var b = $(obj).parent();
        var c = $(b).nextAll(".commentInput");
        if (c.hasClass("hide")) {
            c.removeClass("hide")
        } else {
            c.addClass("hide")
        }
    }

    function submitFormAjax(formId) {
        var $form = $('#' + formId);
        var actionUrl = $form.attr("action");
        $.post(
            actionUrl,
            $form.serialize()
        )
            .done(function (data) {
                var result = data.result;
                if (result === 'other') {
                    app.showMsg(data.message, 1500);
                }
                else if (result === 'failure') {
                    app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>', 1500);
                }
                else if (result === 'success') {
                    if (checkPointResult(data.pointResult)){
                        //score-Effect(data.score);
                        scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                    }else{
                        app.showMsg('<?=Yii::t('common', 'operation_success')?>', 1500);
                    }
                    setTimeout(function(){window.location.reload()},1500);
                }
            })
            .fail(function (data) {
                app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_internal_error')?>', 1500);
            });
        return false;
    }

    function subCare(obj) {
        var url = "<?=Url::toRoute(['question/care'])?>";
        $.post(url, {"qid": "<?=$question->kid?>"},
            function (data) {
                AttentionCallBack(obj,data);
            }, "json");
        return false;
    }

    function subCollect(obj) {
        var url = "<?=Url::toRoute(['question/collect'])?>";
        $.post(url, {"qid": "<?=$question->kid?>"},
            function (data) {
                CollectCallBack(obj,data);
            }, "json");
        return false;
    }

    function setRightAnswer(aid) {
        var url = "<?=Url::toRoute(['question/set-right-answer'])?>";
        $.post(url, {"qid": "<?=$question->kid?>",'aid': aid},
            function (data) {
                var result = data.result;
                if (result === 'other') {
                    app.showMsg(data.message, 1500);
                }
                else if (result === 'failure') {
                    app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>', 1500);
                }
                else if (result === 'success') {
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>', 1500);
                    setTimeout('window.location.reload()', 1500);
                }
            }, "json");
        return false;
    }
    function attentionUser(obj, id) {
        var url = "<?=Url::toRoute(['common/attention-user'])?>";
        $.post(url, {"uid": id},
            function (data) {
                AttentionCallBack(obj,data);
            }, "json");
        return false;
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
        app.alert("#newFollow");
        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_success')?></div>');
        formReset();
    }

    function formReset()
    {
        $("#soshare-content").val(null);
    }
    function AttentionCallBack(obj,data) {
        var result = data.result;
        if (result === 'other'){
            app.showMsg(data.message);
        }else if (result === 'failure') {
            app.showMsg("<?=Yii::t('common', 'operation_confirm_warning_failure')?>", 1000);
        }else{
            var uid=$(obj).attr("data-id");

            if ($(obj).text() == '<?=Yii::t('common', 'attention')?>') {
                $("a[data-id='" + uid + "']").text('<?=Yii::t('common', 'canel_attention')?>');
                $("div[data-id='" + uid + "']").text('<?=Yii::t('common', 'canel_attention')?>');
                if (checkPointResult(data.pointResult)){
                    //score-Effect(data.point);
                    scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                }
                else {
                    app.showMsg("<?= Yii::t('frontend', 'attention_sucess') ?>", 1000);
                }
            } else {
                $("a[data-id='" + uid + "']").text('<?=Yii::t('common', 'attention')?>');
                $("div[data-id='" + uid + "']").text('<?=Yii::t('common', 'attention')?>');
                app.showMsg(<?=Yii::t('common', 'cancel_attention')?>, 1000);
            }
            if(!uid)
            {
                loadCare();
            }
        }
    }
    function CollectCallBack(obj,data) {
        var result = data.result;
        if (result === 'other'){
            app.showMsg(data.message);
        }else if (result === 'failure') {
            app.showMsg("<?=Yii::t('common', 'operation_confirm_warning_failure')?>", 1000);
        }else{
            if ($(obj).text() == '<?=Yii::t('common', 'collection')?>') {
                $(obj).text('<?=Yii::t('common', 'canel_collection')?>');
                if (checkPointResult(data.pointResult)){
                    //score-Effect(data.point);
                    scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                }
                else {
                    app.showMsg("<?=Yii::t('frontend', 'collection_sucess')?>", 1000);
                }
            } else {
                $(obj).text('<?=Yii::t('common', 'collection')?>');
                app.showMsg("<?=Yii::t('frontend', 'cancel_sucess')?>", 1000);
            }
        }
    }
</script>
<!-- 收藏的弹出窗口 -->
<div class="ui modal" id="newFollow">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'prompt')?></h4>
    </div>
    <div class="body"></div>
</div>