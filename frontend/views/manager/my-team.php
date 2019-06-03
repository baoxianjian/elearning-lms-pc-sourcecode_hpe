<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/7
 * Time: 13:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;

$this->pageTitle = Yii::t('frontend', 'home_myteam_text');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style type="text/css">
    .pagination .disabled{
        display: none;
    }
    #newTask .taskList .taskLine{
        margin-top: 0px;
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
            <div class="panel panel-default hotNews" style="min-height: 300px">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend', 'learning_task')?>
                    <a class="pull-right" href="#"  onclick="app.alertWide('#newTask');"  style="color:#00609d">+ <?=Yii::t('frontend', 'new_task')?></a>
                </div>
                <div class="col-sm-12">
                    <form class="form-inline pull-right" style="width:100%">
                        <div class="form-group" style="width:100%">
                            <div class="form-group pull-left">
                                <select id="sel_owner" class="form-control">
                                    <option value="manager"><?=Yii::t('frontend', 'my_push')?></option>
                                    <option value="admin"><?=Yii::t('frontend', 'manage_push')?></option>
                                </select>
                            </div>
                            <div class="form-group pull-left">
                                <select id="sel_type" class="form-control">
                                    <option value="all"><?=Yii::t('frontend', 'all_task')?></option>
                                    <option value="0"><?= Yii::t('common', 'course') ?></option>
                                    <option value="1"><?= Yii::t('frontend', 'exam') ?></option>
                                    <option value="2"><?= Yii::t('common', 'investigation') ?></option>
                                </select>
                            </div>
                            <button onclick="searchTask()" type="button" class="btn btn-success pull-right"><?= Yii::t('frontend', 'top_search_text') ?></button>
                            <input id="task_key" type="text" class="form-control pull-right" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','task_name')]) ?>">
                        </div>
                    </form>
                </div>
                <div id="task_list_panel" class="panel-body" style="padding:0;">
                </div>
                <div id="task_list_panel_loading" class="loadingWaiting hide">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <p><?=Yii::t('frontend', 'loading')?>...</p>
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
                    <!-- <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">讨论榜</a></li> -->
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
                    <?if($team_users):?>
                        <ul class="thumbList popOverPanel"  style="display: none;">
                            <? foreach($team_users as $user):?>
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
                            <? endforeach;?>
                        </ul>
                        <div class="pageController pageController2">
                            <a href="###" class="btn btn-xs pull-right" id="nextSwitch">&gt;</a>
                            <a href="###" class="btn btn-xs pull-right" id="prevSwitch">&lt;</a>
                        </div>
                    <? else:?>
                        <ul class="thumbList">
                            <div class="centerBtnArea noData ">
                                <i class="glyphicon glyphicon-calendar"></i>
                                <p><?=Yii::t('common', 'no_data')?></p>
                            </div>
                        </ul>
                    <?endif;?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 新任务的弹出窗口 -->
<div class="modal ui" id="newTask" >
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="taskClose"><span aria-hidden="true">&times;</span></button>
        <h4 id="myModalLabelTask"><?=Yii::t('frontend', 'new_task')?></h4>
    </div>
    <div class="content" id="pushDiv">
    </div>
</div>
<!-- /container -->
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
<script>
    var taskListUrl="<?=Yii::$app->urlManager->createUrl('manager/task-list')?>";

    app.extend("alert");
    function showAll(obj)
    {
        var b = $(obj).parent();
        var c = $(b).nextAll(".thumbList");
        if (c.hasClass("showAllList")) {
            c.removeClass("showAllList")
        } else {
            c.addClass("showAllList")
        }
    }

    function FmodalLoad(target, url)
    {
        if(url){
            $('#'+target).empty();
            var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
            $('#'+target).html(loadingDiv); // 设置页面加载时的loading图片
            $('#'+target).load(url);
        }
    }

    $(document).ready(
        function () {
            changeTaskList();
            FmodalLoad('pushDiv','<?=Yii::$app->urlManager->createUrl('manager/panel-task-push')?>');
            app.genSwitch($(".thumbList.popOverPanel"), $(".pageController2 #prevSwitch"), $(".pageController2 #nextSwitch"));
        });//ready end

    $("#sel_owner").change(function(){
        changeTaskList();
    });

    $("#sel_type").change(function(){
        changeTaskList();
    });

    function reloadForm() {
        var fun = "FmodalLoad('pushDiv','<?=Yii::$app->urlManager->createUrl('manager/panel-task-push')?>')";
        setTimeout(fun, 1000);
    }

    $('#taskClose').bind("click",function(){
        reloadForm();
    });

    var task_owner="manager";
    var task_type="all";

    function changeTaskList() {
        $('#task_list_panel_loading').removeClass('hide');
        $('#task_list_panel').empty();

        task_owner = $("#sel_owner").val();
        task_type = $("#sel_type").val();

        app.get(taskListUrl + "?owner=" + task_owner + "&type=" + task_type, function (data) {
            $('#task_list_panel_loading').addClass('hide');
            $('#task_list_panel').html(data);
        });
    }

    function searchTask(){
        $('#task_list_panel_loading').removeClass('hide');
        $('#task_list_panel').empty();

        var key = $("#task_key").val().trim();

        app.get(taskListUrl + "?owner=" + task_owner + "&type=" + task_type + "&key=" + encodeURI(key), function (data) {
            $('#task_list_panel_loading').addClass('hide');
            $('#task_list_panel').html(data);
        });
    }

    var score_list_url = "<?=Url::toRoute(['common/person-score-list']);?>";

    function showCourseDetail(uid,cid)
    {
        $('#score_list').empty();
        loadScore(score_list_url + "?uid=" + uid + "&cid=" + cid);
        app.alertWide('#scoreDetails');
    }

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
</script>
<?= html::jsFile('/static/frontend/js/Chart.js') ?>
<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>
<script>
    function sendRemind(container, kid, type, plan_complete_at) {
        var idArr = new Array();

        $("#" + container + " input[type=hidden]").each(
            function (i, n) {
                idArr.push($(n).val());
            });

        var url = "<?=Url::toRoute(['manager/send-remind'])?>";
        $.post(url, {"ids": idArr, 'cid': kid, 'type': type, 'plan_complete_at': plan_complete_at},
            function (data) {
                var result = data.result;
                if (result === 'other') {
                    app.showMsg(data.message, 1000);
                }
                else if (result === 'success') {
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>', 1000);
                }
            }, "json");
        return false;
    }

    function sendRemindP(uid, kid, type, plan_complete_at) {
        var idArr = new Array();
        idArr.push(uid);

        var url = "<?=Url::toRoute(['manager/send-remind'])?>";
        $.post(url, {"ids": idArr, 'cid': kid, 'type': type, 'plan_complete_at': plan_complete_at},
            function (data) {
                var result = data.result;
                if (result === 'other') {
                    app.showMsg(data.message, 1000);
                }
                else if (result === 'success') {
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>', 1000);
                }
            }, "json");
        return false;
    }
</script>
