<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/30
 * Time: 15:32
 */
use common\models\learning\LnComponent;
use components\widgets\TBreadcrumbs;
use frontend\widgets\ContinueLearning;
use frontend\widgets\CourseLibrary;
use frontend\widgets\QuestionArea;
use frontend\widgets\RecommendCourse;
use frontend\widgets\UserPanel;
use yii\helpers\Url;
$this->pageTitle = Yii::t('frontend', 'top_mycourse_text');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;

$current_time = time();
?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-4">
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
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                <li role="presentation"><a href="#allLesson" aria-controls="allLesson" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'signup_yes')?>(<?=$reg_count?>)</a></li>
                <li role="presentation"><a href="#finished" aria-controls="finished" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'page_lesson_hot_tab_3')?>(<?=$done_count?>)</a></li>
                <li role="presentation"><a href="#unfinished" aria-controls="unfinished" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'page_lesson_hot_tab_2')?>(<?=$doing_count?>)</a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane" id="allLesson">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="input-group" style="width:260px; float:right; margin:10px 0;">
                                        <input id="all_key" type="text" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common', 'course_name')])?>">
                                        <span class="input-group-btn"><button id="all_btn" class="btn btn-success btn-sm search_btn" type="button"><?=Yii::t('frontend', 'top_search_text')?></button></span>
                                    </div>
                                </div>
                                <div id="allLesson_list">
                                </div>
                            </div>
                            <div class="row" style="postion:relative; text-align:center;">
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
                <div role="tabpanel" class="tab-pane" id="finished">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="input-group" style="width:260px; float:right; margin:10px 0;">
                                        <input id="finished_key" type="text" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common', 'course_name')])?>">
                                        <span class="input-group-btn"><button id="finished_btn" class="btn btn-success btn-sm search_btn" type="button"><?=Yii::t('frontend', 'top_search_text')?><?= Yii::t('frontend', 'top_search_text') ?></button></span>
                                    </div>
                                </div>
                                <div id="finished_list">
                                </div>
                            </div>
                            <div class="row" style="postion:relative; text-align:center;">
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
                <div role="tabpanel" class="tab-pane" id="unfinished">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="input-group" style="width:260px; float:right; margin:10px 0;">
                                        <input id="unfinished_key" type="text" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common', 'course_name')])?>">
                                        <span class="input-group-btn"><button id="unfinished_btn" class="btn btn-success btn-sm search_btn" type="button"><?=Yii::t('frontend', 'top_search_text')?><?= Yii::t('frontend', 'top_search_text') ?></button></span>
                                    </div>
                                </div>
                                <div id="unfinished_list">
                                </div>
                            </div>
                            <div class="row" style="postion:relative; text-align:center;">
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
    </div>
</div>

<!-- 成绩单弹出窗口 -->
<div class="modal ui bs-example-modal-lg" id="scoreDetails" style="padding-left: 0px;">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'transcript')?></h4>
    </div>
    <div class="content">
        <div class="panel-body" id="score_list">
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

<div class="ui modal" id="view_mod_detail">
</div>

<!-- 作业弹出窗口 -->
<div class="modal ui bs-example-modal-lg" id="view_homework_detail" style="padding-left: 0px;">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'homework_result')?></h4>
    </div>
    <div id="homework_detail">
    </div>
</div>

<script>
    var loading = true;

    var type = '';

    var all_page = 1;
    var all_url = "<?=Url::toRoute(['student/get-course-list','type'=>'all','current_time'=> $current_time])?>" + "&page=";
    var all_key = '';
    var all_end = false;
    var all_read = false;
    var all_load_time = '<?=$current_time?>';

    var finished_page = 1;
    var finished_url = "<?=Url::toRoute(['student/get-course-list','type'=>'finished','current_time'=> $current_time])?>" + "&page=";
    var finished_key = '';
    var finished_end = false;
    var finished_read = false;
    var finished_load_time = '<?=$current_time?>';

    var unfinished_page = 1;
    var unfinished_url = "<?=Url::toRoute(['student/get-course-list','type'=>'unfinished','current_time'=> $current_time])?>" + "&page=";
    var unfinished_key = '';
    var unfinished_end = false;
    var unfinished_read = false;
    var unfinished_load_time = '<?=$current_time?>';

    var score_list_url = "<?=Url::toRoute(['common/person-score-list', 'showHomework' => true]);?>";

    $(function () {
        var tab = getUrlParam('tab');
        if (tab !== null && tab !== '') {
            $("a[aria-controls=" + tab + "]").parent().addClass('active');
            $("#" + tab).addClass('active');
            readTab(tab);
        }
        else {
            $("#myTab li:first").addClass('active');
            $(".tab-pane:first").addClass('active');
            readTab('allLesson');
        }

        $("#myTab a").click(function(){
            var tab=$(this).attr("aria-controls");
            readTab(tab);
        });

        $(".search_btn").bind('click',function(){
            var btnId = $(this).attr('id');
            if (btnId === 'all_btn') {
                all_key = $("#all_key").val().trim();
                $("#allLesson_list").empty();
                all_page = 1;
                all_end = false;
                loadTab(all_url + all_page + '&key=' + all_key, 'allLesson_list');
            }
            else if (btnId === 'finished_btn') {
                finished_key = $("#finished_key").val().trim();
                $("#finished_list").empty();
                finished_page = 1;
                finished_end = false;
                loadTab(finished_url + finished_page + '&key=' + finished_key, 'finished_list');
            }
            else if (btnId === 'unfinished_btn') {
                unfinished_key = $("#unfinished_key").val().trim();
                $("#unfinished_list").empty();
                unfinished_page = 1;
                unfinished_end = false;
                loadTab(unfinished_url + unfinished_page + '&key=' + unfinished_key, 'unfinished_list');
            }
        });

        $('.tab-content').delegate('.score', 'click', function () {
            var cid = $(this).attr("data-cid");
            $('#score_list').empty();
            loadScore(score_list_url + "&cid=" + cid);
            app.alertWide('#scoreDetails');
        });

        $(window).scroll(function () {
            var bot = 100; // 底部距离的高度
            if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                if (type == 'allLesson' && !all_end) {
                    loading = true;
                    all_page++;
                    loadTab(all_url + all_page + '&key=' + all_key, 'allLesson_list');
                }
                else if (type == 'finished' && !finished_end) {
                    loading = true;
                    finished_page++;
                    loadTab(finished_url + finished_page + '&key=' + finished_key, 'finished_list');
                }
                else if (type == 'unfinished' && !unfinished_end) {
                    loading = true;
                    unfinished_page++;
                    loadTab(unfinished_url + unfinished_page + '&key=' + unfinished_key, 'unfinished_list');
                }
            }
        });
    });

    function loadScore(ajaxUrl) {
        $('#scoreDetails .loadingWaiting').removeClass('hide');
        $('#score_list').empty();
        app.get(ajaxUrl, function (r) {
            if (r) {
                bind('score_list', r);
            }
        });
    }
    function bind(target, data) {
        $('#scoreDetails .loadingWaiting').addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            loadScore(url);
            return false;
        });
    }

    function loadTab(ajaxUrl, container) {
        $("#" + container).parent().next().find(".loadingWaiting").removeClass('hide');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $("#" + target).parent().next().find(".loadingWaiting").addClass('hide');
        $("#" + target).append(data);
        loading = false;
        var count=$(data).filter('.myLessonList').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'allLesson_list') {
                all_end = true;
            }
            else if (target == 'finished_list') {
                finished_end = true;
            }
            else if (target == 'unfinished_list') {
                unfinished_end = true;
            }
        }
    }

    function readTab(tab)
    {
        type=tab;
        if (!all_read && tab === 'allLesson') {
            all_read=true;
            loadTab(all_url + all_page, 'allLesson_list');
        }
        else if (!finished_read && tab === 'finished') {
            finished_read=true;
            loadTab(finished_url + finished_page, 'finished_list');
        }
        else if (!unfinished_read && tab === 'unfinished') {
            unfinished_read=true;
            loadTab(unfinished_url + unfinished_page, 'unfinished_list');
        }
    }

    function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg);  //匹配目标参数
        if (r != null) return unescape(r[2]); return null; //返回参数值
    }

    function LoadScoreDetail(componentCode, courseId, modId, modResId, objectId, companyId) {
        if (componentCode === "<?=LnComponent::COMPONENT_CODE_INVESTIGATION ?>") {
            //查看调查
            var modalId = "view_mod_detail";

            var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('common/person-question-detail')?>";
            ajaxUrl = urlreplace(ajaxUrl, 'courseid', courseId);
            ajaxUrl = urlreplace(ajaxUrl, 'modresid', modResId);
            ajaxUrl = urlreplace(ajaxUrl, 'inkid', objectId);
        }
        else if (componentCode === "<?=LnComponent::COMPONENT_CODE_HOMEWORK ?>") {
            //查看调查
            var modalId = "view_homework_detail";

            var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('resource/player/homework-result-view')?>";
            ajaxUrl = urlreplace(ajaxUrl, 'homeworkId', objectId);
            ajaxUrl = urlreplace(ajaxUrl, 'userId', "<?=Yii::$app->user->getId() ?>");
            ajaxUrl = urlreplace(ajaxUrl, 'companyId', companyId);
            ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
            ajaxUrl = urlreplace(ajaxUrl, 'modId', modId);
            ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);

            app.get(ajaxUrl, function (r) {
                $('#homework_detail').html(r);
                app.alertWideAgain('#' + modalId);
            });
            return;
        }
        else if (componentCode === "<?=LnComponent::COMPONENT_CODE_EXAMINATION ?>") {
            var modalId = "examination_log";
            var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('exam-manage-main/view-log')?>";
            ajaxUrl = urlreplace(ajaxUrl, 'id', objectId);
            ajaxUrl = urlreplace(ajaxUrl, 'userId', "<?=Yii::$app->user->getId() ?>");
            ajaxUrl = urlreplace(ajaxUrl, 'companyId', companyId);
            ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
            ajaxUrl = urlreplace(ajaxUrl, 'modId', modId);
            ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        }
        else {
            var modalId = "courseware";

            var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('teacher/item-complete-info')?>";
            ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
            ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        }
        modalTotalClear(modalId);
        app.get(ajaxUrl, function (r) {
            $('#' + modalId).html(r);
            app.alertWideAgain('#' + modalId);
        });
    }
</script>
<div class="ui modal" id="examination_log"></div>