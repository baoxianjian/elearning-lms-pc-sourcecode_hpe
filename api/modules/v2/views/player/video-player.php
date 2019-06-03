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
    <?php if ($player == "html5") {?>
        <video id="iframe-player" src="<?=$iframeUrl?>" width="100%" height="100%" controls="controls" preload="true">你的浏览器不支持Html5视频，请更换浏览器收看.</video>
    <?php
        }else{
    ?>
        <embed id="iframe-player" src="<?=$iframeUrl?>" type="video/x-ms-wmv" width="100%" height="100%" autoStart="1" showControls="1"  />
        *建议使用IE浏览器播放此课件
    <?php } ?>
