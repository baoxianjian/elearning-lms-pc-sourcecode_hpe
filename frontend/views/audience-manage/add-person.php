<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/30
 * Time: 10:18
 */
use yii\helpers\Html;
use yii\helpers\Url;
?>
<?=Html::cssFile('/components/jstree/dist/themes/default/style.css')?>
<?=Html::jsFile('/components/jstree/dist/jstree.min.js')?>
<!-- 添加人员 -->
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=Yii::t('common', 'add_person')?></h4>
</div>
<div class="content">
    <div class="col-md-3 col-sm-12 jsPanel" style="margin-top: 90px; float: left !important; overflow: hidden;">
        <div class="jsTree-heading" style="margin-bottom: 5px;"><?=Yii::t('common', 'orgnization')?></div>
        <div id="jsTree">
            <input type="hidden" id="tree" name="tree">
            <div id="loadOrgnization" class=""></div>
        </div>
    </div>
    <div class="col-md-9 col-sm-12" style=" float: right !important;">
        <div id="rightList"></div>
    </div>
    <div class="c"></div>
    <div class="action centerBtnArea groupAddMember">
        <a href="###" class="btn centerBtn" id="saveAddPerson" onclick="saveAddPerson();"><?=Yii::t('common', 'save')?></a>
        <a href="###" class="btn centerBtn" id="closeAddPerson"><?=Yii::t('common', 'close')?></a>
    </div>
</div>
<script>
$(function(){
    $("#closeAddPerson").on('click', function(e){
       app.hideAlert($("#addPerson"));
    });
    jsTree();
    getOrgnizationUserList();
    $("#rightList").on('click', '#searchPersonButton', function(e){
        e.preventDefault();
        getOrgnizationUserList();
        return false;
    });
    $("#rightList").on('click', '#resetPersonButton', function(){
        $("#searchPerson").attr('value', '');
    });
    $("#rightList").on('keypress', '#searchPerson', function (e) {
        var keyCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
        if (keyCode == 13) {
            e.preventDefault();
            getOrgnizationUserList();
            return false;
        }
    });
});
/**
 * 加载组织树
 */
function jsTree(){
    var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'orgnization',
        'ContentName'=>'tree-node',
        'ListRoute'=>'../audience-manage/get-orgnization-user-list',
        'IncludeRoot'=>'True',
        'MergeRoot'=>'False',
        'ShowContentCount'=>'False',
        'DeleteNode'=>'False',
        'EditNode'=>'False',
        'ListRouteParams' => '1'
        ])?>";
    $.get(ajaxUrl, function(html){
        if (html){
            $("#loadOrgnization").html(html);
        }
    });
}
function getOrgnizationUserList(){
    var error = 0;
    var TreeNodeKid = $("#jsTree_tree_selected_result").val();
    TreeNodeKid = eval(TreeNodeKid);//转换成数组
    if (typeof TreeNodeKid == 'undefined'){
        TreeNodeKid = "-1";
    }else{
        TreeNodeKid = TreeNodeKid[0];
    }

    var searchPerson = $("#searchPerson").val();
    if (typeof searchPerson == 'undefined'){
        searchPerson = '';
    }else{
        searchPerson = searchPerson.replace(/(^\s*)|(\s*$)/g,'');
        $("#searchPerson").val(searchPerson);
        var xss_searchPerson = filterXSS(searchPerson);
        if (searchPerson != xss_searchPerson){
            error ++;
            $("#searchPerson").focus();
            app.showMsg('<?=Yii::t('common', 'input_xss_error')?>');
            return false;
        }
    }
    if (error > 0) return false;
    app.showLoadingMsg();
    $.get('<?=Url::toRoute(['/audience-manage/get-orgnization-user-list'])?>', {TreeNodeKid:TreeNodeKid,keyword:searchPerson,audience_batch: '<?=$audience_batch?>'}, function(r){
        app.hideLoadingMsg();
        if (r){
            $("#rightList").html(r);
        }else{
            app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
        }
    });
}
</script>