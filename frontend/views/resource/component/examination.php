<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TGridView;
use common\services\learning\ComponentService;
use common\models\learning\LnExamination;

?>
<style>
    .searchForm, .search-result {margin: 8px;}
    .searchForm .form-group{float: left;}
    .component-list li.component-thead {border: 1px solid #ddd; background-color: #eee !important;}
    .component-list li.component-thead a {color: #333;}
    .component-list li:nth-of-type(odd) {background-color: transparent;}
    .component-list li {border: 1px solid #ddd; padding: 3px 8px; line-height: 24px;}
    .component-list li:not(:first-child) {border-top: 0 none;}
    .component-list li .addAction {width: 10%; text-align: center;}
    .component-list .component-tbody {display: block; width: 90%; border: 0 none;}
    .component-list .component-tbody font {display: block; float: left;}
    .component-list .component-tbody font:first-child { width: 70%;}
    .component-list .component-tbody font:nth-child(2) { width: 20%;}
    /*.component-list .component-tbody font:nth-child(3) { width: 30%;}*/
</style>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?= Yii::t('common', '{pop}_ln_component',['pop'=>Yii::t('frontend','exam')]) ?></h4>
</div>
<div class="content clearfix">
    <div class="col-md-3 col-sm-3 jsPanel" style="float: left!important; overflow: hidden;">
        <div class="jsTree-heading"><?= Yii::t('frontend', 'exam') ?><?= Yii::t('common', 'category') ?></div>
        <div id="jsTree" class="demo jstree jstree-1 jstree-default"></div>
    </div>
    <div class="col-md-9 col-sm-9" style="float: left!important;">
        <div class="modal-body-view" id="componentList">
            <input type="hidden" name="sequence_number" id="sequence_number" value="<?=$params['sequence_number']?>">
            <div class="form-inline pull-right searchForm">
                <?php $form = ActiveForm::begin([
                    'id' => 'searchForm',
                    'method' => 'get',
                ]);
                ?>
                <div class="form-group">
                    <input type="text" class="form-control" id="searchText" value="<?=isset($params['keywords'])?$params['keywords']:''?>" placeholder="<?= Yii::t('common', 'name') ?>/<?= Yii::t('common', 'description') ?>" />
                </div>
                <?= Html::button(Yii::t('common', 'search'), ['id'=>'searchBtn','class' => 'btn btn-primary']) ?>
                <?= Html::button(Yii::t('common', 'reset'), ['onclick'=>'resetForm()','class' => 'btn btn-default']) ?>
                <?php
                if ($params['from'] != 'teacher'){
                ?>
                &nbsp;&nbsp;
                <a href="<?=Url::toRoute(['/exam-manage-main/index'])?>" class="btn btn-default" target="_blank"><?= Yii::t('frontend', 'build') ?><?= Yii::t('frontend', 'exam') ?></a>
                <?php
                }
                ?>
                <?php ActiveForm::end(); ?>
            </div>
            <div style="clear: both"></div>
            <div class="panel-default search-result" id="rightList" style="min-height: 383px;"><?= Yii::t('frontend', 'loading') ?>...</div>
        </div>
    </div>
    <div class="c"></div>
</div>
<div class="actions">
    <?= Html::button( Yii::t('frontend', 'choose_component'), ['id' => 'saveBtn', 'class' => 'btn btn-primary']) ?>
</div>
<script>
    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'companyId' => $params['companyId'],
        'TreeType'=>'examination-category',
        'ContentName'=>'tree-node',
        'ListRoute'=> ['/resource/component/examination-page','component_id' => $params['component_id'],'sequence_number' => $params['sequence_number'],'domain_id' => $params['domain_id'],'component_code' => $params['component_code'],'mod_num' => $params['mod_num'],'isCourseType' => $params['isCourseType'],'is_copy'=>$params['is_copy'],'companyId'=>$params['companyId']],
        'ListRouteParams' => '1',
        'IncludeRoot'=>'True',
        'MergeRoot'=>'False',
        'ShowContentCount'=>'True'])?>";
        app.get(ajaxUrl, function(r){
            if (r){
                $("#jsTree").html(r);
                reloadForm();
            }
        });
    }
$(function(){
    loadTree();
    $("#searchBtn").click(function() {
        $("#searchText").val($("#searchText").val().replace(/(^\s*)|(\s*$)/g,''));
        reloadForm();
    });
    $("#searchText").keypress(function(event){
        var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
        if (keyCode == 13){
            return false;
        }
    });
    $("#saveBtn").click(function(){
        var liSelected = $('#addModal').find('.componentSelected');
        if(liSelected.length == 0){
            //app.showMsg("请选择资源！");
            //return false;
        }
        var url = null;
        var code = $("#addModal").attr('data-code');
        var type = $("#addModal").attr('data-type');
        var componentId = $("#addModal").attr('data-componentid');
        var item = $("#addModal").attr('data-id');
        $('#addModal').find('.component').each(function(){
            if ($(this).hasClass('componentSelected')){
                if ($('.ulEditContent').eq(item-1).find("#"+$(this).attr('id')).length > 0){
                    /*列表已经存在*/
                }else {
                    var data_uri = $(this).find("a").attr('data-uri');
                    if (typeof data_uri != 'undefined' && data_uri.length > 0){
                        url = $(this).find("a").attr('data-uri');
                    }
                    $(this).find("a").attr('onclick', 'loadModalFormData(\'addModal\',\''+url+'?component_id='+componentId+'&sequence_number='+item+'&domain_id='+domain_id+'&component_code='+code+'\',this,\''+type+'\',\''+code+'\',0);');
                    $(this).unbind('click').find('.addAction').html('<a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a><a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+$(this).attr('data-id')+'&title='+encodeURI($(this).attr('data-title'))+'\',this,\''+type+'\',\''+code+'\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>');
                    var html = '<li id="'+$(this).attr('id')+'" class="component componentSelected" data-component="<?=$params['component_code']?>">'+$(this).html()+'</li>';
                    var sequence = $('.ulEditContent').eq(item-1).find(".componentSelected").length;
                    html = html.replace('[]', '['+(sequence+1)+']');
                    $('.ulEditContent').eq(item-1).append(html);
                    html = '';
                    if ($("#finalscorelist").children().length > 0) {
                        app.showMsg('<?= Yii::t('frontend', '{value}_reset',['value'=>Yii::t('frontend','weight_for_score')]) ?>！');
                        $("#finalscorelist").empty();
                    }
                }
            }else{
                $('.ulEditContent').eq(item-1).find("#"+$(this).attr("id")).remove();
                if ($("#finalscorelist").children().length > 0) {
                    app.showMsg('<?= Yii::t('frontend', '{value}_reset',['value'=>Yii::t('frontend','weight_for_score')]) ?>！');
                    $("#finalscorelist").empty();
                }
            }
        });
        $("li[data-empty='"+$("#addModal").attr('data-li')+"_empty']").remove();
        $("#addModal").attr('data-id','').attr('data-li','').attr('data-code','').attr('data-type','');
        app.hideAlert($("#addModal"));
        $('#addModal').empty();
    });
});
function reloadForm()
{
    var ajaxUrl = "<?=Url::toRoute(['/resource/component/examination-page','component_id'=>$params['component_id'],'sequence_number'=>$params['sequence_number'],'domain_id'=>$params['sequence_number'], 'isCourseType' => $params['isCourseType'],'is_copy'=>$params['is_copy'],'companyId'=>$params['companyId']])?>";
    var pagesize = $('#pageSizeSelect_grid').val();
    if (typeof pagesize == 'undefined') pagesize = '<?=$pageSize?>';
    ajaxUrl = urlreplace(ajaxUrl,'PageSize', pagesize);
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
    ajaxUrl = urlreplace(ajaxUrl,'keywords',encodeURIComponent(keywords));
    var cat_id = $("#jsTree_tree_selected_result").val();
    if (typeof cat_id != 'undefined') {
        cat_id = eval(cat_id);//转换成数组
        if (cat_id.length > 0 || cat_id[0] == -1) {
            ajaxUrl = urlreplace(ajaxUrl, 'TreeNodeKid', cat_id[0]);
        } else {
            ajaxUrl = urlreplace(ajaxUrl, 'TreeNodeKid', '');
        }
    } else {
        ajaxUrl = urlreplace(ajaxUrl, 'TreeNodeKid', '');
    }
    $.get(ajaxUrl, function(html){
        $('#rightList').html(html);
    });
}
function resetForm(){
    $("#searchText").val('');
}
</script>
