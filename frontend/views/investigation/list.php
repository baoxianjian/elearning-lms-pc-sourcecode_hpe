<?php


use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;

$gridColumns = [
    
    [
    	'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'investigation_title'),
    	'template' =>'{preview1}',
        'buttons' => [
            'preview1' => function ($url, $model, $key) {
            	
            	return $model->investigation_type == '1' ?
            	   Html::a($model->title, 'javascript:;',['title' =>  Html::decode($model->title),'class'=>'preview' , 'onclick' => 'previewVote(\''.Yii::$app->urlManager->createUrl(['investigation/preview-vote','id'=>$key]).'\')'])
            	   
            	   :
                   Html::a($model->title, 'javascript:;',['title' => Html::decode($model->title), 'class'=>'preview' ,'onclick' => 'previewSurvey(\''.Yii::$app->urlManager->createUrl(['investigation/preview-survey','id'=>$key]).'\')'])
                   ;
                    
            }
        ],
        'contentOptions' => function(){
        	return ['align' => 'left'];
        },
       
        
    ],
   
    [
        'header' => Yii::t('common', 'investigation_type'),
        'value' => function($model){
           // return $model->investigation_type == '0' ? '问卷' : '投票';
           if($model->investigation_type == '0'){
           	  if($model->answer_type == '0'){
           	  	return Yii::t('frontend','questionnaire_real_name');
           	  }else{
           	  	return Yii::t('frontend','questionnaire_pritave_name');
           	  }
           }else{
	           	if($model->answer_type == '0'){
	           		return  Yii::t('frontend', 'vote_real_name');
	           	}else{
	           		return  Yii::t('frontend', 'vote_private_name');
	           	}
           }
        }
        
    ],
    [
        'header' => Yii::t('common', 'status'),
        'value' => function($model){
           // return $model->status == '0' ? '未发布' : '已发布';
           if($model->status =='0'){
           	  return Yii::t('frontend','publish_status_no');
           }else if($model->status =='1'){
           	  return Yii::t('frontend','publish_status_yes');
           }else if($model->status =='2'){
           	  return Yii::t('common','status_2');
           }
        }
    ],
    [
    'header' => Yii::t('common', 'investigation_range_'),
    'value' => function($model){
    	return $model->investigation_range == '0' ?  Yii::t('frontend', 'independent_call')  : Yii::t('frontend', 'course_call');
    }
    ],
    [
        
    	'header' => Yii::t('common', 'created_at'),
        'value' => function ($model) {
            return date("Y-m-d",$model->created_at);
        }
    ],
    [
       
    	'header' => Yii::t('common', 'created_by_name'),
        'value' => function ($model) {
            return '<span class="preview">'.$model->getCreatedBy().'</span>';
        },
        'contentOptions' => function($model, $key, $index, $column){
        	return ['title' => $model->getCreatedBy(),'align' => 'left'];
        },
        'format' => 'html',
        
    ],
    [
        'header' => Yii::t('common', 'question_num'),
        'value' => function($model){
        	return $model->investigation_type == '0'?$model->getQuestionNum():1;
            
        }
    ],
   
    
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{publish1} {copy1} {update1} {delete1}',
        'buttons' => [
            'publish1' => function ($url, $model, $key) {
                

                if($model->status == '1'||$model->status == '2'){
                	if($model->investigation_range == '0'){
                		return  Html::a('ဇ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'view_button'), 'onclick' => 'viewInvestigation('.$model->investigation_type.',\''.$key.'\',\''.$model->title.'\',\''.$model->answer_type.'\')'])
                		;
                	}else{
                		return "";
                	}
                	
                }else if($model->status == '0'){
                	return Html::a('င', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'art_publish'), 'onclick' => 'publishInvestigation(\''.Yii::$app->urlManager->createUrl(['investigation/publish','id'=>$key]).'\')'])
                	;
                }
                
                   
            },
            
            'copy1' => function ($url, $model, $key) {
            	return Html::a('စ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'copy_button'), 'onclick' => 'copyInvestigation(\''.Yii::$app->urlManager->createUrl(['investigation/copy-investigation','id'=>$key]).'\')'])
            	;
            
            },
            
            'update1' => function ($url, $model, $key) {
            	if($model->status=='0'){
            		
            	
            	return $model->investigation_type == '1' ?
            	   Html::a('ခ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'edit_button'), 'onclick' => 'editVote(\''.Yii::$app->urlManager->createUrl(['investigation/edit-vote-ui','id'=>$key]).'\')'])
            	   
            	   :
                   Html::a('ခ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'edit_button'), 'onclick' => 'editSurvey(\''.Yii::$app->urlManager->createUrl(['investigation/edit-survey-ui','id'=>$key]).'\')'])
                   ;
            	}else{
            		return "";
            	}
                    
            },
            'delete1' => function ($url, $model, $key) {
            	if($model->status=='0'){
                return
                
                Html::a('ဆ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'delete_button'), 'onclick' => 'deleteInvestigation(\''.Yii::$app->urlManager->createUrl(['investigation/delete-one','id'=>$key]).'\')'])
                ;}else{
                	return "";
                }
                   
            },
        ],
    ],
];

?>

<div id="publish_div" style="display: none;"></div>
<script>

     function editVote(url){
    	FmodalLoad("edit_vote",url);

     }

     

     function editSurvey(url){
     	FmodalLoad("edit_survey",url);

      }


     function previewVote(url){
         $("#new_vote_preview_view").empty();
         $("#new_vote_read_preview_view").empty();
         
    	 FmodalLoad2("new_vote_read_preview_view",url);

     }

     function previewSurvey(url){

    	 
    	 $("#new_survey_preview_view").empty();
    	 $("#new_survey_read_preview_view").empty();
    	 
    	 FmodalLoad2("new_survey_read_preview_view",url);

      }

   
    function publishInvestigation(url){
        
        $.get(url,function(){
        	app.showMsg("<?= Yii::t('frontend', 'issue_sucess') ?>");
        	reloadForm();
        	
            });
    }


    function deleteInvestigation(url){
    	$("#msm_alert_content").text("<?= Yii::t('common', 'data_confirm') ?>");
    	app.alert("#foo",
    			{
    				ok: function ()
    					{
    					    console.log("<?= Yii::t('frontend', 'delete_sucess') ?>");
    					    $.get(url,function(){
    					    	app.showMsg("<?= Yii::t('frontend', 'delete_sucess') ?>");
    					    	reloadForm();
    			            });
    						return true;
    					},
    				cancel: function ()
    					{
    						
    						return true;
    					}
    			}
    	);
    	 
    }


    function copyInvestigation(url){

    	$.get(url,function(){
    		app.showMsg("<?=Yii::t('frontend','copy_sucess')?>");
         	reloadForm();
          });

    }
    
    function ReloadPageAfterDelete() {
        reloadForm();
    }
    function reloadForm()
    {
        var ajaxUrl = $("#jumpPageButton_grid").attr('href');
        if (typeof ajaxUrl == 'undefined'){
            ajaxUrl = "<?=Url::toRoute([$this->context->id.'/list'])?>";
        }
        var pageSize = $('#pageSizeSelect_grid').val();
        if(typeof pageSize != 'undefined'){
            ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
        }
       // ajaxGetWithForm('searchForm', ajaxUrl,'invest_content_list');
        var inputdata = {keyword:$("#inve_query_keyword").val()};
        ajaxGet(ajaxUrl, "invest_content_list",null,inputdata);
    }
    function seeModal(modalId,url){
        openModalForm(modalId, url);
    }
    function preView(modalId,url){
        var loadimg="/static/common/images/loading.gif"; // 加载时的loading图片
        if(url){
            //alert($('#'+target).find(".modal-body-view").html());
            $('#'+modalId).find(".modal-body-view").empty();
            var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
            $('#'+modalId).find(".modal-body-view").html(loadingDiv); // 设置页面加载时的loading图片
            //$('#'+target).find(".modal-body-view").html('<img src="'+loadimg+'"/> <?=Yii::t('frontend', 'loading')?>...');// 设置页面加载时的loading图片
            $('#'+modalId).find(".modal-body-view").load(url);
        }
        $('#'+modalId).modal({backdrop: 'static', keyboard: false });
        if ($('#'+modalId).find(".modal-body-view").html().length == ""){
            preView(modalId,url);
        }
    }
    function closePreview(){
        reloadForm();
        modalHidden('previewModal');
        $("#previewModal .modal-body-view").html('');
    }
</script>

<div style="clear: both"></div>
<div style="text-align:right">
<?= TGridView::widget([
    'id'=>'grid',
    'dataProvider' => $dataProvider,
    'columns' => $gridColumns,
    'pjax'=>false,
    'pjaxSettings'=>[
        'neverTimeout'=>true,
    ]
]); ?>
</div>
<script>
    $(function(){
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), "invest_content_list");
        });
        $(".preview").on('click', function(e){
            e.preventDefault();
            preView('previewModal', $(this).attr('href'));
        });
        $("#previewModal .modal-body-view").css('minHeight', $(window).height()+'px');
    });
</script>
<style>
    #grid .summary {display: none;}
    .table > thead:first-child > tr:first-child > th,.table-bordered > tbody > tr > td {text-align: center;}
    .btn-xs {padding: 5px 5px;}
    #grid table td:last-child {text-align: left;}
    #grid table td:first-child {text-align: left;}
</style>	
