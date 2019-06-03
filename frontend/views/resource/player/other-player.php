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

<div id="iframe-player" data-type="doc">
<? if ($isAllowDownload) { ?>
    <!--                学习课程达到45分钟以上为完成-->
    <div class="col-md-4 col-sm-4">
        <div class="cover text-center">
            <a href="<?= $iframeUrl ?>" target='_blank'>
                <img src="/static/frontend/images/file/other-file.png" />
            </a>
        </div>
        <h5 class="cover text-center">
            <? echo Html::a($downloadFileName,$iframeUrl,['target'=>'_blank'])?>
        </h5>
    </div>

<? } else {?>

    <div class="col-md-4 col-sm-4">
        <div class="cover text-center">
            <img src="/static/frontend/images/file/other-file.png" />
        </div>
        <h5 class="cover text-center">
            <?=$downloadFileName?>
        </h5>
    </div>

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
