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

$this->pageTitle = Yii::t('frontend','course_view');
$this->params['breadcrumbs'][] = ['label'=>$courseName];
$this->params['breadcrumbs'][] = $this->pageTitle;
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
            btnZoomIn.text("缩小");
            playWindow.addClass("expandWin");
            outWindow.addClass("expandWin");
            hideMenu.addClass("hide");
            change_size(true);
        } else {
            btnZoomIn.text("全屏");
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

//                $("#catalog-frame").empty();
//                ajaxUrlCatalog = "<?//=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId, 'mode' => 'normal'])?>//";
//                ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
//                ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'scoId', scoId);
//              // alert(ajaxUrlCatalog);
//                ajaxGet(ajaxUrlCatalog, "catalog-frame");
//
//                $("#player-frame").empty();
//                ajaxUrlPlayer = "<?//=Url::toRoute(['resource/player/scorm-player','modResId'=>$modResId, 'courseId'=>$courseId, 'mode' => 'normal'])?>//";
//                ajaxUrlPlayer = urlreplace(ajaxUrlPlayer, 'modResId', modResId);
//                ajaxUrlPlayer = urlreplace(ajaxUrlPlayer, 'scoId', scoId);
//               // alert(ajaxUrlPlayer);
//                ajaxGet(ajaxUrlPlayer, "player-frame");
            }
            else
            {
                var msg = "是否重新加载此单元？";
                NotyConfirm(msg, function (data) {
                    newurl = "<?=Url::toRoute(['resource/course/play-preview','modResId'=>$modResId, 'scoId'=>$scoId,
                    'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                    newurl = urlreplace(newurl, 'modResId', modResId);
                    newurl = urlreplace(newurl, 'scoId', scoId);
                    window.location.href = newurl;

//                    $("#player-frame").empty();
//                    ajaxUrlPlayer = "<?//=Url::toRoute(['resource/player/scorm-player','modResId'=>$modResId, 'courseId'=>$courseId, 'mode' => 'normal'])?>//";
//                    ajaxUrlPlayer = urlreplace(ajaxUrlPlayer, 'modResId', modResId);
//                    ajaxUrlPlayer = urlreplace(ajaxUrlPlayer, 'scoId', scoId);
//                    // alert(ajaxUrlPlayer);
//                    ajaxGet(ajaxUrlPlayer, "player-frame");
                });

            }
        }
        else{
            if (modResId != currentModResId) {
                newurl = "<?=Url::toRoute(['resource/course/play-preview','modResId'=>$modResId,
                'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                newurl = urlreplace(newurl, 'modResId', modResId);
//                newurl = urlreplace(newurl, 'scoId', scoId);
                window.location.href = newurl;

//                $("#catalog-frame").empty();
//                ajaxUrlCatalog = "<?//=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId, 'mode' => 'normal'])?>//";
//                ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
//                //alert(ajaxUrlCatalog);
//                ajaxGet(ajaxUrlCatalog, "catalog-frame");
//
//                $("#player-frame").empty();
//                ajaxUrlPlayer = "<?//=Url::toRoute(['resource/player/component-code-player','modResId'=>$modResId, 'courseId'=>$courseId, 'mode' => 'normal'])?>//";
//                ajaxUrlPlayer = ajaxUrlPlayer.replace("component-code", componentCode);
//                ajaxUrlPlayer = urlreplace(ajaxUrlPlayer, 'modResId', modResId);
//                //alert(ajaxUrlPlayer);
//                ajaxGet(ajaxUrlPlayer, "player-frame");
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
            // alert(ajaxUrlCatalog);
            ajaxGet(ajaxUrlCatalog, "catalog-frame");
        }
        else{
            ajaxUrlCatalog = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,
            'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
            'attempt'=>$attempt, 'mode' => 'preview'])?>";
            ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
            //alert(ajaxUrlCatalog);
            ajaxGet(ajaxUrlCatalog, "catalog-frame");
        }
    }




</script>
<div class="container">
    <!--<div style="height: 60px;"></div>-->
    <div class="row">
        <div class="col-md-12" >

            <div class="panel panel-default hotNews pull-left" id="outWindow">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> 课程播放
                    <a href="javascript:history.back();" style="float:right; color:#337ab7;">返回</a>
                </div>
                <div class="col-md-8" id="playWindow">
                    <div class="panel-body" >
                        <h5 style="line-height: 2.1em;">
                            <?=$model->courseware_name?>
                            <a href="#" class="btn btn-sm btn-default pull-right btnZoomIn">全屏</a>
                        </h5>
                        <hr/>
                        <div id="player-frame"></div>
                    </div>
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
<!-- 全屏脚本 -->
<script>
   $(".btnZoomIn").bind("click", function () {
        zoom();
    });
</script>