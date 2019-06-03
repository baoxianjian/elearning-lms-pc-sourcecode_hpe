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
<?php if ($player == "flv") {?>
    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
            codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab" width="100%">
        <param name="movie" value="/components/flvplayer/Flvplayer.swf?vcastr_file=<?=$iframeUrl  . "&IsAutoPlay=1" ?>">
        <param name="quality" value="high">
        <param name="wmode" value="transparent">
        <param name="allowFullScreen" value="true" />
<!--        <param name="IsAutoPlay" value="1" />-->
        <embed id="iframe-player" data-type="video"
               src="/components/flvplayer/Flvplayer.swf?vcastr_file=<?=$iframeUrl . "&IsAutoPlay=1" ?>"
               allowFullScreen="true"
               quality="high"
               pluginspage="http://www.macromedia.com/go/getflashplayer"
               type="application/x-shockwave-flash"
               width="100%"
               wmode="transparent">
        </embed>
    </object>
<?php
    }else{
?>
    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
            codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab" width="100%">
        <param name="movie" value="<?=$iframeUrl?>">
        <param name="quality" value="high">
        <param name="wmode" value="transparent">
        <param name="Play" value="true">
        <param name="Loop" value="false">
        <embed id="iframe-player" data-type="video"
               src="<?=$iframeUrl?>"
               play=true
               loop=false
               quality=high pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"
               type="application/x-shockwave-flash"
               width="100%"
               wmode="transparent">
        </embed>
    </object>
<?php } ?>

<?// if ($isAllowDownload) { ?>
<!--    <div class="col-md-4 col-sm-4">-->
<!--        <div class="cover text-center">-->
<!--            <a href="--><?//= $downloadUrl ?><!--" target='_blank'>-->
<!--                <img src="/static/frontend/images/file/flash-file.png" />-->
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
