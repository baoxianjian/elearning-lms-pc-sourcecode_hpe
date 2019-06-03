<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/28
 * Time: 17:46
 */
use common\models\message\MsTask;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'task_code') ?>: <?=$task->task_code?></h4>
</div>
<div class="content">
    <div class="col-md-12 col-sm-12">
        <label><?= Yii::t('frontend', 'list_task') ?></label>
        <ul class="taskList" style="margin: 15px 0; height:auto; overflow:hidden;">
            <? foreach($task_items as $item):?>
            <li>
                <div class="taskLine">
                    <h5 title="<?= Html::encode($item['item_name']) ?>"><?= TStringHelper::GetTaskItemTypeText($item['item_type']) . Html::encode(TStringHelper::subStr($item['item_name'], 16, 'utf-8', 0, '...')) ?></h5>
                </div>
                <input style="width: 41% !important;" readonly class="form-control pull-right dateInput" type="text"
                       placeholder="<?= Yii::t('common', 'end_time2') ?>:<?= $item['plan_complete_at'] ? TTimeHelper::toDateTime($item['plan_complete_at']) : '' ?>">
            </li>
            <? endforeach;?>
        </ul>
    </div>
    <div class="col-md-12 col-sm-12 myGroupList_mini">
        <label><?=Yii::t('frontend', 'list_of_{value}',['value'=>Yii::t('frontend','student')])?></label>
        <div class="courseInfo">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                <li role="presentation" class="active"><a href="#task_unfinish"
                                                          aria-controls="task_unfinish" role="tab"
                                                          data-toggle="tab" aria-expanded="true"><?= Yii::t('common', 'failed') ?></a></li>
                <li role="presentation"><a href="#task_finish" aria-controls="task_finish" role="tab"
                                           data-toggle="tab" onclick="tabClick();"><?=Yii::t('frontend', 'complete_status_done')?></a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="task_unfinish">
                    <div class=" panel-default scoreList">
                        <div class="panel-body">
                            <div class="input-group " style="margin:20px 0;">
                                <input id="fail_key" type="text" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('common', 'real_name')])?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-success btn-sm" type="button" onclick="Search(fail_url, 'view-fail')"><?= Yii::t('frontend', 'top_search_text') ?></button>
                                </span>
                            </div>
                            <div id="view-fail">
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="task_finish">
                    <div class=" panel-default scoreList">
                        <div class="panel-body">
                            <div class="input-group " style="margin:20px 0;">
                                <input id="success_key" type="text" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('common', 'real_name')])?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-success btn-sm" type="button" onclick="Search(success_url, 'view-success')"><?= Yii::t('frontend', 'top_search_text') ?></button>
                                </span>
                            </div>
                            <div id="view-success">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="view_loading" class="loadingWaiting hide" style="margin:30px auto;">
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
    <? if($task->status===MsTask::STATUS_FLAG_TEMP): ?>
        <div class="centerBtnArea" style="position: initial;">
            <a href="javascript:void(0);" onclick="return deleteTask('<?= $task->kid ?>');" class="btn btn-sm btn-danger centerBtn" style="margin:20px 12px 20px 0;" role="button"><?= Yii::t('frontend', 'task_delete') ?></a>
        </div>
    <? elseif($task->status===MsTask::STATUS_FLAG_NORMAL): ?>
        <? if($task->task_status===MsTask::TASK_STATUS_TODO && $task->push_prepare_at > 0): ?>
            <div class="centerBtnArea" style="position: initial;">
                <a href="javascript:void(0);" onclick="return deleteTask('<?= $task->kid ?>');" class="btn btn-sm btn-danger centerBtn" style="margin:20px 12px 20px 0;" role="button"><?= Yii::t('frontend', 'task_delete') ?></a>
            </div>
        <? endif; ?>
    <? endif; ?>
    <div class="c"></div>
</div>
<script>
    var fail_url='<?= Url::toRoute(['task/view-fail','id'=>$task->kid])?>';
    var success_url='<?= Url::toRoute(['task/view-success','id'=>$task->kid])?>';

    $(document).ready(function () {
        LoadList(fail_url, 'view-fail');
        LoadList(success_url, 'view-success');
    });

    function tabClick() {
        setTimeout(function(){app.refreshAlert("#task_checker")},0);
    }

    function LoadList(ajaxUrl, container) {
        $("#" + container).empty();
        $("#view_loading").removeClass('hide');
        ajaxGet(ajaxUrl, container, viewBind);
    }
    function Search(url, container) {
        var key;
        if (container == 'view-fail') {
            key = $("#fail_key").val().trim();
        }
        else if (container == 'view-success') {
            key = $("#success_key").val().trim();
        }
        if(key){
            LoadList(url + '&key=' + key, container);
        }
        else{
            LoadList(url, container);
        }
    }
    function viewBind(target, data) {
        $("#view_loading").addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            LoadList(url, target);
            return false;
        });
    }
</script>