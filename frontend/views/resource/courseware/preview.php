<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */

use common\models\learning\LnComponent;
use common\models\learning\LnModRes;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;

$this->pageTitle = Yii::t('frontend', 'page_info_good_cancel');
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common', 'courseware_management'),'url'=>['manage']];
$this->params['breadcrumbs'][] = $this->pageTitle;

/* @var $model common\models\learning\LnCourseware */
?>
<style>
    .hidePreview{  background-color: #222 !important;}
</style>
<nav class="navbar navbar-inverse"  style="opacity: 0.8;">
    <div class="">
        <div class="navbar-header">
            <a class="navbar-brand" href="#" style="background:none; padding-left:20px;"><?=Yii::t('frontend', 'courseware_view')?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav pull-right">
                <li>
                    <a href="javascript:;" class="hidePreview" onclick="closePreview();">
                        <i class="glyphicon glyphicon-remove"></i> <?= Yii::t('frontend', 'page_info_good_cancel') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<iframe id="iframe" style="width: 100%;" frameborder="0"></iframe>
<script>
    $(function(){
       $("#iframe").attr('src', '<?=Url::toRoute([$this->context->id.'/preview-iframe','coursewareId'=>$coursewareId, 'scoId' => $scoId])?>').css({
           height: $(window).height()+'px'
       });
    });
    function miniScreen() {
        //alert('miniScreen');
        var
            iframeStyle = "",
            commentInputStyle = "miniInputWide",
            scrollHeight = $(document).scrollTop() + 50,
            hideAnswerTop = $('#hideAnswer').offset().top,
            i = $('#iframe-player').attr('data-type');

        if (i == "video") {
            iframeStyle = "fixedWindow";
            commentInputStyle = "miniInput";
        }

        if (scrollHeight > hideAnswerTop) {
            $('#iframe-player').addClass(iframeStyle);
            $('.commentInput').addClass(commentInputStyle);
            //alert('miniScreen1');
        } else {
            $('#iframe-player').removeClass(iframeStyle);
            $('.commentInput').removeClass(commentInputStyle);
            //alert('miniScreen2');
        }
    }
    function changeCourseWareStatus($status) {
//        alert($status);
        var done = "<?=Yii::t('frontend','complete_status_done');?>";
        var doing = "<?=Yii::t('frontend','complete_status_doing');?>";
        if ($status == "2") {
            $('#currentCoursewareStatus').val("("+done+")");
        }
        else {
            $('#currentCoursewareStatus').val("("+doing+")");
        }
    }

    function miniScreen(){

    }
</script>