<?php

use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
//$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','exam_management_question'),'url'=>['/exam-manage-main/question']];
$this->params['breadcrumbs'][] = Yii::t('common','exam_management_question');
$this->params['breadcrumbs'][] = '';

?>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<style>
  .empty {text-align: left;}
  #grid .summary {display: none;}
  .table > thead:first-child > tr:first-child > th,.table-bordered > tbody > tr > td {text-align: center;}
  .btn-xs {padding: 5px 5px;}
  #grid table td:last-child {text-align: left;}
  .-query-list{
    display:inline-block;
    width:75%;
  }
  #grid th:nth-child(2) {max-width: 50%;}
  .scoreList span.not-set {color: #888;}
  span.delete_tree_node,span.edit_tree_node {color: #23527c; margin-left: 20px!important; cursor: pointer;}
</style>
<div class="container">
    <div class="row">
      <?= TBreadcrumbs::widget([
          'tag' => 'ol',
          'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
      ]) ?>
      <div class="col-md-12 col-sm-12">
        <div class="courseInfo">
          <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
            <li role="presentation" class="active"><a href="<?=Url::toRoute(['exam-manage-main/question'])?>"><?=Yii::t('frontend', 'exam_tikuguanli')?></a></li>
            <li role="presentation"><a href="<?=Url::toRoute(['exam-paper-manage/index'])?>"><?=Yii::t('frontend', 'exam_shijuanguanli')?></a></li>
            <li role="presentation"><a href="<?=Url::toRoute(['exam-manage-main/index'])?>"><?=Yii::t('frontend', 'exam_kaoshiguanli')?></a></li>
          </ul>
          <div class="tab-content">
            <div class=" panel-default scoreList">
              <div class="panel-body">
                <div class="col-md-2 col-sm-12 jsPanel" style="overflow: hidden;">
                  <div class="jsTree-heading"><?=Yii::t('frontend', 'exam_tiku')?><a href="#" id="add-catlog" class="addNodeBtn pull-right"><?=Yii::t('frontend', 'exam_xintiku')?></a></div>
                  <div id="jsTree"></div>
                </div>
                <div class="col-md-10 col-sm-12">
                  <div id="rightList"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
<!-- 增加新的目录 -->
<div class="ui modal" id="category-dialog"></div>
<div class="ui modal" id="new_exam_question"></div>
<div class="ui modal" id="import_exam_question"></div>
<!-- 消息弹出框 -->
<div id="foo" class="ui modal">
  <div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <?= Yii::t('frontend', 'top_message_text') ?>
  </div>
  <div class="content">
    <p id="msm_alert_content"><?= Yii::t('frontend', 'issue_sucess') ?></p>
  </div>
  <div class="actions">
    <div class="btn btn-default cancel"><?= Yii::t('frontend', 'page_info_good_cancel') ?></div>
    <div class="btn btn-default ok"><?=Yii::t('frontend', 'be_sure')?></div>
  </div>
</div>
<script type="text/javascript">
  app.extend('alert');
  var form_url = '<?=Url::toRoute(['/exam-manage-main/question-list'])?>';
  $(document).ready(function(){
    loadTree();
    loadList();
    $("#jsTree").on("click.jstree", ".delete_tree_node", function(){
      var delete_url = '<?=Url::toRoute(['/exam-manage-main/delete-question-category'])?>';
      var tree_node_id = $(this).attr('data-key');
      NotyConfirm('<?=Yii::t('frontend', 'exam_caozuobukeni')?>',  function(data) {
        $.get(delete_url, {tree_node_id: tree_node_id}, function (data) {
          if (data.result == 'success') {
            loadTree();
          } else {
            app.showMsg('<?=Yii::t('frontend', 'exam_del_fail')?>');
            return false;
          }
        });
      });
    });
    $("#jsTree").on("click.jstree", ".edit_tree_node", function(e){
      e.preventDefault();
      $('#category-dialog').empty();
      var edit_url = '<?=Url::toRoute(['/exam-manage-main/add-examination-question-category'])?>';
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
    $("#add-catlog").click(function(e){
      e.preventDefault();
      $('#category-dialog').empty();
      $.get("<?=Url::toRoute(['/exam-manage-main/add-examination-question-category'])?>",function(data){
        if (data){
          $('#category-dialog').html(data);
          app.alert('#category-dialog');
        }
      });
    });
  });
  function loadTree(){
    var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'examination-question-category',
        'ContentName'=>'tree-node',
        'ListRoute'=>'../exam-manage-main/question-list',
        'IncludeRoot'=>'True',
        'MergeRoot'=>'False',
        'ShowContentCount'=>'True',
        'DeleteNode'=>'False',
        'EditNode'=>'False'])?>";
    app.get(ajaxUrl, function(r){
      if (r){
        $("#jsTree").html(r);
      }
    });
  }
  function TreeCallback(){}
  function loadList(){
    var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'exam_page_loading')?></p></div></div>';
    $('#rightList').html(loadingDiv);
    app.get(form_url, function(r){
      if (r){
        $("#rightList").html(r);
      }
    });
  }
  function reloadForm(){
    var keywords = $("#keywords").val().replace(/(^\s*)|(\s*$)/g,'');
    /*关键词查询*/
    var error = 0;
    var xss_keywords = filterXSS(keywords);
    if (keywords != xss_keywords){
      error ++;
      app.showMsg('<?=Yii::t('frontend', 'exam_kw_ill_err')?>');
      return false;
    }
    if (error > 0) return false;
    var tree_node_id = $("#jsTree_tree_selected_result").val();
    tree_node_id = eval(tree_node_id);//转换成数组
    if (tree_node_id[0] == -1){
      tree_node_id = "";
    }else{
      tree_node_id = tree_node_id.join();
    }
    var examination_question_type = $("#examination_question_type").val();
    $.get(form_url, {keywords: keywords, examination_question_type: examination_question_type, TreeNodeKid: tree_node_id}, function(data){
      if (data){
        $("#rightList").html(data);
      }else{
        app.showMsg('<?= Yii::t('frontend', 'network_anomaly') ?>');
        return false;
      }
    });
    return false;
  }
</script>