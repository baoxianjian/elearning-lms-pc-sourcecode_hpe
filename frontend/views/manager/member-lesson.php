<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/12/27
 * Time: 11:31
 */
use common\models\learning\LnComponent;
use common\helpers\TStringHelper;
use components\widgets\TBreadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

$this->pageTitle = Yii::t('frontend', 'home_myteam_text');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = ['url' => Yii::$app->urlManager->createUrl('manager/my-team'), 'label' => $this->pageTitle];
$this->params['breadcrumbs'][] = Yii::t('frontend', '{value}_score_record',['value'=>$real_name]);

$current_time = time();
?>
<style>
    .myLessonList {
        width: 100%;
    }
    .popInfo .a {height:auto !important; text-decoration: none;}
</style>
<?=$this->render('/common/point-trans')?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-8">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                <li role="presentation" class="active"><a href="#tab_course" aria-controls="tab_course" role="tab" data-toggle="tab"><?= Yii::t('common', 'course') ?></a></li>
                <li role="presentation"><a href="#tab_exam" aria-controls="tab_exam" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'exam') ?></a></li>
                <li role="presentation"><a href="#tab_survey" aria-controls="tab_survey" role="tab" data-toggle="tab"><?= Yii::t('common', 'investigation') ?></a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="tab_course">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="actionBar">
                                        <form class="form-inline pull-right" style="width:100%">
                                            <div class="form-group" style="width:100%">
                                                <div class="form-group pull-left">
                                                    <select class="form-control" id="course_search_type">
                                                        <option value="all"><?=Yii::t('frontend', 'signup_yes')?></option>
                                                        <option value="finished"><?=Yii::t('frontend', 'complete_status_done')?></option>
                                                        <option value="unfinished"><?=Yii::t('frontend', 'tab_btn_todo')?></option>
                                                    </select>
                                                </div>
                                                <button id="course_search_btn" type="button" class="btn btn-success pull-right"><?= Yii::t('frontend', 'top_search_text') ?></button>
                                                <input id="course_search_key" type="text" class="form-control pull-right" placeholder="<?=Yii::t('frontend', 'input_keyword')?>">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div id="course_list">
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
                <div role="tabpanel" class="tab-pane" id="tab_exam">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="actionBar">
                                        <form class="form-inline pull-right" style="width:100%">
                                            <div class="form-group" style="width:100%">
                                                <div class="form-group pull-left">
                                                    <select class="form-control" id="exam_search_type">
                                                        <option value="all"><?=Yii::t('frontend', 'all_record')?></option>
                                                        <option value="finished"><?=Yii::t('frontend', 'complete_status_done')?></option>
                                                        <option value="unfinished"><?=Yii::t('frontend', 'page_lesson_hot_tab_2')?></option>
                                                    </select>
                                                </div>
                                                <button id="exam_search_btn" type="button" class="btn btn-success pull-right"><?= Yii::t('frontend', 'top_search_text') ?></button>
                                                <input id="exam_search_key" type="text" class="form-control pull-right" placeholder="<?=Yii::t('frontend', 'input_keyword')?>">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div id="exam_list">
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
                <div role="tabpanel" class="tab-pane" id="tab_survey">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="actionBar">
                                        <form class="form-inline pull-right" style="width:100%">
                                            <div class="form-group" style="width:100%">
                                                <div class="form-group pull-left">
                                                    <select class="form-control" id="survey_search_type">
                                                        <option value="all"><?=Yii::t('frontend', 'all_record')?></option>
                                                        <option value="0"><?= Yii::t('frontend', 'questionnaire') ?></option>
                                                        <option value="1"><?= Yii::t('frontend', 'vote') ?></option>
                                                    </select>
                                                </div>
                                                <div class="form-group pull-left">
                                                    <select class="form-control" id="survey_search_status">
                                                        <option value="all"><?=Yii::t('frontend', 'all_record')?></option>
                                                        <option value="finished"><?=Yii::t('frontend', 'complete_status_done')?></option>
                                                        <option value="unfinished"><?=Yii::t('frontend', 'page_lesson_hot_tab_2')?></option>
                                                    </select>
                                                </div>
                                                <button id="survey_search_btn" type="button" class="btn btn-success pull-right"><?= Yii::t('frontend', 'top_search_text') ?></button>
                                                <input id="survey_search_key" type="text" class="form-control pull-right" placeholder="<?=Yii::t('frontend', 'input_keyword')?>">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div id="survey_list">
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
        <div class="col-md-4">
            <div role="tabpanel" class="topList">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs hotNews " role="tablist" id="myTab">
                    <li role="presentation" class="active"><a href="#topLearner" aria-controls="topLearner" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'list_learning')?></a></li>
                    <li role="presentation"><a href="#topAnswer" aria-controls="topAnswer" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'list_question')?></a></li>
                    <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'list_share')?></a></li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="topLearner">
                        <div class="panel panel-default ">
                            <div class="panel-body">
                                <ul class="thumbList">
                                    <? if($courseTop): foreach($courseTop as $v):?>
                                        <li class="popContainer" style="position: relative">
                                            <a href="<?= Yii::$app->urlManager->createUrl(['manager/member-lesson', 'id' => $v['user_id']]) ?>"><img src="<?=TStringHelper::Thumb($v['thumb'], $v['gender'])?>" alt="scoreList1"  />
                                                <p class="name" title="<?=$v['email']?>"><?=Html::encode($v['real_name'])?></p>
                                            </a>
                                            <?if($uid != $v['user_id']):?>
                                                <ul class="popPanel" style="right: 200px;top:10px;">
                                                    <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$v['user_id']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                                                </ul>
                                            <?endif;?>
                                            <div class="scoresBlock">
                                                <p class="scores"><?=Yii::t('frontend', 'learning_task_year_{value}',['value'=>'<strong>'.$v['y_count'].'</strong>'])?></p>
                                                <p class="scores"><?=Yii::t('frontend', 'learning_task_total_{value}',['value'=>$v['a_count']])?></p>
                                            </div>
                                        </li>
                                    <? endforeach; else:?>
                                        <div class="centerBtnArea noData ">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                            <p><?=Yii::t('common', 'no_data')?></p>
                                        </div>
                                    <? endif; ?>

                                </ul>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="topAnswer">
                        <div class="panel panel-default ">
                            <div class="panel-body">
                                <ul class="thumbList">
                                    <? if($answerTop): foreach($answerTop as $v):?>
                                        <li class="popContainer" style="position: relative">
                                            <a href="<?= Yii::$app->urlManager->createUrl(['manager/member-lesson', 'id' => $v['user_id']]) ?>"><img src="<?=TStringHelper::Thumb($v['thumb'], $v['gender'])?>" alt="scoreList1" />
                                                <p class="name" title="<?=$v['email']?>"><?=Html::encode($v['real_name'])?></p>
                                            </a>
                                            <?if($uid != $v['user_id']):?>
                                                <ul class="popPanel" style="right: 200px;top:10px;">
                                                    <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$v['user_id']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                                                </ul>
                                            <?endif;?>
                                            <div class="scoresBlock">
                                                <p class="scores"><?=Yii::t('frontend', 'learning_question_year_{value}',['value'=>$v['y_count']])?></p>
                                                <p class="scores"><?=Yii::t('frontend', 'learning_question_total_{value}',['value'=>$v['a_count']])?></p>
                                            </div>
                                        </li>
                                    <? endforeach; else:?>
                                        <div class="centerBtnArea noData ">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                            <p><?=Yii::t('common', 'no_data')?></p>
                                        </div>
                                    <? endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="messages">
                        <div class="panel panel-default ">
                            <div class="panel-body">
                                <ul class="thumbList">
                                    <? if($shareTop): foreach($shareTop as $v):?>
                                        <li class="popContainer" style="position: relative">
                                            <a href="<?= Yii::$app->urlManager->createUrl(['manager/member-lesson', 'id' => $v['user_id']]) ?>"><img src="<?=TStringHelper::Thumb($v['thumb'], $v['gender'])?>" alt="scoreList1"  />
                                                <p class="name" title="<?=$v['email']?>"><?=Html::encode($v['real_name'])?></p>
                                            </a>
                                            <?if($uid != $v['user_id']):?>
                                                <ul class="popPanel" style="right: 200px;top:10px;">
                                                    <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$v['user_id']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                                                </ul>
                                            <?endif;?>
                                            <div class="scoresBlock">
                                                <p class="scores"><?=Yii::t('frontend', 'learning_share_year_{value}',['value'=>$v['y_count']])?></p>
                                                <p class="scores"><?=Yii::t('frontend', 'learning_share_total_{value}',['value'=>$v['a_count']])?></p>
                                            </div>
                                        </li>
                                    <? endforeach; else:?>
                                        <div class="centerBtnArea noData ">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                            <p><?=Yii::t('common', 'no_data')?></p>
                                        </div>
                                    <? endif;?>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel panel-default finishLearn">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend', 'team_member')?>
                    <a class="pull-right" href="javascript:void(0);" role="button"><?=Yii::t('frontend', 'more')?> &raquo;</a>
                </div>
                <div class="panel-body">
                    <ul class="thumbList popOverPanel"  style="display: none;">
                        <?if($team_users): foreach($team_users as $user):?>
                            <li>
                                <a  href="<?= Yii::$app->urlManager->createUrl(['manager/member-lesson', 'id' => $user['kid']]) ?>"><img src="<?= TStringHelper::Thumb($user['thumb'], $user['gender']) ?>" />
                                    <p class="name" title="<?=$user['email']?>"><?= Html::encode($user['real_name'])?></p>
                                </a>
                                <div class="popInfo">
                                    <a class="a" style="margin:0" href="<?= Yii::$app->urlManager->createUrl(['manager/member-lesson', 'id' => $user['kid']]) ?>">
                                        <p><?=Yii::t('frontend', 'learning_task_year_{value}',['value'=>$user['y_count']])?></p>
                                        <p><?=Yii::t('frontend', 'learning_task_total_{value}',['value'=>$user['a_count']])?></p>
                                    </a>
                                    <?if($uid != $user['kid']):?>
                                        <a href="javascript:void(0);" style="display:inline; padding-left: 18%;" class="btn btn-xs" onclick="showPointTransBox('<?=$user['kid']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a>
                                    <?endif;?>
                                </div>
                            </li>
                        <? endforeach; else:?>
                            <div class="centerBtnArea noData ">
                                <i class="glyphicon glyphicon-calendar"></i>
                                <p><?=Yii::t('common', 'no_data')?></p>
                            </div>
                        <?endif;?>

                    </ul>
                    <div class="pageController pageController2">
                        <a href="###" class="btn btn-xs pull-right" id="nextSwitch">&gt;</a>
                        <a href="###" class="btn btn-xs pull-right" id="prevSwitch">&lt;</a>
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
<div class="ui modal" id="examination_log"></div>
<script>
    var loading = true;

    var type = 'tab_course';

    var course_page = 1;
    var course_url = "<?=Url::toRoute(['manager/member-course-list', 'id' => $member_id, 'current_time' => $current_time])?>" + "&page=";
    var course_key = '';
    var course_type = 'all';
    var course_end = false;
    var course_read = false;
    var course_load_time = '<?=$current_time?>';

    var exam_page = 1;
    var exam_url = "<?=Url::toRoute(['manager/member-exam-list', 'id' => $member_id, 'current_time' => $current_time])?>" + "&page=";
    var exam_key = '';
    var exam_type = 'all';
    var exam_end = false;
    var exam_read = false;
    var exam_load_time = '<?=$current_time?>';

    var survey_page = 1;
    var survey_url = "<?=Url::toRoute(['manager/member-survey-list', 'id' => $member_id, 'current_time' => $current_time])?>" + "&page=";
    var survey_key = '';
    var survey_type = 'all';
    var survey_status = 'all';
    var survey_end = false;
    var survey_read = false;
    var survey_load_time = '<?=$current_time?>';

    var score_list_url = "<?=Url::toRoute(['common/person-score-list', 'uid' => $member_id, 'self' => false]);?>";

    $(function () {
        app.genSwitch($(".thumbList.popOverPanel"), $(".pageController2 #prevSwitch"), $(".pageController2 #nextSwitch"));

        readTab(type);

        $("#myTab a").click(function () {
            var tab = $(this).attr("aria-controls");
            readTab(tab);
        });

        $("#course_search_type").change(function () {
            course_type = $(this).val();
            course_page = 1;
            course_end = false;
            $("#course_list").empty();
            loadTab(course_url + course_page + '&type=' + course_type + '&key=' + encodeURI(course_key), 'course_list');
        });

        $("#course_search_btn").bind('click', function () {
            course_key = $("#course_search_key").val().trim();
            course_page = 1;
            course_end = false;
            $("#course_list").empty();
            loadTab(course_url + course_page + '&type=' + course_type + '&key=' + encodeURI(course_key), 'course_list');
        });

        $("#exam_search_type").change(function () {
            exam_type = $(this).val();
            exam_page = 1;
            exam_end = false;
            $("#exam_list").empty();
            loadTab(exam_url + exam_page + '&type=' + exam_type + '&key=' + encodeURI(exam_key), 'exam_list');
        });

        $("#exam_search_btn").bind('click', function () {
            exam_key = $("#exam_search_key").val().trim();
            exam_page = 1;
            exam_end = false;
            $("#exam_list").empty();
            loadTab(exam_url + exam_page + '&type=' + exam_type + '&key=' + encodeURI(exam_key), 'exam_list');
        });

        $("#survey_search_type").change(function () {
            survey_type = $(this).val();
            survey_page = 1;
            survey_end = false;
            $("#survey_list").empty();
            loadTab(survey_url + survey_page + '&type=' + survey_type + '&status=' + survey_status + '&key=' + encodeURI(survey_key), 'survey_list');
        });

        $("#survey_search_status").change(function () {
            survey_status = $(this).val();
            survey_page = 1;
            survey_end = false;
            $("#survey_list").empty();
            loadTab(survey_url + survey_page + '&type=' + survey_type + '&status=' + survey_status + '&key=' + encodeURI(survey_key), 'survey_list');
        });

        $("#survey_search_btn").bind('click', function () {
            survey_key = $("#survey_search_key").val().trim();
            survey_page = 1;
            survey_end = false;
            $("#survey_list").empty();
            loadTab(survey_url + survey_page + '&type=' + survey_type + '&key=' + encodeURI(survey_key), 'survey_list');
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
                if (type == 'tab_course' && !course_end) {
                    loading = true;
                    course_page++;
                    loadTab(course_url + course_page + '&type=' + course_type + '&key=' + encodeURI(course_key), 'course_list');
                }
                else if (type == 'tab_exam' && !exam_end) {
                    loading = true;
                    exam_page++;
                    loadTab(exam_url + exam_page + '&type=' + exam_type + '&key=' + encodeURI(exam_key), 'exam_list');
                }
                else if (type == 'tab_survey' && !survey_end) {
                    loading = true;
                    survey_page++;
                    loadTab(survey_url + survey_page + '&type=' + survey_type + '&status=' + survey_status + '&key=' + encodeURI(survey_key), 'survey_list');
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
        var count = $(data).filter('.myLessonList').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'course_list') {
                course_end = true;
            }
            else if (target == 'exam_list') {
                exam_end = true;
            }
            else if (target == 'survey_list') {
                survey_end = true;
            }
        }
    }

    function readTab(tab) {
        type = tab;
        if (!course_read && tab === 'tab_course') {
            course_read = true;
            loadTab(course_url + course_page + '&type=' + course_type, 'course_list');
        }
        else if (!exam_read && tab === 'tab_exam') {
            exam_read = true;
            loadTab(exam_url + exam_page, 'exam_list');
        }
        else if (!survey_read && tab === 'tab_survey') {
            survey_read = true;
            loadTab(survey_url + survey_page, 'survey_list');
        }
    }

    function LoadScoreDetail(componentCode, courseId, modId, modResId, objectId, companyId) {
        if (componentCode === "<?=LnComponent::COMPONENT_CODE_INVESTIGATION ?>") {
            //查看调查
            var modalId = "view_mod_detail";

            var ajaxUrl = "<?= Yii::$app->urlManager->createUrl(['common/person-question-detail', 'uid' => $member_id])?>";
            ajaxUrl = urlreplace(ajaxUrl, 'courseid', courseId);
            ajaxUrl = urlreplace(ajaxUrl, 'modresid', modResId);
            ajaxUrl = urlreplace(ajaxUrl, 'inkid', objectId);
        }
        else if (componentCode === "<?=LnComponent::COMPONENT_CODE_HOMEWORK ?>") {

        }
        else if (componentCode === "<?=LnComponent::COMPONENT_CODE_EXAMINATION ?>") {
            var modalId = "examination_log";
            var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('exam-manage-main/view-log')?>";
            ajaxUrl = urlreplace(ajaxUrl, 'id', objectId);
            ajaxUrl = urlreplace(ajaxUrl, 'userId', "<?=$member_id ?>");
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