<?php


use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;

$gridColumns = [
    
    [
        'header' => Yii::t('common', 'certification_name'),
        'value' => function ($model){
          
            return '<span class="preview">'.$model->certification_name.'</span>';
        },
        'contentOptions' => function($model, $key, $index, $column){
        	return ['title' => $model->certification_name,'align' => 'left'];
        },
        'format' => 'html',
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
    
	    'header' => Yii::t('common', 'created_at'),
	    'value' => function ($model) {
	    	return date("Y-m-d",$model->created_at);
	    }
    ],
   
    [
        'header' =>  Yii::t('common', 'template_url'),
        'value' => function($model){
           
            return '<span class="preview">'.$model->getTemplateName().'</span>';
        },
        'format' => 'html',
        
    ],
    [
	    'header' => Yii::t('frontend', 'pick_up_people'),
	    'value' => function($model){
	    	return $model->getObtainNum();
	    }
    
    ],
   
   
    
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{publish1} {detail1} {update1} {delete1}',
        'buttons' => [
            'publish1' => function ($url, $model, $key) {
                return 
                    Html::a('ထ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('frontend', 'issue'), 'onclick' => 'publishCertification(\''.Yii::$app->urlManager->createUrl(['certification/publish-certification','id'=>$key]).'\')'])
                    ;
            },
            'detail1' => function ($url, $model, $key) {
            	 
            	return
            	Html::a('ဂ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'art_datail'), 'onclick' => 'viewCertification(\''.Yii::$app->urlManager->createUrl(['certification/view-certification','id'=>$key]).'\')'])
            	;
            
            },
          
            
            'update1' => function ($url, $model, $key) {
            	  
            	 if($model->getObtainNum()>0){
            	    	return "";
            	 }else{
            		return Html::a('ခ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'edit_button'), 'onclick' => 'editCertification(\''.Yii::$app->urlManager->createUrl(['certification/edit-certification-ui','id'=>$key]).'\')'])
            		;
            	}       	  
                   
                    
            },
            'delete1' => function ($url, $model, $key) {
                
            	if($model->getObtainNum()>0){
            		return "";
            	}else{
                    return Html::a('ဆ', 'javascript:;',['class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'delete_button'), 'onclick' => 'deleteCertification(\''.Yii::$app->urlManager->createUrl(['certification/delete-one','id'=>$key]).'\')'])
                ;}
                   
            },
        ],
    ],
];

?>

<div id="publish_div" style="display: none;"></div>
<script>

     function viewCertification(url){
    	FmodalLoad("view_certification",url);

     }

     function editCertification(url){
     	FmodalLoad("edit_certification",url);

      }

   
    function publishCertification(url){
        console.log("publishCertification");
        FmodalLoadFull("publish_certification",url);
    	
    }

   

    function deleteCertification(url){
    	 
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
    		 $("#msm_alert_content").text("<?=Yii::t('frontend','copy_sucess')?>");
   		        app.alert("#foo",{
       		       afterHide: function (){ reloadForm(); }
                });
          });

    }
    
    function ReloadPageAfterDelete() {
        reloadForm();
    }
    function reloadForm()
    {
        var ajaxUrl = $("#jumpPageButton_grid").attr('href');
        if (typeof ajaxUrl == 'undefined'){
          
            ajaxUrl = "<?=Url::toRoute(['certification/list'])?>";
        }
        var pageSize = $('#pageSizeSelect_grid').val();
        if(typeof pageSize != 'undefined'){
            ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
        }
       // ajaxGetWithForm('searchForm', ajaxUrl,'invest_content_list');
        var inputdata = {keyword:$("#certi_key_word").val()};
        ajaxGet(ajaxUrl, "certif_content_list",null,inputdata);
    }
    function seeModal(modalId,url){
        openModalForm(modalId, url);
    }
    function preView(modalId,url){
        //var loadimg="/static/common/images/loading.gif"; // 加载时的loading图片
        //if(url){
            //alert($('#'+target).find(".modal-body-view").html());
          //  $('#'+modalId).find(".modal-body-view").empty();
            //var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
          //  $('#'+modalId).find(".modal-body-view").html(loadingDiv); // 设置页面加载时的loading图片
            //$('#'+target).find(".modal-body-view").html('<img src="'+loadimg+'"/> <?=Yii::t('frontend', 'loading')?>...');// 设置页面加载时的loading图片
           // $('#'+modalId).find(".modal-body-view").load(url);
       // }
       // $('#'+modalId).modal({backdrop: 'static', keyboard: false });
      //  if ($('#'+modalId).find(".modal-body-view").html().length == ""){
           // preView(modalId,url);
       // }
    }
    function closePreview(){
        reloadForm();
        modalHidden('previewModal');
        $("#previewModal .modal-body-view").html('');
    }
</script>

  <input type="hidden" id="page_num_id" value="<?=$page_num ?>"/>

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
            ajaxGet($(this).attr('href'), "certif_content_list");
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
