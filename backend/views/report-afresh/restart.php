<?php

use common\helpers\TStringHelper;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\TTimeHelper;


use components\widgets\TDatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertificationTemplate */

header("Content-type: text/xml");
?>

<div class="eln-certificaiton-template-view">
    <table width="100%" border="0" class="table table-striped table-bordered">
   	   
        <tr>
            <th>
                <?= Yii::t('common', 'service_name');?>
            </th>
            <td>
                <?= $model->service_name ?>
            </td>
            <td>
             </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'time');?>
            </th>
            <td>
<?
  $fmt='yyyy-mm-dd';
 
  if($model->restart_cycle=='2'){
  	$fmt='yyyy-mm';
  }

  echo 
  TDatePicker::widget([
    'language' => 'zh-CN',
    'name'  => 'begin_time',
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => $fmt,
        'todayHighlight' => true
    ],
    'options'=>[
    		'style'=>'width:200px',
    		'id'=>'begin_time',
    		'placeholder'=>Yii::t('frontend', 'exam_kaishishijian'),
    ]
    
]) ?> 
 </td>
  <td>
  <?  
  $fmt='yyyy-mm-dd';
 
  if($model->restart_cycle=='2'){
  	$fmt='yyyy-mm';
  }
  echo TDatePicker::widget([
    'language' => 'zh-CN',
    'name'  => 'end_time',
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => $fmt,
        'todayHighlight' => true
    ],
    'options'=>[
    		'style'=>'width:200px',
    		'id'=>'end_time',
    		'placeholder'=>Yii::t('frontend', 'end_time') ,
    ]
    
]) ?>
            </td>
            
            
        </tr>
        <tr>
         <td colspan="3" style="text-align: center;">
         <?=Html::a('<span class="btn btn-default btn-xs">'.Yii::t('backend', 'report_regenerate').'</span>', '#',
                			['id'=>'runButton',
                			//                                'class'=>'modal',
                			//                                'data-target'=>'#viewModal',
                		          			]);  ?>
           </td>
         </tr>
       
   </table>
</div>

<script type="text/javascript">

var url="<?= Yii::$app->urlManager->createUrl(['report-afresh/run-restart','id'=>$model->kid]) ?>";
	
var service_code="<?=$model->service_code ?>";

$(function(){
	//

	 $("#runButton").click(function(){
		 var begin_time=$("#begin_time").val();
		 var end_time=$("#end_time").val();

		 if(!begin_time){
			 NotyWarning("<?= Yii::t('frontend', 'exam_kaishishijian_buneng_null') ?>", 1500);
			 
	     	 return;
		 }

		 
		 if(<?=$model->restart_cycle ?>=='2'){
	 			var arys1=begin_time.split('-');      
				var sdate=new Date(arys1[0],parseInt(arys1[1]-1));      
				var arys2=end_time.split('-');      
			    var edate=new Date(arys2[0],parseInt(arys2[1]-1));      
				if(sdate >= edate) {
					NotyWarning("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>", 1500);
					return;
				}  		
		 	}else{
		 		var arys1=begin_time.split('-');      
				var sdate=new Date(arys1[0],parseInt(arys1[1]-1),arys1[2]);        
				var arys2=end_time.split('-');      
				var edate=new Date(arys2[0],parseInt(arys2[1]-1),arys2[2]);       
				if(sdate >= edate) {
					NotyWarning("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>", 1500);
					return;
				}      
		 		
		}

		 var ajaxOpt = {
		         url: url,
		         data: {begin:begin_time,end:end_time,code:service_code},
		         async: false,
		         success: function(msg){

		        	 console.log(msg);
		        	 if(msg=='2'){
		        		 reloadForm();
		        		 $('#closeBtn').trigger("click");
			         }else{
			        	 NotyWarning("<?= Yii::t('frontend', 'exam_network_err') ?>", 1500);

				     }
		        	
			     }
		 }

		 $.ajax(ajaxOpt); 

	 });
	
	//
});
                			
</script>

