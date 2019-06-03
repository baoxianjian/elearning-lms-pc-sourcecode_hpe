<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/2
 * Time: 10:35
 */

use frontend\widgets\CourseLibrary;
use frontend\widgets\QuestionArea;
use frontend\widgets\RecommendCourse;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use frontend\widgets\UserPanel;
use frontend\widgets\ContinueLearning;
use frontend\widgets\QuickChannel;

$this->pageTitle = Yii::t('frontend','my_care');// Yii::t('frontend', 'page_lesson_hot_title');
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
                        <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('frontend','attention_tree')?>
                    </div>
                    <div class="panel-body textCenter">
                        <div class="filterBtn">
                            <div class="btn-group timeScope pull-left">
                                <select id="select_time" class="form-control timelineFilter">
                                    <option value="1"><?=Yii::t('frontend','one_week')?></option>
                                    <option value="2"><?=Yii::t('frontend','one_month')?></option>
                                    <option value="3"><?=Yii::t('frontend','three_month')?></option>
                                    <option value="0"><?=Yii::t('frontend','time_free')?></option>
                                </select>
                            </div>
                            <a href="javascript:void(0);" id="btnCate1" class="btnFilter activeBtn"><?=Yii::t('common','all_data')?></a>
                            <a href="javascript:void(0);" id="btnCate2" class="btnFilter"><?=Yii::t('frontend','care_man')?></a>
                            <a href="javascript:void(0);" id="btnCate3" class="btnFilter"><?=Yii::t('frontend','care_question')?></a>
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
                            <p><?=Yii::t('frontend','loading')?>...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var loading = true;
    var current_tab = 'btnCate1';
    var skip_flag = false;

    var user_page = 1;
    var user_url = "<?=Url::toRoute(['student/attention-list','current_time'=> $current_time,'filter'=>1])?>" + "&page=";
    var user_one_url = "<?=Url::toRoute(['student/get-attention-one','current_time'=> $current_time,'filter'=>1])?>" + "&page=";
    var user_end = false;
    var user_time = 1;

    var question_page = 1;
    var question_url = "<?=Url::toRoute(['student/attention-list','current_time'=> $current_time,'filter'=>2])?>" + "&page=";
    var question_one_url = "<?=Url::toRoute(['student/get-attention-one','current_time'=> $current_time,'filter'=>2])?>" + "&page=";
    var question_end = false;
    var question_time = 1;

    var all_page = 1;
    var all_url = "<?=Url::toRoute(['student/attention-list','current_time'=> $current_time,'filter'=>3])?>" + "&page=";
    var all_one_url = "<?=Url::toRoute(['student/get-attention-one','current_time'=> $current_time,'filter'=>3])?>" + "&page=";
    var all_end = false;
    var all_time = 1;

    $(document).ready(
        function () {
            loadTab(all_url + all_page + '&time=' + all_time, 'timeline1', true);
            loadTab(user_url + user_page + '&time=' + user_time, 'timeline2', true);
            loadTab(question_url + question_page + '&time=' + question_time, 'timeline3', true);

            $("#select_time").change(function() {
                var time = $(this).val();

                if (current_tab == 'btnCate1') {
                    loading = true;
                    all_time = time;
                    all_page = 1;
                    all_end = false;

                    loadTab(all_url + all_page + '&time=' + all_time, 'timeline1', true);
                }
                if (current_tab == 'btnCate2') {
                    loading = true;
                    user_time = time;
                    user_page = 1;
                    user_end = false;
                    loadTab(user_url + user_page + '&time=' + user_time, 'timeline2', true);
                }
                if (current_tab == 'btnCate3') {
                    loading = true;
                    question_time = time;
                    question_page = 1;
                    question_end = false;
                    loadTab(question_url + question_page + '&time=' + question_time, 'timeline3', true);
                }
            });

            $(window).scroll(function () {
                var bot = 100; //bot是底部距离的高度
                if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                    //当底部基本距离+滚动的高度〉=文档的高度-窗体的高度时；
                    //我们需要去异步加载数据了
                    if (current_tab == 'btnCate1' && !all_end) {
                        loading = true;
                        all_page++;
                        loadTab(all_url + all_page + '&time=' + all_time, 'timeline1', false);
                    }
                    if (current_tab == 'btnCate2' && !user_end) {
                        loading = true;
                        user_page++;
                        loadTab(user_url + user_page + '&time=' + user_time, 'timeline2', false);
                    }
                    if (current_tab == 'btnCate3' && !question_end) {
                        loading = true;
                        question_page++;
                        loadTab(question_url + question_page + '&time=' + question_time, 'timeline3', false);
                    }
                }
            });
        });
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
                user_end = true;
            }
            else if (target == 'timeline3') {
                question_end = true;
            }
        }
        if (skip_flag) {
            skip_flag = false;
        }
    }

    function cancelCare(obj, objId, type) {
        if (type == 'q') {
            var url = "<?=Url::toRoute(['question/care'])?>";
            $.post(url, {"qid": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other'){
                        app.showMsg(data.message);
                    }else if (result === 'failure') {
                        app.alert("#newFollow");
                        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common','operation_confirm_warning_failure')?></div>');
                    }else{
                        if($(obj).text() == '<?=Yii::t('common','attention')?>'){
                            $(obj).text('<?=Yii::t('common','canel_attention')?>');
                            app.alert("#newFollow");
                            $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('frontend','attention_sucess')?></div>');
                        }else{
                            $(obj).text('<?=Yii::t('common','attention')?>');
                            app.alert("#newFollow");
                            $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common','cancel_attention')?></div>');
                            $(obj).parent().parent().remove();
                            skip_flag = true;
                            if (current_tab == 'btnCate1' && !all_end) {
                                loading = true;
                                loadTab(all_one_url + all_page + '&time=' + all_time, 'timeline1', false);
                            }
                            if (current_tab == 'btnCate2' && !user_end) {
                                loading = true;
                                loadTab(user_one_url + user_page + '&time=' + user_time, 'timeline2', false);
                            }
                            if (current_tab == 'btnCate3' && !question_end) {
                                loading = true;
                                loadTab(question_one_url + question_page + '&time=' + question_time, 'timeline3', false);
                            }
                        }
                    }
                }, "json");
        }
        else if (type == 'u') {
            var url = "<?=Url::toRoute(['common/attention-user'])?>";
            $.post(url, {"uid": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other'){
                        app.showMsg(data.message);
                    }else if (result === 'failure') {
                        app.alert("#newFollow");
                        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common','operation_confirm_warning_failure')?></div>');
                    }else{
                        if($(obj).text() == '<?=Yii::t('common','attention')?>'){
                            $(obj).text('<?=Yii::t('common','canel_attention')?>');
                            app.alert("#newFollow");
                            $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('frontend','attention_sucess')?></div>');
                        }else{
                            $(obj).text('<?=Yii::t('common','attention')?>');
                            app.alert("#newFollow");
                            $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common','cancel_attention')?></div>');
                            $(obj).parent().parent().remove();
                            skip_flag = true;
                            if (current_tab == 'btnCate1' && !all_end) {
                                loading = true;
                                loadTab(all_one_url + all_page + '&time=' + all_time, 'timeline1', false);
                            }
                            if (current_tab == 'btnCate2' && !user_end) {
                                loading = true;
                                loadTab(user_one_url + user_page + '&time=' + user_time, 'timeline2', false);
                            }
                            if (current_tab == 'btnCate3' && !question_end) {
                                loading = true;
                                loadTab(question_one_url + question_page + '&time=' + question_time, 'timeline3', false);
                            }
                        }
                    }
                }, "json");
        }
        return false;
    }
</script>
<!-- 时间树切换组件 -->
<script>
    // 设置按钮的id名称
    var btnNum = $('.btnFilter').length
    for (i = 0; i < btnNum; i++) {
        $($('.btnFilter')[i]).attr("data-num", i)
    }

    //为每个按钮添加事件
    $('.btnFilter').bind('click', function() {
        var btnId=$(this).attr('id');
        var actBtn = $(this).attr("data-num")
        // 给选中的按钮加上 activeBtn 样式
        $('.btnFilter').removeClass('activeBtn')
        $(this).addClass('activeBtn')

        $('.timeline').addClass('hidden')
        $($('.timeline')[actBtn]).removeClass('hidden');

        current_tab = btnId;

        var cur_time;
        if (current_tab == 'btnCate1') {
            cur_time = all_time;
        }
        else if (current_tab == 'btnCate2') {
            cur_time = user_time;
        }
        else if (current_tab == 'btnCate3') {
            cur_time = question_time;
        }

        $("#select_time").val(cur_time);
    })
</script>
<!-- 收藏的弹出窗口 -->
<div class="ui modal" id="newFollow">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','prompt')?></h4>
    </div>
    <div class="body"></div>
</div>