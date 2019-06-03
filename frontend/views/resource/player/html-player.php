<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?= Html::hiddenInput("currentScoId",$currentScoId,['id'=>'currentScoId'])?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<? if($display == 0){?>
    <? if ((!empty($iframeUrl)&&$type=='url')||(!empty($iframeUrl)&&$type=='file')) { ?>
        <iframe id="iframe-player" data-type="scorm" frameborder="0" src="<?=$iframeUrl?>"></iframe>
        <? }elseif(!empty($iframeCode)&&$type=='code'){?>
        <div id="iframe-player" data-type="scorm">
            <?=$iframeCode;?>
        </div>
    <? } else { ?>
        <div id="iframe-player" data-type="doc">
            <?=Yii::t('frontend', 'file_empty_no_loading')?>
        </div>
    <? }?>
<?}elseif($display == 1){?>
    <? if ((!empty($iframeUrl)&&$type=='url')||(!empty($iframeUrl)&&$type=='file')) { ?>
        <script>
            window.open("<?=$iframeUrl?>");
        </script>
        <div id="iframe-player" data-type="doc">
            <?=Yii::t('frontend', 'new_window_display')?>
        </div>
    <? }elseif(!empty($iframeCode)&&$type=='code'){?>
        <script>
            var url = "<?=Yii::$app->urlManager->createUrl(['resource/courseware/code-view','coursewareId'=>$coursewareId])?>";
            window.open(url);
        </script>
        <div id="iframe-player" data-type="doc">
            <?=Yii::t('frontend', 'new_window_display')?>
        </div>
    <? } else { ?>
        <div id="iframe-player" data-type="doc">
            <?=Yii::t('frontend', 'file_empty_no_loading')?>
        </div>
    <? }?>

<?}?>

<script>
    $(document).ready(function() {
        LoadiFramePlayer();
    });

    function LoadiFramePlayer(){
        resizeIframe();
        miniScreen();
        diffTemp();
    }
</script>