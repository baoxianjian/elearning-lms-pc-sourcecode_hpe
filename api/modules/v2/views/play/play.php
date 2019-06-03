<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 15/11/27
 * Time: 上午10:29
 */
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
?>


<?= Html::hiddenInput("duration",$duration,['id'=>'duration'])?>

<script>
    $(document).ready(function() {

        setInterval(playerInterval, 15*1000);//1000为1秒钟
        setInterval(aiccInterval, 10000);//1000为1秒钟
        loadPlayer();
    });


    function loadPlayer(){
        $("#player-frame").empty();
        var scoId = "<?=$scoId?>";
        var ajaxUrl = "<?=Url::toRoute(['player/'.$componentCode.'-player','modResId'=>$modResId, 'courseId'=>$courseId,'courseRegId'=>$courseRegId,
        'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,'attempt'=>$attempt, 'mode' => 'normal','system_key'=>$system_key,'access_token'=>$access_token,'supportEncryptPdfVer'=>$supportEncryptPdfVer])?>";
        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
        <?php
       /*加载考试结果，防止刷新*/
       if (isset($iframe) && $iframe == 'examination'){
       ?>
        ajaxUrl = "<?=Url::toRoute(['exam-manage-main/play-result', 'id' => $resultUserId, 'mode' => 'course','access_token'=>$access_token,'system_key'=>$system_key])?>";
        <?php
        }
        ?>
        ajaxGet(ajaxUrl,"player-frame");
    }



    function playerInterval()
    {
        var currentResCompleteProcessId = "<?= $resCompleteProcessId ?>";
        var currentResCompleteFinalId = "<?=$resCompleteFinalId ?>";

        var currentComponentCode = $("#currentComponentCode").val();

        var ajaxUrl = "<?=Url::toRoute(['/v2/play/record-data',
            'mode'=>'normal',
            'courseCompleteProcessId'=>$courseCompleteProcessId,
            'courseCompleteFinalId'=>$courseCompleteFinalId,
            'system_key'=>$system_key,
            'courseRegId' => $courseRegId,
            'modResId' => $modResId,
            'scoId' => $scoId,
            'coursewareId' => $coursewareId,
            'attempt' => $attempt,
            'access_token'=>$access_token])?>";
        ajaxUrl = urlreplace(ajaxUrl, 'resCompleteProcessId', currentResCompleteProcessId);
        ajaxUrl = urlreplace(ajaxUrl, 'resCompleteFinalId', currentResCompleteFinalId);
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
        var currentComponentCode = $("#currentComponentCode").val();

        if (currentComponentCode == "aicc") {
            var currentModResId = $("#currentModResId").val();
            var currentScoId = $("#currentScoId").val();
            var currentStatus = $("#currentStatus").val();
            var currentIsResCompleteStr = $("#currentIsResCompleteStr").val();
            var ajaxUrl = "<?=Url::toRoute(['play/get-scorm-status',
            'courseCompleteFinalId'=>$courseCompleteFinalId,
            'courseRegId'=>$courseRegId,
            'modResId'=>$modResId,
            'scoId'=>$scoId,
            'attempt'=>$attempt,
            'system_key'=>$system_key,
            'access_token'=>$access_token])?>";
            ajaxUrl = urlreplace(ajaxUrl, 'modResId', currentModResId);
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', currentScoId);
            ajaxData(ajaxUrl,
                "POST",
                {},
                "json",
                function (data) {
                    var status = data.data.status;
                    var isResCompleteStr = data.data.isResCompleteStr;

                    if (currentStatus != status || currentIsResCompleteStr != isResCompleteStr) {

                        $("#currentStatus").val(status);
                        $("#currentIsResCompleteStr").val(isResCompleteStr);
                        mod_scorm_catalog_update();
                    }
                }
            );
        }
    }

</script>

<div id="player-frame" style="width:100%;height:100%;"></div>

