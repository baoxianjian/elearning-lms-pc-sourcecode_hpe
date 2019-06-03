<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

?>
<style>
	#appMsg{margin-top:auto !important}
	#playWindow{min-height: 500px}
</style>

<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>

<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>
<input type="hidden" data-type='special' id="iframe-player" />
<!-- 预览问卷的弹出窗口 -->
 <style>
#appMsg{margin-top:auto !important}
</style>
  <div class="courseInfo">
                <div role="tabpanel" class="tab-pane active" id="teacher_info">
                  <div class=" panel-default scoreList">
                    <div class="panel-body">
 
 			      <div  class="blockScreen hide">
                        <span class="glyphicon glyphicon-ok"></span>
                        <p><?= Yii::t('frontend', 'finish_investigation') ?></p>
                   </div>
                  <div class="infoBlock">
                     
                    <div class="row questionGroup_quest ">
                      <div class="hide">
                      <p><?= Yii::t('common', 'time_validity') ?>：<label id="start_at_pre_id"></label>-<label id="end_at_pre_id"></label></p>
                      </div>
                      <h4 id="title_pre_id"></h4>
                     <p style="text-align:left"><?= Yii::t('frontend', 'brief') ?>:<h4 id="description_pre_id"></h4> </p>
                    </div>
                    
                      <div id="pre_first_survey_row"></div>
                   
                  
                
                    
                    <div class="col-md-12 col-sm-12 addNewChoice hide" id="course_play_survey_result_id">
                         
                        </div>
                  </div>
            
           </div>
        </div>
       </div>
      </div> 
        
         <script type="text/javascript">

		var point_is_showing=false;
         function play_survey_res_completeFinal(){
        	 $.get("<?=Url::toRoute(['investigation/play-investigation-res-complete',])?>"+"?course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteFinalId?>"+"&complete_type=1&course_reg_id="+course_play_survey_course_reg_id,function(data){
				 if(point_is_showing)
				 {
					 setTimeout("scorePointEffect("+data.pointResult.show_point+","+data.pointResult.point_name+","+data.pointResult.available_point+")",1000);
				 }
				 else
				 {
					 scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
					 point_is_showing=true;
				 }
            	 });
             }

         function play_survey_res_completing(){
        	 $.get("<?=Url::toRoute(['investigation/play-investigation-res-complete',])?>"+"?course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteProcessId?>"+"&complete_type=0&course_reg_id="+course_play_survey_course_reg_id,function(data){
				 if(point_is_showing)
				 {
					 setTimeout("scorePointEffect("+data.pointResult.show_point+","+data.pointResult.point_name+","+data.pointResult.available_point+")",1000);
				 }
				 else
				 {
					 scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
					 point_is_showing=true;
				 }
        		
            	 });
             }

         function get_play_survey_result(){

        	 $.get("<?=Url::toRoute(['investigation/get-play-investigation-submit-result',])?>"+"?course_complete_id=<?=$courseCompleteFinalId?>&course_reg_id=<?=$course_reg_id?>&course_id=<?=$courseId?>&mod_id=<?=$mod_id?>&investigation_id=<?=$id?>&attempt=<?=$attempt?>",function(data){

        		 if(data.result=='yes'){
            		// $(".blockScreen").removeClass('hide');
            		
            		 get_play_survey_result_show();
            	 }else{

            		 init();
                	 }

            	 });
         }

         function get_play_survey_result_show(){

        	 $.get("<?=Url::toRoute(['investigation/get-sub-survey-result',])?>"+"?id=<?=$id?>&course_complete_id=<?=$courseCompleteFinalId?>&course_reg_id=<?=$course_reg_id?>&course_id=<?=$courseId?>&mod_id=<?=$mod_id?>&investigation_id=<?=$id?>",function(msg){
        		 $("#title_pre_id").text(msg.result.title);
 				$("#description_pre_id").text(msg.result.description);
        		 $("#pre_first_survey_row").empty();

        		 var lists_arrs=msg.result.question;
           	  survey_submit_get_obj=msg.result;
           	  var first_pagination_code=_.findIndex(lists_arrs, {question_type:'3'}); 

                 var pre_page_data="";
                 var next_page_data="";

                 var div_id="";

                 var preview_num_show=1;

   			   for(var i=0;i<lists_arrs.length;i++){

   				   var lists_tmp=lists_arrs[i];
   				   console.log(lists_tmp);
   				   if(i==0){
   					   var lists=[];
   					   lists.push({id:lists_tmp.id});
   					   div_id=lists_tmp.id;
   					   pre_page_data=lists_tmp.id;
   					   next_page_data=lists_tmp.id;
   					   //分页div块
   					   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
   					   $("#pre_first_survey_row").append(fenye_t_templ);
   				   }
   				   
   				   if(lists_tmp.question_type=='2'){
   					   var lists=[];
   					   lists_tmp.preview_num_show=preview_num_show;
   					   lists.push(lists_tmp);
   					   var edit_t7_templ=_.template($("#pre_t7").html(),{variable: 'data'})({datas:lists});
   				       $("#"+div_id).append(edit_t7_templ);
   				       preview_num_show++;
   				   }else if(lists_tmp.question_type=='3'){
   					  if(first_pagination_code==i){

   						  next_page_data=lists_tmp.id;
   						  //下步按钮
   						   var lists_p=[{id:pre_page_data,nid:next_page_data}];
   						   var next_page_t_templ=_.template($("#next_page_t").html(),{variable: 'data'})({datas:lists_p});
   						  
   						   $("#"+div_id).append(next_page_t_templ);

   						   var lists=[];
   						   lists.push({id:lists_tmp.id,hide:'hidden="hidden"'});
   						   div_id=lists_tmp.id;
   						 //分页div块
   						   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
   						   $("#pre_first_survey_row").append(fenye_t_templ);	 
   					  }else{
   						  if(lists_arrs.length!=3){
   							  var lists_p=[{id:pre_page_data,uid:next_page_data,pid:next_page_data,nid:lists_tmp.id}];
   							   pre_page_data=next_page_data;
   							   next_page_data=lists_tmp.id;
   							  //上下步按钮
   							 
   							   var pre_next_page_t_templ=_.template($("#pre_next_page_t").html(),{variable: 'data'})({datas:lists_p});
   							   
   							  
   							   $("#"+div_id).append(pre_next_page_t_templ);
   							 
   							   var lists=[];
   							   lists.push({id:lists_tmp.id,hide:'hidden="hidden"'});
   							   div_id=lists_tmp.id;
   							 //分页div块
   							   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
   							   $("#pre_first_survey_row").append(fenye_t_templ);

   						  }
   						  
   					  }
   				   }else if(lists_tmp.question_type=='1'){
   					   
   					   var lists=[];
   					   lists_tmp.preview_num_show=preview_num_show;
   					   lists.push(lists_tmp);
   					   var edit_t8_templ=_.template($("#pre_t8").html(),{variable: 'data'})({datas:lists});
   					   $("#"+div_id).append(edit_t8_templ);
   					   preview_num_show++;
   				   }else if(lists_tmp.question_type=='0'){
   					  
   					   var lists=[];
   					   lists_tmp.preview_num_show=preview_num_show;
   					   lists.push(lists_tmp);
   					   var edit_t6_templ=_.template($("#pre_t6").html(),{variable: 'data'})({datas:lists});
   					   $("#"+div_id).append(edit_t6_templ);
   					   preview_num_show++;
   				   }

   				   if(i==lists_arrs.length-1&&i!=0&&first_pagination_code!=-1){
   					   //上步按钮
   					   var lists_p=[{id:pre_page_data,pid:next_page_data}];
   					   var pre_page_t_templ=_.template($("#pre_page_t").html(),{variable: 'data'})({datas:lists_p});
   						  
   					   $("#"+div_id).append(pre_page_t_templ);
   					   
   				   }

   				 <?php
   	  					if ($mode != 'preview'){
   	  					?>
   	  				   if(i==(lists_arrs.length-1)){

   	  					   console.log("<?= Yii::t('frontend', 'exam_submit') ?>");
   	  					   var survey_submit_btn_id=[1];
   	  					   var survey_submit_btn_id_templ=_.template($("#survey_submit_btn_id").html(),{variable: 'data'})({datas:survey_submit_btn_id});
   	  					
   	  					   
   	  					   $("#"+div_id).append(survey_submit_btn_id_templ);
   	  				   }
   	  				   <?php
   	  				   }
   	  				   ?>

   	  				 $("#survey_play_submit_a").hide();
   				  
   			  }
           		 

        		 //end
            	 });
         }

         
         function toYYYYMMDD(val_date){
	    	 var ooo=val_date.replace('-','年');
				ooo=ooo.replace('-','月');
				ooo=ooo+"日";
				return ooo;
	      }

	      var survey_submit_get_obj;
	      var survey_submit_count=0;
	      var course_play_survey_course_id="<?=$courseId?>";
	      var course_play_survey_course_reg_id="<?=$course_reg_id?>";
	      var course_play_survey_mod_id="<?=$mod_id?>";
	      var course_play_survey_mod_res_id="<?=$modResId?>";
	      var course_play_survey_courseactivity_id="<?=$courseactivity_id?>";
	      var course_play_survey_component_id="<?=$component_id?>";
	      
	      function survey_play_submit(){
	    		 var survey_submit_to_result_obj_arr=[];
	    		 
	    		 var s_investigation_id=survey_submit_get_obj.id;

	    		 var submit_flag=false;

	    		 var quest_num=1;
	    		
	    		 for(var i=0;i<survey_submit_get_obj.question.length;i++){
	    			 var survey_submit_to_result_obj={};
	    			 
	    			 var survey_submit__question= survey_submit_get_obj.question[i];
	    			 survey_submit_to_result_obj.investigation_question_id=survey_submit__question.id;
	    			 survey_submit_to_result_obj.investigation_id=s_investigation_id;
	    			 survey_submit_to_result_obj.question_title=survey_submit__question.question_title;
	    			 survey_submit_to_result_obj.question_type=survey_submit__question.question_type;
	    			 survey_submit_to_result_obj.attempt="<?=$attempt?>";

	    			 survey_submit_to_result_obj.course_id=course_play_survey_course_id;
	    			 survey_submit_to_result_obj.course_reg_id=course_play_survey_course_reg_id;
	    			 survey_submit_to_result_obj.mod_id=course_play_survey_mod_id;
	    			 survey_submit_to_result_obj.mod_res_id=course_play_survey_mod_res_id;
	    			 survey_submit_to_result_obj.courseactivity_id=course_play_survey_courseactivity_id;
	    			 survey_submit_to_result_obj.component_id=course_play_survey_component_id;
	    			 survey_submit_to_result_obj.course_complete_id="<?=$courseCompleteFinalId?>";

//	    			 alert(survey_submit_to_result_obj.question_type);
	    			 if(survey_submit_to_result_obj.question_type=='2'){
	    				 survey_submit_to_result_obj.question_description=survey_submit__question.question_description;

						 if($.trim($("#"+survey_submit__question.id+"wendati").val())==""){
							 app.showMsg((quest_num)+'.'+survey_submit__question.question_title+"<?= Yii::t('frontend', '{value}_not_fill',['value'=>'']) ?>");
							 submit_flag=true;
							 break ;
						 }
						 quest_num++;
						 survey_submit_to_result_obj.option_title= $("#"+survey_submit__question.id+"wendati").val();
	    				 survey_submit_to_result_obj.option_result= survey_submit_to_result_obj.option_title;
						 survey_submit_to_result_obj_arr.push(survey_submit_to_result_obj);
		    		 }


	    			 if(survey_submit_to_result_obj.question_type=='0'){

	    				 if($.trim($("input[name='"+survey_submit__question.id+"danxuanti']:checked").val())==""){
							 app.showMsg((quest_num)+'.'+survey_submit__question.question_title+"<?= Yii::t('frontend', '{value}_not_choose',['value'=>'']) ?>");
							 submit_flag=true;
							 break ;
						 }
	    				 quest_num++;
	    				 survey_submit_to_result_obj.investigation_option_id=$("input[name='"+survey_submit__question.id+"danxuanti']:checked").val();
						 var option_result = $("input[name='"+survey_submit__question.id+"danxuanti']:checked").attr("result");
//						 alert(option_result);
						 survey_submit_to_result_obj.option_title=option_result;
						 survey_submit_to_result_obj.option_result=option_result;
	    				 survey_submit_to_result_obj_arr.push(survey_submit_to_result_obj);	
		    		 }


	    			 if(survey_submit_to_result_obj.question_type=='1'){
		    			 console.log("duoxuanti");
		    			 if($("input[name='"+survey_submit__question.id+"duoxuanti']:checked").length==0){
		    				 app.showMsg((quest_num)+'.'+survey_submit__question.question_title+"<?= Yii::t('frontend', '{value}_not_choose',['value'=>'']) ?>");
		    				 submit_flag=true;
		    				 break ;
			    		 }
		    			 quest_num++;
	    				 $("input[name='"+survey_submit__question.id+"duoxuanti']:checked").each(function() { //由于复选框一般选中的是多个,所以可以循环输出
	    					 
	    					 var survey_submit_to_result_obj_tmp_={};
	    					 survey_submit_to_result_obj.investigation_option_id=$(this).val();
							 var option_result = $(this).attr("result");
//							 alert(option_result);
							 survey_submit_to_result_obj.option_title=option_result;
							 survey_submit_to_result_obj.option_result=option_result;
	    					 console.log( survey_submit_to_result_obj.investigation_option_id);
	    					 survey_submit_to_result_obj_tmp_=_.clone(survey_submit_to_result_obj) ;
	    					 survey_submit_to_result_obj_arr.push(survey_submit_to_result_obj_tmp_);	
	    				  });
	    				
		    		 }


	    			 
		         }

		         if(submit_flag){

			         return ;
			     }

		         if(survey_submit_count==0){
		        	 survey_submit_count++;
		        	  $.ajax({
			          	   async: false,
			          	   type: "POST",
			          	   
			  			   url: "<?=Url::toRoute(['investigation/investigation-submit-result',])?>",
			  			   data:{param:survey_submit_to_result_obj_arr},
			  			   success: function(msg){
			  				 $("#survey_play_submit_a").hide();
							   if("<?=$isMobile?>" == "1"){
								   play_survey_res_completing();
								   play_survey_res_completeFinal();
							   }

				  			   if($("#btnNextModres").length==1){
				  				   // app.showMsg("你已完成此项调查,即将跳转到下一节.");
				  				  app.showMsg("<?= Yii::t('frontend', 'finish_investigation') ?>");
				  				   play_survey_res_completing();
				  				   play_survey_res_completeFinal();
				  				//  setTimeout(function (){
				  				//	$("#btnNextModres").trigger("click");
					  			//  }, 1000);
				  				    
					  				
					  		   }

				  			   if(($("#btnNextModres").length==0)&&$("#btnPreviousModres").length==1){
				  				 //   app.showMsg("你已完成此项调查,即将退出学习.");
				  				  app.showMsg("<?= Yii::t('frontend', 'finish_investigation') ?>");
				  				   play_survey_res_completing();
				  				   play_survey_res_completeFinal();
				  				 // setTimeout(function (){
					  			//	$("#btnExit").trigger("click");
				  				//  }, 1000);
					  		   }

				  			 if(($("#btnNextModres").length==0)&&$("#btnPreviousModres").length==0){
				  				   // app.showMsg("你已完成此项调查,即将退出学习.");
				  				    app.showMsg("<?= Yii::t('frontend', 'finish_investigation') ?>");
				  				   play_survey_res_completing();
				  				   play_survey_res_completeFinal();
				  				//  setTimeout(function (){
					  			//	$("#btnExit").trigger("click");
				  				//  }, 1000);
					  		   }

				  			 setTimeout(function (){
				  				reloadCatalog("<?=$componentCode?>","<?=$modResId?>", "");
					  				  }, 1000);
				  			
				  			   }});
		  			   
		        	  
			      }
		        
		       

		      }
	      
         $(function(){
        	 get_play_survey_result();

			 LoadiFramePlayer();
         });

		function LoadiFramePlayer(){
			openMenu();
			miniScreen();
			diffTemp();
		}

         function survey_play_load(){
        	  console.log("----------");
        	    $('.addNewChoice').removeClass('hide');

        	    var url_result_1="<?=Url::toRoute(['investigation/course-play-survey-result',])?>"+"?id=<?=$id?>&course_complete_id=<?=$courseCompleteProcessId?>&course_reg_id=<?=$course_reg_id?>&course_id=<?=$courseId?>&mod_id=<?=$mod_id?>&investigation_id=";
        	 	  
        	    $('#course_play_survey_result_id').empty();
 	            $('#course_play_survey_result_id').load(url_result_1,function (){
 					
 	               });

             }

         function init(){

        	 $.ajax({
            	   async: false,
    			   url: "<?=Url::toRoute(['investigation/get-survey',])?>",
    			   data: {id:'<?=$id?>'},
    			   success: function(msg){
  				//$("#start_at_pre_id").text(toYYYYMMDD(msg.result.start_at));
   				//$("#end_at_pre_id").text(toYYYYMMDD(msg.result.end_at));

   				$("#title_pre_id").text(msg.result.title);
   				$("#description_pre_id").text(msg.result.description);
   				 
          	  var lists_arrs=msg.result.question;
          	  survey_submit_get_obj=msg.result;
          	  var first_pagination_code=_.findIndex(lists_arrs, {question_type:'3'}); 

                var pre_page_data="";
                var next_page_data="";

                var div_id="";

                var preview_num_show=1;

  			   for(var i=0;i<lists_arrs.length;i++){

  				   var lists_tmp=lists_arrs[i];
  				   console.log(lists_tmp);
  				   if(i==0){
  					   var lists=[];
  					   lists.push({id:lists_tmp.id});
  					   div_id=lists_tmp.id;
  					   pre_page_data=lists_tmp.id;
  					   next_page_data=lists_tmp.id;
  					   //分页div块
  					   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
  					   $("#pre_first_survey_row").append(fenye_t_templ);
  				   }
  				   
  				   if(lists_tmp.question_type=='2'){
  					   var lists=[];
  					   lists_tmp.preview_num_show=preview_num_show;
  					   lists.push(lists_tmp);
  					   var edit_t7_templ=_.template($("#pre_t7").html(),{variable: 'data'})({datas:lists});
  				       $("#"+div_id).append(edit_t7_templ);
  				       preview_num_show++;
  				   }else if(lists_tmp.question_type=='3'){
  					  if(first_pagination_code==i){

  						  next_page_data=lists_tmp.id;
  						  //下步按钮
  						   var lists_p=[{id:pre_page_data,nid:next_page_data}];
  						   var next_page_t_templ=_.template($("#next_page_t").html(),{variable: 'data'})({datas:lists_p});
  						  
  						   $("#"+div_id).append(next_page_t_templ);

  						   var lists=[];
  						   lists.push({id:lists_tmp.id,hide:'hidden="hidden"'});
  						   div_id=lists_tmp.id;
  						 //分页div块
  						   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
  						   $("#pre_first_survey_row").append(fenye_t_templ);	 
  					  }else{
  						  if(lists_arrs.length!=3){
  							  var lists_p=[{id:pre_page_data,uid:next_page_data,pid:next_page_data,nid:lists_tmp.id}];
  							   pre_page_data=next_page_data;
  							   next_page_data=lists_tmp.id;
  							  //上下步按钮
  							 
  							   var pre_next_page_t_templ=_.template($("#pre_next_page_t").html(),{variable: 'data'})({datas:lists_p});
  							   
  							  
  							   $("#"+div_id).append(pre_next_page_t_templ);
  							 
  							   var lists=[];
  							   lists.push({id:lists_tmp.id,hide:'hidden="hidden"'});
  							   div_id=lists_tmp.id;
  							 //分页div块
  							   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
  							   $("#pre_first_survey_row").append(fenye_t_templ);

  						  }
  						  
  					  }
  				   }else if(lists_tmp.question_type=='1'){
  					   
  					   var lists=[];
  					   lists_tmp.preview_num_show=preview_num_show;
  					   lists.push(lists_tmp);
  					   var edit_t8_templ=_.template($("#pre_t8").html(),{variable: 'data'})({datas:lists});
  					   $("#"+div_id).append(edit_t8_templ);
  					   preview_num_show++;
  				   }else if(lists_tmp.question_type=='0'){
  					  
  					   var lists=[];
  					   lists_tmp.preview_num_show=preview_num_show;
  					   lists.push(lists_tmp);
  					   var edit_t6_templ=_.template($("#pre_t6").html(),{variable: 'data'})({datas:lists});
  					   $("#"+div_id).append(edit_t6_templ);
  					   preview_num_show++;
  				   }

  				   if(i==lists_arrs.length-1&&i!=0&&first_pagination_code!=-1){
  					   //上步按钮
  					   var lists_p=[{id:pre_page_data,pid:next_page_data}];
  					   var pre_page_t_templ=_.template($("#pre_page_t").html(),{variable: 'data'})({datas:lists_p});
  						  
  					   $("#"+div_id).append(pre_page_t_templ);
  					   
  				   }
  				   <?php
  					if ($mode != 'preview'){
  					?>
  				   if(i==(lists_arrs.length-1)){

  					   console.log("<?= Yii::t('frontend', 'exam_submit') ?>");
  					   var survey_submit_btn_id=[1];
  					   var survey_submit_btn_id_templ=_.template($("#survey_submit_btn_id").html(),{variable: 'data'})({datas:survey_submit_btn_id});
  					
  					   
  					   $("#"+div_id).append(survey_submit_btn_id_templ);
  				   }
  				   <?php
  				   }
  				   ?>
  			  }


  			  
  			   //get_play_survey_result();

    			  }
  			 });   	  
  			  

             }


        

         function pre_page_fun(id,pid){

             $("#"+pid).hide();
             $("#"+id).show();
         }

         function next_page_fun(id,nid){

        	 $("#"+id).hide();
        	 $("#"+nid).show();

         }

       
        
     
         </script>   
        
   
   
    <script id="pre_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label"><%=item.preview_num_show%>.[<?=Yii::t('common', 'question_radio')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                       
	                      </div>
                     </div>
                     <div class="col-md-12 col-sm-12">
 <%_.each(item.options, function(item_option) {%>
                       <div class="options" style="display: inline-block; float: left; text-indent: 8px;">
                          <label style="margin-right:40px;">
                            <input type="radio" <%=item_option.isCheck%> name="<%=item.id%>danxuanti" value="<%=item_option.kid%>" result="<%=item_option.option_title%>"> <%=item_option.option_title%>
                          </label>
                        </div>
 <%});%>
                       
                        
                      </div>
        </div>
  <%});%>
    </script>
    
<script id="pre_t8" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label"><%=item.preview_num_show%>.[<?=Yii::t('common', 'question_checkbox')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                        
	                      </div>
                     </div>
                     <div class="col-md-12 col-sm-12">
 <%_.each(item.options, function(item_option) {%>
                       <div class="options" style="display: inline-block; float: left; text-indent: 8px;">
                          <label style="margin-right:40px;">
                            <input type="checkbox" <%=item_option.isCheck%> name="<%=item.id%>duoxuanti" value="<%=item_option.kid%>" result="<%=item_option.option_title%>"> <%=item_option.option_title%>
                          </label>
                        </div>
 <%});%>
                       
                        
                      </div>
        </div>
  <%});%>
    </script>
  
 <script id="pre_t7" type="text/template">
  <%_.each(data.datas, function(item) {%>
	<div class="row questionGroup_quest">
       <div class="col-md-12 col-sm-12">
   		   <div class="form-group form-group-sm">
   		   <label class="col-sm-9 control-label"><%=item.preview_num_show%>. <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
   		     
   		   </div>
   		   </div>
   	    	<div class="col-md-12 col-sm-12">
            <%=item.question_description%>
   	 	    <textarea id="<%=item.id%>wendati"><%=item.option_result%></textarea>
   	  </div>
   	</div>
      <%});%>
    </script>  
    
    <script id="fenye_t" type="text/template">
  <%_.each(data.datas, function(item) {%>
	<div id="<%=item.id%>" <%=item.hide%> >
      
   	</div>
      <%});%>
    </script>  
    
    <script id="next_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">
                      <a  class="btn btn-sm btn-success pull-right" onClick="next_page_fun('<%=item.id%>','<%=item.nid%>')"><?= Yii::t('frontend', 'next_step') ?></a>
                     </div>   
 <%});%>
    </script> 
    
     
    
     <script id="pre_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">               
                      <a  class="btn btn-sm btn-success pull-left" onClick="pre_page_fun('<%=item.id%>','<%=item.pid%>')"><?= Yii::t('frontend', 'last_step') ?></a>
                     </div>  
 <%});%>
    </script>         
    
     <script id="pre_next_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">                
                      <a  class="btn btn-sm btn-success pull-left" onClick="pre_page_fun('<%=item.id%>','<%=item.pid%>')"><?= Yii::t('frontend', 'last_step') ?></a>
                      <a  class="btn btn-sm btn-success pull-right" onClick="next_page_fun('<%=item.uid%>','<%=item.nid%>')"><?= Yii::t('frontend', 'next_step') ?></a>
                    </div>  
 <%});%>
    </script> 
             
    <script id="survey_submit_btn_id" type="text/template"> 
<%_.each(data.datas, function(item) {%> 
<div class="row">
    <div class="col-md-12 col-sm-12 centerBtnArea">
     <a onClick="survey_play_submit()" id="survey_play_submit_a" class="btn btn-info btn-sm centerBtn" style="width:30%" data-dismiss="modal" aria-label="Close"><?= Yii::t('frontend', 'exam_submit') ?></a>
   <!--   <a onClick="survey_play_load()" class="btn btn-default btn-sm centerBtn btnaddNewChoice" style="width:30%"><?=Yii::t('frontend', 'view_result')?></a>   -->
 </div>
</div>
 <%});%>
 </script>          