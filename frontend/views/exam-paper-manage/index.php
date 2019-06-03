<?php

use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
//$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','exam_paper_management'),'url'=>['/exam-paper-manage/index']];
$this->params['breadcrumbs'][] = Yii::t('common','exam_paper_management');
$this->params['breadcrumbs'][] = '';

?>
<style>
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
          <div class="courseInfo">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
              
              <li role="presentation"><a href="<?=Url::toRoute(['exam-manage-main/question'])?>"><?=Yii::t('frontend', 'exam_tikuguanli')?></a></li>
              <li role="presentation" class="active"><a href="<?=Url::toRoute(['exam-paper-manage/index'])?>"><?=Yii::t('frontend', 'exam_shijuanguanli')?></a></li>
              <li role="presentation"><a href="<?=Url::toRoute(['exam-manage-main/index'])?>"><?=Yii::t('frontend', 'exam_kaoshiguanli')?></a></li>
            </ul>
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="report_stat">
                <div class=" panel-default scoreList">
                  <div class="panel-body">
                    <div class="col-md-2 col-sm-12 jsPanel" style="overflow: hidden;">
                      <div class="jsTree-heading"><?=Yii::t('frontend', 'exam_shijuanfenlei')?>
                        <a href="###" id="add-catlog" class="addNodeBtn pull-right"><?=Yii::t('frontend', 'exam_xinfenlei')?></a>
                      </div>
                      <div id="jsTree">
                       
                      </div>
                    </div>
                    <div class="col-md-10 col-sm-12">
                      <div class="actionBar" style="margin-top: 20px">
                         <a class="btn btn-success  pull-left" id="add-exam-paper"><?=Yii::t('frontend', 'exam_new_paper')?></a>
                         <form class="form-inline pull-right">
                         <div class="form-group field-courseservice-course_type">
                            <select id="examination_paper_type_search_id" class="form-control" name="">
                              <option value=""><?=Yii::t('frontend', 'exam_xuanzeshijuanleixing')?></option>
                              <option value="0"><?=Yii::t('frontend', 'exam_kaoshijuan')?></option>
                              <option value="1"><?=Yii::t('frontend', 'exam_lianxijuan')?></option>
                            </select>
                            <div class="help-block"></div>
                          </div>
                          <div class="form-group">
                            <input type="text" class="form-control" placeholder="<?=Yii::t('frontend', 'exam_shijuanmingchengmiaoshu')?>" id="exam_paper_key_word">
                            <button type="button" id="exam_paper_index_clear" class="btn btn-default pull-right"><?=Yii::t('frontend', 'reset')?></button>
                            <button type="button" id="exam_paper_index_query" class="btn btn-primary pull-right" style="margin-left:10px;"><?=Yii::t('frontend', 'tag_query')?></button>
                          </div>
                        </form>
                      </div>
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
  </div>

  <input id="jsTree_tree_changed_result" type="hidden"/>
   <input id="jsTree_tree_selected_result"  type="hidden"/>
  
  
           
  <!-- 增加新的目录 -->
<div class="ui modal" id="category-dialog"></div>

 <!-- 增加新的试卷-->
 <div class="ui modal" id="new_exam_paper" >
  </div>
  
   <!-- 增加新的试卷-->
 <div class="ui modal" id="edit_exam_paper" >
  </div>
  
  <!-- 预览试卷 -->
  <div class="ui modal" id="view_exam_paper" >
  </div>
  
  <!-- 消息弹出框 -->
   <div id="foo" class="ui modal">
		<div class="header">
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<?=Yii::t('frontend', 'exam_xiaoxi')?>
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
    $(document).ready(function(){

    	loadTree();
    	loadList();

        $("#jsTree").on("click.jstree", ".delete_tree_node", function(){
            var delete_url = '<?=Url::toRoute(['/exam-paper-manage/delete-paper-category'])?>';
            var tree_node_id = $(this).attr('data-key');
            NotyConfirm('<?=Yii::t('frontend', 'exam_bukehuifu')?>',  function(data) {
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
            var edit_url = '<?=Url::toRoute(['/exam-paper-manage/add-category', 'tree_type_code'=>'examination-paper-category'])?>';
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

    	$("#exam_paper_index_query").click(function(){

    		loadList();
    		
    	});

    	$("#exam_paper_index_clear").click(function(){

    		$("#examination_paper_type_search_id option:first").prop("selected", 'selected');
    		$("#exam_paper_key_word").val("");
    		
    	});
        
      
        ajaxGet("<?=Url::toRoute(['exam-paper-manage/add-category','tree_type_code'=>'examination-paper-category'])?>",'category-dialog');


        $("#add-exam-paper").click(function(){
            var cat_id=$("#jsTree_tree_changed_result").val();

            cat_id=cat_id.replace('["',"");
            cat_id=cat_id.replace('"]',"");

            if(cat_id==""){
            	app.showMsg("<?=Yii::t('frontend', 'exam_xuanzeshijuanfenlei')?>");
            	return;
            }

            
            
            if(cat_id==-1){
            	app.showMsg("<?=Yii::t('frontend', 'exam_xuanzeshijuanfenlei')?>");
            	return;
            }
        	FmodalLoadData("new_exam_paper","<?=Yii::$app->urlManager->createUrl(['exam-paper-manage/new-exam-paper'])?>"+"?id="+cat_id);
        	
         });
        
    });

    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
            'TreeType'=>'examination-paper-category',
            'ContentName'=>'tree-node',
            'ListRoute'=>'../exam-paper-manage/list',
            'IncludeRoot'=>'True',
            'MergeRoot'=>'False',
            'ShowContentCount'=>'True',
            'DeleteNode'=>'False',
            'EditNode'=>'False'])
            ?>";
            app.get(ajaxUrl, function(r){
                if (r){
                  $("#jsTree").html(r);
                  getCategoryDialog();
                }
              });
    }

    function TreeCallback(){
    }

    function getCategoryDialog(){
        $('#category-dialog').empty();
        $.get("<?=Url::toRoute(['exam-paper-manage/add-category','tree_type_code'=>'examination-paper-category','title'=>Yii::t('frontend', 'exam_shijuanfenlei')])?>",function(data){
          if (data){
            $('#category-dialog').html(data);
          }
        });
    }
   

    function FmodalLoadData(target, url)
	 {
    	
    	 if(url){
      	   $('#'+target).empty();
             $('#'+target).load(url, function (){
             		 app.alert("#"+target,{
             			afterHide: function (){ 
             				$('#'+target).empty();
                 	    }
         		    });
                 });
            
         }
	  }

    function FmodalLoadData1(target, url)
	 {
   	
   	 if(url){
     	   $('#'+target).empty();
            $('#'+target).load(url, function (){
            		 app.alertWide("#"+target,{
            			afterHide: function (){ 
            				$('#'+target).empty();
            				$("#exam_paper_index_query").trigger("click");
                	    }
        		    });
                });
           
        }
	  }

    function loadList(){
        var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'exam_page_loading')?></p></div></div>';
        $('#certif_content_list').html(loadingDiv); // 设置页面加载时的loading图片
        var ajaxUrl = "<?=Url::toRoute(['exam-paper-manage/list'])?>";

        var cat_id111=$("#jsTree_tree_changed_result").val();

        cat_id111=cat_id111.replace('["',"");
        cat_id111=cat_id111.replace('"]',"");

        
        var inputdata = {keyword:$("#exam_paper_key_word").val(),examination_paper_type:$("#examination_paper_type_search_id").val(),
        		TreeNodeKid:cat_id111};
//        alert(inputdata['keyword']);
        ajaxGet(ajaxUrl, "rightList",null,inputdata);
    }
  
   
     </script>
     
  
  