<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use yii\helpers\Html;

?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<!--                学习课程达到45分钟以上为完成-->
<div id="iframe-player" data-type="doc">
    <? if ($errorClient == "pc") {?>
        <?=Yii::t('frontend', 'file_empty_no_loading')?>
    <? } else { ?>
        <?=Yii::t('frontend', 'course_mobile_not_use')?>
    <? } ?>
</div>

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
