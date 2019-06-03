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


<noscript>
    <div id="noscript">
        <?= Yii::t('common','scorm_no_script');?>
    </div>
</noscript>

<? if (!empty($iframeUrl)) { ?>
    <iframe id="iframe-player" data-type="scorm"  frameborder="0" src="<?=$iframeUrl?>"></iframe>
<? } else { ?>
    <div id="iframe-player" data-type="doc" >
        <?=Yii::t('frontend', 'file_empty_no_loading')?>
    </div>
<? }?>

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