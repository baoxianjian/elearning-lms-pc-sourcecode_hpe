<?php


use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;

$gridColumns = [
   
   
    [
        'header' => Yii::t('common', 'real_name'),
        'value' => function($model){
            return $model->teacher_name;
        },
             
    ],
    [
        'header' => Yii::t('common', 'teacher_type'),
        'value' => function($model){
            return $model->getTeacherTypes($model->teacher_type);
            /*
        	$re="";
        	if($model->teacher_type == '0'){
        		$re="内部讲师";
        	}elseif ($model->teacher_type == '1')
        	{
        		$re="外聘讲师";
        	}else {
        		$re="助教";
        	}
            return $re;
            */
        }
    ],
    [
        
    	'header' => Yii::t('common', 'gender'),
        'value' => function ($model) {
    	    if ($model->gender == 'male'){
    	        $sex = Yii::t('common', 'gender_male');
            }elseif ($model->gender == 'female'){
                $sex = Yii::t('common', 'gender_female');
            }else{
                $sex = $model->gender;
            }

            return $sex;
        }
    ],
    [
       
    	'header' => Yii::t('common', 'birthday'),
        'value' => function ($model) {
        	if($model->birthday){
        		
        		
        		return date('Y-m-d', strtotime($model->birthday));
        	}else{
        		return "";
        	}
            
        }
    ],
    [
        'header' => Yii::t('common', 'degree'),
        'value' => function($model){
        	return $model->degree;
            
        }
    ],
    /*
    [
    'header' => Yii::t('common', 'graduate_school'),
    'value' => function($model){
    	return $model->graduate_school;
    
    },
    ],
    */
    [
        'header' => Yii::t('common', 'teacher_level'),
        'value' => function($model){
            if(!$model->teacher_level)
            {
                return null;
            }
            return $model->getteacherLevels($model->teacher_level_id,'name');

        },
    ],
    [
    'header' => Yii::t('common', 'teach_year'),
    'value' => function($model){
    	return $model->teach_year;
    
    },
    ],
    [
    'header' => Yii::t('common', 'teach_domain'),
    'value' => function($model){
    	return $model->teach_domain;
    
    },
       
    ],
   
    
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{detail1} {update1} {delete1}',
        'buttons' => [
           
            
           'detail1' => function ($url, $model, $key) {
            	 
            	return
            	   Html::a('ဇ', 'javascript:;',['class' => 'btn-xs icon iconfont', 'title' => Yii::t('common', 'art_datail'), 'onclick' => 'viewTeacher(\''.Yii::$app->urlManager->createUrl(['teacher-manage/view-main','id'=>$key]).'\')'])
            	;
            
            },
          
            
            'update1' => function ($url, $model, $key) {
            	
            	return             	  
                   Html::a('ခ', 'javascript:;',['class' => 'btn-xs icon iconfont', 'title' => Yii::t('common', 'edit_button'), 'onclick' => 'editTeacher(\''.Yii::$app->urlManager->createUrl(['teacher-manage/edit-teacher','id'=>$key]).'\')'])
                   ;
                    
            },
            'delete1' => function ($url, $model, $key) {
            	
            	if($model->getCoursingToTeacher($key)){
            		return "";
            	}else{
            		return
            		  Html::a('ဆ', 'javascript:;',['class' => 'btn-xs icon iconfont', 'title' => Yii::t('common', 'delete_button'), 'onclick' => 'deleteTeacher(\''.Yii::$app->urlManager->createUrl(['teacher-manage/delete-one','id'=>$key]).'\')'])
            		;
            	}
              
                   
            },
        ],
    ],
];

?>

<div id="publish_div" style="display: none;"></div>
<script>

  
	function viewTeacher(url){
		FmodalLoad("view_teacher_main",url);
	
	 }


	function editTeacher(url){
		FmodalLoad("edit_teacher",url);
	
	 }

	function deleteTeacher(url){
   	 
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
        var inputdata = {keyword:$("#certi_key_word").val()};
        ajaxGet(ajaxUrl, "teacher_manage_list", null, inputdata);
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
	
