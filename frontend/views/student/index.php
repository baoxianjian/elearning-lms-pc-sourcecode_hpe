<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/8
 * Time: 10:49
 */
use common\models\message\MsMessage;
use frontend\widgets\ContinueLearning;
use frontend\widgets\CourseLibrary;
use frontend\widgets\QuestionArea;
use frontend\widgets\RecommendCourse;
use frontend\widgets\UserPanel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$current_time = time();
?>
<style>
    .control-label {
        display: none;
    }

    .help-block {
        display: none;
    }

    .form-control {
        width: 100% !important;
    }

    /* 我要记录表单css */
    .field-sorecord-title .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-title .form-control:focus{
        border: 1px solid #CCC;
        border-top: 1px solid #66afe9;
        box-shadow:none;
    }
    .field-sorecord-title .has-success .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-title .has-success .form-control:focus{
        border: 1px solid #CCC;
        border-top: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-sorecord-title .has-error .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-title .has-error .form-control:focus{
        border: 1px solid #CCC;
        border-top: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-sorecord-content .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-content .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        box-shadow:none;
    }
    .field-sorecord-content .has-success .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-content .has-success .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-sorecord-content .has-error .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-content .has-error .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }

    .field-sorecord-url .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-url .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        box-shadow:none;
    }
    .field-sorecord-url .has-success .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-url .has-success .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-sorecord-url .has-error .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-url .has-error .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }

    .field-sorecord-start_at .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-start_at .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        box-shadow:none;
    }
    .field-sorecord-start_at .has-success .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-start_at .has-success .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-sorecord-start_at .has-error .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-sorecord-start_at .has-error .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    @media only screen and (max-width:767px){
        .tiwen {width:100%;margin-bottom: 30px;}
        .tiwen .huati{width:35px;}
        .tiwen .tdwrapper,.tiwen .tdwrapper #search_tag{width:100px;}

        .tiwen .mouren{width:50px;}
        .tiwen .tdwrapper,.tiwen .tdwrapper #search_people{width:100px}
        #shareBtn{
            margin: 150px 0 0 -100px;
            position: absolute;
            float: none !important;
        }
    }
    .textout-small{width: 80px; overflow: hidden;white-space: nowrap;text-overflow: ellipsis;position: absolute;}
    .textout-wide{width: 180px; overflow: hidden;white-space: nowrap;text-overflow: ellipsis;position: absolute;}
    .tdwrapper #course_search_people{width:160px}
</style>
<script src="/static/common/js/d.modal.js" type="text/javascript"></script>
<?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
<script>
    var questionDivId = 'iWantAsk',
        recordWebDivId = 'iWantRecordWeb',
        recordEventDivId = 'iWantRecordEvent',
        recordBookDivId = 'iWantRecordBook',
        recordExpDivId = 'iWantRecordExp';

    var questionViewUrl = "<?=Yii::$app->urlManager->createUrl(['student/index-tab-question'])?>",
        recordWebViewUrl = "<?=Yii::$app->urlManager->createUrl(['student/index-tab-web'])?>",
        recordEventViewUrl = "<?=Yii::$app->urlManager->createUrl(['student/index-tab-event'])?>",
        recordBookViewUrl = "<?=Yii::$app->urlManager->createUrl(['student/index-tab-book'])?>",
        recordExpViewUrl = "<?=Yii::$app->urlManager->createUrl(['student/index-tab-exp'])?>";

    var questionFormId = 'questionForm',
        recordWebFormId = 'recordWebForm',
        recordEventFormId = 'recordEventForm',
        recordBookFormId = 'recordBookForm',
        recordExpFormId = 'recordExpForm';

    function FmodalLoad(target, url)
    {
        if(url){
            $('#'+target).empty();
            $('#'+target).load(url);
        }
    }

    function reloadForm(formId) {
        var fun;
        if (formId == questionFormId) {
            $("#" + questionDivId).modal('hide');
            fun = "FmodalLoad('" + questionDivId + "', '" + questionViewUrl + "');treeReload('timeline2');";
        }
        else if (formId == recordWebFormId) {
            $("#" + recordWebDivId).modal('hide');
            fun = "FmodalLoad('" + recordWebDivId + "', '" + recordWebViewUrl + "')";
        }
        else if (formId == recordEventFormId) {
            $("#" + recordEventDivId).modal('hide');
            fun = "FmodalLoad('" + recordEventDivId + "', '" + recordEventViewUrl + "')";
        }
        else if (formId == recordBookFormId) {
            $("#" + recordBookDivId).modal('hide');
            fun = "FmodalLoad('" + recordBookDivId + "', '" + recordBookViewUrl + "')";
        }
        else if (formId == recordExpFormId) {
            $("#" + recordExpDivId).modal('hide');
            fun = "FmodalLoad('" + recordExpDivId + "', '" + recordExpViewUrl + "')";
        }
        setTimeout(fun, 1500);
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose, data) {
        if (checkPointResult(data.pointResult)){
            //score-Effect(data.point);
            scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
        }
        else {
            app.showMsg('<?=Yii::t('common','operation_success')?>', 1500);
        }
        reloadForm(formId);
    }

    function ResetForm(formId)
    {
        $('#'+formId)[0].reset();
        $('#'+formId + ' #btn_dropdown').html('<?=Yii::t('frontend','duration_time')?> &nbsp;<span class="caret"></span>');
        $('#'+formId + ' #sorecord-duration').val('');
        if (formId == questionFormId) {
            $('#'+formId + ' #tags').val('');
            $('#'+formId + ' #select_value').val('');
        }
        else if (formId == recordWebFormId) {
            $('#'+formId + ' #web_upload').html('<?=Yii::t('frontend','enclosure')?>');
            $('#'+formId + ' #sorecord-attach_original_filename').val('');
            $('#'+formId + ' #sorecord-attach_url').val('');
            $('#'+formId + ' .upload-info').html('');
            $('div:last').removeAttr('title');
        }
        else if (formId == recordEventFormId) {
            $('#'+formId + ' #event_upload').html('<?=Yii::t('frontend','enclosure')?>');
            $('#'+formId + ' #sorecord-attach_original_filename').val('');
            $('#'+formId + ' #sorecord-attach_url').val('');
            $('#'+formId + ' .upload-info').html('');
            $('div:last').removeAttr('title');
        }
        else if (formId == recordBookFormId) {
            $('#'+formId + ' #book_upload').html('<?=Yii::t('frontend','enclosure')?>');
            $('#'+formId + ' #sorecord-attach_original_filename').val('');
            $('#'+formId + ' #sorecord-attach_url').val('');
            $('#'+formId + ' .upload-info').html('');
            $('div:last').removeAttr('title');
        }
        else if (formId == recordExpFormId) {
            $('#'+formId + ' #exp_upload').html('<?=Yii::t('frontend','enclosure')?>');
            $('#'+formId + ' #sorecord-attach_original_filename').val('');
            $('#'+formId + ' #sorecord-attach_url').val('');
            $('#'+formId + ' .upload-info').html('');
            $('div:last').removeAttr('title');
        }
    }
</script>
<div class="container">
    <div class="row">
        <ol class="breadcrumb">
        </ol>
        <div class="col-md-4 wideScreenBlock ">
            <?
            $userPanel = UserPanel::widget();
            $recommendCourse = RecommendCourse::widget();
            $continueLearning = ContinueLearning::widget();
            $courseLibrary = CourseLibrary::widget();
            $questionArea = QuestionArea::widget();
            ?>
            <?
            echo $userPanel;
            ?>
            <?
            echo $continueLearning;
            ?>
            <?
            echo $recommendCourse;
            ?>
            <?
            echo $courseLibrary;
            ?>
            <?
            echo $questionArea;
            ?>
        </div>
        <div class="col-md-8">
            <div class="row submitModule">
                <a class="glyphicon glyphicon-plus tipsBtn" href="#" onclick="return false;"></a>
                <span class="tips"><?=Yii::t('frontend','what_want_to_do_today')?></span>
                <ul class="popBtnGroup hide">
                    <li><a href="javascript:void(0);" onclick="ResetForm(questionFormId);" data-toggle="modal" data-target="#iWantAsk" class="glyphicon glyphicon-question-sign popBtn"><span><?=Yii::t('frontend','questions')?></span></a></li>
                    <li><a href="javascript:void(0);" onclick="ResetForm(recordWebFormId);" data-toggle="modal" data-target="#iWantRecordWeb" class="glyphicon glyphicon-globe popBtn"><span><?=Yii::t('frontend','web_page')?></span></a></li>
                    <li><a href="javascript:void(0);" onclick="ResetForm(recordEventFormId);" data-toggle="modal" data-target="#iWantRecordEvent" class="glyphicon glyphicon-calendar popBtn"><span><?=Yii::t('frontend','event')?></span></a></li>
                    <li><a href="javascript:void(0);" onclick="ResetForm(recordBookFormId);" data-toggle="modal" data-target="#iWantRecordBook" class="glyphicon glyphicon-book popBtn"><span><?=Yii::t('frontend','book')?></span></a></li>
                    <li><a href="javascript:void(0);" onclick="ResetForm(recordExpFormId);" data-toggle="modal" data-target="#iWantRecordExp" class="glyphicon glyphicon-education popBtn"><span><?=Yii::t('frontend','experience')?></span></a></li>
                </ul>
            </div>
<!--            <div class="row">-->
<!--                <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">-->
<!--                    <li role="presentation" class="active"><a href="#question" aria-controls="question" role="tab"-->
<!--                                                              data-toggle="tab">--><?//=Yii::t('frontend','my_question')?><!--</a></li>-->
<!--                    <li role="presentation"><a href="#shareSomething" aria-controls="shareSomething" role="tab"-->
<!--                                               data-toggle="tab">--><?//=Yii::t('frontend','want_share')?><!--</a></li>-->
<!--                    <li role="presentation"><a href="#motion" aria-controls="motion" role="tab"-->
<!--                                               data-toggle="tab">--><?//=Yii::t('frontend','want_record')?><!--</a></li>-->
<!--                </ul>-->
<!--                <div class="tab-content panel panel-default">-->
<!--                    <div role="tabpanel" class="tab-pane active  panel-body" id="question">-->
<!--                    </div>-->
<!--                    <div role="tabpanel" class="tab-pane  panel-body" id="shareSomething">-->
<!--                    </div>-->
<!--                    <div role="tabpanel" class="tab-pane  panel-body" id="motion">-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
            <div class="row">
                <div class="panel panel-default hotNews topBordered">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('frontend','dynamic')?>
                    </div>

                    <div class="panel-body textCenter">
                        <div class="filterBtn">
<!--                            <div id="timeline_drop" class="btn-group timeScope pull-left">-->
<!--                                <button class="btn btn-default btn-xs dropdown-toggle" type="button"-->
<!--                                        data-toggle="dropdown" aria-expanded="false">--><?//=Yii::t('frontend','sort_by_endtime')?><!-- &nbsp;<span-->
<!--                                        class="caret"></span>-->
<!--                                </button>-->
<!--                                <ul class="dropdown-menu" role="menu">-->
<!--                                    <li id="li_time_1" class="active"><a id="a_time_1" href="javascript:void(0);" onclick="changeTime(this,1)">--><?//=Yii::t('frontend','sort_by_endtime')?><!--</a></li>-->
<!--                                    <li id="li_time_2"><a id="a_time_2" href="javascript:void(0);" onclick="changeTime(this,2)">--><?//=Yii::t('frontend','sort_by_created')?><!--</a></li>-->
<!--                                </ul>-->
<!--                            </div>-->
                            <div id="timeline_drop" class="btn-group timeScope pull-left">
                                <select class="form-control timelineFilter">
                                    <option value="1"><?=Yii::t('frontend','sort_by_endtime')?></option>
                                    <option value="2"><?=Yii::t('frontend','sort_by_created')?></option>
                                </select>
                            </div>
                            <a href="javascript:void(0);" id="btnCate0" class="btnFilter activeBtn"><?=Yii::t('frontend','tab_btn_todo')?></a>
                            <a href="javascript:void(0);" id="btnCate1" class="btnFilter"><?=Yii::t('frontend','tab_btn_qa')?></a>
                            <a href="javascript:void(0);" id="btnCate2" class="btnFilter"><?=Yii::t('frontend','tab_btn_news')?></a>
                            <a href="javascript:void(0);" id="btnCate3" class="btnFilter"><?=Yii::t('frontend','tab_btn_social')?></a>
                        </div>
                        <div id="timeline_todo">
                            <p class="filterInfo"><?= Yii::t('frontend', 'please_complete_task') ?></p>
                            <div class="timeline" id="timeline1">
                            </div>
                        </div>
                        <div class="timeline hidden" id="timeline2">
                        </div>
                        <div class="timeline hidden" id="timeline3">
                        </div>
                        <div class="timeline hidden" id="timeline4">
                        </div>
                        <div id="timeline_loading" class="loadingWaiting hide">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <p><?=Yii::t('frontend','loading')?>...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 窄视图时显示 start -->
        <div class="col-md-4 narrowScreenBlock">
            <?
            echo $userPanel;
            ?>
            <?
            echo $continueLearning;
            ?>
            <?
            echo $recommendCourse;
            ?>
            <?
            echo $courseLibrary;
            ?>
            <?
            echo $questionArea;
            ?>
        </div>
        <!-- 窄视图时显示 end -->
    </div>
</div>
<!-- 我要提问弹出界面 -->
<div class="modal fade" id="iWantAsk" tabindex="-1" role="dialog" aria-labelledby="iWantAsk" aria-hidden="true" style="height: 100%">
</div>
<!-- 记录网页弹出界面 -->
<div class="modal fade" id="iWantRecordWeb" tabindex="-1" role="dialog" aria-labelledby="iWantRecordWeb" aria-hidden="true" style="height: 100%">
</div>
<!-- 我要记录弹出界面 -->
<div class="modal fade" id="iWantRecordEvent" tabindex="-1" role="dialog" aria-labelledby="iWantRecordEvent" aria-hidden="true" style="height: 100%">
</div>
<!-- 记录书籍弹出界面 -->
<div class="modal fade" id="iWantRecordBook" tabindex="-1" role="dialog" aria-labelledby="iWantRecordBook" aria-hidden="true" style="height: 100%">
</div>
<!-- 记录经验弹出界面 -->
<div class="modal fade" id="iWantRecordExp" tabindex="-1" role="dialog" aria-labelledby="iWantRecordExp" aria-hidden="true" style="height: 100%">
</div>

    <div id="foo" class="ui modal">
        <div class="header"><?=Yii::t('frontend','top_remind_text')?></div>
        <div class="content">
            <p><?=Yii::t('frontend','warning_for_delete')?></p>
            <div class="c"></div> <!--新增-->
        </div>
        <div class="actions">
            <div class="btn btn-default cancel"><?=Yii::t('frontend','page_info_good_cancel')?></div>
            <div class="btn btn-default ok"><?=Yii::t('frontend','be_sure')?></div>
        </div>
    </div>
<!--<link rel="stylesheet" href="/components/kindeditor/plugins/code/prettify.css"/>-->
<!--<link rel="stylesheet" href="/components/kindeditor/themes/default/default.css"/>-->
<!--<script src="/components/kindeditor/plugins/code/prettify.js"></script>-->
<!--<script src="/components/kindeditor/kindeditor-all-min.js"></script>-->
<!--<script>-->
<!--    var editor;-->
<!--    KindEditor.ready(function (K) {-->
<!--        editor = K.create('#soquestion-question_content', {-->
<!---->
<!--            newlineTag: 'br',-->
<!--            resizeType: 1,-->
<!--            allowPreviewEmoticons: false,-->
<!--            allowImageUpload: true,-->
<!--            uploadJson: '/components/kindeditor/php/upload_json.php',-->
<!--//            fileManagerJson : '/components/kindeditor/php/file_manager_json.php',-->
<!--            allowFileManager: false,-->
<!--            items: [-->
<!--                'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',-->
<!--                'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',-->
<!--                'insertunorderedlist', '|', 'emoticons', 'image', 'link', 'fullscreen', 'source'],-->
<!--            afterCreate: function () {-->
<!--                this.sync();-->
<!--            },-->
<!--            afterBlur: function () {-->
<!--                this.sync();-->
<!--                if (editor.isEmpty()) {-->
<!--                    $(".ke-container").attr('style', 'border-color: #A94442;');-->
<!--                }-->
<!--                else {-->
<!--                    $(".ke-container").attr('style', 'border-color: #3C763D;');-->
<!--                }-->
<!--            }-->
<!--        });-->
<!--    });-->
<!--</script>-->
<script>
    var loading = true;

    var type = '<?=MsMessage::TYPE_TODO?>';

    var course_page = 1;
    var course_url = "<?=Url::toRoute(['common/get-todo-dynamic-message','current_time'=> $current_time])?>" + "&page=";
    var course_one_url = "<?=Url::toRoute(['common/get-todo-timeline-one','current_time'=> $current_time])?>" + "&page=";
    var course_end = false;
    var course_time = 1;
    var course_new = false;
    var course_load_time = '<?=$current_time?>';
    var course_new_load_time = '<?=$current_time?>';

    var qa_page = 1;
    var qa_url = "<?=Url::toRoute(['common/get-qa-dynamic-message','current_time'=> $current_time])?>" + "&page=";
    var qa_end = false;
    var qa_time = 2;
    var qa_read = false;
    var qa_new = false;
    var qa_load_time = '<?=$current_time?>';
    var qa_new_load_time = '<?=$current_time?>';

    var news_page = 1;
    var news_url = "<?=Url::toRoute(['common/get-news-dynamic','current_time'=> $current_time])?>" + "&page=";
    var news_end = false;
    var news_time = 2;
    var news_read = false;
    var news_new = false;
    var news_new_load_time = '<?=$current_time?>';

    var social_page = 1;
    var social_url = "<?=Url::toRoute(['common/get-social-dynamic-message','current_time'=> $current_time])?>" + "&page=";
    var social_end = false;
    var social_time = 2;
    var social_read = false;
    var social_new = false;
    var social_new_load_time = '<?=$current_time?>';

    $(document).ready(function () {
        $(".timelineFilter").change(function(){
            var time=$(this).val();
            if (type == '<?=MsMessage::TYPE_TODO?>') {
                course_time = time;
                course_page = 1;
                course_end = false;
                $("#timeline1").empty();
                loadTab(course_url + course_page + '&time=' + course_time, 'timeline1');
            }
        });

        loadTab(course_url + course_page + '&time=' + course_time, 'timeline1');

        $(window).scroll(function () {
            var bot = 100; // 底部距离的高度
            if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                if (type == '<?=MsMessage::TYPE_TODO?>' && !course_end) {
                    loading = true;
                    course_page++;
                    loadTab(course_url + course_page + '&time=' + course_time, 'timeline1');
                }
                else if (type == '<?=MsMessage::TYPE_QA?>' && !qa_end) {
                    loading = true;
                    qa_page++;
                    loadTab(qa_url + qa_page, 'timeline2');
                }
                else if (type == '<?=MsMessage::TYPE_NEWS?>' && !news_end) {
                    loading = true;
                    news_page++;
                    loadTab(news_url + news_page + '&time=' + news_time, 'timeline3');
                }
                else if (type == '<?=MsMessage::TYPE_SOCIAL?>' && !social_end) {
                    loading = true;
                    social_page++;
                    loadTab(social_url + social_page + '&time=' + social_time, 'timeline4');
                }
            }
        });
    });
    function loadTab(ajaxUrl, container) {
        $("#timeline_loading").removeClass('hide');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $("#timeline_loading").addClass('hide');

        if (target == 'timeline1') {
            count = $(data).filter('.noData').length;
            if (count === 1) {
                $("#timeline_drop").remove();
                $("#timeline_todo").html(data);
//                $("#timeline_todo").attr('id','timeline1');
                timeline1 = $("#timeline_todo");
                course_end = true;
                return;
            }
        }

        $("#" + target).append(data);
        loading = false;
        var count=$(data).filter('.timeline-item').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'timeline1') {
                course_end = true;
            }
            else if (target == 'timeline2') {
                qa_end = true;
            }
            else if (target == 'timeline3') {
                news_end = true;
            }
            else if (target == 'timeline4') {
                social_end = true;
            }
        }
    }

    function loadNewDataCallback(target, data) {
        loading = false;
        var count = $(data).filter('.timeline-item').length;
        if (count % 2 != 0) {
            $(".timeline-content").each(function (i, n) {
                if ($(this).hasClass('right')) {
                    $(this).removeClass('right');
                }
                else {
                    $(this).addClass('right');
                }
            });
        }
        $("#" + target).prepend(data);
    }

    var
        btnFilter = $(".btnFilter"),

        btnCate0 = $("#btnCate0"),
        btnCate1 = $("#btnCate1"),
        btnCate2 = $("#btnCate2"),
        btnCate3 = $("#btnCate3");

     var timeline1 = $("#timeline1"),
        timeline2 = $("#timeline2"),
        timeline3 = $("#timeline3"),
        timeline4 = $("#timeline4");


    btnFilter.bind("click", function () {
        var b = $(this);
        btnFilter.removeClass("activeBtn");
        b.addClass("activeBtn");
    });

    btnCate0.bind("click", function () {
        if(course_new)
        {
            var timestamp = Date.parse(new Date())/1000;
            loading=true;
            ajaxGet('<?=Url::toRoute(['common/get-todo-new-timeline'])?>'+'?start_time='+course_new_load_time+'&end_time='+timestamp+'&time='+course_time,  'timeline1', loadNewDataCallback);
            course_new_load_time = timestamp;
            course_new=false;
            $("#btnCate0").removeClass('dot_newMessage');
        }
        if (timeline1.hasClass("hidden")) {
            hiddenAll();
            timeline1.removeClass("hidden");
            type = '<?=MsMessage::TYPE_TODO?>';
            $('#timeline_drop').show();
            $(".filterInfo").show();
        }
    });

    btnCate1.bind("click", function () {
        var timestamp = Date.parse(new Date())/1000;
        if (!qa_read) {
            qa_url=qa_url.replace(qa_new_load_time,timestamp);
            qa_read = true;
            loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline2');
            qa_new_load_time = timestamp;
        }
        if(qa_new)
        {
            loading=true;
            ajaxGet('<?=Url::toRoute(['common/get-qa-new-timeline'])?>'+'?start_time='+qa_new_load_time+'&end_time='+timestamp,  'timeline2', loadNewDataCallback);
            qa_new_load_time = timestamp;
            qa_new=false;
            $("#btnCate1").removeClass('dot_newMessage');
        }
        if (timeline2.hasClass("hidden")) {
            hiddenAll();
            timeline2.removeClass("hidden");
            type = '<?=MsMessage::TYPE_QA?>';
            $('#timeline_drop').hide();
            $(".filterInfo").hide();
        }
    });

    btnCate2.bind("click", function () {
        var timestamp = Date.parse(new Date())/1000;
        if (!news_read) {
            news_url=news_url.replace(news_new_load_time,timestamp);
            news_read = true;
            loadTab(news_url + news_page + '&time=' + news_time, 'timeline3');
            news_new_load_time = timestamp;
        }
        if(news_new)
        {
            loading=true;
            ajaxGet('<?=Url::toRoute(['common/get-news-new-timeline'])?>'+'?start_time='+news_new_load_time+'&end_time='+timestamp,  'timeline3', loadNewDataCallback);
            news_new_load_time = timestamp;
            news_new=false;
            $("#btnCate2").removeClass('dot_newMessage');
        }
        if (timeline3.hasClass("hidden")) {
            hiddenAll();
            timeline3.removeClass("hidden");
            type = '<?=MsMessage::TYPE_NEWS?>';
            $('#timeline_drop').hide();
            $(".filterInfo").hide();
        }
    });

    btnCate3.bind("click", function () {
        var timestamp = Date.parse(new Date())/1000;
        if (!social_read) {
            social_url=social_url.replace(social_new_load_time,timestamp);
            social_read = true;
            loadTab(social_url + social_page + '&time=' + social_time, 'timeline4');
            social_new_load_time = timestamp;
        }
        if(social_new)
        {
            loading=true;
            ajaxGet('<?=Url::toRoute(['common/get-social-new-timeline'])?>'+'?start_time='+social_new_load_time+'&end_time='+timestamp,  'timeline4', loadNewDataCallback);
            social_new_load_time = timestamp;
            social_new=false;
            $("#btnCate3").removeClass('dot_newMessage');
        }
        if (timeline4.hasClass("hidden")) {
            hiddenAll();
            timeline4.removeClass("hidden");
            type = '<?=MsMessage::TYPE_SOCIAL?>';
            $('#timeline_drop').hide();
            $(".filterInfo").hide();
        }
    });

    function hiddenAll() {
        timeline1.addClass("hidden");
        timeline2.addClass("hidden");
        timeline3.addClass("hidden");
        timeline4.addClass("hidden");
    }

    function treeReload(treeId) {
        if (treeId == 'timeline1') {
            course_page = 1;
            $("#" + treeId).empty();
            loadTab(course_url + course_page + '&time=' + course_time, treeId);
        }
        else if (treeId == 'timeline2') {
            qa_page = 1;
            $("#" + treeId).empty();
            loadTab(qa_url + qa_page, treeId);
        }
        else if (treeId == 'timeline3') {
            news_page = 1;
            $("#" + treeId).empty();
            loadTab(news_url + news_page, treeId);
        }
        else if (treeId == 'timeline4') {
            social_page = 1;
            $("#" + treeId).empty();
            loadTab(social_url + social_page, treeId);
        }
    }
    // 设置按钮的id名称
    var btnNum = $('.btnFilter').length
    for (i = 0; i < btnNum; i++) {
        $($('.btnFilter')[i]).attr("data-num", i)
    }

    //为每个按钮添加事件
//    $('.btnFilter').bind('click', function() {
//        var actBtn = $(this).attr("data-num")
//        // 给选中的按钮加上 activeBtn 样式
//        $('.btnFilter').removeClass('activeBtn');
//        $(this).addClass('activeBtn')
//
//        $('.timeline').addClass('hidden');
//        $($('.timeline')[actBtn]).removeClass('hidden');
//    });
    var
        selectBtn = $(".selectBtn"),
        selectPanel = $(".selectPanel"),
        btnComfirm = $(".btnComfirm");

    selectBtn.bind("click", function() {
        if (selectPanel.hasClass("hide")) {
            selectPanel.removeClass("hide")
        } else {
            selectPanel.addClass("hide")
        }
    });

    btnComfirm.bind("click", function() {
        if (selectPanel.hasClass("hide")) {
            selectPanel.removeClass("hide")
        } else {
            selectPanel.addClass("hide")
        }
    });
    $(function() {
        var
            tipsBtn = $('.tipsBtn'),
            tips = $('.submitModule .tips'),
            popBtnGroup = $('.popBtnGroup');

        tipsBtn.bind('click', function() {
            if (popBtnGroup.hasClass('hide')) {
                popBtnGroup.removeClass('hide');
                tips.addClass('hide');
                tipsBtn.removeClass('glyphicon-plus').addClass('glyphicon-arrow-left');
            } else {
                popBtnGroup.addClass('hide');
                tips.removeClass('hide');
                tipsBtn.addClass('glyphicon-plus').removeClass('glyphicon-arrow-left');
            }
        });

        $('.submitModule').siblings().bind('click', function() {
            if (tips.hasClass('hide')) {
                popBtnGroup.addClass('hide');
                tips.removeClass('hide');
                tipsBtn.addClass('glyphicon-plus').removeClass('glyphicon-arrow-left');
            }
        })
    });

    function selectDuration(formId, obj, duration) {
        $("#" + formId + " #btn_dropdown").html($(obj).html() + ' &nbsp;<span class="caret">');
        $("#" + formId + " #sorecord-duration").val(duration);
    }
    function submitNoShare(formId) {
        $("#" + formId + " #saveBtn").attr({"disabled":"disabled"});
        $("#" + formId + " #saveShareBtn").attr({"disabled":"disabled"});
        $("#" + formId + " #is_share").val("0");
        $("#" + formId).submit();
    }

    function submitAndShare(formId) {
        $("#" + formId + " #saveBtn").attr({"disabled":"disabled"});
        $("#" + formId + " #saveShareBtn").attr({"disabled":"disabled"});
        $("#" + formId + " #is_share").val("1");
        $("#" + formId).submit();
    }

    function moreContent(btn)
    {
        var thisBtn = $(btn);
        if (thisBtn.text() == '<?=Yii::t('common','menu_collapse')?>') {
            thisBtn.parent().find('.moreContent').css('height', 'auto');
            thisBtn.text('<?=Yii::t('frontend','page_myteam_noshow')?>');
        } else {
            thisBtn.parent().find('.moreContent').css('height', '50px');
            thisBtn.text('<?=Yii::t('common','menu_collapse')?>');
        }
    }

    function submitDownload(obj_id,obj_type)
    {
        $("#downloadform-id").val(obj_id);
        $("#downloadform-type").val(obj_type);
        $("#downloadForm").submit();
        return false;
    }
    function submitShare(id){
        var url = "<?=Url::toRoute(['common/social-share'])?>";
        $.post(url, {"id": id},
            function (data) {
                var result = data.result;
                if (result === 'other') {
                    app.showMsg(data.message, 1500);
                }
                else if (result === 'success') {
                    app.showMsg('<?=Yii::t('common','operation_success')?>', 1500);
                }
            }, "json");
        return false;
    }

    window.onload = function () {
        <? if(!$notShowSetTag): ?>
        tag_init();
        <? endif; ?>
        coursePeopleQueryList = app.queryList("#course_search_people");

        setTimeout(LoadTabContent, 1000);
        setTimeout(LoadNewDataCount, 60000);
    };

    function LoadTabContent()
    {
        FmodalLoad(questionDivId,questionViewUrl);
        FmodalLoad(recordWebDivId, recordWebViewUrl);
        FmodalLoad(recordEventDivId, recordEventViewUrl);
        FmodalLoad(recordBookDivId, recordBookViewUrl);
        FmodalLoad(recordExpDivId, recordExpViewUrl);
    }

    var count_url='<?=Url::toRoute(['common/get-timeline-new-data'])?>';

    function LoadNewDataCount() {
        AjaxGetNewCount('<?=\common\models\message\MsTimeline::TIMELINE_TYPE_TODO?>', course_new_load_time);
        if(qa_read){
            AjaxGetNewCount('<?=\common\models\message\MsTimeline::TIMELINE_TYPE_QA?>', qa_new_load_time);
        }
        if(news_read) {
            AjaxGetNewCount('<?=\common\models\message\MsTimeline::TIMELINE_TYPE_NEWS?>', news_new_load_time);
        }
        if(social_read) {
            AjaxGetNewCount('<?=\common\models\message\MsTimeline::TIMELINE_TYPE_SOCIAL?>', social_new_load_time);
        }
        setTimeout(LoadNewDataCount,60000);
    }

    function AjaxGetNewCount(type, time) {
        $.getJSON(count_url, {'type': type, 'time': time}, function (data) {
            if (data.result === 'success') {
                if (type==='<?=\common\models\message\MsTimeline::TIMELINE_TYPE_TODO?>' && data.count > 0) {
                    course_new = true;
                    $("#btnCate0").addClass('dot_newMessage');
                }
                if (type==='<?=\common\models\message\MsTimeline::TIMELINE_TYPE_QA?>' && data.count > 0) {
                    qa_new = true;
                    $("#btnCate1").addClass('dot_newMessage');
                }
                if (type==='<?=\common\models\message\MsTimeline::TIMELINE_TYPE_NEWS?>' && data.count > 0) {
                    news_new = true;
                    $("#btnCate2").addClass('dot_newMessage');
                }
                if (type==='<?=\common\models\message\MsTimeline::TIMELINE_TYPE_SOCIAL?>' && data.count > 0) {
                    social_new = true;
                    $("#btnCate3").addClass('dot_newMessage');
                }
            }
        });
    }

    function deleteTimeline(id)
    {
        app.alertSmall("#foo",
            {
                ok: function ()
                {
                    var url = "<?=Url::toRoute(['common/delete-timeline'])?>";
                    $.post(url, {"id": id},
                        function (data) {
                            var result = data.result;
                            if (result === 'success') {
                                $("#"+id).nextAll().each(function(i){
                                    var content = $(this).find('.timeline-content');
                                    $(this).hide();
                                    if (content.hasClass('right')) {
                                        content.removeClass('right');
                                    }
                                    else {
                                        content.addClass('right');
                                    }
                                    $(this).fadeIn(1000);
                                });
                                $("#"+id).remove();
                                if(!course_end){
                                    loading = true;
                                    ajaxGet(course_one_url + course_page + '&time=' + course_time, 'timeline1',function(target, data) {
                                        $("#" + target).append(data);
                                        loading = false;
                                    });
                                }
                            }else if (result === 'other') {
                                app.showMsg(data.message, 1500);
                            }
                            else if (result === 'failure') {
                                app.showMsg('<?=Yii::t('frontend','operation_confirm_warning_failure')?>', 1500);
                            }

                        }, "json");
                    return true;
                },
                cancel: function ()
                {
                    return true;
                }
            }
        );
    }
    function stickTimeline(id,target)
    {
        var url = "<?=Url::toRoute(['common/stick-timeline'])?>";
        $.post(url, {"id": id},
            function (data) {
                var result = data.result;
                if (result === 'success') {
                    $("#"+id).insertBefore($("#"+target+" .timeline-item").first()).find('.timeline-content').removeClass('right');
                    $("#"+id).find("[title='<?=Yii::t('common','art_top')?>']").remove();
                    $("#"+id).find('h2').prepend('<a href="javascript:void(0);" onclick="cancelStickTimeline(\''+id+'\',\''+target+'\')" class="glyphicon glyphicon-pushpin " title="<?=Yii::t('frontend','untop')?>" style="display: inline-block;"></a>');

                    var flag=true;
                    $("#"+target+" .timeline-content:not(:first)").each(function(i) {
                        if (flag) {
                            $(this).addClass('right');
                        }
                        else {
                            $(this).removeClass('right');
                        }
                        flag = !flag;
                    });
                }else if (result === 'other') {
                    app.showMsg(data.message, 1500);
                }
                else if (result === 'failure') {
                    app.showMsg('<?=Yii::t('frontend','operation_confirm_warning_failure')?>', 1500);
                }
            }, "json");
        return false;
    }

    function cancelStickTimeline(id,target)
    {
        var url = "<?=Url::toRoute(['common/cancel-stick-timeline'])?>";
        $.post(url, {"id": id},
            function (data) {
                var result = data.result;
                if (result === 'success') {
                    var timestamp = Date.parse(new Date()) / 1000;
                    $("#"+target).empty();
                    if(target === 'timeline1'){
                        course_url = course_url.replace(course_load_time, timestamp);
                        course_one_url = course_one_url.replace(course_load_time, timestamp);
                        course_load_time = timestamp;
                        course_new_load_time = timestamp;
                        course_page = 1;
                        course_end = false;
                        course_new = false;

                        loadTab(course_url + course_page + '&time=' + course_time, target);
                    }
                    else if(target === 'timeline2'){
                        qa_url = qa_url.replace(qa_load_time, timestamp);
                        qa_load_time = timestamp;
                        qa_new_load_time = timestamp;
                        qa_page = 1;
                        qa_end = false;
                        qa_new = false;

                        loadTab(qa_url + qa_page, target);
                    }
                }else if (result === 'other') {
                    app.showMsg(data.message, 1500);
                }
                else if (result === 'failure') {
                    app.showMsg('<?=Yii::t('frontend','operation_confirm_warning_failure')?>', 1500);
                }
            }, "json");
        return false;
    }

    function loadOneCallback(target, data) {
        $("#" + target).append(data);
        loading = false;
    }

    var getTitleUrl="<?=Url::toRoute(['common/get-url-title'])?>";

    function getUrlTitle(obj,formId) {
        $("#" + formId + " #saveBtn").attr({"disabled":"disabled"});
        $("#" + formId + " #saveShareBtn").attr({"disabled":"disabled"});
//        $("#recordWebForm #sorecord-url").change(function () {
//            var title = $("#recordWebForm #sorecord-title").val();
//            if (title === '') {
            app.get(getTitleUrl + "?url=" + $(obj).val(), function (r) {
                $("#" + formId + " #sorecord-title").val(r.result);
                $("#" + formId + " #saveBtn").removeAttr("disabled");
                $("#" + formId + " #saveShareBtn").removeAttr("disabled");
            });
//            }
//        });
    }

    var getPointUrl = "<?=Url::toRoute(['common/get-open-url-point'])?>";
    function openUrl(url, objId, type) {
        app.get(getPointUrl + "?objId=" + objId + "&type=" + type, function (r) {
            if (checkPointResult(r.pointResult)){
                //score-Effect(r.point);
                scorePointEffect(r.pointResult.show_point,r.pointResult.point_name,r.pointResult.available_point);
                setTimeout(function () {
                    window.location.href = url;
                }, 1000);
            }
            else {
                window.location.href = url;
            }
        });
    }

    var getDownloadPointUrl = "<?=Url::toRoute(['common/get-download-point'])?>";
    function openDownloadUrl(objId,type) {
        app.get(getDownloadPointUrl + "?objId=" + objId, function (r) {
            if (checkPointResult(r.pointResult)){
                //score-Effect(r.point);
                scorePointEffect(r.pointResult.show_point,r.pointResult.point_name,r.pointResult.available_point);
                setTimeout(function () {
                    submitDownload(objId,type);
                }, 2200);
            }
            else {
                submitDownload(objId,type);
            }
        });
    }

    var getTagDataUrl = "<?=Url::toRoute(['common/get-tag-data'])?>";

    function saveSetTag() {
        var strIds = new Array();//声明一个存放id的数组
        for (i = 0; i < privateLabel.length; i++) {
            strIds.push(privateLabel[i].id);
        }

        if (strIds.length > 0) {
            $.post("<?=Url::toRoute(['student/set-tag'])?>", {"tags": strIds},
                function (data) {
                    if (data.result === 'success') {
                        app.hideAlert("#create_label");

                        if (checkPointResult(data.pointResult)) {
                            scorePointEffect(data.pointResult.show_point, data.pointResult.point_name, data.pointResult.available_point);
                        }
                        else {
                            app.showMsg('<?=Yii::t('common','operation_success')?>', 1500);
                        }
                    }
                },
                "json");
        }
        else {
            app.showMsg('<?=Yii::t('common','select_{value}',['value'=>Yii::t('common','tag')])?>', 1000);
        }
    }
    var peopleQueryList;

    function showCourseShare(id, title) {
        var temp = title.substring(title.indexOf("<?=Yii::t('common','course')?> [") + 4, title.length - 1);

        $("#course-share-content").val(null);
        $("#course-share-id").val(id);
        $("#course-share-title").val(temp);
        $("#course-share-name").html('<?=Yii::t('common','course_name')?>: 《' + temp + '》');

        app.alertSmall("#newShareCourse");
    }
    function courseShare(){
        var actionUrl = '<?=Yii::$app->urlManager->createUrl(['common/course-share'])?>';
        var id = $("#course-share-id").val();
        if($("#course-share-content").val() == ''){
            $("#course-share-content").focus();
            return;
        }
        var title = $("#course-share-title").val();
        var content = $("#course-share-content").val();
        var users = coursePeopleQueryList.get();

        var data = {'courseId': id, 'title': title, 'content': content, 'users': users};

        $.post(
            actionUrl,
            data
        ).done(function(data) {
            var result = data.result;
            if (result === 'other'){
                app.showMsg(data.message);
            }else if (result === 'failure') {
                app.showMsg('<?=Yii::t('frontend','operation_confirm_warning_failure')?>');
            }else{
                app.hideAlert("#newShareCourse");
                app.showMsg('<?=Yii::t('common','operation_success')?>');
            }
        }).fail(function() {
            app.showMsg("<?=Yii::t('common','operation_confirm_warning_internal_error')?>");
        });
        return true;
    }
</script>
<!-- 分享课程 弹出窗口 -->
<div class="ui modal" id="newShareCourse">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','share_to_{value}',['value'=>Yii::t('frontend','tab_btn_social')])?></h4>
    </div>
    <div class="content" style="padding: 10px;">
        <div class="row" style="padding: 10px;">
            <input type="hidden" id="course-share-id" class="form-control" name="SoShare[obj_id]" value="">
            <input type="hidden" id="course-share-title" class="form-control" name="SoShare[title]" value="">
            <textarea id="course-share-content" class="form-control" name="SoShare[content]" style="width:100%; height: 100px; border:1px solid #eee;" placeholder="<?=Yii::t('frontend','say_something')?>"></textarea>
            <div id="course-share-name" style="width:100%;padding: 10px 0" data-title="">
            </div>
            <br/>
            <table class="tiwen">
                <tr>
                    <td class="mouren" style="text-align:center">@<?=Yii::t('frontend','some_one')?></td>
                    <td class="tdwrapper" style="width:160px">
                        <input id="course_search_people" style="height:30px" type="text" data-url="<?=Yii::$app->urlManager->createUrl('common/search-people')?>?format=new" data-mult="1" />
                    </td>
                    <td style="width:180px">
                        <button type="button" id="shareBtn" class="btn btn-success btn-sm pull-right" onclick="courseShare()"><?=Yii::t('frontend','share')?></button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="c"></div>
</div>
<?php
$form = ActiveForm::begin([
    'id' => 'downloadForm',
    'method' => 'post',
    'action'=>Url::toRoute(['common/download']),
]);
?>
<input type="hidden" id="downloadform-id" class="form-control" name="DownloadForm[id]">
<input type="hidden" id="downloadform-type" class="form-control" name="DownloadForm[type]">
<?php ActiveForm::end(); ?>
<div class="ui modal" id="create_label">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','add_{value}',['value'=>Yii::t('frontend','personal_tag')])?></h4>
    </div>
    <div class="content">
        <div class="privateLabel">
            <div class="row">
            </div>
            <p class="tag_tip" style="text-align: center; color: red; display: none; margin-top: 10px; margin-bottom: -10px"><?=Yii::t('frontend','most_{value}_tag',['value'=> 20])?></p>
        </div>
        <div class="publicLabel">
            <div class="searchLabel">
                <input type="text" class="searchArea"/>
                <a href="###" class="searchBtn"><?=Yii::t('common','search')?></a>
                <a href="###" class="changeLabel" style="float: right;"><?=Yii::t('frontend','change_all')?></a>
            </div>
            <div class="resultPanel">
                <div class="row">
                </div>
            </div>
        </div>

    </div>
    <div class="centerBtnArea groupAddMember">
        <a href="javascript:void(0);" class="btn centerBtn" onclick="saveSetTag()"><?=Yii::t('common','save')?></a>
    </div>
    <div class="c"></div>
</div>
<script src="/static/frontend/js/label.js" type="text/javascript"></script>
