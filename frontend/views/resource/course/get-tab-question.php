<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/8
 * Time: 11:52
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>
<style>
    .peopleTag {min-height: 50px;}
    .peopleTag .tags {
        padding: 5px 6px!important;
        background: #eee;
        font-size: 10px;
        float: left;
        margin: 2px 1px!important;
        color: #888;
    }
    .peopleTag .tags:hover {
        background: #f56a40;
        color: #fff;
    }
    .tagsGroup .tags {
        width: auto !important;
        padding: 1px 5px !important;
        color: #fff !important;
        background: #f56a40 !important;
        margin-right: 5px !important;
    }
    .commentInput .-query-list {
        display: inline-block;
        width: 154px;
    }
    .commentInput .-search-list{
        height: 28px;
    }
    .commentInput label{
        vertical-align: top;
        margin-top: 7px;
    }
    #pageSizeSelect_page {
        display: none;
    }
    .answerBlock {
        min-height: 190px !important;
    }
</style>

<?php
// if (!$preview){
?>
<div class="answerBlock">
    <?php $form = ActiveForm::begin([
        'id' => 'questionForm',
        'method' => 'post',
        'action' => Url::toRoute(['resource/course/set-course-question', 'id' => $courseId]),
    ]); ?>
    <div class="commentInput commentInputMain">
        <input type="text" name="question_title" id="soquestion-title" class="form-control" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>" style="width:100%;">
        <textarea name="question_content" id="soquestion-question_content" class="form-control" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_content2')])?>" style="height:70px;width:100%;"></textarea>
        <div style="text-align:left">
            <label><?= Yii::t('frontend', 'topic') ?></label>
            <input type="text" class="search_input" id="conversation" placeholder="<?= Yii::t('frontend', 'can_choose_more_{value}',['value'=> Yii::t('frontend', 'topic') ]) ?>" data-url="<?=Url::to(['/student/get-tag','format'=>'new'])?>" data-option="1" data-mult="1" autocomplete="off">
            <label>@<?= Yii::t('frontend', 'some_one') ?></label>
            <input type="text" class="search_input" id="search_people" data-url="<?=Url::to(['/common/search-people', 'format'=>'new'])?>" data-mult="1" autocomplete="off">
            <a href="javascript:;" class="btn btn-success pull-right" id="setQuestion"><?= Yii::t('frontend', 'questions') ?></a>
            <div class="hidden" id="tagDiv"></div>
            <div class="hidden" id="teacherDiv"></div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<div id="question-list"></div>

<?php
// }
?>
<script>
    var questionUrl = '<?=Url::toRoute(['resource/course/get-question','courseId'=>$courseId,'preview'=>$preview])?>';
    var conversation = app.queryList("#conversation");
    var search_people = app.queryList("#search_people");
    function getQuestionList(url) {
        $.ajax({
            url: url,
            type: 'GET',
            async: false,
            success: function (data) {
                $("#question-list").html(data);
            }
        });
    }
    getQuestionList(questionUrl);
    $(function() {
        $("#conversation").keypress(function (event) {
            var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
            if (keyCode == 13) {
                return false;
            }
        });
        $("#search_people").keypress(function (event) {
            var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
            if (keyCode == 13) {
                return false;
            }
        });
    });

    function ReloadPageAfterUpdate()
    {
        app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
        $("#soquestion-title").val('');
        $("#soquestion-question_content").val('');
        $(".tagsGroup").find("span").each(function(){
           if ($(this).hasClass('tags')){
               $(this).remove();
           }
        });
        getQuestionList(questionUrl);
    }
    <?php
    if (!$preview){
    ?>
    $("#setQuestion").on('click', function (e) {
        e.preventDefault();
        var question_title = $("#soquestion-title").val();
        question_title = question_title.replace(/\s+/, '');
        if (question_title == '') {
            $("#question_title").focus();
            app.showMsg('<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','question_answer_title')]) ?>');
            return false;
        }
        if (app.stringLength(question_title) > 100){
            app.showMsg('<?= Yii::t('frontend', '{value}_limit_100_word',['value'=>Yii::t('frontend','question_answer_title')]) ?>');
            return false;
        }
        var questionContent = $("#soquestion-question_content").val();
        questionContent = questionContent.replace(/\s+/, '');
        if (questionContent == '') {
            $("#question_content").focus();
            app.showMsg('<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','question_content')]) ?>');
            return false;
        }
        $("#tagDiv").empty();
        var tags = [];
        var tag_json = conversation.get();
        if (typeof tag_json != 'undefined'){
            var tag_length  = tag_json.length;
            if (tag_length > 0){
                for (var i = 0; i < tag_length; i++){
                    tags.push(tag_json[i]['title']);
                    $("#tagDiv").append('<input type="hidden" name="tag[]" value="'+tag_json[i]['title']+'" />');
                }
            }
        }
        $("#teacherDiv").empty();
        var teacher_json = search_people.get();
        if (typeof teacher_json != 'undefined'){
            var teacher_length = teacher_json.length;
            if (teacher_length > 0){
                for (var j = 0; j < teacher_length; j++){
                    $("#teacherDiv").append('<input type="hidden" name="users[]" value="'+teacher_json[j]['kid']+'" />');
                }
            }
        }
        var $form = $('#questionForm');
        var actionUrl = $form.attr("action");
        $.post(
            actionUrl,
            $form.serialize()
        ).done(function(data) {
                var result = data.result;
                conversation.reset();
                search_people.reset();
                if (result === 'other'){
                    app.showMsg(data.message);
                }
                else if (result === 'failure') {
                    app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>.');
                }
                else
                {
                    ReloadPageAfterUpdate();
                }
            })
            .fail(function() {
                app.showMsg("<?= Yii::t('common', 'operation_confirm_warning_internal_error') ?>");
            });
        $("#questionForm").find('.collapse').hide();
    });
    <?php
    }
    ?>
</script>