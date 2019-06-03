<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use yii\helpers\Html;

?>
<style type="text/css">
    #iframe-player{height:70px !important;}
</style>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<?//
//$TFileModelHelper = new \common\helpers\TFileModelHelper();
//$TFileModelHelper->Play($fileId,$download = false);
//?>
<div style="width: 100%;height: 350px;display: block;overflow: hidden;text-align: center;">
    <img src="/static/frontend/images/course_theme_big.png" style="width: 600px;">
</div>
<audio id="iframe-player" data-type="video" controls="controls" style="width: 100%">
    <source src="<?= $iframeUrl?>" type="audio/mpeg" autostart="true">
    <?=Yii::t('frontend', 'warning_for_ie9')?>
</audio>

<?// if ($isAllowDownload) { ?>
<!--    <div class="col-md-4 col-sm-4">-->
<!--        <div class="cover text-center">-->
<!--            <a href="--><?//= $downloadUrl ?><!--" target='_blank'>-->
<!--                <img src="/static/frontend/images/file/audio-file.png" />-->
<!--            </a>-->
<!--        </div>-->
<!--        <h5 class="cover text-center">-->
<!--            --><?// echo Html::a($downloadFileName,$downloadUrl,['target'=>'_blank'])?>
<!--        </h5>-->
<!--    </div>-->
<!---->
<?// }?>

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