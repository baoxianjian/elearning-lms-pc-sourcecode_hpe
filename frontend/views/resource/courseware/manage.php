<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use components\widgets\TBreadcrumbs;
$componentName = str_replace('resource/','',$this->context->id);
$this->pageTitle = Yii::t('common','{value}_management',['value'=>Yii::t('common',$componentName)]);
$this->params['breadcrumbs'][] = ['label'=> Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = $this->pageTitle;
$this->params['breadcrumbs'][] = '';

$ListRoute = '/../resource/courseware/list';
?>
<style>
    .hotNews span{margin: 0}
    .form-group .form-control{margin-right: 10px}
    span.delete_tree_node,span.edit_tree_node {color: #23527c; margin-left: 20px!important; cursor: pointer;}
</style>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script type="text/javascript">
    app.extend('alert');
    $(document).ready(function(){
        loadList();
        loadTree();
        $("#jsTree").on("click.jstree", ".delete_tree_node", function(){
            var delete_url = '<?=Url::toRoute(['/resource/courseware/delete-category'])?>';
            var tree_node_id = $(this).attr('data-key');
            $.get(delete_url, {tree_node_id: tree_node_id}, function(data){
                if (data.result == 'success'){
                    loadTree();
                }else{
                    app.showMsg('<?=Yii::t('frontend', 'delete_failed')?>');
                    return false;
                }
            });
        });
        $("#jsTree").on("click.jstree", ".edit_tree_node", function(e){
            e.preventDefault();
            $('#category-dialog').empty();
            var edit_url = '<?=Url::toRoute(['/resource/add-category', 'tree_type_code'=>'courseware-category'])?>';
            var tree_node_id = $(this).attr('data-key');
            $.get(edit_url, {tree_node_id: tree_node_id, edit: 'True'}, function(data){
                if (data){
                    $('#category-dialog').html(data);
                    app.alert('#category-dialog');
                }else{
                    app.showMsg('<?=Yii::t('frontend', 'page_exception')?>');
                    return false;
                }
            });
        });
    });
    function loadList(){
        var loadingDiv = '<p><?=Yii::t('frontend', 'loading')?>...</p>';
        $('#rightList').html(loadingDiv); // 设置页面加载时的loading图片
        var ajaxUrl = "<?=Url::toRoute([$this->context->id.'/list'])?>";
        $.get(ajaxUrl,function(data){
            $("#rightList").html(data);
        });
    }
    function TreeCallback(){}
    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'courseware-category',
        'ContentName'=>'tree-node',
        'ListRoute'=> $ListRoute,
        'IncludeRoot'=>'True',
        'MergeRoot'=>'False',
        'ShowContentCount'=>'True',
        'DeleteNode'=>'False',
        'EditNode'=>'False'])?>";

        app.get(ajaxUrl,function(data){
            $("#jsTree").html(data);
            getCategoryDialog();
        });
    }
    function getCategoryDialog(){
        $('#category-dialog').empty();
        $.get("<?=Url::toRoute(['/resource/add-category','tree_type_code'=>'courseware-category'])?>",function(data){
            if (data){
                $('#category-dialog').html(data);
            }
        });
    }
    function delItem(url){
        NotyConfirm('<?=Yii::t('common', 'operation_confirm')?>',  function(data){
            $.post(url, function(data){
                if (data.result === 'failure') {
                    app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>.');
                }else{
                    reloadForm();
                    Refreshopener();
                }
            },'json');
        });
    }
</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      <div class="col-md-12 col-sm-12">
        <div class="panel panel-default hotNews">
          <div class="panel-heading">
            <i class="glyphicon glyphicon-list"></i> <?=Yii::t('common','{value}_list',['value'=>Yii::t('common',$componentName)])?>
          </div>
          <div class="panel-body" id="content-body">
              <div class="row">
                  <div class="col-md-2 col-sm-2 jsPanel" style="margin-top: 54px; overflow: hidden;">
                      <div class="jsTree-heading"><?=Yii::t('common', 'courseware_category')?><a href="#" id="add-catlog" class="addNodeBtn pull-right"><?=Yii::t('frontend', 'new_catalog')?></a></div>
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
</div>
<div id="NoPermissionModal" class="ui modal">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?=Yii::t('common', 'courseware_edit')?></h4>
    </div>
    <div class="body">
        <iframe id="NoPermissioniframe" name="NoPermissioniframe" width="100%" height="100%" frameborder="0" style="min-height:400px;box-shadow:0 0 2px #ccc;"></iframe>
    </div>
    <div class="actions" style="text-align:center;border-top:0;padding-top:0;">
        <input type="hidden" id="coursewareId" value="">
        <?= Html::button(Yii::t('common','save'), ['type'=>'submit', 'onclick' => 'UpdateForm();','id'=>'saveBtn','class' => 'btn btn-primary']) ?>
        <?//= Html::button(Yii::t('common','delete_button'), ['class' => 'btn btn-default', 'id' => 'closeBtn', 'data-dismiss'=>'modal']) ?>
    </div>
</div>
<div id="SeeModal" class="ui modal">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?=Yii::t('common', 'courseware_view')?></h4>
    </div>
    <div class="body">
        <div class="modal-body-view"></div>
    </div>
    <div class="actions" style="text-align: center;">
        <button type="button" class="btn btn-default" onclick="app.hideAlert('#SeeModal');"><?=Yii::t('common','close')?></button>
    </div>
</div>
<div class="ui modal" id="previewModal">
    <div class="body">
        <div class="modal-body-view"></div>
    </div>
</div>
<!-- 增加新的目录 -->
<div class="ui modal" id="category-dialog"></div>
<script>
    $(document).ready(function() {
        Refreshopener();
    });
    function Refreshopener(){
        try {
            if(window.opener!=null) {
                window.opener.resetForm1();
                window.opener.reloadForm1();
            }
        }catch (e){
            /*if (e.code == DOMException.INVALID_CHARACTER_ERR) {
                alert ("The attribute name is invalid");
            }*/
            return;
        }
    }
    function ReloadPageAfterDelete() {
        reloadForm();
        Refreshopener();
    }
    var loading = true;
    function reloadForm()
    {
        if (!loading) return ;
        var ajaxUrl = $("#jumpPageButton_grid").attr('href');
        if (typeof ajaxUrl == 'undefined'){
            ajaxUrl = "<?=Url::toRoute([$this->context->id.'/list'])?>";
        }
        var pageSize = $('#pageSizeSelect_grid').val();
        if(typeof pageSize != 'undefined'){
            ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
        }
        $("#searchForm input[type='text']").each(function(){
            ajaxUrl = urlreplace(ajaxUrl,this.name,encodeURI($(this).val()));
        });
        /*关键词查询*/
        var error = 0;
        var keywords = $("#searchText").val().replace(/(^\s*)|(\s*$)/g,'');
        var xss_keywords = filterXSS(keywords);
        if (keywords != xss_keywords){
            error ++;
            $("#searchText").focus();
            app.showMsg('<?=Yii::t('common', 'input_xss_error')?>');
            loading = true;
            return false;
        }
        if (error > 0){
            loading = true;
            return false;
        }

        $("#searchForm input[type='hidden']").each(function(){
            ajaxUrl = urlreplace(ajaxUrl,this.name,encodeURI($(this).val()));
        });

        $("#searchForm input[type='checkbox']").each(function(){
            if (this.checked) {
                ajaxUrl = urlreplace(ajaxUrl, this.name, '1');
            }
            else
            {
                ajaxUrl = urlreplace(ajaxUrl, this.name, '0');
            }
        });

        $("#searchForm select").each(function(){
            ajaxUrl = urlreplace(ajaxUrl,this.name,encodeURI($(this).val()));
        });
        $.get(ajaxUrl, function(e){
            loading = true;
            if (e){
                $("#rightList").html(e);
            }
        });
        return false;
    }
    function loadModalFormData(modalId,frameId,url,id)
    {
        modalClear(modalId);
        app.get(url,function(data){
            if (data) {
                $("#" + modalId).find(".modal-body-view").html(data);
            }
        });
        app.alert($("#"+modalId));
        showtip(modalId,frameId,url);
        $("#coursewareId").val(id);
    }
    function hiddentip(){
        app.hideAlert($('#NoPermissionModal'));
        modalClear("NoPermissionModal");
        $("#NoPermissioniframe").attr("src", "");
        $("#coursewareId").val('');
    }
    function showtip(modalId,frameId,frameSrc){
        $("#"+frameId).attr('src', frameSrc);
        app.alert($('#'+modalId));
    }
    $(function(){
        //$("#previewModal .modal-body-view").css('minHeight', $(window).height()+'px');
        /*弹窗删除课件*/
        $("#closeBtn").on('click', function(e){
            e.stopPropagation();
            var key = $("#coursewareId").val();
            if (key == ""){
                return ;
            }
            var url = '<?=Yii::$app->urlManager->createUrl([$this->context->id.'/delete'])?>?id='+key;
            NotyConfirm('<?=Yii::t('common', 'operation_confirm')?>',  function(data){
                if (key == '') {
                    app.showMsg('<?=Yii::t('frontend', 'select_delete_record')?>');
                }
                else {
                    var method = 'POST';
                    var dataType = 'json';
                    ajaxData(url, method, key, dataType, function(data){
                        if (data.result === 'failure') {
                            app.showMsg('<?=Yii::t('frontend', 'operation_confirm_warning_failure')?>.');
                        }
                        else
                        {
                            hiddentip();
                            reloadForm();
                            Refreshopener();
                        }
                        return false;
                    });
                }
            });
        });

        $("#rightList").on('click', '#searchSubmit', function(e) {
            reloadForm();
        });

        $("#rightList .pagination a").on('click', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(e){
                if (e){
                    $("#rightList").html(e);
                }
            });
            return false;
        });

        $("#rightList").on('click', '.preview', function(e)
        {
            e.preventDefault();
            $('#previewModal').find(".modal-body-view").empty();
            app.get($(this).attr('href'), function (r){
                if(r)
                {
                    $('#previewModal').find(".modal-body-view").html(r);
                    app.alertFull('#previewModal');
                }
            });
            return false;
        });
    });

    function UpdateForm(){
        var result = NoPermissioniframe.window.checkFrm();
        if (result == 'success'){
            hiddentip();
            reloadForm();
            Refreshopener();
        }
    }
    function seeModal(modalId,url){
        if(url){
            $('#'+modalId).find(".modal-body-view").empty();
            app.get(url, function (r){
                if(r)
                {
                    $('#'+modalId).find(".modal-body-view").html(r);
                    app.alert('#'+modalId);
                }
            });
        }
    }
    function closePreview(){
        app.hideAlert($('#previewModal'));
        $("#previewModal .modal-body-view").html('');
    }

</script>