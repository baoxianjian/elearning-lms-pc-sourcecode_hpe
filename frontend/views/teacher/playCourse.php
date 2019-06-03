<?php
/**
 * User: adophper
 * Date: 2015/12/8
 * Time: 11:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;

?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=$resourceName?></h4>
</div>
<div class="content">
    <div id="player-frame" style="/*min-height: 500px; height: auto;*/"></div>
    <div class="c"></div>
</div>
<div class="c"></div>
<script>
    var scoId = "<?=$scoId?>";
    var ajaxUrl = "<?=Url::toRoute(['resource/player/'.$componentCode.'-player', 'courseId'=>$courseId,  'modResId'=>$modResId, 'courseRegId' => null,
    'courseCompleteFinalId'=>$courseCompleteFinalId,'courseCompleteProcessId'=>$courseCompleteProcessId,
    'attempt'=>$attempt, 'mode' => 'preview'])?>";
    if (scoId != "") {
        ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
    }
    $(function() {
        $.get(ajaxUrl, function (html) {
            if (html) {
                $("#player-frame").html(html);
                <?php
                if ($componentCode == 'investigation-preview'){
                ?>
                $("#player-frame #teacher_info").find(".blockScreen").remove();
                <?php
                }elseif ($componentCode == 'audio'){
                ?>
                $("#iframe-player").css('minHeight', '0px');
                var t = setInterval(function(){
                    if ($("#iframe-player").length > 0) {
                        $("#iframe-player").css('minHeight', '0px');
                        clearInterval(t);
                    }
                }, 1000);
                <?php
                }elseif ($componentCode == 'homework'){
                ?>
                $("#iframe-player").css('minHeight', '0px');
                var t = setInterval(function(){
                    if ($("#iframe-player").length > 0) {
                        $("#iframe-player").css('minHeight', '0px');
                        clearInterval(t);
                    }
                }, 1000);
                <?php
                }elseif ($componentCode == 'pdf'){
                ?>
                $("#iframe-player").css('overflow-y', 'scroll');
                <?php
                }
                ?>
            }
        });
    });

 /*   var t = setInterval(function(){
        if ($("#iframe-player").length > 0) {
            $("#iframe-player").css({
                width: '100%',
                minHeight: '500px',
                height: 'auto'
            });
            clearInterval(t);
        }
    }, 1000);*/
</script>