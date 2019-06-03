<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use common\crpty\AES;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<? if ($isConvertOffice) { ?>
<!--    需要转换成PDF-->
    <? if (!$isConverted) { ?>

            <h5 class="cover text-center">
                <?=$downloadFileName?>，文档正在转换格式，请稍后再来
                <?= $iframeUrl ?>
            </h5>

    <? } else {
        $time = time();
        $str = base64_encode($file_id . '|||' . $time);
        $aes = new AES();
        $hash = $aes->encrypt($str);
        ?>
        <iframe src="/components/pdfplayer/web/viewer.html?file=<?= urlencode( Url::toRoute( ['player/pdf-view', 'id'=>$file_id, 'hash'=>$hash[1]] ) ) ?>" width="100%" height="100%"></iframe>

    <? } ?>

<? } else { ?>
    <? if ($isAllowDownload) { ?>
        <!--                学习课程达到45分钟以上为完成-->
        <div class="col-md-4 col-sm-4">
            <div class="cover text-center">
                <a href="<?= $iframeUrl ?>" target='_blank'>
                    <img src="/static/frontend/images/file/<?=$fileType?>-file.png" />
                </a>
            </div>
            <h5 class="cover text-center">
                <? echo Html::a($downloadFileName,$iframeUrl,['target'=>'_blank'])?>
            </h5>
        </div>

    <? } else {?>

        <div class="col-md-4 col-sm-4">
            <div class="cover text-center">
                <img src="/static/frontend/images/file/<?=$fileType?>-file.png" />
            </div>
            <h5 class="cover text-center">
                <?=$downloadFileName?>
            </h5>
        </div>
    <? } ?>
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
