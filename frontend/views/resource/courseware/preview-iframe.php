<?php
use common\models\learning\LnComponent;
use common\models\learning\LnModRes;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;

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
        var ajaxUrl = "<?=Url::toRoute(['resource/course/catalog','coursewareId'=>$coursewareId, 'mode' => 'preview'])?>";

        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
        //ajaxGet(ajaxUrl, "catalog-frame");
    }

    function zoom() {
        var btnZoomIn = $(".btnZoomIn");
        var playWindow = $("#playWindow");
        var hideMenu = $("#hideMenu");
        var outWindow = $("#outWindow");

        if (!playWindow.hasClass("expandWin")) {
            btnZoomIn.text("<?=Yii::t('frontend', 'narrow')?>");
            playWindow.addClass("expandWin");
            outWindow.addClass("expandWin");
            hideMenu.addClass("hide");
            change_size(true);
        } else {
            btnZoomIn.text("<?=Yii::t('frontend', 'full_screen')?>");
            playWindow.removeClass("expandWin");
            outWindow.removeClass("expandWin");
            hideMenu.removeClass("hide");
            change_size(false);
        }
    }

    function loadPlayer(){
        var scoId = "<?=$scoId?>";
        var ajaxUrl = "<?=Url::toRoute(['resource/player/'.$componentCode.'-player','coursewareId'=>$coursewareId, 'mode' => 'preview'])?>";
        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
        ajaxGet(ajaxUrl, "player-frame");
    }

    function getCookie(){
        /**/
    }

    function reloadplayer(componentCode,coursewareId, scoId){

        var currentCoursewareId = $("#currentCoursewareId").val();
        var ajaxUrlCatalog = "";
        var ajaxUrlPlayer = "";
        var newurl = "";
        if (componentCode == "scorm") {

            var currentScoId = $("#currentScoId").val();
            if (coursewareId != currentCoursewareId || scoId != currentScoId) {
                newurl = "<?=Url::toRoute(['resource/courseware/preview-iframe','coursewareId'=>$coursewareId, 'scoId'=>$scoId])?>";
                newurl = urlreplace(newurl, 'coursewareId', coursewareId);
                newurl = urlreplace(newurl, 'scoId', scoId);
                window.location.href = newurl;
            }
        }
        else{
            if (coursewareId != currentCoursewareId) {
                newurl = "<?=Url::toRoute(['resource/courseware/preview-iframe','coursewareId'=>$coursewareId])?>";
                newurl = urlreplace(newurl, 'coursewareId', coursewareId);
                window.location.href = newurl;
            }
        }
    }
</script>
<style>
    body,body>.container:nth-of-type(1) {padding-top: 0;}
    #iframe-player {width: 100%;}
</style>
<div class="container">
    <div class="row">
        <!--<div style="height:80px"></div>-->
        <div class="col-md-12" >
            <div class="panel panel-default hotNews" id="outWindow">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('frontend', 'course_play')?>
                </div>
                <div class="col-md-12" id="playWindow" style="height: auto!important;">
                    <div class="panel-body" style="padding-bottom: 0!important;">
                        <h5 style="line-height: 2.1em;"><?=$model->courseware_name?></h5>
                        <!--<div class="row controlPanel">
                            <a href="javascript:;" class="pull-left btnZoomIn"><i class="glyphicon glyphicon-fullscreen"></i><?/*=Yii::t('frontend', 'full_screen')*/?></a>
                        </div>-->
                    </div>
                    <hr/>
                    <div id="player-frame" ></div>
                </div>

                <div class="col-md-12" id="hideAnswer">
                    <div class="panel-body" style="width: 1000px"></div>
                </div>
                <div class="c"></div>
            </div>
        </div>
        <div style="height:80px"></div>
    </div>
</div>
<!-- 全屏脚本 -->
<script>
    $(".btnZoomIn").bind("click", function () {
        zoom();
    });
    function miniScreen(){}
    function diffTemp(){}
    function resizeIframe() {
        //alert('resizeIframe');
        var rate = 0.7;
        var i = $('#iframe-player').attr('data-type');
        if (i == "doc") {
            rate = 0.9;
        }
        if ($('#iframe-player').is('audio')){
            return ;
        }

        var height = window.innerHeight * rate;

        if (height >= 500) {
            $('#playWindow').css('min-height', height);
            $('#iframe-player').css('height', height);
            $('#hideMenu').css('height', height + 40);
            //alert('resizeIframe1');
        } else {
            $('#playWindow').css('min-height', '500px');
            $('#iframe-player').css('height', '500px');
            $('#hideMenu').css('height', '530px');
            //alert('resizeIframe2');
        }
    }
</script>