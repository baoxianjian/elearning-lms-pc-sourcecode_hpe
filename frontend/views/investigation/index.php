<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = Yii::t('common','investigation_manage');
$this->params['breadcrumbs'][] = '';

?>
<style>
	.voteValue{max-width: 100%;}
</style>
<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>




<div class="container">
  <div class="row" style="margin-bottom:80px;">


<?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

  
  <div class="col-md-12 col-sm-12">
    <div class="panel panel-default hotNews">
      <div class="panel-heading">
         <i class="glyphicon glyphicon-cloud-upload"></i> <?=Yii::t('common','{value}_list',['value'=>Yii::t('common','investigation')])?>
      </div>
        <div class="panel-body">
        <div class="col-md-12 col-sm-12">
           <div class="actionBar">
             <div class="btn-group">
                 <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                 <?=Yii::t('frontend','build_investigation')?>    <span class="caret stBtn"></span>
		         </button>
		         <ul class="dropdown-menu">
		          <li><a  onclick="FmodalLoad(newvoteId,newvoteForm);"><?=Yii::t('frontend','build_vote')?> </a></li>
		          <li><a  onclick="FmodalLoad(newsurveyId,newsurveyForm);"><?=Yii::t('frontend','build_questionnaire')?> </a></li>
		         </ul>
		     </div>
		     <form class="form-inline pull-right" id="searchForm">
				<div class="form-group">
				<input type="text" id="inve_query_keyword" class="form-control" placeholder="<?=Yii::t('frontend','fuzzy_search')?>">
				<button type="reset" class="btn btn-primary pull-right"><?=Yii::t('frontend', 'reset')?></button>
				<button type="button" id="inve_query_id" class="btn btn-primary pull-right" style="margin-left:10px;">
				<?=Yii::t('common', 'search')?>
				</button>
				
				
				
				</div>
		     </form>
		    </div>
			   <div id="invest_content_list"></div>
             </div>
          </div>
        </div>
	</div>
	</div>
	</div>
  
  <!-- 新建投票的弹出窗口  -->
  <div class="ui modal" id="new_vote" >
  </div>
  
  <!-- 修改投票的弹出窗口  -->
  <div class="ui modal" id="edit_vote">
  </div>
 
  <!-- 新建问卷的弹出窗口 -->
  <div class="ui modal" id="new_survey">
  </div>
  
  <!-- 修改问卷的弹出窗口 -->
  <div class="ui modal" id="edit_survey">
  </div>

  <!-- 查看投票调查结果的弹出窗口 -->
  <div class="ui modal" id="view_survey_toupiao">
  		<div class="header">
    		<button type="button" class="close"><span aria-hidden="true">×</span></button>
    		<h4 class="modal-title"><?= Yii::t('common', 'view_button') ?><strong><?= Yii::t('common', 'investigation_vote') ?></strong><?= Yii::t('frontend', 'result') ?></h4>
        </div>
  </div>
  
  <!-- 查看问卷调查结果的弹出窗口 -->
  <div class="ui modal" id="view_survey">
  		<div class="header">
    		<button type="button" class="close"><span aria-hidden="true">×</span></button>
    		<h4 class="modal-title"><?= Yii::t('common', 'view_button') ?><strong><?= Yii::t('common', 'investigation_questionnaire') ?></strong><?= Yii::t('frontend', 'result') ?></h4>
        </div>
        <div class="content"><div class="courseInfo"><div class="panel-default scoreList view_surve_body" style="padding:20px"></div></div></div>
  </div>

  <!--投票人列表-->
  <div class="ui modal" id="list_survey">
  </div>

  <!--个人投票明细-->
  <div class="ui modal" id="detail_survey">
  </div>


  <!-- 问卷预览 -->
   <div class="ui modal" id="new_survey_preview_view" >
   </div>
   
   <!-- 投票预览 -->
   <div class="ui modal" id="new_vote_preview_view">
   </div>
   
    <!-- 问卷查看预览 -->
   <div class="ui modal" id="new_survey_read_preview_view" >
   </div>
   
   <!-- 投票查看预览 -->
   <div class="ui modal" id="new_vote_read_preview_view">
   </div>

   <!--4.1-->
   <div class="ui modal" id="checksurvay"></div>
   
   <!-- 消息弹出框 -->
   <div id="foo" class="ui modal">
		<div class="header">
		 <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
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
  <!-- container -->
  <!-- -->
  
  <script>
  //$('#jsTree').jstree();
  </script>
  <script type="text/javascript">
  
  app.extend("alert");	//扩展弹出层库
  $(function(){
	  //

    loadList();

    $("#inve_query_id").click(function(){

    	loadList();
    });


  

    
	  //
  });
  
  $('.btnaddNewChoice').bind('click', function() {
    $('.addNewChoice').removeClass('hide')
  });

  $('.btnaddNewQuestion').bind('click', function() {
    $('.addNewQuestion').removeClass('hide')
  });

//   $('.cancelBtn').bind('click', function() {
//     $(this).parent().addClass('hide')
//   })


    function compareDate(checkStartDate, checkEndDate) {      
		var arys1= new Array();      
		var arys2= new Array();      
		if(checkStartDate != null && checkEndDate != null) { 
			console.log(checkStartDate); 
			if(checkStartDate!=""&&checkEndDate!=""&&checkStartDate==checkEndDate){
				   app.showMsg("<?= Yii::t('frontend', 'start_time_no_end_time') ?>");
		       	   // app.alertSmall("#foo");	      
				   return false;      
			}    
		    arys1=checkStartDate.split('-');      
		    var sdate=new Date(arys1[0],parseInt(arys1[1]-1),arys1[2]);      
		    arys2=checkEndDate.split('-');      
		    var edate=new Date(arys2[0],parseInt(arys2[1]-1),arys2[2]);      
			if(sdate > edate) {      
			    
				app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
	       	   // app.alertSmall("#foo");	      
			    return false;         
			}  else {   
			    
			    return true;      
			}   
       }      
   }     


    var newvoteId='new_vote';

    var newvoteForm="<?=Yii::$app->urlManager->createUrl(['investigation/newvote'])?>";

    var newsurveyId='new_survey';

    var newsurveyForm="<?=Yii::$app->urlManager->createUrl(['investigation/newsurvey'])?>";

//     console.log(questionForm);

	function FmodalLoad(target, url, doAlert)
    {
	    console.log(target);
        if(url){
        	   $('#'+target).empty();
        	   if(target=='new_vote'){
		    	   $('#edit_vote').empty();
		    	   $('#new_survey').empty();
		    	   $('#edit_survey').empty();

		    	   $('#'+target).load(url, function (){
	            		!doAlert && app.alert("#"+target,{
	            			afterHide: function (){ 
	            				//$('#'+target).empty();
	                	    }
	        		    });
	                });
		    	   return;
			   }

			   if(target=='edit_vote'){
				   $('#new_vote').empty();
		    	   $('#new_survey').empty();
		    	   $('#edit_survey').empty();

		    	   $('#'+target).load(url, function (){
	            		!doAlert && app.alert("#"+target,{
	            			afterHide: function (){ 
	            				//$('#'+target).empty();
	                	    }
	        		    });
	                });
		    	   return;
			    }

			   if(target=='new_survey'){
				   $('#new_vote').empty();
		    	   $('#edit_vote').empty();
		    	   $('#edit_survey').empty();

		    	   $('#'+target).load(url, function (){
	            		!doAlert && app.alertWide("#"+target,{
	            			afterHide: function (){ 
	            				//$('#'+target).empty();
	                	    }
	        		    });
	                });
		    	   return;
			    }

			   if(target=='edit_survey'){
				   $('#new_vote').empty();
		    	   $('#new_survey').empty();
		    	   $('#edit_vote').empty();

		    	   $('#'+target).load(url, function (){
	            		!doAlert && app.alertWide("#"+target,{
	            			afterHide: function (){ 
	            				//$('#'+target).empty();
	                	    }
	        		    });
	                });
		    	   return;
			    }

			    if(target=='view_survey'){
			    	app.get(url, function (r)
			    	{
			    		if(r)
			    		{
			    			$('#'+target).html(r);
			    			app.alertWide("#"+target);
			    		}
			    	});
			    	return;
			    }

			    if(target.indexOf("Choice")>0){
			    	$('#'+target).load(url, function (){
	            		!doAlert && app.alertWide("#"+target,{
	            			afterHide: function (){ 
	            				//$('#'+target).empty();
	                	    }
	        		    });
	                });
	                return;
			    }


			    if(target.indexOf("Question")>0){
			    	$('#'+target).load(url, function (){
	            		!doAlert && app.alertWide("#"+target,{
	            			afterHide: function (){ 
	            				//$('#'+target).empty();
	                	    }
	        		    });
	                });
	                return;
			    }

        }
    }

	

	 function loadList(){
	        var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
	        $('#invest_content_list').html(loadingDiv); // 设置页面加载时的loading图片
	        var ajaxUrl = "<?=Url::toRoute(['investigation/list'])?>";
		 var inputdata = {keyword:$("#inve_query_keyword").val()};
	        ajaxGet(ajaxUrl, "invest_content_list",null,inputdata);
	 }





	 function FmodalLoad2(target, url)
	 {
	       if(url){

		       console.log(target);
		       $('#'+target).empty();
		       if(target.indexOf("vote")>0){

		    	   $('#'+target).load(url,function (){
	        	       //$("#new_survey").modal('hide');
	           		   app.alert("#"+target);
	               });
			   }

			   if(target.indexOf("survey")>0){

		    	   $('#'+target).load(url,function (){
	        	       //$("#new_survey").modal('hide');
	           		   app.alertWide("#"+target);
	               });
			   }
	           
	           
	       }
	  }
	    


	 function FmodalLoadData(target, url, data)
	 {
	       if(url){
	    	   $("#new_vote_read_preview_view").empty();
	      	   $("#new_survey_read_preview_view").empty();
	           $('#'+target).empty();

	           if(target.indexOf("vote")>0){

	        	   $('#'+target).load(url,data ,function (){
	        	       //$("#new_survey").modal('hide');
	           		   app.alert("#"+target);
	               });
		           }


	           if(target.indexOf("survey")>0){
	        	   $('#'+target).load(url,data ,function (){
	        	       //$("#new_survey").modal('hide');
	           		   app.alertWide("#"+target);
	               });

		           }
	         
	       }
	  }

	 function FmodalLoadData1(target, url, data)
	 {
	       if(url){
	           
	           $('#'+target).empty();
	           $('#'+target).load(url,data ,function (){
					
	               });
	       }
	  }

	  function validateInvestgationTitleSimple(vid,vtype,vmsg,lmsg,nmsg,validation,title){

		  if($.trim($("#"+vid).val())!=''){

			  if($("#"+vid).val().length>250){
				  validation.showAlert("#"+vid,lmsg);
			  }else{


				  if(title){

					  if(title!=$("#"+vid).val()){
						  $.get("<?=Yii::$app->urlManager->createUrl(['investigation/vote-name-validate'])?>", {name: $("#"+vid).val(),investigation_type:vtype},
			        			  function(data){
			    			 
			        			    if(data.result=='yes'){
			        			    	 $("#"+vid).val('');
			        			    	
			        		       	     $(".dimmer").scrollTop("0");	
			        			    	 setTimeout(function (){validation.showAlert("#"+vid,vmsg )}, 500);	
			        		       	    
			            			}else{
			            				 validation.hideAlert("#"+vid);

				            	    }
			        	  });
					 }
					 
					  
			      }else{
			    	  $.get("<?=Yii::$app->urlManager->createUrl(['investigation/vote-name-validate'])?>", {name: $("#"+vid).val(),investigation_type:vtype},
		        			  function(data){
		    			 
		        			    if(data.result=='yes'){
		        			    	 $("#"+vid).val('');
		        			    	
		        		       	     setTimeout(function (){validation.showAlert("#"+vid,vmsg)}, 500);
		        		       	     
		        		       	     return false;
		            			}else{


		            				validation.hideAlert("#"+vid);
		            				
			            		}
		        	  });

				  }

			  }
			 
			  
			  }else{

				  validation.showAlert("#"+vid,nmsg);
			  }
		  

		  }

	  function validateInvestgationTitle(vid,vtype,vmsg,lmsg,nmsg,validation,title){

		 

		  $("#"+vid).blur(function(){

			  validateInvestgationTitleSimple(vid,vtype,vmsg,lmsg,nmsg,validation,title);
			 
          });

		  //
      }

	  function toYYYYMMDD(val_date){
	    	 var ooo=val_date.replace('-','年');
				ooo=ooo.replace('-','月');
				ooo=ooo+"日";
				return ooo;
	      }
	
	//静态函数
	function viewInvestigation(investigation_type, kid, title, answer_type)
	{
		if(investigation_type=="0"){
			 window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/survey-manage-result-survey'])?>"+"?id="+kid;
		}else{
			 window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/survey-manage-result-vote'])?>"+"?id="+kid;
	    }
    }

    function showToupiaoSurveyDetail(THIS, kid, type)
    {
    	$(THIS).attr("disabled", true);
    	app.get(app.genURL("/teacher/get-vote-result.html?type="+type+"&category=vote&inkid="+kid), function (r)
    	{
    		if(r)
    		{
    			$("#list_survey").html(r);
    			$("#list_survey .Result_noName_survey").append('<center><a class="btn btn-default btn-sm centerBtn" style="width:20%" onclick="app.alertWide(\'#view_survey_toupiao\')"><?= Yii::t('common', 'back_button') ?></a> <a class="btn btn-default btn-sm centerBtn" style="width:20%" onclick="app.hideAlert(\'#list_survey\')"><?= Yii::t('common', 'close') ?></a></center>');
    			app.alertWide("#list_survey");
    		}
            else
            {
                app.showMsg("<?= Yii::t('frontend', 'network_anomaly') ?>.")
            }
            $(THIS).attr("disabled", false);
    	});
    }
	
    function showSurveyDetail(THIS, kid, type)
    {
    	$(THIS).attr("disabled", true);
    	app.get(app.genURL("/teacher/get-vote-result.html?type="+type+"&category=questionaire&inkid="+kid), function (r)
    	{
    		if(r)
    		{
    			$("#list_survey").html(r);
    			$("#list_survey .Result_noName_survey").append('<center><a class="btn btn-default btn-sm centerBtn" style="width:20%" onclick="app.alertWide(\'#view_survey\')"><?= Yii::t('common', 'back_button') ?></a> <a class="btn btn-default btn-sm centerBtn" style="width:20%" onclick="app.hideAlert(\'#list_survey\')"><?= Yii::t('common', 'close') ?></a></center>');
    			app.alertWide("#list_survey");
    		}
            else
            {
                app.showMsg("<?= Yii::t('frontend', 'network_anomaly') ?>.")
            }
            $(THIS).attr("disabled", false);
    	});
    }
	//个人投票情况(函数名不可修改)
	function detail($userId,courseId,modResId,inkid)
	{
		app.get(app.genURL("/teacher/questionaire-result.html?inkid="+inkid+"&user_id="+$userId), function (r)
		{
			if(r)
			{
				$("#detail_survey").html(r);
				app.alertWide("#detail_survey");
			}
            else
            {
                app.showMsg("<?= Yii::t('frontend', 'network_anomaly') ?>.");
            }
		});
	}
	function detailrenturn(v, v2, kid)
	{
		//app.alertWide("#list_survey");
		viewInvestigation(null, kid, null, null);
	}
	/*
	function detailrenturn(isToupiao, kid, title, type)
	{
		//app.alertWide("#list_survey");
		viewInvestigation(isToupiao, kid, title, type);
	}
	*/
  function Refreshopener(){
	  if(window.opener!=null) {
		  window.opener.resetForm1();
		  window.opener.reloadForm1();
	  }
  }
  </script>
  
  