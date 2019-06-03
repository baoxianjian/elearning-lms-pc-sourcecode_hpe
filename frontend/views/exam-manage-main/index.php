<?php

use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
//$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','exam_management'),'url'=>['/exam-manage-main/index']];
$this->params['breadcrumbs'][] = Yii::t('common','exam_management');
$this->params['breadcrumbs'][] = '';

?>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<style type="text/css">
  .empty {text-align: left;}
  #grid .summary,#grid_score .summary {display: none;}
  .table > thead:first-child > tr:first-child > th,.table-bordered > tbody > tr > td {text-align: center;}
  .btn-xs {padding: 5px 5px;}
  #grid table td:last-child {text-align: left;}
  #grid_score span.not-set {color: #888;}
  /*#new_exam,#previewExam {margin: 3.5rem auto !important;position: static !important;}*/
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
            <li role="presentation"><a href="<?=Url::toRoute(['exam-manage-main/question'])?>"><?=Yii::t('frontend', 'exam_tikuguanli')?></a></li>
            <li role="presentation"><a href="<?=Url::toRoute(['exam-paper-manage/index'])?>"><?=Yii::t('frontend', 'exam_shijuanguanli')?></a></li>
            <li role="presentation" class="active"><a href="<?=Url::toRoute(['exam-manage-main/index'])?>"><?=Yii::t('frontend', 'exam_kaoshiguanli')?></a></li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="report_stat">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="col-md-2 col-sm-12 jsPanel" style="overflow: hidden;">
                    <div class="jsTree-heading"><?=Yii::t('frontend', 'exam_kaoshimulu')?><a href="#" id="add-catlog" class="addNodeBtn pull-right"><?=Yii::t('frontend', 'exam_xinmulu')?></a></div>
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
  </div>
<!-- 增加新的目录 -->
<div class="ui modal" id="category-dialog"></div>
<div class="ui modal" id="new_exam"></div>
<!-- 考试预览弹出窗口 -->
<div class="ui modal" id="previewExam" style="height: 750px;"></div>
<!-- 成绩查看 -->
<div class="ui modal" id="view_score"></div>
<!-- 考试查看 -->
<div class="ui modal" id="detail"></div>
<!-- 个人考试成绩记录查看 -->
<div class="ui modal" id="examination_log"></div>
<!-- 成绩详情 -->
<div class="ui modal" id="result_user_log"></div>
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
    <div class="btn btn-default ok"><?=Yii::t('frontend', 'exam_queding')?></div>
  </div>
</div>
<script type="text/javascript">
  app.extend('alert');
  var form_url = '<?=Url::toRoute(['exam-manage-main/list'])?>';
  $(document).ready(function(){
    loadList();
    loadTree();
    $("#jsTree").on("click.jstree", ".delete_tree_node", function(){
      var delete_url = '<?=Url::toRoute(['/exam-manage-main/delete-examination-category'])?>';
      var tree_node_id = $(this).attr('data-key');
      NotyConfirm('<?=Yii::t('frontend', 'exam_warn_one')?>',  function(data) {
        $.get(delete_url, {tree_node_id: tree_node_id}, function (data) {
          if (data.result == 'success') {
            loadTree();
          } else {
            app.showMsg('<?=Yii::t('frontend', 'exam_del_fail')?>');
            return false;
          }
        });
      })
    });
    $("#jsTree").on("click.jstree", ".edit_tree_node", function(e){
      e.preventDefault();
      $('#category-dialog').empty();
      var edit_url = '<?=Url::toRoute(['/resource/add-category', 'tree_type_code'=>'examination-category'])?>';
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
  function loadTree(){
    var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'examination-category',
        'ContentName'=>'tree-node',
        'ListRoute'=>'../exam-manage-main/list',
        'IncludeRoot'=>'True',
        'MergeRoot'=>'False',
        'ShowContentCount'=>'True',
        'DeleteNode'=>'False',
        'EditNode'=>'False'
        ])?>";
    app.get(ajaxUrl, function(r){
      if (r){
          $("#jsTree").html(r);
          getCategoryDialog();
      }
    });
  }
  function TreeCallback(){}
  function getCategoryDialog(){
    $('#category-dialog').empty();
    $.get("<?=Url::toRoute(['/resource/add-category','tree_type_code'=>'examination-category','title'=>Yii::t('frontend', 'exam_kaoshimulu')])?>",function(data){
      if (data){
        $('#category-dialog').html(data);
      }
    });
  }
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
    $("#keywords").val(keywords);
    /*关键词查询*/
    var error = 0;
    var xss_keywords = filterXSS(keywords);
    if (keywords != xss_keywords){
      error ++;
      app.showMsg('<?=Yii::t('frontend', 'exam_kw_ill_err')?>');
      return false;
    }
    if (error > 0) return false;
    var examination_mode = $("#examination_mode").val();
    var tree_node_id = $("#jsTree_tree_selected_result").val();
    tree_node_id = eval(tree_node_id);//转换成数组
    if (tree_node_id[0] == -1){
      tree_node_id = "";
    }else{
      tree_node_id = tree_node_id.join();
    }
    $.get(form_url, {examination_mode: examination_mode, keywords: keywords, TreeNodeKid: tree_node_id}, function(data){
      if (data){
        $("#rightList").html(data);
      }else{
        app.showMsg('<?= Yii::t('frontend', 'network_anomaly') ?>');
        return false;
      }
    });
    return false;
  }
  function deleteExam(id){
    $("#msm_alert_content").text("<?=Yii::t('frontend', 'exam_del_choose')?>");
    app.alert("#foo",
        {
          ok: function ()
          {

            if (id) {
              $.get('<?=Url::toRoute(['/exam-manage-main/exam-delete'])?>', {id: id}, function (response) {
                if (response.result == 'success') {
                  app.showMsg('<?=Yii::t('frontend', 'exam_del_succeed')?>');
                  reloadForm();
                } else {
                  app.showMsg(response.errmsg);
                  return false;
                }
              }, 'json');
            }else{
              app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
              return false;
            }
            return true;
          },
          cancel: function ()
          {

            return true;
          }
        }
    );
  }
  function editButton(id){
    if (id){
      $("#new_exam").empty();
      $.get('<?=Url::toRoute(['/exam-manage-main/exam-edit'])?>', {id: id}, function(data){
        if (data){
          $("#new_exam").html(data);
          app.alertWideAgain($("#new_exam"));
        }else{
          app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
          return false;
        }
      });
    }else{
      app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
      return false;
    }
  }
  function publishButton(id){
    if (id){
      $.get('<?=Url::toRoute(['/exam-manage-main/exam-publish'])?>', {id: id}, function(data){
        if (data.result == 'success'){
          app.showMsg('<?= Yii::t('frontend', 'issue_sucess') ?>');
          reloadForm();
        }else{
          app.showMsg(data.errmsg);
          return false;
        }
      },'json');
    }else{
      app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
      return false;
    }
  }

  function viewButton(id){
    $("#view_score").empty();
    if (id){
      $.get('<?=Url::toRoute(['/exam-manage-main/view-score'])?>', {id: id}, function(data){
        if (data){
          $("#view_score").html(data);
          app.alertFullAgain($("#view_score"));
        }else{
          app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
          return false;
        }
      });
    }else{
      app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
      return false;
    }
  }
  function seeModal(modalId,url){
    if(url){
      $('#'+modalId).empty();
      app.get(url, function (r){
        if(r)
        {
          $('#'+modalId).html(r);
          app.alertWide('#'+modalId);
        }
      });
    }
  }
</script>