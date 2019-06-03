<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/20
 * Time: 11:12
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

$this->pageTitle = Yii::t('common','resource_management');
$this->params['breadcrumbs'][] = ['label' => $this->pageTitle, 'url' => ['/resource/index']];
$this->params['breadcrumbs'][] = Yii::t('frontend', 'push_task');
?>
<style type="text/css">
    .pagination{
        font-size: 12px;
    }
    .pagination .disabled{
        display: none;
    }
    #newTask .taskList .taskLine{
        margin-top: 0px;
    }
    .selectPanel_task #courseList .btn{
        margin-right:0px;
    }
</style>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-cloud-upload"></i> <?=Yii::t('frontend', 'task_list')?>
                </div>
                <div class="panel-body">
                    <div class="actionBar">
                        <div class="btn-group">
                            <? if(isset($domain_list) && count($domain_list) === 1): ?>
                                <button type="button" class="btn btn-success dropdown-toggle" data-kid="<?= $domain_list[0]->kid ?>"
                                        data-name="<?= Html::encode($domain_list[0]->domain_name) ?>"
                                        onclick="selectDomain(this)">
                                    <?=Yii::t('frontend', 'new_task')?>
                                </button>
                            <? elseif(isset($domain_list) && count($domain_list) > 1): ?>
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                <?=Yii::t('frontend', 'new_task')?> <span class="caret stBtn"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <? foreach ($domain_list as $domain): ?>
                                    <li><a href="#" data-kid="<?= $domain->kid ?>"
                                           data-name="<?= Html::encode($domain->domain_name) ?>"
                                           onclick="selectDomain(this)"><?= Html::encode($domain->domain_name) ?></a></li>
                                <? endforeach; ?>
                            </ul>
                            <? endif; ?>
                        </div>
                        <form class="form-inline pull-right">
                            <div class="form-group">
                                <button type="reset" class="btn btn-default pull-right"><?=Yii::t('frontend', 'reset')?></button>
                                <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" onclick="search()"><?=Yii::t('common', 'search')?></button>
                                <input readonly style="background: #fff;width: 120px !important;text-align: center;" id="search_date" type="text" class="form-control pull-right" data-type="rili" placeholder="<?= Yii::t('frontend', 'date_search') ?>"/>
                                <input id="task_search_key" type="text" class="form-control pull-right" placeholder="<?= Yii::t('frontend', 'fuzzy_search_code') ?>"/>
                                <div class="form-group pull-right">
                                    <select id="search_type" class="form-control">
                                        <option value="-1"><?= Yii::t('common', 'select_{value}',['value'=>Yii::t('common','push_status')]) ?></option>
                                        <option value="0"><?=Yii::t('frontend', 'complete_status_doing')?></option>
                                        <option value="1"><?= Yii::t('frontend', 'task_all_sucess') ?></option>
                                        <option value="2"><?= Yii::t('frontend', 'task_all_fail') ?></option>
                                        <option value="3"><?= Yii::t('frontend', 'task_part_fail') ?></option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="list">
                    </div>
                    <div id="list_loading" class="loadingWaiting hide" style="margin:150px auto;">
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

<!-- 新建任务的弹出窗口 -->
<div class="ui modal" id="create_task">
</div>
<!-- 编辑任务的弹出窗口 -->
<div class="ui modal" id="edit_task">
</div>
<!-- 查看任务的弹出窗口 -->
<div class="ui modal" id="task_checker">
</div>
<script>
    var list_url = "<?=Url::toRoute(['task/list'])?>";
    var create_url = "<?=Url::toRoute(['task/create'])?>";
    var edit_url = "<?=Url::toRoute(['task/edit'])?>";
    var view_url = "<?=Url::toRoute(['task/view'])?>";

    $(document).ready(function () {
        app.genCalendar();
//        load(create_url, 'create_task', false);
        load(list_url, 'list', true);
    });

    function load(ajaxUrl, container, is_bind) {
        $("#" + container).empty();
        if (is_bind) {
            $("#list_loading").removeClass('hide');
            ajaxGet(ajaxUrl, container, bind);
        }
        else {
            ajaxGet(ajaxUrl, container, null);
        }
    }
    function bind(target, data) {
        $("#list_loading").addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            load(url, target, true);
            return false;
        });
    }

    $(function () {
        $('.showList').bind('click', function () {
            $('.theShownList').removeClass("hide")
        })
    });

    function selectDomain(obj) {
        var kid = $(obj).attr('data-kid');
        var domain_name = $(obj).attr('data-name');
        $("#create_task").html($("#loading_html").html());
        app.alertWide('#create_task',{
            afterHide: function (){
                $("#create_task").empty();
            }
        });

        app.get(create_url,function(html){
            $("#create_task").html(html);
            $("#domain_id").val(kid);
            $("#all_domain").val('0,' + kid);
            $("#new_task_title").text('<?=Yii::t('frontend', 'new_task')?>(' + domain_name + ')');
        });
//        load(create_url, 'create_task', false);
    }

    function search() {
        var key = $("#task_search_key").val().trim();
        var date = $("#search_date").val();
        var type = $("#search_type").val();

        load(list_url + '?search_key=' + key + '&search_date=' + date + '&search_type=' + type, 'list', true);
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose) {
        app.alert("#newFollow");
        var val = $("#is_temp").val();
        if (val === 'yes') {
            $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?= Yii::t('frontend', 'temp_mode') ?></div><div class="c"></div>');
        }
        else {
            $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?= Yii::t('frontend', 'task_sucess') ?></div><div class="c"></div>');
        }
        reloadForm(formId);
    }
    function reloadForm(formId) {
        var fun;
        fun = "load(create_url, 'create_task', false);load(list_url, 'list', true);";
        setTimeout(fun, 500);
    }
    function viewTask(id) {
        app.alert('#task_checker');
        load(view_url + "?id=" + id, 'task_checker', false);
        return false;
    }

    function deleteTask(id) {
        app.alert("#confirm",
            {
                ok: function ()
                {
                    var url = "<?=Url::toRoute(['task/delete'])?>";
                    $.post(url, {"task_id": id},
                        function (data) {
                            var result = data.result;
                            if (result === 'other') {
                                app.showMsg(data.message);
                            } else if (result === 'failure') {
                                app.alert("#newFollow");
                                $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_confirm_warning_failure')?></div><div class="c"></div>');
                            } else {
                                app.alert("#newFollow");
                                $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_success')?></div><div class="c"></div>');
                                reloadList();
                            }
                        }, "json");
                    return false;
                }
            }
        );
        return false;
    }

    function repushTask(task_id, sponsor_id, domain_id) {
        app.alert("#confirm",
            {
                ok: function ()
                {
                    var url = "<?=Url::toRoute(['task/repush'])?>";
                    $.post(url, {"task_id": task_id, "sponsor_id": sponsor_id, "domain_id": domain_id},
                        function (data) {
                            var result = data.result;
                            if (result === 'other') {
                                app.showMsg(data.message);
                            } else if (result === 'failure') {
                                app.alert("#newFollow");
                                $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_confirm_warning_failure')?></div><div class="c"></div>');
                            } else {
                                app.alert("#newFollow");
                                $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_success')?></div><div class="c"></div>');
                                reloadList();
                            }
                        }, "json");
                    return false;
                }
            }
        );

        return false;
    }

    function reloadList() {
        var url = $("#list").attr('url');
        if (url) {
            load(url, 'list', true);
        }
        else {
            load(list_url, 'list', true);
        }
    }

    function immediatelyPushTask(task_id, sponsor_id, domain_id) {
        app.alert("#confirm",
            {
                ok: function () {
                    app.hideAlert('#confirm');
                    var url = "<?=Url::toRoute(['task/immediately-push'])?>";
                    $.post(url, {"task_id": task_id, "sponsor_id": sponsor_id, "domain_id": domain_id},
                        function (data) {
                            var result = data.result;
                            if (result === 'other') {
                                app.showMsg(data.message);
                            } else if (result === 'failure') {
                                app.alert("#newFollow");
                                $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_confirm_warning_failure')?></div><div class="c"></div>');
                            } else {
                                app.alert("#newFollow");
                                $("#newFollow .content").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_success')?></div><div class="c"></div>');
                                reloadList();
                            }
                        }, "json");
                    return false;
                }
            }
        );

        return false;
    }

    function editTask(task_id)
    {
        $("#create_task").html($("#loading_html").html());
        app.alertWide('#create_task',{
            afterHide: function (){
                $("#create_task").empty();
            }
        });

        app.get(edit_url + "?id=" + task_id,function(html){
            $("#create_task").html(html);
        });

//        load(edit_url + "?id=" + task_id, 'edit_task', false);
    }
</script>
<!-- 收藏的弹出窗口 -->
<div class="ui modal" id="newFollow">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'prompt')?></h4>
    </div>
    <div class="content"></div>
</div>
<div id="confirm" class="ui modal">
    <div class="header"><?=Yii::t('frontend', 'prompt')?></div>
    <div class="content">
        <div style="text-align: center;padding: 20px;"><?= Yii::t('common', 'action_confirm') ?></div>
        <div class="c"></div> <!--新增-->
    </div>
    <div class="actions">
        <div class="btn btn-default cancel"><?= Yii::t('frontend', 'page_info_good_cancel') ?></div>
        <div class="btn btn-default ok"><?=Yii::t('frontend', 'be_sure')?></div>
    </div>
</div>
<div class="hide" id="loading_html">
    <div class="loadingWaiting" style="margin:150px auto;">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <p><?=Yii::t('frontend', 'loading')?>...</p>
    </div>
</div>