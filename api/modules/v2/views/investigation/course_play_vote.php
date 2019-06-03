
<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

// deprecated by GROOT at 2016.04.22
?>
<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>

<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>



          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                
                <div  class="blockScreen hide">
                        <span class="glyphicon glyphicon-ok"></span>
                        <p>您已完成此项调查</p>
                     </div>
                     
                  <div class="infoBlock">
                    <div class="row questionGroup_quest hide">
                     
                      
                      
                    </div>
                     <div id="pre_pre_first_vote_row"></div>

                     <div class="row" id="vote_btn_div">
                         
                     </div>
                  
                     
                     <div id="course_play_vote_result_id" class="col-md-12 col-sm-12 addNewChoice hide">
                          
                        </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      
        
     <script type="text/javascript">


     function play_res_completeFinal(){
    	 $.get("<?=Url::toRoute(['investigation/play-investigation-res-complete',])?>"+"?system_key=<?= $system_key ?>&access_token=<?= $access_token ?>&course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteFinalId?>"+"&complete_type=1&course_reg_id="+course_play_vote_course_reg_id,function(data){

    		
        	 });
         }

     function play_res_completing(){
    	 $.get("<?=Url::toRoute(['investigation/play-investigation-res-complete',])?>"+"?system_key=<?= $system_key ?>&access_token=<?= $access_token ?>&course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteProcessId?>"+"&complete_type=0&course_reg_id="+course_play_vote_course_reg_id,function(data){

    		
        	 });
         }

     function get_play_result(){

    	 $.get("<?=Url::toRoute(['investigation/get-play-investigation-submit-result',])?>"+"?system_key=<?= $system_key ?>&access_token=<?= $access_token ?>&course_complete_id=<?=$courseCompleteProcessId?>&course_reg_id=<?=$course_reg_id?>&course_id=<?=$courseId?>&mod_id=<?=$mod_id?>&investigation_id=<?=$id?>",function(data){

    		 if(data.data.result=='yes'){
        		 get_play_result_show();
        	 }else{
        		 init();
            	 }

        	 });
     }

     function get_play_result_show(){

    	 $.get("<?=Url::toRoute(['investigation/get-sub-vote-result',])?>"+"?system_key=<?= $system_key ?>&access_token=<?= $access_token ?>&id=<?=$id?>&course_complete_id=<?=$courseCompleteProcessId?>&course_reg_id=<?=$course_reg_id?>&course_id=<?=$courseId?>&mod_id=<?=$mod_id?>&investigation_id=<?=$id?>",function(msg){
    		 $("#pre_pre_first_vote_row").empty();
    		 var pre_vote_obj=msg.data.result;

			 vote_submit_get_obj=msg.data.result;


			if(pre_vote_obj.question_type=='0'){

	        	   var lists=[];
				   lists.push(pre_vote_obj);
				   var edit_t6_templ=_.template($("#pre_pre_vote_t6").html(),{variable: 'data'})({datas:lists});
				   $("#pre_pre_first_vote_row").append(edit_t6_templ);
	        }else{

	        	   var lists=[];
				   lists.push(pre_vote_obj);
				   var edit_t8_templ=_.template($("#pre_pre_vote_t8").html(),{variable: 'data'})({datas:lists});
				   $("#pre_pre_first_vote_row").append(edit_t8_templ);
	        }

			   var vote_submit_btn_id=[1];
			   var vote_submit_btn_id_templ=_.template($("#vote_submit_btn_id").html(),{variable: 'data'})({datas:vote_submit_btn_id});
			
			   
			   $("#vote_btn_div").append(vote_submit_btn_id_templ);	
			   $("#vote_play_submit_a").hide();

        	 });
     }

     
     function toYYYYMMDD(val_date){
    	 var ooo=val_date.replace('-','年');
			ooo=ooo.replace('-','月');
			ooo=ooo+"日";
			return ooo;
      }

      var vote_submit_get_obj;
      var vote_submit_count=0;
      var course_play_vote_course_id="<?=$courseId?>";
      var course_play_vote_course_reg_id="<?=$course_reg_id?>";
      var course_play_vote_mod_id="<?=$mod_id?>";
      var course_play_vote_mod_res_id="<?=$modResId?>";
      var course_play_vote_courseactivity_id="<?=$courseactivity_id?>";
      var course_play_vote_component_id="<?=$component_id?>";
      
      function vote_play_submit(){
    		 var vote_submit_to_result_obj_arr=[];
    		 
    		 var v_investigation_id=vote_submit_get_obj.id;

    		 var vote_flag=false;
    		
    		
    			 var vote_submit_to_result_obj={};
    			 
    			 var vote_submit__question= vote_submit_get_obj;
    			 vote_submit_to_result_obj.investigation_question_id=vote_submit__question.investigation_question_id;
    			 vote_submit_to_result_obj.investigation_id=v_investigation_id;
    			 vote_submit_to_result_obj.question_title=vote_submit__question.question_title;
    			 vote_submit_to_result_obj.question_type=vote_submit__question.question_type;

    			 vote_submit_to_result_obj.course_id=course_play_vote_course_id;
    			 vote_submit_to_result_obj.course_reg_id=course_play_vote_course_reg_id;
    			 vote_submit_to_result_obj.mod_id=course_play_vote_mod_id;
    			 vote_submit_to_result_obj.mod_res_id=course_play_vote_mod_res_id;
    			 vote_submit_to_result_obj.courseactivity_id=course_play_vote_courseactivity_id;
    			 vote_submit_to_result_obj.component_id=course_play_vote_component_id;
    			 vote_submit_to_result_obj.course_complete_id="<?=$courseCompleteProcessId?>";
    			
    			 vote_submit_to_result_obj.attempt="<?=$attempt?>";


    			 if(vote_submit_to_result_obj.question_type=='0'){

    				 if($.trim($("input[name='"+vote_submit__question.id+"danxuanti']:checked").val())==""){
						 app.showMsg(vote_submit__question.question_title+"项未选择");
						 vote_flag=true;
						
					 }
    				 vote_submit_to_result_obj.investigation_option_id=$("input[name='"+vote_submit__question.id+"danxuanti']:checked").val();
//					 alert($("input[name='"+vote_submit__question.id+"danxuanti']:checked").val());
					 var option_result = $("input[name='"+vote_submit__question.id+"danxuanti']:checked").attr("result");
//					 alert(option_result);
					 vote_submit_to_result_obj.option_title=option_result;
					 vote_submit_to_result_obj.option_result=option_result;
    				 vote_submit_to_result_obj_arr.push(vote_submit_to_result_obj);	
	    		 }


    			 if(vote_submit_to_result_obj.question_type=='1'){
	    			 console.log("duoxuanti");
	    			 if($("input[name='"+vote_submit__question.id+"duoxuanti']:checked").length==0){
	    				 app.showMsg(vote_submit__question.question_title+"项未选择");
	    				 vote_flag=true;
	    				
		    		 }
    				 $("input[name='"+vote_submit__question.id+"duoxuanti']:checked").each(function() { //由于复选框一般选中的是多个,所以可以循环输出

    					 var vote_submit_to_result_obj_tmp_={};
    					 vote_submit_to_result_obj.investigation_option_id=$(this).val();
						 var option_result = $(this).attr("result");
						 vote_submit_to_result_obj.option_title=option_result;
						 vote_submit_to_result_obj.option_result=option_result;

    					 vote_submit_to_result_obj_tmp_=_.clone(vote_submit_to_result_obj) ;
    					 vote_submit_to_result_obj_arr.push(vote_submit_to_result_obj_tmp_);	

    					
    					
    				  });
    				
	    		 }


    			 
	        

	         if(vote_flag){

		         return ;
		     }

	         if(vote_submit_count==0){
	        	 vote_submit_count++;
	        	  $.ajax({
		          	   async: false,
		          	   type: "POST",
		          	   
		  			   url: "<?=Url::toRoute(['investigation/investigation-submit-result','system_key'=>$system_key,'access_token'=>$access_token])?>",
		  			   data:{param:vote_submit_to_result_obj_arr},
		  			   success: function(msg){

		  				 $("#vote_play_submit_a").hide();
			  			   if($("#btnNextModres").length==1){
			  				//    app.showMsg("你已完成此项调查,即将跳转到下一节.");
			  				app.showMsg("你已完成此项调查");
			  				   play_res_completing();
			  				   play_res_completeFinal();
			  				//  setTimeout(function (){
			  				//	$("#btnNextModres").trigger("click");
				  			//  }, 1000);
			  				    
				  				
				  		   }

			  			   if(($("#btnNextModres").length==0)&&$("#btnPreviousModres").length==1){
			  				  //  app.showMsg("你已完成此项调查,即将退出学习.");
			  				  app.showMsg("你已完成此项调查");
			  				   play_res_completing();
			  				   play_res_completeFinal();
			  				//  setTimeout(function (){
				  			//	$("#btnExit").trigger("click");
			  				//  }, 1000);
				  		   }


			  			 if(($("#btnNextModres").length==0)&&$("#btnPreviousModres").length==0){
			  				   // app.showMsg("你已完成此项调查,即将退出学习.");
			  				  app.showMsg("你已完成此项调查");
			  				   play_res_completing();
			  				   play_res_completeFinal();
			  				 // setTimeout(function (){
				  				//$("#btnExit").trigger("click");
			  				  //}, 1000);
				  		   }
			  			reloadCatalog("<?=$componentCode?>","<?=$modResId?>", "");
		  				
			  			   }});
	  			   
	        	  
		      }
	    
	      }

      function init(){

    	  $.ajax({
          	   async: false,
   			   url: "<?=Url::toRoute(['investigation/get-vote','system_key'=>$system_key,'access_token'=>$access_token])?>",
   			   data: {id:'<?=$id?>'},
   			   success: function(msg){

   				var pre_vote_obj=msg.data.result;

   				 vote_submit_get_obj=msg.data.result;
   				if(pre_vote_obj.question_type=='0'){
   		        	   var lists=[];
   					   lists.push(pre_vote_obj);
   					   var edit_t6_templ=_.template($("#pre_pre_vote_t6").html(),{variable: 'data'})({datas:lists});
   					   $("#pre_pre_first_vote_row").append(edit_t6_templ);
   		        }else{
   		        	   var lists=[];
   					   lists.push(pre_vote_obj);
   					   var edit_t8_templ=_.template($("#pre_pre_vote_t8").html(),{variable: 'data'})({datas:lists});
   					   $("#pre_pre_first_vote_row").append(edit_t8_templ);
   		        }

   			       var vote_submit_btn_id=[1];
				   var vote_submit_btn_id_templ=_.template($("#vote_submit_btn_id").html(),{variable: 'data'})({datas:vote_submit_btn_id});
				
				   
				   $("#vote_btn_div").append(vote_submit_btn_id_templ);

   				//get_play_result();

   				
   					  
   			   }
   			 });
          

          }

     $(function(){

    	 get_play_result();
    	 
    	
    	  

       
      });

     function vote_result_load(){

    	 $('.addNewChoice').removeClass('hide');

	  	    $('#course_play_vote_result_id').empty();
	  	    var url_result="<?=Url::toRoute(['investigation/course-play-vote-result',])?>"+"?system_key=<?= $system_key ?>&access_token=<?= $access_token ?>&id=<?=$id?>&course_complete_id=<?=$courseCompleteProcessId?>&course_reg_id=<?=$course_reg_id?>&course_id=<?=$courseId?>&mod_id=<?=$mod_id?>&investigation_id=";
	  	    	
	         $('#course_play_vote_result_id').load(url_result,function (){
	 				
	         });

         }


    
     
     </script>   
     
   
  
    <script id="pre_pre_vote_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[单选] 问题:<%=item.question_title%></label>
	                       <div class="col-sm-1">
                            </div>
	                      </div>
                     </div>
          </div>           
 <%_.each(item.options, function(item_option) {%>
  <div class="row">
                       <div class="col-md-12 col-sm-12">
                         <div class="form-group form-group-sm">
                            <div class="col-sm-1 control-label">
                             <label style="margin-right:40px;">
                              <input type="radio" <%=item_option.isCheck%> name="<%=item.id%>danxuanti" value="<%=item_option.kid%>" result="<%=item_option.option_title%>">
                             </label>
                             </div>

 						      <div class="col-sm-11">
                                <%=item_option.option_title%>
                              </div>
                        </div>
                       </div>
    </div>
 <%});%>
              
       
  <%});%>
    </script>
    
<script id="pre_pre_vote_t8" type="text/template">
  <%_.each(data.datas, function(item) {%>
       <div class="row">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[多选] 问题:<%=item.question_title%></label>
	                       <div class="col-sm-1">
                            </div>
	                      </div>
                     </div>
          </div>           
 <%_.each(item.options, function(item_option) {%>
  <div class="row">
                       <div class="col-md-12 col-sm-12">
                         <div class="form-group form-group-sm">
                            <div class="col-sm-1 control-label">
                             <label style="margin-right:40px;">
                              <input type="checkbox" <%=item_option.isCheck%> name="<%=item.id%>duoxuanti" value="<%=item_option.kid%>" result="<%=item_option.option_title%>">
                             </label>
                             </div>

 						      <div class="col-sm-11">
                                <%=item_option.option_title%>
                              </div>
                        </div>
                       </div>
    </div>
 <%});%>
              
       
  <%});%>
    </script>
    
     <script id="vote_submit_btn_id" type="text/template"> 
<%_.each(data.datas, function(item) {%> 
 <div class="col-md-12 col-sm-12 centerBtnArea">							
							  <a onClick="vote_play_submit()" id="vote_play_submit_a" class="btn btn-info btn-sm centerBtn" style="width:30%">提交</a>
			<!--			<a onClick="vote_result_load()"  class="btn btn-default btn-sm centerBtn btnaddNewChoice" style="width:30%">查看结果</a> -->
 </div>
 <%});%>
 </script>          
     