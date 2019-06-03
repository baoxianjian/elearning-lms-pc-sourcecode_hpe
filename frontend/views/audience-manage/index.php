<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/14
 * Time: 15:12
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = Yii::t('common', 'audience').Yii::t('common','management');
$this->params['breadcrumbs'][] = '';
?>
<style>
    .hotNews span{margin: 0}
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
                    <i class="glyphicon glyphicon-cloud-upload"></i> <?=Yii::t('common', '{value}_list', ['value' => Yii::t('common', 'audience')])?>
                </div>
                <div class="panel-body">
                    <div class="col-md-2 col-sm-12 jsPanel" style="overflow: hidden;">
                        <div class="jsTree-heading"><?=Yii::t('common', 'category')?><a href="###" class="addNodeBtn pull-right" id="add-catlog"><?=Yii::t('frontend', 'new_kind')?></a></div>
                        <div id="jsTree"></div>
                    </div>
                    <div class="col-md-10 col-sm-12">
                        <div class="actionBar">
                            <a class="btn btn-success pull-left" data-src="<?=Url::toRoute(['/audience-manage/add'])?>" id="addAudience"><?=Yii::t('common', 'audience_add')?></a>
                            <div class="form-group pull-right">
                                <select class="form-control" id="data_status" style="width: 120px;">
                                    <option value=""><?=Yii::t('common', 'all_data')?></option>
                                    <option value="0"><?=Yii::t('common', 'status_0')?></option>
                                    <option value="1"><?=Yii::t('common', 'status_1')?></option>
                                    <option value="2"><?=Yii::t('common', 'status_2')?></option>
                                </select>
                                <input type="text" class="form-control" id="searchTxt" placeholder="<?=Yii::t('common', 'address_code')?>/<?=Yii::t('common', 'name')?>">
                                <button type="submit" class="btn btn-default pull-right" id="reset"><?=Yii::t('common', 'reset')?></button>
                                <button type="submit" class="btn btn-primary pull-right" id="search" style="margin-left:10px;" onclick="reloadForm();"><?=Yii::t('common', 'search')?></button>
                            </div>
                        </div>
                        <div id="rightList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 增加新的目录 -->
<div class="ui modal" id="category-dialog"></div>
<?= Html::jsFile('/static/frontend/js/xss.js')?>
<script type="text/javascript">
    app.extend('alert');
    $(document).ready(function(){
        loadList();
        loadTree();
        $("#jsTree").on("click.jstree", ".delete_tree_node", function(){
            var delete_url = '<?=Url::toRoute(['/audience-manage/delete-category'])?>';
            var tree_node_id = $(this).attr('data-key');
            deleteButton(tree_node_id, delete_url);
            /*$.get(delete_url, {tree_node_id: tree_node_id}, function(data){
                if (data.result == 'success'){
                    loadTree();
                }else{
                    app.showMsg('删除失败');
                    return false;
                }
            });*/
        });
        $("#jsTree").on("click.jstree", ".edit_tree_node", function(e){
            e.preventDefault();
            $('#category-dialog').empty();
            var edit_url = '<?=Url::toRoute(['/resource/add-category', 'tree_type_code'=>'audience-category'])?>';
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

        $("#addAudience").on('click', function(e){
            e.preventDefault();
            var cat_id=$("#jsTree_tree_selected_result").val();
            if (typeof cat_id == 'undefined'){
                app.showMsg('<?=Yii::t('frontend', 'loading')?>');
                return false;
            }
            cat_id = eval(cat_id);//转换成数组
            if (cat_id.length > 1 || cat_id[0] == -1){
                app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('frontend','audience_kind')])?>");
                return false;
            }
            var url = $(this).attr('data-src');
            location.href = url+'?TreeNodeId='+cat_id[0];
        });

        $("#reset").on('click', function(e){
           $("#searchTxt").val('').attr('value', '');
            $("#data_status").find('option').removeAttr('selected').eq(0).attr('selected', 'selected');
        });

        $("#rightList").on('click', "a.copy", function(e){
            e.preventDefault();
            var kid = $(this).attr('data-key');
            $.get('<?=Url::toRoute(['/audience-manage/copy'])?>', {kid: kid}, function(e){
                if (e.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    $("#jsTree_tree_selected_result").val('"[-1]"');
                    loadTree();
                    reloadForm();
                }else{
                    app.showMsg("<?=Yii::t('common', 'loading_fail')?>");
                    return false;
                }
            }, 'json');
        });

        $("#rightList").on('click', "a.publish", function(e){
            e.preventDefault();
            var kid = $(this).attr('data-key');
            $.get('<?=Url::toRoute(['/audience-manage/publish'])?>', {kid: kid}, function(e){
                if (e.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    reloadForm();
                }else{
                    app.showMsg("<?=Yii::t('common', 'loading_fail')?>");
                    return false;
                }
            }, 'json');
        });

        $("#rightList").on('click', "a.stop", function(e){
            e.preventDefault();
            var kid = $(this).attr('data-key');
            $.get('<?=Url::toRoute(['/audience-manage/stop'])?>', {kid: kid}, function(e){
                if (e.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    reloadForm();
                }else{
                    app.showMsg("<?=Yii::t('common', 'loading_fail')?>");
                    return false;
                }
            }, 'json');
        });

        $("#rightList").on('click', "a.start", function(e){
            e.preventDefault();
            var kid = $(this).attr('data-key');
            $.get('<?=Url::toRoute(['/audience-manage/start'])?>', {kid: kid}, function(e){
                if (e.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    reloadForm();
                }else{
                    app.showMsg("<?=Yii::t('common', 'loading_fail')?>");
                    return false;
                }
            }, 'json');
        });

        /**
         * 删除
         * */
        $("#rightList").on('click', "a.deleted", function(e){
            e.preventDefault();
            var kid = $(this).attr('data-key');
            deleteButton(kid, '<?=Url::toRoute(['/audience-manage/deleted'])?>');
            /*$.get('<?=Url::toRoute(['/audience-manage/deleted'])?>', {kid: kid}, function(e){
                if (e.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    $("#jsTree_tree_selected_result").val('"[-1]"');
                    loadTree();
                    reloadForm();
                }else{
                    app.showMsg("<?=Yii::t('common', 'loading_fail')?>");
                    return false;
                }
            }, 'json');*/
        });
    });

    function ReloadPageAfterDelete(){
        app.showMsg('<?=Yii::t('common', 'Operation_Success')?>');
        $("#jsTree_tree_selected_result").val('"[-1]"');
        loadTree();
        reloadForm();
    }

    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'audience-category',
        'ContentName'=>'tree-node',
        'ListRoute'=>'../audience-manage/list',
        'IncludeRoot'=>'True',
        'MergeRoot'=>'False',
        'ShowContentCount'=>'True',
        'DeleteNode'=>'False',
        'EditNode'=>'False'
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
        var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
        $('#rightList').html(loadingDiv);
        var ajaxUrl = "<?=Url::toRoute(['/audience-manage/list'])?>";
        $.get(ajaxUrl, function(html){
            if (html){
                $("#rightList").html(html);
            }
        });
    }
    function getCategoryDialog(){
        $('#category-dialog').empty();
        $.get("<?=Url::toRoute(['/resource/add-category','tree_type_code'=>'audience-category','title' => Yii::t('common', 'category')])?>",function(data){
            if (data){
                $('#category-dialog').html(data);
            }
        });
    }
    function reloadForm()
    {
        var ajaxUrl = $("#jumpPageButton_grid").attr('href');
        if (typeof ajaxUrl == 'undefined'){
            ajaxUrl = "<?=Url::toRoute(['/audience-manage/list'])?>";
        }
        var pageSize = $('#pageSizeSelect_grid').val();
        if(typeof pageSize != 'undefined'){
            ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
        }
        /*关键词查询*/
        var error = 0;
        var keywords = $("#searchTxt").val().replace(/(^\s*)|(\s*$)/g,'');
        $("#searchTxt").val(keywords);
        var xss_keywords = filterXSS(keywords);
        if (keywords != xss_keywords){
            error ++;
            $("#searchTxt").focus();
            app.showMsg('<?=Yii::t('common', 'input_xss_error')?>');
            return false;
        }
        if (error > 0) return false;
        var cat_id=$("#jsTree_tree_selected_result").val();
        cat_id = eval(cat_id);//转换成数组
        var status = $("#data_status").val();
        $.get(ajaxUrl, {TreeNodeKid: cat_id[0], status: status, keyword: keywords}, function(r){
            if (r){
                $("#rightList").html(r);
            }else{
                app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
            }
        });
    }
</script>
