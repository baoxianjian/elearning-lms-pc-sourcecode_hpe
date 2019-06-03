<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use common\models\learning\LnCourseware;
use common\models\learning\LnFiles;
use common\helpers\TFileModelHelper;
use yii\helpers\Html;

?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<!--                学习课程达到45分钟以上为完成-->
    <?php if ($fileType == LnCourseware::FILE_TYPE_EMBED_CODE) {?>
       <div id="iframe-player" data-type="video"><?=$fileEmbedCode?></div>
    <?php
        }else if ($fileType == LnCourseware::FILE_TYPE_LOCAL){
    ?>
        <?php if ($formatTransferStatus == LnFiles::FORMAT_TRANSFER_STATUS_WAITING){ ?>
        <div id="iframe-player" data-type="doc">
            <?=Yii::t('frontend', 'video_change_code_waiting')?>
        </div>
        <?php } else if ($formatTransferStatus == LnFiles::FORMAT_TRANSFER_STATUS_FAILED){ ?>
        <div id="iframe-player" data-type="doc">
            <?=Yii::t('frontend', 'video_change_code_failed')?>
        </div>
        <?php } else { ?>
            <script src="/components/flowplayer-flash/flowplayer-3.2.13.min.js"></script>

            <div id="iframe-player" data-type="video"></div>

            <script language="JavaScript">
                flowplayer("iframe-player", "/components/flowplayer-flash/flowplayer-3.2.18.swf", {
                    clip: {
                        url: '<?=$fileUrl?>',//修改为$fileUrl
//                        url: 'mp4:vod/demo.flowplayer/buffalo_soldiers.mp4',//修改为$fileUrl
                        scaling: 'fit',
                        provider: 'rtmp'
                    },
                    plugins: {
                        rtmp: {
                            url: "/components/flowplayer-flash/flowplayer.rtmp-3.2.13.swf",
                            // netConnectionUrl defines where the streams are found
//                            netConnectionUrl: 'rtmp://r.demo.flowplayer.netdna-cdn.com/play/'//修改为$rtmpUrl
                            netConnectionUrl: '<?=$rtmpUrl?>'
                        }
                    },
                    canvas: {
                        backgroundGradient: 'none'
                    }
                });
            </script>
        <?php } ?>
    <?php
        }else{
    ?>
        <video id="iframe-player" data-type="video" src="<?=$fileUrl?>" controls="controls"><?=Yii::t('frontend', 'browser_do_not_support')?></video>
    <?php } ?>

<?// if ($isAllowDownload) { ?>
<!--    <div class="col-md-4 col-sm-4">-->
<!--        <div class="cover text-center">-->
<!--            <a href="--><?//= $downloadUrl ?><!--" target='_blank'>-->
<!--                <img src="/static/frontend/images/file/video-file.png" />-->
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
