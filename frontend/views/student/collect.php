<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/21
 * Time: 10:54
 */
use frontend\widgets\CourseLibrary;
use frontend\widgets\QuestionArea;
use frontend\widgets\RecommendCourse;
use frontend\widgets\UserPanel;
use frontend\widgets\ContinueLearning;
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use frontend\widgets\QuickChannel;
use yii\helpers\html;
use yii\widgets\ActiveForm;

$this->pageTitle = Yii::t('frontend', 'my_favorite');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;

$current_time = time();
?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-4 wideScreenBlock">
            <?
            $userPanel = UserPanel::widget();
            $continueLearning = ContinueLearning::widget();
            $courseLibrary = CourseLibrary::widget();
            $questionArea = QuestionArea::widget();
            $recommendCourse = RecommendCourse::widget();
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
            <div class="row">
                <div class="panel panel-default hotNews">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-dashboard"></i><?=Yii::t('frontend', 'collect_tree')?>
                    </div>
                    <div class="panel-body textCenter">
                        <div class="filterBtn">
                            <div class="btn-group timeScope pull-left">
                                <select id="select_time" class="form-control timelineFilter">
                                    <option value="1"><?=Yii::t('frontend', 'one_week')?></option>
                                    <option value="2"><?=Yii::t('frontend', 'one_month')?></option>
                                    <option value="3"><?=Yii::t('frontend', 'three_month')?></option>
                                    <option value="0"><?=Yii::t('frontend', 'time_free')?></option>
                                </select>
                            </div>
                            <a href="javascript:void(0);" id="btnCate1" class="btnFilter activeBtn"><?=Yii::t('common', 'all_data')?></a>
                            <a href="javascript:void(0);" id="btnCate2" class="btnFilter"><?=Yii::t('frontend', 'question_answer')?></a>
                            <a href="javascript:void(0);" id="btnCate3" class="btnFilter"><?=Yii::t('common', 'course')?></a>
                        </div>
                        <div id="timeline1" class="timeline miniLine">
                        </div>
                        <div id="timeline2" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline3" class="timeline miniLine hidden">
                        </div>
                        <div class="loadingWaiting hide">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <p><?=Yii::t('frontend', 'loading')?>...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var loading = true;
    var skip_flag = false;
    var current_tab = 'btnCate1';

    var all_page = 1;
    var all_url = "<?=Url::toRoute(['student/collect-list','current_time'=> $current_time,])?>" + "&page=";
    var all_one_url = "<?=Url::toRoute(['student/collect-one','current_time'=> $current_time,])?>" + "&page=";
    var all_end = false;
    var all_time = 1;

    var qa_page = 1;
    var qa_url = "<?= Url::toRoute(['student/collect-list','current_time'=> $current_time,'type'=>2]) ?>" + "&page=";
    var qa_one_url = "<?= Url::toRoute(['student/collect-one','current_time'=> $current_time,'type'=>2]) ?>" + "&page=";
    var qa_end = false;
    var qa_time = 1;
    var qa_read = false;

    var course_page = 1;
    var course_url = "<?= Url::toRoute(['student/collect-list','current_time'=> $current_time,'type'=>3]) ?>" + "&page=";
    var course_one_url = "<?= Url::toRoute(['student/collect-one','current_time'=> $current_time,'type'=>3]) ?>" + "&page=";
    var course_end = false;
    var course_time = 1;
    var course_read = false;

    $(document).ready(
        function () {
            $("#select_time").change(function(){
                var time=$(this).val();

                loading = true;

                if (current_tab == 'btnCate1') {
                    all_time = time;
                    all_page = 1;
                    all_end = false;
                    $("#timeline1").empty();
                    loadTab(all_url + all_page + '&time=' + all_time, 'timeline1', true);
                }
                else if (current_tab == 'btnCate2') {
                    qa_time = time;
                    qa_page = 1;
                    qa_end = false;
                    $("#timeline2").empty();
                    loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline2', true);
                }
                else if (current_tab == 'btnCate3') {
                    course_time = time;
                    course_page = 1;
                    course_end = false;
                    $("#timeline3").empty();
                    loadTab(course_url + course_page + '&time=' + course_time, 'timeline3', true);
                }
            });


            $(".loadingWaiting").removeClass('hide');
            loadTab(all_url + all_page + '&time=' + all_time,'timeline1', true);
            $(window).scroll(function () {
                var bot = 100; //bot是底部距离的高度
                if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                    if (current_tab == 'btnCate1' && !all_end) {
                        loading = true;
                        all_page++;
                        loadTab(all_url + all_page + '&time=' + all_time, 'timeline1', false);
                    }
                    else if (current_tab == 'btnCate2' && !qa_end) {
                        loading = true;
                        qa_page++;
                        loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline2', false);
                    }
                    else if (current_tab == 'btnCate3' && !course_end) {
                        loading = true;
                        course_page++;
                        loadTab(course_url + course_page + '&time=' + course_time, 'timeline3', false);
                    }
                }
            });
        });
    var btnFilter = $(".btnFilter"),
        tab1 = $("#timeline1"),
        tab2 = $("#timeline2"),
        tab3 = $("#timeline3");

    btnFilter.bind("click", function () {
        var b = $(this);
        btnFilter.removeClass("activeBtn");
        b.addClass("activeBtn");
        var btnId = $(this).attr('id');
        var tabId = btnId.replace("btnCate", "timeline");

        current_tab = btnId;

        if (tabId == 'timeline2' && !qa_read) {
            qa_read = true;
            $(".loadingWaiting").removeClass('hide');
            loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline2', true);
        }
        else if (tabId == 'timeline3' && !course_read) {
            course_read = true;
            $(".loadingWaiting").removeClass('hide');
            loadTab(course_url + course_page + '&time=' + course_time, 'timeline3', true);
        }

        var cur_time;
        if (current_tab == 'btnCate1') {
            cur_time = all_time;
        }
        else if (current_tab == 'btnCate2') {
            cur_time = qa_time;
        }
        else if (current_tab == 'btnCate3') {
            cur_time = course_time;
        }

        $("#select_time").val(cur_time);

        if ($("#" + tabId).hasClass("hidden")) {
            hiddenAll();
            $("#" + tabId).removeClass("hidden");
        }
    });

    function hiddenAll() {
        tab1.addClass("hidden");
        tab2.addClass("hidden");
        tab3.addClass("hidden");
    }

    function loadTab(ajaxUrl, container, is_clear) {
        $(".loadingWaiting").removeClass('hide');
        if (is_clear) {
            $("#" + container).empty();
        }
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $(".loadingWaiting").addClass('hide');
        $("#" + target).append(data);
        loading = false;
        var count = $(data).filter('.timeline-item').length;
        if (!skip_flag && (data == null || data == '' || count < 10)) {
            if (target == 'timeline1') {
                all_end = true;
            }
            else if (target == 'timeline2') {
                qa_end = true;
            }
            else if (target == 'timeline3') {
                course_end = true;
            }
        }
        if (skip_flag) {
            skip_flag = false;
        }
    }

    function cancelCollect(obj,objId, type){
        if (type == 'c') {
            var url = "<?=Url::toRoute(['resource/course/collection'])?>";
            $.post(url, {"obj_id": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other'){
                        app.showMsg(data.message);
                    }else if (result === 'failure') {
                        app.alert("#newFollow");
                        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_confirm_warning_failure')?></div>');
                    }else{
                        app.alert("#newFollow");
                        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('frontend', 'cancel_sucess')?></div>');
                        $(obj).parent().parent().remove();
                        skip_flag = true;
                        if (current_tab == 'btnCate1' && !all_end) {
                            loading = true;
                            loadTab(all_one_url + all_page + '&time=' + all_time, 'timeline1', false);
                        }
                        if (current_tab == 'btnCate2' && !qa_end) {
                            loading = true;
                            loadTab(qa_one_url + qa_page + '&time=' + qa_time, 'timeline2', false);
                        }
                        if (current_tab == 'btnCate3' && !course_end) {
                            loading = true;
                            loadTab(course_one_url + course_page + '&time=' + course_time, 'timeline3', false);
                        }
                    }
                }, "json");

            return false;
        }
        else if (type == 'q') {
            var url = "<?=Url::toRoute(['question/collect'])?>";
            $.post(url, {"qid": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other'){
                        app.showMsg(data.message);
                    }else if (result === 'failure') {
                        app.alert("#newFollow");
                        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_confirm_warning_failure')?></div>');
                    }else{
                        app.alert("#newFollow");
                        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('frontend', 'cancel_sucess')?></div>');
                        $(obj).parent().parent().remove();
                        skip_flag = true;
                        if (current_tab == 'btnCate1' && !all_end) {
                            loading = true;
                            loadTab(all_one_url + all_page + '&time=' + all_time, 'timeline1', false);
                        }
                        if (current_tab == 'btnCate2' && !qa_end) {
                            loading = true;
                            loadTab(qa_one_url + qa_page + '&time=' + qa_time, 'timeline2', false);
                        }
                        if (current_tab == 'btnCate3' && !course_end) {
                            loading = true;
                            loadTab(course_one_url + course_page + '&time=' + course_time, 'timeline3', false);
                        }
                    }
                }, "json");

            return false;
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