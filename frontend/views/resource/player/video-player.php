<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use common\models\framework\FwDictionary;
use yii\helpers\Html;

?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<link href="/components/danmu-player/css/scojs.css" rel="stylesheet">
<link href="/components/danmu-player/css/colpick.css" rel="stylesheet">
<link href="/components/danmu-player/css/bootstrap.css" rel="stylesheet">
<link href="/components/danmu-player/css/main.css" rel="stylesheet" >

<!--<script src="/components/danmu-player/js/jquery-2.1.4.min.js"></script>-->
<script src="/components/danmu-player/js/jquery.shCircleLoader.js"></script>
<script src="/components/danmu-player/js/sco.tooltip.js"></script>
<script src="/components/danmu-player/js/colpick.js"></script>
<script src="/components/danmu-player/js/jquery.danmu.js"></script>
<script src="/components/danmu-player/js/main.js"></script>

<!--                学习课程达到45分钟以上为完成-->
    <?php if ($player == "html5") {?>
        <?php if ($isAllowDanmu == FwDictionary::YES) {?>
            <div id="iframe-player" data-type="video"></div>
        <?php }else{?>
            <video id="iframe-player"  data-type="video" style="width:100%"  src="<?=$iframeUrl?>" controls="controls" ><?=Yii::t('frontend', 'browser_do_not_support')?></video>
        <?php } ?>
    <?php
        }else{
    ?>
        <embed id="iframe-player" data-type="video" style="width:100%" src="<?=$iframeUrl?>" type="video/x-ms-wmv" autoStart="1" showControls="1"  />
        <?=Yii::t('frontend', 'play_by_ie')?> 
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
    <?php if ($player == "html5" && $isAllowDanmu == FwDictionary::YES) {?>
    var playerHeight = window.innerHeight *  0.7 - 45;
    var getDanMuUrl = "<?=Yii::$app->urlManager->createUrl(["/resource/player/get-danmu",
        'courseId' => $courseId,
        'modResId' => $modResId])?>";
    var postDanMuUrl = "<?=Yii::$app->urlManager->createUrl(["/resource/player/post-danmu",
        'courseId' => $courseId,
        'modResId' => $modResId])?>";
    $("#iframe-player").DanmuPlayer({
        src:"<?=$iframeUrl?>",
        width:"99%",
//        autostart: true
        height:playerHeight,
        urlToGetDanmu:getDanMuUrl,
        urlToPostDanmu:postDanMuUrl
    });

    //自动播放
    $(".play-btn").click();

//    $("#iframe-player .danmu-div").danmu("addDanmu",[
//        { "text":"这是滚动弹幕" ,color:"white",size:1,position:0,time:2}
//        ,{ "text":"我是嫩总" ,color:"green",size:1,position:0,time:3}
//        ,{ "text":"哈哈哈啊哈" ,color:"black",size:1,position:0,time:10}
//        ,{ "text":"这是顶部弹幕" ,color:"yellow" ,size:1,position:1,time:3}
//        ,{ "text":"这是底部弹幕" , color:"red" ,size:1,position:2,time:9}
//        ,{ "text":"大家好，我是学魅" ,color:"orange",size:1,position:1,time:3}
//
//    ]);

    <?php
    }
    ?>
</script>
