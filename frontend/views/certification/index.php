<?php


use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = Yii::t('common','certification_manage');
$this->params['breadcrumbs'][] = '';

?>

<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>

 <div class="container">
    <div class="row">
     <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      <div class="col-md-12 col-sm-12">
        <div class="panel panel-default hotNews">
          <div class="panel-heading">
            <i class="glyphicon glyphicon-cloud-upload"></i> <?=Yii::t('common','{value}_list',['value'=>Yii::t('common','serial')])?>
          </div>
          <div class="panel-body">
            <div class="actionBar">
              <a class="btn btn-success  pull-left" onclick="FmodalLoad(newCertificationId,newCertificationForm);"><?=Yii::t('frontend', 'add_certificate')?></a>
              <form class="form-inline pull-right">
                <div class="form-group">
                  <input type="text" id="certi_key_word" class="form-control" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('common','certification_name')])?>">
                  <button type="reset" class="btn btn-primary pull-right"><?=Yii::t('frontend', 'reset')?></button>
                  <button type="button" id="cert_index_query" class="btn btn-primary pull-right" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
                </div>
              </form>
            </div>
            
          
          
            <div id="certif_content_list"></div>
          </div>
         
        </div>
      </div>
    </div>
  </div>
  
  <!-- 新增证书 -->
  <div class="ui modal" id="new_certification" >
  </div>
  
  <!-- 修改证书 -->
  <div class="ui modal" id="edit_certification" >
  </div>
  
  
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
	
	<!-- 详情界面 -->
	<div class="ui modal" id="view_certification">
	</div>
	
	<!-- 颁发界面 -->
	<div class="ui modal" id="publish_certification" >
    </div>
  
   <script type="text/javascript">
   app.extend("alert");	//扩展弹出层库


   

   $(function(){
//

	loadList();

	 $("#cert_index_query").click(function(){

	    	loadList();
	    });
	

//
	   });

   function loadList(){
       var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
       $('#certif_content_list').html(loadingDiv); // 设置页面加载时的loading图片
       var ajaxUrl = "<?=Url::toRoute(['certification/list'])?>";

       var inputdata = {keyword:$("#certi_key_word").val()};
       ajaxGet(ajaxUrl, "certif_content_list", null, inputdata);
   }


   var newCertificationId='new_certification';

   var newCertificationForm="<?=Yii::$app->urlManager->createUrl(['certification/new-certification'])?>";


   function FmodalLoad(target, url, doAlert)
   {

	 
       if(url){
           if(target=='new_certification'){
        	   
               }
           else{
            	   url=url+"&p_page="+$("#page_num_id").val();
               }
    	  
    	   console.log(url);
           $('#'+target).empty();
           $('#'+target).load(url, function (){
           		!doAlert && app.alertWide("#"+target,{
           			afterHide: function (){ 
           				$('#'+target).empty();
               	    }
       		    });
               });
       }
   }

   function FmodalLoadFull(target, url, doAlert)
   {

	 
       if(url){
           if(target=='new_certification'){
        	   
               }
           else{
            	   url=url+"&p_page="+$("#page_num_id").val();
               }
    	  
    	   console.log(url);
           $('#'+target).empty();
           $('#'+target).load(url, function (){
           		!doAlert && app.alertFull("#"+target,{
           			afterHide: function (){ 
           				$('#'+target).empty();
               	    }
       		    });
               });
       }
   }

   var user_uuid="";
   
   

   function selectTemplate(formId, obj, duration) {
	   console.log("1111");
       $("#" + formId + " #btn_dropdown").html($(obj).html() + ' &nbsp;<span class="caret">');
       $("#" + formId + " #certification_template_id").val(duration);
   }

   function selectExpireTimeType(formId, obj, duration) {

	   $("#" + formId + " #btn_dropdown_type").html($(obj).html() + ' &nbsp;<span class="caret">');
       $("#" + formId + " #expire_time_type_id").val(duration);
       if(duration=='0'){
    	   $("#expire_time_day_id").show();
    	   $("#expire_time_rili_id").hide();
    	   $("#expire_time_day_id").val('');
    	   $("#expire_time_rili_id").val('');
           app.preventGenCalendar();
       }else if(duration=='1'){
    	   $("#expire_time_day_id").hide();
    	   $("#expire_time_rili_id").show();
    	  
    	   $("#expire_time_day_id").val('');
    	   $("#expire_time_rili_id").val('');
       }

   	}

   function selectExpireTimeType1(formId, obj, duration) {

	   $("#" + formId + " #btn_dropdown_type").html($(obj).html() + ' &nbsp;<span class="caret">');
       $("#" + formId + " #expire_time_type_id").val(duration);
       if(duration=='2'){
    	   $("#expire_time_day_id").hide();
    	   $("#expire_time_rili_id").hide();
    	   $("#expire_time_day_id").val('');
    	   $("#expire_time_rili_id").val('');
           app.preventGenCalendar();
       }

   	}

   function compareDate(checkStartDate, checkEndDate,validation,vid) {      
		var arys1= new Array();      
		var arys2= new Array();      
		if(checkStartDate != null && checkEndDate != null) {      
		    arys1=checkStartDate.split('-');      
		    var sdate=new Date(arys1[0],parseInt(arys1[1]-1),arys1[2]);      
		    arys2=checkEndDate.split('-');      
		    var edate=new Date(arys2[0],parseInt(arys2[1]-1),arys2[2]);      
			if(sdate > edate) {      
			    
				//app.showMsg("有效期时间必须大于当前时间");
				validation.showAlert("#"+vid,"<?=Yii::t('frontend', 'alert_warning_time4')?>");
	       	   // app.alertSmall("#foo");	      
			    return false;         
			}  else {   
			    
			    return true;      
			}   
     }      
 }  


   function validateCertificationNameSimple(vid,vmsg,lmsg,nmsg,validation,title){

	   if($.trim($("#"+vid).val())!=''){
			  console.log("-----------");
			  if($("#"+vid).val().length>25){
				  validation.showAlert("#"+vid,lmsg);
			  }else{


				  if(title){

					  if(title!=$("#"+vid).val()){
						  $.get("<?=Yii::$app->urlManager->createUrl(['certification/certification-name-validate'])?>", {name: $("#"+vid).val()},
			        			  function(data){
			    			 
			        			    if(data.result=='yes'){
			        			    	 $("#"+vid).val('');
			        			    	// $("#msm_alert_content").text(vmsg);
			        		       	   //  app.alertSmall("#foo");	
			        			    	 setTimeout(function (){validation.showAlert("#"+vid,vmsg )}, 500);	
			        		       	    
			            			}else{
			            				 validation.hideAlert("#"+vid);

				            	    }
			        	  });
					 }
					 
					  
			      }else{
			    	  console.log("-----0------");
			    	  $.get("<?=Yii::$app->urlManager->createUrl(['certification/certification-name-validate'])?>", {name: $("#"+vid).val()},
		        			  function(data){
		    			 
		        			    if(data.result=='yes'){
		        			    	 $("#"+vid).val('');
		        			    	// $("#msm_alert_content").text(vmsg);
		        		       	     //app.alertSmall("#foo");
		        		       	     setTimeout(function (){validation.showAlert("#"+vid,vmsg)}, 500);	
		        		       	     return true;
		            			}else{


		            				validation.hideAlert("#"+vid);
		            				
			            		}
		        	  });

				  }

			  }
			 
			  
			  }else{
console.log(nmsg);
				  validation.showAlert("#"+vid,nmsg);
			  }

	   }   

   function validateCertificationName(vid,vmsg,lmsg,nmsg,validation,title){


		  $("#"+vid).blur(function(){

			 
			  validateCertificationNameSimple(vid,vmsg,lmsg,nmsg,validation,title);
			  
        	 

          });    

   }

   </script>
   
   <script>
  ! function($) {
    var hash = location.hash && location.hash.substr(1).split("@"),
      $_element, $_evt;
    if (hash && 2 === hash.length) {
      $_element = hash[0];
      $_evt = hash[1];
      try {
        $($_element)[$_evt]();
      } catch (e) {
        console.log(e.stack || e);
      }
    }
  }(jQuery);
  </script>
   