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
    <iframe id="iframe-player" width="100%" height="500px" frameborder="0" src="<?=$iframeUrl?>"></iframe>
<? } else { ?>
    文件地址为空，无法加载
<? }?>

<script>
    $(document).ready(function() {

        LoadiFramePlayer();
    });

    function change_size(zoom)
    {
        //此方法必须存在，以便play.php调用
        var iframeWindow = $("#iframe-player");
        if (zoom) {
            //alert(zoom);
            iframeWindow.height(750);
        }
        else
        {
            if (navigator.userAgent.indexOf('MSIE') >= 0){
                //alert('你是使用IE')
            }
            else {
                iframeWindow.height(500);
            }
        }
    }


    function LoadiFramePlayer(){
//        alert(compnentCode);
        var playZoom = getCookie("play_zoom");
        if (playZoom == "0")
        {
            change_size(true);
        }
    }
</script>