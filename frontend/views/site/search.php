<?php

use common\models\social\SoRecord;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
$this->pageTitle = Yii::t('frontend', 'search_result');
$this->params['breadcrumbs'][] = $this->pageTitle;

$uid=Yii::$app->user->getId();
?>
<style type="text/css">
    #question nav,#course nav{text-align: right;}
    .pageSizeSelect{display: none;}
    .highlight-color{
        color:#FF5A5F;
    }
</style>
<style>
  /*内联样式*/
  .nameList{
      margin-top: 20px;
  }
  .nameList li,.nameList li:hover{
      background: none;
  }
  .nameList .btn.btn-sm{
      margin: 5px 5px 0 5px;
  }
  .popContainer .popPanel li {
      padding: 0;
      text-align: center;
  }
  .popContainer .popPanel{
      right:30%
  }
</style>
<div class="container">
    <div class="row">
        <ol class="breadcrumb">
            <li>
                <h2><?=Yii::t('frontend','content_search')?></h2></li>
            <li><a href="#"><?=Yii::t('frontend','{value}_search_result',['value'=>'"'.Html::encode($key).'"'])?></a></li>
            <p class="pull-right" style="margin-top:34px;"><?=Yii::t('frontend','wanted_{value]_result',['value'=>($c_count+$q_count+$p_count+$s_count)])?>（<?=Yii::t('frontend','cost_{value}_time',['value'=>$time])?>）</p>
        </ol>
        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                <li role="presentation" class="active"><a href="#course" aria-controls="course" role="tab" data-toggle="tab"><?=Yii::t('common','course')?>(<?=$c_count ?>)</a></li>
                <li role="presentation"><a href="#question" aria-controls="question" role="tab" data-toggle="tab" onclick="tabShow('question')"><?=Yii::t('frontend','question')?>(<?=$q_count ?>)</a></li>
                <li role="presentation"><a href="#person" aria-controls="person" role="tab" data-toggle="tab" onclick="tabShow('person')"><?=Yii::t('common','user_{value}',['value'=>''])?>(<?=$p_count ?>)</a></li>
                <li role="presentation"><a href="#shared" aria-controls="messages" role="tab" data-toggle="tab" onclick="tabShow('shared')"><?=Yii::t('frontend','share')?>(<?=$s_count ?>)</a></li>
            </ul>
            <div class="tab-content search-results">
                <div role="tabpanel" class="tab-pane active" id="course">
                </div>
                <div role="tabpanel" class="tab-pane" id="question">
                </div>
                <div role="tabpanel" class="tab-pane" id="person">
                </div>
                <div role="tabpanel" class="tab-pane" id="shared">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body">
                            <div class="actionBar" style="margin-top:20px">
                                <form class="form-inline pull-left">
                                    <div class="form-group">
                                        <div class="form-group field-courseservice-course_type">
                                            <select id="select_type" class="form-control" onchange="changeType(this)">
                                                <option value="all"><?=Yii::t('frontend','all_type')?></option>
                                                <option value="<?=SoRecord::RECORD_TYPE_WEB ?>"><?=Yii::t('frontend','web_page')?></option>
                                                <option value="<?=SoRecord::RECORD_TYPE_EVENT ?>"><?=Yii::t('frontend','event')?></option>
                                                <option value="<?=SoRecord::RECORD_TYPE_BOOK ?>"><?=Yii::t('frontend','book')?></option>
                                                <option value="<?=SoRecord::RECORD_TYPE_EXP ?>"><?=Yii::t('frontend','experience')?></option>
                                            </select>
                                        </div>
                                        <div class="form-group field-courseservice-course_type hide">
                                            <select id="select_time" class="form-control" onchange="changeTime(this)">
                                                <option value="0"><?=Yii::t('frontend','time_free')?></option>
                                                <option value="1"><?=Yii::t('frontend','one_week')?></option>
                                                <option value="2"><?=Yii::t('frontend','one_month')?></option>
                                                <option value="3"><?=Yii::t('frontend','one_year')?></option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div id="share_list">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="load_1" class="loadingWaiting hide">
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
<!-- 分享记录详情的弹出窗口 -->
<div class="ui modal" id="sharedHistory">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="shareModalTitle"><?=Yii::t('frontend','share_record')?></h4>
    </div>
    <div class="content textCenter">
        <div id="history" class="timeline miniLine"></div>
        <div id="load_2" class="loadingWaiting hide">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <p><?=Yii::t('frontend','loading')?>...</p>
        </div>
        <div id="no_data" style="display: none"><?=Yii::t('frontend','without')?><?=Yii::t('frontend','share_record')?></div>
        <a href="#load_2" class="btn btn-success btn-md timeLineLoadMore" onclick="loadMore()"><?=Yii::t('frontend','more')?>...</a>
    </div>
</div>
<script type="text/javascript">
    var course_url = "<?=Url::toRoute(['site/search-course','key' => $key, 'count' => $c_count])?>";

    var question_url = "<?=Url::toRoute(['site/search-question','key' => $key, 'count' => $q_count])?>";
    var question_read = false;

    var person_url = "<?=Url::toRoute(['site/search-person','key' => $key, 'count' => $p_count])?>";
    var person_read = false;

    var share_url = "<?=Url::toRoute(['site/search-share','key' => $key, 'count' => $s_count])?>";
    var share_read = false;

    $(document).ready(function () {
        <? if ($pointResult['result']=='1'):?>
        //scoreEffect(<?=$point?>);
        scorePointEffect('<?=$pointResult['show_point']?>','<?=$pointResult['point_name']?>','<?=$pointResult['available_point']?>');
        <? endif; ?>
        $('#myTab a:first').tab('show');
        $("#load_1").removeClass('hide');
        loadTab(course_url, 'course');
    });

    function tabShow(container) {
        if (container == 'question' && !question_read) {
            question_read = true;
            $("#load_1").removeClass('hide');
            loadTab(question_url, container);
        }
        else if (container == 'person' && !person_read) {
            person_read = true;
            $("#load_1").removeClass('hide');
            loadTab(person_url, container);
        }
        else if (container == 'shared' && !share_read) {
            share_read = true;
            $("#load_1").removeClass('hide');
            loadTab(share_url, 'share_list');
        }
    }

    function loadTab(ajaxUrl, container) {
        ajaxGet(ajaxUrl, container, bind);
    }
    function bind(target, data) {
        $("#load_1").addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            $('html, body').animate({scrollTop:108}, 'fast');
            var url = $(this).attr('href');
            loadTab(url, target);
            return false;
        });
    }

    function loadHistory(ajaxUrl, container) {
        $(".timeLineLoadMore").hide();
        $("#load_2").removeClass('hide');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $("#load_2").addClass('hide');
        $("#" + target).append(data);
        var count = $(data).filter('.timeline-item').length;
        if (data == null || data == '' || count < 10) {
            history_end = true;
            if ($("#history").html() == '') {
                $("#no_data").show();
            }
        }
        else {
            $(".timeLineLoadMore").show();
        }
    }

    function attentionUser(obj, id) {
        var url = "<?=Url::toRoute(['common/attention-user'])?>";
        $.post(url, {"uid": id},
            function (data) {
                var result = data.result;
                if (result === 'other'){
                    app.showMsg(data.message);
                }else if (result === 'failure') {
                    app.showMsg("<?=Yii::t('common','operation_confirm_warning_failure')?>", 1000);
                }else{
                    if ($(obj).text() == '<?=Yii::t('common', 'attention')?>') {
                        $(obj).text('<?=Yii::t('common', 'canel_attention')?>');
                        if (checkPointResult(data.pointResult)){
                            //scoreEffect(data.point);
                            scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                        }
                        else {
                            app.showMsg("<?=Yii::t('frontend','attention_sucess')?>", 1000);
                        }
                    } else {
                        $(obj).text('<?=Yii::t('common', 'attention')?>');
                        app.showMsg("<?=Yii::t('common','cancel_attention')?>", 1000);
                    }
                }
            }, "json");
        return false;
    }

    function careQuestion(obj,id) {
        var url = "<?=Url::toRoute(['question/care'])?>";
        $.post(url, {"qid": id},
            function (data) {
                var result = data.result;
                if (result === 'other'){
                    app.showMsg(data.message);
                }else if (result === 'failure') {
                    app.showMsg("<?=Yii::t('common','operation_confirm_warning_failure')?>", 1000);
                }else{
                    if($(obj).text() == '<?=Yii::t('common','attention')?>'){
                        $(obj).text('<?=Yii::t('common','canel_attention')?>');
                        if (checkPointResult(data.pointResult)){
                            //scoreEffect(data.point);
                            scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                        }
                        else {
                            app.showMsg("<?=Yii::t('frontend','attention_sucess')?>", 1000);
                        }
                    }else{
                        $(obj).text('<?=Yii::t('common','attention')?>');
                        app.showMsg("<?=Yii::t('common','cancel_attention')?>", 1000);
                    }
                }
            }, "json");
        return false;
    }

    function collectCourse(obj,id){
        var actionUrl = '<?=Url::toRoute(['resource/course/collection'])?>';
        $.post(
            actionUrl,
            'obj_id='+id
        ).done(function(data) {
                CollectCallBack(obj,data);
            }).fail(function() {
                app.showMsg("<?=Yii::t('common','operation_confirm_warning_internal_error')?>");
            });
    }

    function CollectQuestion(obj, id) {
        var url = "<?=Url::toRoute(['question/collect'])?>";
        $.post(url, {"qid": id},
            function (data) {
                CollectCallBack(obj,data);
            }, "json");
        return false;
    }

    var history_page = 1;
    var history_url = "<?=Url::toRoute(['site/search-history'])?>" + "?page=";
    var history_end = false;
    var history_uid = null;
    var is_show = false;

    function showHistory(uid,name)
    {
        history_page = 1;
        history_end = false;
        $("#shareModalTitle").html(name + '<?=Yii::t('frontend','{value}_search_result',['value'=>''])?>');
        $("#history").empty();
        $(".timeLineLoadMore").show();
        $("#no_data").hide();
        history_uid = uid;
        loadHistory(history_url + history_page + '&uid=' + history_uid, 'history');
        app.alert('#sharedHistory');
    }

    function loadMore()
    {
        if(!history_end){
            history_page++;
            loadHistory(history_url + history_page + '&uid=' + history_uid, 'history');
        }
    }
    function CollectCallBack(obj,data) {
        var result = data.result;
        if (result === 'other'){
            app.showMsg(data.message);
        }else if (result === 'failure') {
            app.showMsg("<?=Yii::t('common','operation_confirm_warning_failure')?>", 1000);
        }else{
            if($(obj).text() == '<?=Yii::t('common','collection')?>'){
                $(obj).text('<?=Yii::t('common','canel_collection')?>');
                if (checkPointResult(data.pointResult)){
                    //score-Effect(data.point);
                    scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                }
                else {
                    app.showMsg("<?=Yii::t('frontend','collection_sucess')?>", 1000);
                }
            }else{
                $(obj).text('<?=Yii::t('common','collection')?>');
                app.showMsg("<?=Yii::t('frontend','cancel_sucess')?>", 1000);
            }
        }
    }

    /* 分享tab 相关事件 */
    function submitShare(id){
        var url = "<?=Url::toRoute(['common/share-record'])?>";
        $.post(url, {"rid": id},
            function (data) {
                var result = data.result;
                if (result === 'other') {
                    app.showMsg(data.message, 1500);
                }
                else if (result === 'success') {
                    app.showMsg('<?=Yii::t('frontend','share_sucess')?>', 1500);
                }
            }, "json");
        return false;
    }

    function submitDownload(obj_id,obj_type)
    {
        $("#downloadform-id").val(obj_id);
        $("#downloadform-type").val(obj_type);
        $("#downloadForm").submit();
        return false;
    }

    function moreContent(btn)
    {
        var thisBtn = $(btn);
        if (thisBtn.text() == '<?=Yii::t('common','menu_collapse')?>') {
            thisBtn.parent().find('.moreContent').css('height', 'auto');
            thisBtn.text('<?=Yii::t('frontend','page_myteam_noshow')?>');
        } else {
            thisBtn.parent().find('.moreContent').css('height', '20px');
            thisBtn.text('<?=Yii::t('common','menu_collapse')?>');
        }
    }

    function changeType(obj)
    {
        $("#share_list").empty();
        $("#load_1").removeClass('hide');
        loadTab(share_url + "&type=" + $(obj).val(), 'share_list');
    }
    function changeTime(obj)
    {
        loadTab(share_url + "&time=" + $(obj).val(), 'share_list');
    }

    var getPointUrl = "<?=Url::toRoute(['common/get-open-url-point'])?>";
    function openUrl(url, objId, type) {
        app.get(getPointUrl + "?objId=" + objId + "&type=" + type, function (r) {
            if (checkPointResult(r.pointResult)){
                //score-Effect(r.point);
                scorePointEffect(r.pointResult.show_point,r.pointResult.point_name,r.pointResult.available_point);
                setTimeout(function () {
                    window.open(url);
                }, 2200);
            }
            else {
                window.open(url);
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
</script>
<!-- 收藏的弹出窗口 -->
<div class="ui modal" id="newFollow">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','prompt')?></h4>
    </div>
    <div class="body"></div>
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