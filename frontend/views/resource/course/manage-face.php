<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
$this->pageTitle = Yii::t('common', 'face_to_face') . Yii::t('common','course') . Yii::t('common','management');
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = $this->pageTitle;
$this->params['breadcrumbs'][] = '';
?>
<style>
    .hotNews span{margin: 0}
    .courseDetails .form-group {float: none;}
    .courseDetails h2,.courseDetails h4 {margin: 0;}
    .infoBlock {
        margin-top: 10px;
        float: left;
        width: 100%;
    }

    .infoBlock hr {
        margin-top: 0 !important;
    }

    .infoBlock .row {
        margin: 15px 0;
    }

    .courseInfo {
        float: none;
    }

    .modal .modal-header {
        background: #00a8de;
        color: #fff;
    }

    .resourcePanel {
        width:100%;
        border: 1px dotted #ccc;
        padding: 10px;
        margin: 5px 0;
    }

    .resourcePanel:hover{
        background: #efefef;
    }

    .resourcePanel strong{
        color: #333;
    }
    .courseInfoInput{
        padding: 0 20px;
    }
    span.delete_tree_node,span.edit_tree_node {color: #23527c; margin-left: 20px!important; cursor: pointer;}
</style>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon glyphicon-align-justify"></i> <?=Yii::t('common', 'face_to_face')?><?= Yii::t('common', '{value}_list',['value'=>Yii::t('common','course')]) ?>
                </div>
                <div class="panel-body" id="content-body">
                    <div class="col-md-2 col-sm-2 jsPanel" style="margin-top: 54px; overflow: hidden;">
                        <div class="jsTree-heading"><?= Yii::t('common', 'category_id') ?><a href="#" id="add-catlog" class="addNodeBtn pull-right"><?= Yii::t('frontend', 'new_catalog') ?></a></div>
                        <div id="jsTree"></div>
                    </div>
                    <div class="col-md-10 col-sm-10">
                        <div id="rightList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="previewModal" class="ui modal">
    <div class="body">
        <div class="modal-body-view"></div>
    </div>
</div>
<!-- 查看弹出窗口 -->
<div class="ui modal" id="courseDetails">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('common', 'course_view')?></h4>
    </div>
    <div class="body">
        <div class="modal-body-view"></div>
    </div>
    <div class="actions" style="text-align: center;">
        <button type="button" class="btn btn-default" onclick="app.hideAlert('#courseDetails');"><?=Yii::t('common','close')?></button>
    </div>
</div>
<!-- 增加新的目录 -->
<div class="ui modal" id="category-dialog"></div>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script type="text/javascript">
    app.extend('alert');
    var copyUrl = '<?=Yii::$app->urlManager->createUrl(['resource/course/face-copy'])?>';
    $(document).ready(function(){
        loadList();
        loadTree();
        $("#jsTree").on("click.jstree", ".delete_tree_node", function(){
            var delete_url = '<?=Url::toRoute(['/resource/course/delete-category'])?>';
            var tree_node_id = $(this).attr('data-key');
            $.get(delete_url, {tree_node_id: tree_node_id}, function(data){
                if (data.result == 'success'){
                    loadTree();
                }else{
                    app.showMsg('<?= Yii::t('frontend', 'delete_failed') ?>');
                    return false;
                }
            });
        });
        $("#jsTree").on("click.jstree", ".edit_tree_node", function(e){
            e.preventDefault();
            $('#category-dialog').empty();
            var edit_url = '<?=Url::toRoute(['/resource/add-category', 'tree_type_code'=>'course-category'])?>';
            var tree_node_id = $(this).attr('data-key');
            $.get(edit_url, {tree_node_id: tree_node_id, edit: 'True'}, function(data){
                if (data){
                    $('#category-dialog').html(data);
                    app.alert('#category-dialog');
                }else{
                    app.showMsg('<?= Yii::t('frontend', 'page_exception') ?>');
                    return false;
                }
            });
        });
    });
    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'course-category',
        'ContentName'=>'tree-node',
        'ListRoute'=>'../resource/course/list-face',
        'IncludeRoot'=>'True',
        'MergeRoot'=>'False',
        'ShowContentCount'=>'True',
        'DeleteNode'=>'False',
        'EditNode'=>'False',
        'ListRouteParams' => '1'
        ])?>";
        $.get(ajaxUrl, function(html){
            if (html){
                $("#jsTree").html(html);
                getCategoryDialog();
            }
        });
    }
    function TreeCallback(){}
    function loadList(){
        var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?= Yii::t('frontend', 'loading') ?>...</p></div></div>';
        $('#rightList').html(loadingDiv); // 设置页面加载时的loading图片
        var ajaxUrl = "<?=Url::toRoute(['resource/course/list-face'])?>";
        $.get(ajaxUrl, function(html){
            if (html){
                $("#rightList").html(html);
                getCategoryDialog();
            }
        });
    }
    function getCategoryDialog(){
        $('#category-dialog').empty();
        $("#catlog").remove();
        $.get("<?=Url::toRoute(['/resource/add-category','tree_type_code'=>'course-category'])?>",function(data){
            if (data){
                $('#category-dialog').html(data);
            }
        });
    }
    function courseCopy(id){
        if (typeof id == 'undefined') return false;
        app.showLoadingMsg('<?=Yii::t('frontend', 'operation_is_in_progress')?>');
        $.get(copyUrl, {id: id}, function(data){
            app.hideLoadingMsg();
            if (data.result == 'success'){
                app.showMsg('<?= Yii::t('frontend', 'copy_sucess') ?>');
                location.reload();
            }else{
                app.showMsg('<?= Yii::t('frontend', 'copy_failed') ?>');
            }
        },'json');
    }
    function publishCourse(url){
        app.get(url, function(e){ });
        reloadForm();
    }
    function ReloadPageAfterDelete() {
        reloadForm();
    }
    function reloadForm()
    {
        var ajaxUrl = $("#jumpPageButton_grid").attr('href');
        if (typeof ajaxUrl == 'undefined'){
            ajaxUrl = "<?=Url::toRoute([$this->context->id.'/list-face'])?>";
        }
        var pageSize = $('#pageSizeSelect_grid').val();
        if(typeof pageSize != 'undefined'){
            ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
        }
        /*关键词查询*/
        var error = 0;
        var keywords = $("#searchText").val().replace(/(^\s*)|(\s*$)/g,'');
        var xss_keywords = filterXSS(keywords);
        if (keywords != xss_keywords){
            error ++;
            $("#searchText").focus();
            app.showMsg('<?= Yii::t('common', 'input_xss_error') ?>');
            return false;
        }
        if (error > 0) return false;
        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }
    function seeModal(modalId,url){
        if(url){
            $('#'+modalId).find(".modal-body-view").empty();
            app.get(url, function (r){
                if(r)
                {
                    $('#'+modalId).find(".modal-body-view").html(r);
                    app.alertWide('#'+modalId);
                }
            });
        }
    }
    function preView(modalId,url) {
        app.hideAlert('#previewModal');
        if (url) {
            $('#' + modalId).find(".modal-body-view").empty();
            app.get(url, function (r) {
                if (r) {
                    $('#' + modalId).find(".modal-body-view").html(r);
                    app.alertFull('#' + modalId);
                }
            });
            return false
        }
        return false
    }
    function closePreview(){
        app.hideAlert($('#previewModal'));
        $("#previewModal .modal-body-view").html('');
    }
</script>