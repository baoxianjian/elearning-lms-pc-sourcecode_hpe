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

?>
<!--<a href="###" class="glyphicon glyphicon-fullscreen openFullScreen" onclick="Fullscreen();">&nbsp;--><?//= Yii::t('frontend', 'full_screen') ?><!--</a>-->
<a href="###" class="glyphicon glyphicon-th-list hideMenuBtn" onclick="openMenu();">&nbsp;<?= Yii::t('frontend', 'course_unit') ?></a>
<? if (!empty($previousScoId)) {?>
    <!--        上一单元-->
    <a href="###" class="glyphicon glyphicon-step-backward" onclick="PreviousSco();" id="btnPreviousSco">&nbsp;<?= Yii::t('frontend', 'last_part') ?></a>
<? } else if (!empty($previousModresId)) {?>
    <a href="###" class="glyphicon glyphicon-step-backward" onclick="PreviousModres();" id="btnPreviousModres">&nbsp;<?= Yii::t('frontend', 'last_part') ?></a>
<? } ?>

<? if (!empty($nextScoId)) {?>
    <!--        下一单元-->
    <a href="###" class="glyphicon glyphicon-step-forward" onclick="NextSco();" id="btnNextSco">&nbsp;<?= Yii::t('frontend', 'next_part') ?></a>
<? } else if (!empty($nextModresId) && $nextCanRun) {?>
    <a href="###" class="glyphicon glyphicon-step-forward" onclick="NextModres();" id="btnNextModres">&nbsp;<?= Yii::t('frontend', 'next_part') ?></a>
<? } ?>

<? if (!$isCourseComplete && ($componentCode == 'scorm' || $componentCode == 'aicc') && $canAttempt) {?>
    <a href="###" class="glyphicon glyphicon-repeat" id="btnRestartSco" onclick="RestartConfirmMod();">&nbsp;<?= Yii::t('frontend', 'reset_part') ?></a>
<? } ?>
<? if (!$isCourseComplete && ($componentCode == 'scorm' || $componentCode == 'aicc') && $scoCount > 1 && $canAttempt) {?>
    <a href="###" class="glyphicon glyphicon-refresh" id="btnRestartMod" onclick="RestartConfirmSco();">&nbsp;<?= Yii::t('frontend', 'reset_unit') ?></a>
<? } ?>
<a href="<?=Yii::$app->urlManager->createUrl(['resource/course/view','id'=>$courseId])?>" class="glyphicon glyphicon-stop" id="btnExit">&nbsp;<?= Yii::t('frontend', 'exit_play') ?></a>

<script>
    $(document).ready(function() {
        removeFullScreenInIE();
    });

    function Fullscreen()
    {
        launchFullscreen(document.getElementById('iframe-player'));
    }
    function PreviousModres()
    {
        var modResId = "<?=$previousModresId?>";
        var componentCode = "<?=$previousComponentCode?>";

        reloadplayer(componentCode, modResId, '');
    }

    function PreviousSco()
    {
        var modResId = "<?=$modResId?>";
        var componentCode = "scorm";
        var scoId = "<?=$previousScoId?>";

        reloadplayer(componentCode, modResId, scoId);
    }

    function NextSco()
    {
        var modResId = "<?=$modResId?>";
        var componentCode = "scorm";
        var scoId = "<?=$nextScoId?>";

        reloadplayer(componentCode, modResId, scoId);
    }

    function NextModres()
    {
        var modResId = "<?=$nextModresId?>";
        var componentCode = "<?=$nextComponentCode?>";

        reloadplayer(componentCode, modResId, '');
    }


    function RestartConfirmMod()
    {
        var modResId = "<?=$modResId?>";
        var courseId = "<?=$courseId?>";
        var msg = "<?= Yii::t('frontend', 'reset_confirm_study_record') ?>？";

        var ajaxUrl = "<?=Yii::$app->urlManager->createUrl(['resource/course/restart-modres','mode'=>'mod','courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);

        NotyConfirm(msg,  function(e){
            //alert(ajaxUrl);
            RestartAction(ajaxUrl);
        });
    }

    function RestartConfirmSco()
    {
        var modResId = "<?=$modResId?>";
        var courseId = "<?=$courseId?>";
        var scoId = "<?=$scoId?>";

        var msg = "<?= Yii::t('frontend', 'reset_confirm_study_record') ?>？";

        var ajaxUrl = "<?=Yii::$app->urlManager->createUrl(['resource/course/restart-modres','mode'=>'sco','courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId])?>";
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);

        NotyConfirm(msg,  function(e){
            //alert(ajaxUrl);
            RestartAction(ajaxUrl);
        });
    }

    function RestartAction(ajaxUrl)
    {
        ajaxData(ajaxUrl,
            "POST",
            {},
            "json",
            function (data) {
                var result = data.result;
                if (result == "success")
                {
//                    alert(result);
                    location.reload();
                }
                else
                {
                    var message = data.message;
                    NotyWarning(message);
                }
            }
        );
    }

//    function mod_scorm_launch_next_sco() {
//        alert('mod_scorm_launch_next_sco');
//    }
//
//    function mod_scorm_launch_prev_sco() {
//        alert('mod_scorm_launch_prev_sco');
//    }
</script>