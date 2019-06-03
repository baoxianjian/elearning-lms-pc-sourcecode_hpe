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
        <iframe id="iframe-player" width="100%" height="100%" frameborder="0" src="<?=$iframeUrl?>"></iframe>
        <? }elseif(!empty($iframeCode)&&$type=='code'){?>
        <div id="iframe-player" style="height: 500px;width:100%">
            <?=$iframeCode;?>
        </div>
    <? } else { ?>
        文件地址为空，无法加载
    <? }?>
<?}elseif($display == 1){?>
    <? if ((!empty($iframeUrl)&&$type=='url')||(!empty($iframeUrl)&&$type=='file')) { ?>
        <script>
            window.open("<?=$iframeUrl?>");
        </script>
    <? }elseif(!empty($iframeCode)&&$type=='code'){?>
        <script>
            var url = "<?=Yii::$app->urlManager->createUrl(['resource/courseware/code-view','coursewareId'=>$coursewareId])?>";
            window.open(url);
        </script>

    <? } else { ?>
        文件地址为空，无法加载
    <? }?>

<?}?>

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