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

<?//
//$TFileModelHelper = new \common\helpers\TFileModelHelper();
//$TFileModelHelper->Play($fileId,$download = false);
//?>

<div class="am-onePic" style="background:#000;">
    <img src="/static/app/i/lesson_default.jpeg">
    <audio controls="controls" style="margin-top:5px;">
        <source src="<?= $iframeUrl?>" type="audio/mpeg" autostart="true">
    </audio>
</div>

