<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/10/2015
 * Time: 5:53 PM
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\crpty\AES;

$time = time();
$str = base64_encode($file_id . '|||' . $time);
$aes = new AES();
$hash = $aes->encrypt($str);
?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<!--<script src="/static/frontend/js/jquery.media.js"></script>-->
<!--                学习课程达到45分钟以上为完成-->
<iframe id="iframe-player" data-type="doc" frameborder="0" src="/components/pdfplayer/web/viewer.html?file=<?= urlencode( Url::toRoute( ['/common/pdf-view', 'id'=>$file_id, 'hash'=>$hash[1]] ) ) ?>"></iframe><!--<a class="media" href="/components/pdfplayer/web/viewer.html?file=/common/pdf-view.html?id%3D--><?//=$file_id?><!--"></a>-->

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