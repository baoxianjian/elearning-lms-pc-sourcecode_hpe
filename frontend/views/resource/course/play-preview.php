<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */

use common\models\learning\LnComponent;
use common\models\learning\LnModRes;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;

$this->pageTitle = Yii::t('frontend', 'course_view') ;
$this->title = $courseName;

$this->params['breadcrumbs'][] = $this->pageTitle;
$this->params['breadcrumbs'][] = $courseName;

/* @var $model common\models\learning\LnCourseware */
?>
<script>
    //公共的回调函数
    function commonSuccessCallback(target, data,textStatus){
        //alert(formId+target);
        $("#" + target).html(data);
    }
    function getRandedURL(url){
        //alert(url);
        return urlreplace(url,"_rand",new Date().getTime());
    }
    function urlreplace(url,paramname,paramvalue){

        var index = url.indexOf("?");
        if(index==-1){
            url = url + "?" + paramname + "=" + paramvalue;
        }else{
            var s1 = url.split("?");
            var params = s1[1].split("&");
            var pn = "";
            var flag = false;
            for(i=0;i<params.length;i++){
                pn = params[i].split("=")[0];
                if(pn==paramname){
                    params[i]=paramname+"="+paramvalue;
                    flag = true;
                    break;
                }
            }
            if(!flag){
                url = url + "&" + paramname + "=" + paramvalue;
            }else{
                url = s1[0] + "?";
                for(i=0;i<params.length;i++){
                    if(i>0){
                        url = url + "&";
                    }
                    url = url + params[i];
                }
            }

        }
        return url;
    }
    function ajaxGet(url, target, callback) {
        //$('#'+target).empty();
        //alert(url);
        url=getRandedURL(url);

        //alert(url);
        //debug("ajaxGet url="+url);
        $("#"+target).attr("url",url);
        $.get(url,null,function(data, textStatus) {
            //alert("success");
            if(callback){
                callCommon=callback(target,data);
                if(callCommon){
                    commonSuccessCallback(target,data);
                }
            }else{
                commonSuccessCallback(target,data);
            }
        });
    }
    $(document).ready(function() {
        loadCatalog();
        loadPlayer();
    });

    function loadCatalog(){
        var scoId = "<?=$scoId?>";
        var ajaxUrl = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,
        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
        'attempt'=>$attempt, 'mode' => 'preview'])?>";

        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
        ajaxGet(ajaxUrl, "catalog-frame");
    }

    function zoom() {
        var btnZoomIn = $(".btnZoomIn");
        var playWindow = $("#playWindow");
        var hideMenu = $("#hideMenu");
        var outWindow = $("#outWindow");

        if (!playWindow.hasClass("expandWin")) {
            btnZoomIn.text("<?= Yii::t('frontend', 'narrow') ?>");
            playWindow.addClass("expandWin");
            outWindow.addClass("expandWin");
            hideMenu.addClass("hide");
            change_size(true);
        } else {
            btnZoomIn.text("<?= Yii::t('frontend', 'full_screen') ?>");
            playWindow.removeClass("expandWin");
            outWindow.removeClass("expandWin");
            hideMenu.removeClass("hide");
            change_size(false);
        }
    }

    function loadPlayer(){
        var scoId = "<?=$scoId?>";
//        alert(compnentCode);
        var ajaxUrl = "<?=Url::toRoute(['resource/player/'.$componentCode.'-player', 'courseId'=>$courseId,  'modResId'=>$modResId, 'courseRegId' => null,
        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
        'attempt'=>$attempt, 'mode' => 'preview'])?>";
        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
//        alert(ajaxUrl);
        ajaxGet(ajaxUrl, "player-frame");
    }

    function reloadplayer(componentCode,modResId, scoId){

        var currentModResId = $("#currentModResId").val();
//        alert("componentCode:"+componentCode);

//        alert("modResId:"+modResId +", currentModResId:"+currentModResId);
        var ajaxUrlCatalog = "";
        var ajaxUrlPlayer = "";
        var newurl = "";
        if (componentCode == "scorm") {

            var currentScoId = $("#currentScoId").val();
//            alert("scoId:"+scoId +", currentScoId:"+currentScoId);
            if (modResId != currentModResId || scoId != currentScoId) {
                newurl = "<?=Url::toRoute(['resource/course/play-preview','modResId'=>$modResId, 'scoId'=>$scoId,
                'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                newurl = urlreplace(newurl, 'modResId', modResId);
                newurl = urlreplace(newurl, 'scoId', scoId);
                window.location.href = newurl;
            }
            else
            {
                var msg = "<?= Yii::t('frontend', 'reset_load_unit') ?>？";
                NotyConfirm(msg, function (data) {
                    newurl = "<?=Url::toRoute(['resource/course/play-preview','modResId'=>$modResId, 'scoId'=>$scoId,
                    'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                    newurl = urlreplace(newurl, 'modResId', modResId);
                    newurl = urlreplace(newurl, 'scoId', scoId);
                    window.location.href = newurl;
                });

            }
        }
        else{
            if (modResId != currentModResId) {
                newurl = "<?=Url::toRoute(['resource/course/play-preview','modResId'=>$modResId,
                'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                newurl = urlreplace(newurl, 'modResId', modResId);
                window.location.href = newurl;
            }
        }
    }


    function reloadCatalog(componentCode,modResId, scoId){
        var ajaxUrlCatalog = "";
//        var ajaxUrlPlayer = "";
        if (componentCode == "scorm") {

            ajaxUrlCatalog = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,
            'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
            'attempt'=>$attempt, 'mode' => 'preview'])?>";
            ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
            ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'scoId', scoId);
            ajaxGet(ajaxUrlCatalog, "catalog-frame");
        }
        else{
            ajaxUrlCatalog = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,
            'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
            'attempt'=>$attempt, 'mode' => 'preview'])?>";
            ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
            ajaxGet(ajaxUrlCatalog, "catalog-frame");
        }
    }
</script>
<style>
    .exam_header strong {
        font-size: 14px;
        color: #ccc;
    }
    .exam_header span {
        color: #333;
        font-size: 14px;
        position: relative;
        top: 2px;
    }
    body {padding-top: 0px;}
    .container {padding-top: 0px!important;}
</style>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

        <div class="col-md-12" >

            <div class="panel panel-default hotNews pull-left" id="outWindow">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?= Yii::t('frontend', 'course_play') ?>
                    <a href="javascript:parent.document.getElementById('iframe').src='<?=Url::toRoute(['resource/course/preview-iframe', 'id'=> $courseId])?>'" style="float:right; color:#337ab7;"><?= Yii::t('common', 'back_button') ?></a>
                </div>
                <div class="col-md-8" id="playWindow">
                    <div class="panel-body" style="padding-bottom: 0!important;">
                        <h5 class="<?=$componentCode=='examination' ? 'exam_header' : ''?>" style="line-height: 2.1em;" id="player_title">
                            <?=$itemName?>
                        </h5>
                    </div>
                    <hr id="player_line"/>
                    <div id="player-frame"></div>
                </div>

                <div class="col-md-4" id="hideMenu" style="border-left:1px solid #eee;">
                    <div class="panel-body coursePlayList" style="padding:0;">

                        <div id="catalog-frame"></div>

                    </div>
                </div>

                <div class="col-md-12" id="hideAnswer">
                    <div class="panel-body" style="width: 1000px">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    #iframe-player{width:100%;
        min-height: 600px;;}
    
</style>
<!-- 全屏脚本 -->
<script>
   $(".btnZoomIn").bind("click", function () {
        zoom();
    });
    function miniScreen(){}
    function resizeIframe(){}
    function changeCourseWareStatus(){}
</script>