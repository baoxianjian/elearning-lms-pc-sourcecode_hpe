<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/5/20
 * Time: 16:56
 */

use common\services\learning\CourseService;
use common\services\learning\ResourceCompleteService;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TModal;
use components\widgets\TBreadcrumbs;

$this->pageTitle = $model->course_name;
$this->title = $model->course_name;
$this->params['breadcrumbs'][] = Yii::t('frontend','course_view');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style>
    .hidePreview,.status_pub {background-color: #222 !important;}
</style>
<nav class="navbar navbar-inverse"  style="opacity: 0.8;">
    <div>
        <div class="navbar-header">
            <a class="navbar-brand" href="#" style="background:none; padding-left:20px;"><?=Yii::t('frontend','course_view')?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav pull-right">
                <li>
                    <a href="javascript:;" class="hidePreview copyUrl" data-clipboard-text="http://<?=$_SERVER['HTTP_HOST']?><?=Url::toRoute(['/resource/course/view', 'id' => $model->kid])?>">
                        <i class="glyphicon glyphicon-share"></i> <?= Yii::t('frontend', 'copy') ?>
                    </a>
                </li>
                <li>
                    <a href="javascript:;" class="hidePreview" onclick="closePreview();">
                        <i class="glyphicon glyphicon-remove"></i> <?= Yii::t('frontend', 'page_info_good_cancel') ?>
                    </a>
                </li>
                <?php
                if ($model->status == \common\models\learning\LnCourse::STATUS_FLAG_TEMP && $isCopy != \common\models\learning\LnCourse::IS_COPY_YES){
                ?>
                <li>
                    <a href="javascript:;" class="status_pub">
                        <i class="glyphicon glyphicon-ok"></i> <?= Yii::t('common', 'art_publish') ?>
                    </a>
                </li>
                <?php
                }
                ?>
            </ul>
        </div>
    </div>
</nav>
<?=Html::jsFile('/static/frontend/js/clipboard.min.js')?>
<iframe name="iframe" target="iframe" id="iframe" style="width: 100%;" frameborder="0"></iframe>
<script>
    function FmodalLoad(target, url)
    {
        if(url){
            $('#'+target).empty();
            var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?= Yii::t('frontend', 'loading') ?>...</p></div></div>';
            $('#'+target).html(loadingDiv); // 设置页面加载时的loading图片
            $.get(url, function(r){
                if (r){
                    $('#'+target).html(r);
                }
            });
        }
    }
    $(function(){
        $(".previewContainer").on('click', 'a[role!=tab]',function(e){
            e.stopPropagation();
            e.preventDefault();
        });
        $("#previewModal").css('padding', 0);
        $("#iframe").attr('src', '<?=Url::toRoute([$this->context->id.'/preview-iframe','id'=>$model->kid, 'mode' => 'preview'])?>').css({
            height: $(window).height()+'px'
        });
        $(".status_pub").click(function(e){
            e.preventDefault();
            var url = '<?=Url::toRoute(['resource/course/publish','id'=>$model->kid, 'sync' => 'all'])?>';
            if (typeof publishCourse === 'function'){
                publishCourse(url);
            }else{
                app.get(url, function(e){ });
                location.href = $("#statusTemp").attr('data-src');
            }
            $(this).parent().remove();
            app.showMsg('<?= Yii::t('frontend', 'issue_sucess') ?>');
        });
    });
    var clipboard = new Clipboard('.copyUrl');
    clipboard.on('success', function(e) {
        /*console.info('Action:', e.action);
        console.info('Text:', e.text);
        console.info('Trigger:', e.trigger);*/
        app.showMsg('<?=Yii::t('frontend', 'copy_url_tips')?>');
        e.clearSelection();
    });
    clipboard.on('error', function(e) {
        /*console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);*/
        app.showMsg('<?=Yii::t('frontend', 'copy_failed')?>');
    });
</script>