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

$this->title = $courseName;

$this->params['breadcrumbs'][] = ['url' => Yii::$app->urlManager->createUrl('resource/course/index'), 'label' => Yii::t('frontend','page_learn_path_tab_1')];
$this->params['breadcrumbs'][] = ['label'=>$courseName,'url'=>['view','id'=>$courseId]];
$this->params['breadcrumbs'][] = Yii::t('frontend','course_play');
$this->params['breadcrumbs'][] = '';

/* @var $model common\models\learning\LnCourseware */
?>
<title>test</title>
<?= Html::hiddenInput("duration",$duration,['id'=>'duration'])?>
<script>
    $(document).ready(function() {
//        alert('loadPlayer');
        loadPlayer();
        loadCatalog();
        loadControl();
        loadQuestion();
        showIntro();

        var duration = $("#duration").val() * 1000;
        //        alert(duration);
        setInterval("playerInterval()", duration);//1000为1秒钟，现在是20秒

        setInterval("aiccInterval()", 10000);//1000为1秒钟，现在是10秒

    });

    function loadControl() {
        $("#play-control-frame").empty();
        var scoId = "<?=$scoId?>";
        var ajaxUrl = "<?=Url::toRoute(['resource/course/play-control','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,'modType'=>$modType,
        'attempt'=>$attempt, 'mode' => 'normal'])?>";

        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
//        alert(ajaxUrl);
        setTimeout(function(){
                ajaxGet(ajaxUrl, "play-control-frame");
            },1000
        );
    }


    function loadCatalog(){
        var scoId = "<?=$scoId?>";
        var ajaxUrl = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,'modType'=>$modType,'courseType'=>$courseType,
        'attempt'=>$attempt, 'mode' => 'normal'])?>";

        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
//        alert(ajaxUrl);
        ajaxGet(ajaxUrl, "catalog-frame");
    }

    function loadPlayer(){
        $("#player-frame").empty();
        var scoId = "<?=$scoId?>";
//        alert(compnentCode);
        var ajaxUrl = "<?=Url::toRoute(['resource/player/'.$componentCode.'-player','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
        'attempt'=>$attempt, 'mode' => 'normal'])?>";
        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
//        alert(ajaxUrl);
        <?php
        /*加载考试结果，防止刷新*/
        if (isset($iframe) && $iframe == 'examination'){
        ?>
        ajaxUrl = "<?=Url::toRoute(['/exam-manage-main/play-result', 'id' => $resultUserId, 'mode' => 'course'])?>";
        <?php
        }
        ?>
        ajaxGet(ajaxUrl, "player-frame");
    }

    function reloadplayer(componentCode,modResId, scoId){

        var currentModResId = $("#currentModResId").val();
        var currentComponentCode = $("#currentComponentCode").val();
//        alert("componentCode:"+componentCode);

//        alert("modResId:"+modResId +", currentModResId:"+currentModResId);
        var ajaxUrlCatalog = "";
        var ajaxUrlPlayer = "";
        var newurl = "";
        if (componentCode == "scorm" || componentCode == "aicc") {

            var currentScoId = $("#currentScoId").val();
//            alert("scoId:"+scoId +", currentScoId:"+currentScoId);
            if (modResId != currentModResId || scoId != currentScoId) {
                newurl = "<?=Url::toRoute(['resource/course/play','modResId'=>$modResId, 'scoId'=>$scoId,
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
                var msg = "<?= Yii::t('frontend', 'reset_load_unit') ?>？";
                NotyConfirm(msg, function (data) {
                    newurl = "<?=Url::toRoute(['resource/course/play','modResId'=>$modResId, 'scoId'=>$scoId,
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
        }else if (componentCode == "examination" ){
            var currentExaminationId = $("#currentExaminationId").val();
            if (modResId != currentExaminationId) {
                newurl = "<?=Url::toRoute(['resource/course/play','modResId'=>$modResId,
                'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                newurl = urlreplace(newurl, 'modResId', modResId);
                window.location.href = newurl;
            }else{
                var msg = "<?= Yii::t('frontend', 'reset_load_unit') ?>？";
                NotyConfirm(msg, function (data) {
                    newurl = "<?=Url::toRoute(['resource/course/play','modResId'=>$modResId, 'scoId'=>$scoId,
                    'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                    newurl = urlreplace(newurl, 'modResId', modResId);
                    window.location.href = newurl;
                });
            }
        }
        else{
            if ((currentComponentCode == "scorm" || currentComponentCode == "aicc") && modResId != currentModResId) {
                newurl = "<?=Url::toRoute(['resource/course/play','modResId'=>$modResId,
                'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                newurl = urlreplace(newurl, 'modResId', modResId);
                window.location.href = newurl;
            }
            else if (modResId != currentModResId) {
                newurl = "<?=Url::toRoute(['resource/course/play','modResId'=>$modResId,
                    'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
                newurl = urlreplace(newurl, 'modResId', modResId);
                window.location.href = newurl;

                //如果当前不是scorm课件，则局部刷新页面（20160123：为了方便play页面检查课程已经完成时自动退出至View页面，所以禁用局部刷新功能）

//                $("#catalog-frame").empty();
//                ajaxUrlCatalog = "<?//=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
//        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,'modType'=>$modType,'courseType'=>$courseType,
//        'attempt'=>$attempt, 'mode' => 'normal'])?>//";
//                ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
//                //alert(ajaxUrlCatalog);
//                ajaxGet(ajaxUrlCatalog, "catalog-frame");
//
//                $("#player-frame").empty();
//                ajaxUrlPlayer = "<?//=Url::toRoute(['resource/player/component-code-player','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
//        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
//        'attempt'=>$attempt, 'mode' => 'normal'])?>//";
//                ajaxUrlPlayer = ajaxUrlPlayer.replace("component-code", componentCode);
//                ajaxUrlPlayer = urlreplace(ajaxUrlPlayer, 'modResId', modResId);
//                //alert(ajaxUrlPlayer);
//                ajaxGet(ajaxUrlPlayer, "player-frame");
//
//
//                $("#play-control-frame").empty();
//                ajaxUrlPlayer = "<?//=Url::toRoute(['resource/course/play-control','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
//        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
//        'attempt'=>$attempt, 'mode' => 'normal'])?>//";
//                ajaxUrlPlayer = urlreplace(ajaxUrlPlayer, 'modResId', modResId);
//                //alert(ajaxUrlPlayer);
//                ajaxGet(ajaxUrlPlayer, "play-control-frame");
            }
        }
    }


    function reloadCatalog(componentCode,modResId, scoId){
        $("#catalog-frame").empty();
        var ajaxUrlCatalog = "";
//        var ajaxUrlPlayer = "";
        if (componentCode == "scorm" || componentCode == "aicc") {
            ajaxUrlCatalog = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
            'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,'modType'=>$modType,'courseType'=>$courseType,
            'attempt'=>$attempt, 'mode' => 'normal'])?>";
            ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
            ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'scoId', scoId);
            // alert(ajaxUrlCatalog);
            ajaxGet(ajaxUrlCatalog, "catalog-frame");
        }
        else{
            ajaxUrlCatalog = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
            'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,'modType'=>$modType,'courseType'=>$courseType,
            'attempt'=>$attempt, 'mode' => 'normal'])?>";
            ajaxUrlCatalog = urlreplace(ajaxUrlCatalog, 'modResId', modResId);
            //alert(ajaxUrlCatalog);
            ajaxGet(ajaxUrlCatalog, "catalog-frame");
        }

        loadControl();
    }

    function loadQuestion(){
        $("#question-frame").empty();
        var ajaxUrl = "<?=Url::toRoute(['resource/course/get-tab-question','courseId'=>$courseId])?>";
//        alert(ajaxUrl);
        ajaxGet(ajaxUrl, "question-frame");
    }

    function playerInterval()
    {
//        var currentCourseCompleteProcessId = $("#currentCourseCompleteProcessId").val();
//        var currentCourseCompleteFinalId = $("#currentCourseCompleteFinalId").val();
        var currentResCompleteProcessId = $("#currentResCompleteProcessId").val();
        var currentResCompleteFinalId = $("#currentResCompleteFinalId").val();
        var currentComponentCode = $("#currentComponentCode").val();

//        alert('playerInterval');
        var ajaxUrl = "<?=Url::toRoute(['resource/course/update-duration',
            'mode'=>'normal',
            'courseCompleteProcessId'=>$courseCompleteProcessId,
            'courseCompleteFinalId'=>$courseCompleteFinalId])?>";
//        ajaxUrl = urlreplace(ajaxUrl, 'currentCourseCompleteProcessId', currentCourseCompleteProcessId);
//        ajaxUrl = urlreplace(ajaxUrl, 'currentCourseCompleteFinalId', currentCourseCompleteFinalId);
        ajaxUrl = urlreplace(ajaxUrl, 'resCompleteProcessId', currentResCompleteProcessId);
        ajaxUrl = urlreplace(ajaxUrl, 'resCompleteFinalId', currentResCompleteFinalId);
//         alert(ajaxUrl);
        ajaxData(ajaxUrl,
            "POST",
            {},
            "json",
            function(data){
//                alert(data.result);
            }
        );
    }


    function aiccInterval()
    {
//        var currentCourseCompleteProcessId = $("#currentCourseCompleteProcessId").val();
//        var currentCourseCompleteFinalId = $("#currentCourseCompleteFinalId").val();

        var currentComponentCode = $("#currentComponentCode").val();
        //alert(currentComponentCode);

        if (currentComponentCode == "aicc") {
            var currentModResId = $("#currentModResId").val();
            var currentScoId = $("#currentScoId").val();
            var currentStatus = $("#currentStatus").val();
            var currentIsResCompleteStr = $("#currentIsResCompleteStr").val();
//        alert('aiccInterval');
            var ajaxUrl = "<?=Url::toRoute(['resource/course/get-scorm-status',
            'courseCompleteFinalId'=>$courseCompleteFinalId,
            'courseRegId'=>$courseRegId,
            'modResId'=>$modResId,
            'userId'=>$userId,
            'scoId'=>$scoId,
            'attempt'=>$attempt])?>";
//        ajaxUrl = urlreplace(ajaxUrl, 'currentCourseCompleteProcessId', currentCourseCompleteProcessId);
//        ajaxUrl = urlreplace(ajaxUrl, 'currentCourseCompleteFinalId', currentCourseCompleteFinalId);
            ajaxUrl = urlreplace(ajaxUrl, 'modResId', currentModResId);
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', currentScoId);
//         alert(ajaxUrl);
            ajaxData(ajaxUrl,
                "POST",
                {},
                "json",
                function (data) {
                    var status = data.status;
                    var isResCompleteStr = data.isResCompleteStr;

                    if (currentStatus != status || currentIsResCompleteStr != isResCompleteStr) {

//                       alert("currentStatus:"+currentStatus)
//                        alert("status:"+status)
//                        alert("currentIsResCompleteStr:"+currentIsResCompleteStr)
//                        alert("isResCompleteStr:"+isResCompleteStr)

                        $("#currentStatus").val(status);
                        $("#currentIsResCompleteStr").val(isResCompleteStr);
                        //alert($("#currentStatus").val());
                        mod_scorm_catalog_update();
                    }
//                alert(data.result);
                }
            );
        }
    }

    function resizeIframe() {
        //alert('resizeIframe');
        console.log('resizeIframe() run');
        var rate = 0.7;
        var i = $('#iframe-player').attr('data-type');
        console.log("iframe-player data-type:" + i);
        if (i === "special") {
            return;
        }
        if (i == "doc" || i == "exam") {
            rate = 0.9;
        }

        var height = window.innerHeight * rate;

        console.log("height:" + height);

        if (height >= 500) {
            $('#playWindow').css('height', height);
            $('#iframe-player').css('height', height);
            $('#hideMenu').css('height', height + 40);
            //alert('resizeIframe1');
        } else {
            $('#playWindow').css('height', '500px');
            $('#iframe-player').css('height', '500px');
            $('#hideMenu').css('height', '530px');
            //alert('resizeIframe2');
        }
    }

    function miniScreen() {
        //alert('miniScreen');
        var
            iframeStyle = "",
            commentInputStyle = "miniInputWide",
            scrollHeight = $(document).scrollTop() + 50,
            hideAnswerTop = $('#hideAnswer').offset().top,
            i = $('#iframe-player').attr('data-type');

        if (i == "video") {
            iframeStyle = "fixedWindow";
            commentInputStyle = "miniInput";
        }

        if (scrollHeight > hideAnswerTop) {
            $('#iframe-player').addClass(iframeStyle);
            $('.commentInputMain').addClass(commentInputStyle);
            //alert('miniScreen1');
        } else {
            $('#iframe-player').removeClass(iframeStyle);
            $('.commentInputMain').removeClass(commentInputStyle);
            //alert('miniScreen2');
        }
    }

    function openMenu() {
        var
            el = $('#hideMenu'),
            pw = $('#playWindow');

        if (el.hasClass('hideMenuOn')) {
            el.removeClass('hideMenuOn');
            pw.css('padding-right', '0');
        } else {
            el.addClass('hideMenuOn');
            pw.css('padding-right', '40%');
        }
    }

    function diffTemp() {
        var i = $('#iframe-player').attr('data-type');
        if (i == "doc" || i == "exam") {
            openMenu();
        }
    }

    function showIntro() {
        var closeCourseIntro = getCookie("closeCourseIntro");

        if (closeCourseIntro != "1") {
            setTimeout("$('.play_intro').addClass('play_intro_open');", 100);
        }
        else {
            $('.play_intro_container').remove();
        }
        // $('body').css('overflow','hidden');
    }

    function closeIntro() {
        $('.play_intro').removeClass('play_intro_open');
        setCookie("closeCourseIntro","1");
        setTimeout("$('.play_intro_container').remove()", 500);
        // $('body').css('overflow','auto');
    }

    function changeCourseWareStatus($status) {
//        alert($status);
        var done = "<?=Yii::t('frontend','complete_status_done');?>";
        var doing = "<?=Yii::t('frontend','complete_status_doing');?>";
        if ($status == "2") {
            $('#currentCoursewareStatus').val("("+done+")");
        }
        else {
            $('#currentCoursewareStatus').val("("+doing+")");
        }
    }


    $(window).resize(resizeIframe);
    $(document).bind('scroll', miniScreen);

    function launchFullscreen(element) {
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullScreen();
        }
    }

    function removeFullScreenInIE() {
        //alert('removeFullScreenInIE');
        if (!!window.ActiveXObject || "ActiveXObject" in window)
            $('.openFullScreen').remove();
        else
            return false;
    }
</script>

<div class="container" style="width: 100% !important;padding-top: 0px;">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

        <div class="col-md-12" >
            <div class="panel panel-default hotNews pull-left" id="outWindow">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?= Yii::t('frontend', 'course_play') ?>
                    <span id="currentCoursewareStatus">
                        <?php if ($currentCoursewareStatus == "2") {?>
                            (<?=Yii::t('frontend','complete_status_done');?>)
                        <?php } else { ?>
                            (<?=Yii::t('frontend','complete_status_doing');?>)
                        <?php } ?>
                    </span>
                </div>
                <div class="col-xs-12" id="playWindow">
                    <div id="player-frame"></div>
                </div>

                <div id="hideMenu">
                    <div class="windowBtns" id="play-control-frame"></div>
                    <div class="panel-body coursePlayList">
                        <div id="catalog-frame"></div>
                    </div>
                </div>

                <div class="col-xs-12" id="hideAnswer">
                    <div class="panel-body">
                        <h2><?= Yii::t('frontend', 'course_qa') ?></h2>
                        <div id="question-frame"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="play_intro_container">
    <div class="play_intro">
        <img src="/static/frontend/images/play_introduction.png" width="100%;">
        <div class="centerBtnArea">
            <a href="###" class="btn btn-success centerBtn play_intro_open_btn" onclick="closeIntro();"><?= Yii::t('frontend', 'i_know') ?></a>
        </div>
    </div>
</div>