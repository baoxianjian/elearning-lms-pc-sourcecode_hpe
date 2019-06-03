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
<? if ($errorClient == "pc") {?>
    电脑端无法显示此课件，请登录移动端尝试
<? } else { ?>
    移动端无法显示此课件，请登录电脑端尝试
<? } ?>

<script>
    function change_size(zoom)
    {
        //此方法必须存在，以便play.php调用
    }

    $(document).ready(function() {
        var playZoom = getCookie("play_zoom");
        if (playZoom == "0")
        {
            change_size(true);
        }
    });
</script>
