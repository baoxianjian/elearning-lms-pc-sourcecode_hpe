<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 9/9/2015
 * Time: 12:54 PM
 */
use yii\helpers\Url;

?>
<iframe id="iframe-player" data-type="exam" frameborder="0" src="<?=Url::toRoute(['investigation/course-play-investigation',])?>"+"?modResId=<?=$modResId?>&courseRegId=<?=$courseRegId?>&courseId=<?=$courseId?>&courseCompleteProcessId=<?=$courseCompleteProcessId?>&courseCompleteFinalId=<?=$courseCompleteFinalId?>"></iframe>

<script>
    $(document).ready(function() {
        LoadiFramePlayer();
    });

    function LoadiFramePlayer(){
//        resizeIframe();
        miniScreen();
        diffTemp();
    }
</script>