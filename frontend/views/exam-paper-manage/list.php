<?php


use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;

$gridColumns = [
   
   
    [
        'header' => Yii::t('common', 'course_code'),
        'value' => function($model){
            return $model->code;
        },            
    ],
    [
	    'header' => Yii::t('common', 'exam_paper_title'),
	    'value' => function($model){
	    	return '<span class="preview">'.$model->title.'</span>';
	    },	
	    'contentOptions' => function($model, $key, $index, $column){
	    	return ['title' => $model->title,'align' => 'left'];
	    },
	    'format' => 'html',
    ],
    [
	    'header' => Yii::t('common', 'examination_question_number'),
	    'value' => function($model){
	    	return $model->examination_question_number;
	    },   
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
	    'header' => Yii::t('common', 'exam_update_at'),
	    'value' => function ($model) {
	    	return date("Y-m-d",$model->updated_at);
	    }
    ],
    [
	    'header' => Yii::t('common', 'type'),
	    'value' => function ($model) {    	
	    	if($model->examination_paper_type=='0'){
	    		return Yii::t('frontend', 'exam_kaoshijuan');
	    	}else if($model->examination_paper_type=='1'){
	    		return Yii::t('frontend', 'exam_lianxijuan');
	    	}
	    }
    ]
   ,
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{update1}{detail1}{delete1}',
        'buttons' => [
             'update1' => function ($url, $model, $key) {
            	
            	return             	  
                   Html::a('ခ', 'javascript:;',['class' => 'btn-xs icon iconfont', 'title' => Yii::t('common', 'edit_button'), 'onclick' => 'editExamPaper(\''.Yii::$app->urlManager->createUrl(['exam-paper-manage/edit-exam-paper-ui','id'=>$key]).'\')'])
                   ;
                    
            },
            'detail1' => function ($url, $model, $key) {
            
            	if($model->examination_question_number==0){
            		
            		return "";
            	}else{
            		return
            		Html::a('ဇ', 'javascript:;',['class' => 'btn-xs icon iconfont', 'title' => Yii::t('common', 'preview_button'), 'onclick' => 'viewExamPaper(\''.Yii::$app->urlManager->createUrl(['exam-paper-manage/view-exam-paper','id'=>$key]).'\')'])
            		;
            	}
            	
            
            },
            'delete1' => function ($url, $model, $key) {
            	
            	return
            		  Html::a('ဆ', 'javascript:;',['class' => 'btn-xs icon iconfont', 'title' => Yii::t('common', 'delete_button'), 'onclick' => 'deleteExamPaper(\''.Yii::$app->urlManager->createUrl(['exam-paper-manage/delete-one','id'=>$key]).'\')'])
            		;
              
                   
            },
        ],
    ],
];

?>

<div id="publish_div" style="display: none;"></div>
<script>

	function viewExamPaper(url){
		FmodalLoadData1("view_exam_paper",url);
		 //app.refreshAlert("#view_exam_paper");
	 }


	function editExamPaper(url){
		FmodalLoadData("edit_exam_paper",url);
	
	 }

	function deleteExamPaper(url){
   	 
    	$("#msm_alert_content").text("<?=Yii::t('frontend', 'exam_shifoushanchu')?>");
      	app.alert("#foo",
      			{
      				ok: function ()
      					{
      					    
      					    $.get(url,function(){
      					    	app.showMsg("<?=Yii::t('frontend', 'exam_del_succeed')?>");
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
        var inputdata = {keyword:$("#certi_key_word").val(),examination_paper_type:$("#examination_paper_type_search_id").val()};
        ajaxGet(ajaxUrl, "rightList", null, inputdata);
    }
    function seeModal(modalId,url){
        openModalForm(modalId, url);
    }
    function preView(modalId,url){
        var loadimg="/static/common/images/loading.gif"; // 加载时的loading图片
        if (url){
            //alert($('#'+target).find(".modal-body-view").html());
            $('#'+modalId).find(".modal-body-view").empty();
            var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'exam_page_loading')?></p></div></div>';
            $('#'+modalId).find(".modal-body-view").html(loadingDiv); // 设置页面加载时的loading图片
            //$('#'+target).find(".modal-body-view").html('<img src="'+loadimg+'"/> 页面加载中，请稍后...');// 设置页面加载时的loading图片
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
<div class="actionBar">
   
   
</div>
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
            ajaxGet($(this).attr('href'), "teacher_manage_list");
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
    .table > tbody > tr > td {padding: 8px 0px;}
    .btn-xs {padding: 5px 5px;}
</style>
	
