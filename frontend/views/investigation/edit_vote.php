<?php
use yii\helpers\Url;

?>



      <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'modify_investigation') ?>(<?= Yii::t('frontend', 'vote') ?>)</h4>
       </div>
      <div class="content">
       <div class="courseInfo">
        <div role="tabpanel" class="tab-pane active" id="teacher_info">
         <div class=" panel-default scoreList">
           <div class="panel-body">
           <form id="edit_vote_form_id">
                  <input type="hidden" id="e_vote_investigation_id" value="<?=$id?>">
                  <input type="hidden" id="e_vote_investigation_question_id" value="">
                  <div class="infoBlock" id="infoBlock_content_id">
                    <div class="row" >
	                    <div class="col-md-12 col-sm-12">
	                    		<div class="form-group form-group-sm">
	                    		<label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>
	                    		<div class="col-sm-9">
	                    		<input data-delay="1" data-mode="COMMON" data-condition="^(?!\s)(?!.*?\s$).{1,250}$" data-alert="<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'question_title')])?>" class="form-control pull-left" name="question_title" type="text" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
	                    		</div>
	                            </div>
	                    </div>
                    </div>
                    <!-- 选项 -->
                    <div id="edit_first_row"></div>
                    
                   <div class="row">
                     <div class="col-md-12 col-sm-12 centerBtnArea">
                     <a  class="btn btn-default btn-sm centerBtn" id="editOptionId" style="width:30%"><?=Yii::t('frontend', 'add_option')?></a>
                      </div>
                    </div>
                    <hr/>
                     <div class="infoBlock">
                     
                       <div class="row">
                        <div class="col-md-12 col-sm-12">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'investigation_range_') ?></label>
                            <div class="col-sm-9">
                              <div class="col-sm-12">
                                <div class="btn-group" data-toggle="buttons">
                                  <label style="margin-right:40px;">
                                    <input type="radio" name="investigation_range" value="0" /> <?= Yii::t('frontend', 'independent_call') ?>
                                  </label>
                                  <label>
                                    <input type="radio" name="investigation_range" value="1" /> <?= Yii::t('frontend', 'course_call') ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div> 
                      
                      <div class="row">
                        <div class="col-md-12 col-sm-12">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'course_assessment') ?></label>
                            <div class="col-sm-9">
                              <div class="col-sm-12">
                                <div class="btn-group" data-toggle="buttons">
                                  <label style="margin-right:40px;">
                                    <input type="radio" name="is_estimate" value="1" /> <?= Yii::t('frontend', 'yes') ?>
                                  </label>
                                  <label>
                                    <input type="radio" name="is_estimate" value="0" checked="checked"/> <?= Yii::t('frontend', 'no') ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div> 
                     
                       <div class="row">
                          <div class="col-md-12 col-sm-12">
                           <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'time_validity') ?></label>
                            <div class="col-sm-9">
                              <input id="edit_vote_start_at" readonly="readonly" data-mode="COMMON"  data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'action_start_at')])?>" class="form-control pull-left" name="start_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%; margin-right:6%;">
                              <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('common', 'to2')?></span>
                              <input id="edit_vote_end_at"  readonly="readonly" data-mode="COMMON"  data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')])?>" class="form-control pull-left" name="end_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%;margin-left:6% ;margin-right:6%;">
         				    </div>
         				   </div>
         				  </div>
         			   </div>
         			   <!--  
         			   <div class="row">
         				<div class="col-md-12 col-sm-12">
	         				<div class="form-group form-group-sm">
	         				 <label class="col-sm-3 control-label">受众</label>
	         				 <div class="col-sm-9">
	         				  <input class="form-control pull-left" type="text" id="formGroupInputSmall" placeholder="" style="width:80%">
	         					 <a href="#" class="btn btn-default btn-sm pull-right">选择</a>
	                         </div>
	                        </div>
                        </div>
                       </div>
                       -->
                       
                    
                       
                      <div class="row">
      					<div class="col-md-12 col-sm-12">
      					  <div class="form-group form-group-sm">
      					   <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'vote_type') ?></label>
      					   <div class="col-sm-9">
      					   <div class="col-sm-9">
      					    <div class="btn-group" data-toggle="buttons">
      					     <label style="margin-right:40px;">
      					        <input type="radio" name="answer_type" value="0" /> <?= Yii::t('frontend', 'vote_real_name') ?>
      					      </label>
      					     <label>
      					       <input type="radio" name="answer_type" value="1" />  <?= Yii::t('frontend', 'vote_private_name') ?>
      					     </label>
      					    </div>
      					   </div>
      					  </div>
      					 </div>
                        </div>
                     </div>
           			 <div class="row">
           				<div class="col-md-12 col-sm-12">
           					<div class="form-group form-group-sm">
           							<label class="col-sm-3 control-label"><?=Yii::t('frontend', 'radio_or_checkbox')?></label>
           							<div class="col-sm-9">
           							  <div class="col-sm-9">
           							    <div class="btn-group" data-toggle="buttons">
           							       <label style="margin-right:68px;">
           									<input type="radio" name="question_type" value="0"/> <?=Yii::t('common', 'question_radio')?>
                     		                </label>
                         	                <label>
                         			        <input type="radio" name="question_type" value="1" /> <?=Yii::t('common', 'question_checkbox')?>
                         			        </label>
                         			    </div>
                         			  </div>
                         		    </div>
                              </div>
                          </div>
                      </div>
                      <div class="row">
                     	<div class="col-md-12 col-sm-12 centerBtnArea">
                     	<a  class="btn btn-default btn-sm centerBtn" id="edit_vote_preview" style="width:20%"><?= Yii::t('common', 'preview_button') ?></a>
                     	<a  class="btn btn-success btn-sm centerBtn" style="width:20%; margin:10px 15px;" id="edit_vote_pub"><?= Yii::t('frontend', 'modify') ?></a>
                     
                     	</div>
                      </div>
                    </div>
              </div>
              </form>
                     	</div>
                     	</div>
                     	</div>
                     	</div>
                     	
                     	<div class="c"></div>
                     	</div>

                     	
                     	
    <script type="text/javascript">
    app.genCalendar();
    $(function(){

    	 window.validation_edit_vote =app.creatFormValidation($("#edit_vote_form_id")); 

    	 $("#edit_vote_start_at").attr("disabled","disabled");
         $("#edit_vote_end_at").attr("disabled","disabled");


         $("#edit_vote_form_id input[name='investigation_range']").click(function(){
         	console.log( $("#edit_vote_form_id input[name='investigation_range']:checked").val());
         	var is_investigation_range_check=$("#edit_vote_form_id input[name='investigation_range']:checked").val();
         	    if(is_investigation_range_check=='0'){
         	    	
         		    $("#edit_vote_start_at").removeAttr("disabled");
                     $("#edit_vote_end_at").removeAttr("disabled");
                     
                     $("#edit_vote_start_at").attr("data-condition","required");
                     $("#edit_vote_end_at").attr("data-condition","required");
             	}else{
             		 $("#edit_vote_start_at").attr("disabled","disabled");
                      $("#edit_vote_end_at").attr("disabled","disabled");
                      $("#edit_vote_start_at").val('');
                      $("#edit_vote_end_at").val('');

          		     $("#edit_vote_start_at").removeAttr("data-condition");
                      $("#edit_vote_end_at").removeAttr("data-condition");


                      validation_edit_vote.hideAlert("#edit_vote_start_at");
                      validation_edit_vote.hideAlert("#edit_vote_end_at");
                 }
           });
        

    	 $("#formGroupInputSmall").mouseout(function(){
    		
    		      		$("#formGroupInputSmall").trigger("blur");
         });


    	
    	 
          // 
          var kid='<?=$id?>';
         
          $.ajax({
        	   async: false,
			   url: "<?=Url::toRoute(['investigation/get-vote',])?>",
			   data: {id:kid},
			   success: function(msg){
				   
				   $("#e_vote_investigation_question_id").val(msg.result.investigation_question_id);
				 
				   $("#edit_vote_form_id input[name='start_at']").val(msg.result.start_at);
				   $("#edit_vote_form_id input[name='end_at']").val(msg.result.end_at);
				   $("#edit_vote_form_id input[name='question_title']").val(msg.result.question_title);
				   //var lists=msg.result.options;
				 
				   var lists_no_delete=[];
				   var lists=[];
				   for(var i=0;i<msg.result.options.length;i++){

					   if(i==0||i==1){
						   lists_no_delete.push(msg.result.options[i]);
					   }else{
						   lists.push(msg.result.options[i]);
					   }
				   }
				   
				   if(msg.result.question_type=='0'){
					   $("#edit_vote_form_id input[name='question_type']").get(0).checked = true;
				   }else{
					   $("#edit_vote_form_id input[name='question_type']").get(1).checked = true;
				   }

				   if(msg.result.answer_type=='0'){
					   $("#edit_vote_form_id input[name='answer_type']").get(0).checked = true;
				   }else{
					   $("#edit_vote_form_id input[name='answer_type']").get(1).checked = true;
				   }

				   if(msg.result.is_estimate=='1'){
					   $("#edit_vote_form_id input[name='is_estimate']").get(0).checked = true;
				   }else{
					   $("#edit_vote_form_id input[name='is_estimate']").get(1).checked = true;
				   }

				   if(msg.result.investigation_range=='0'){
					   $("#edit_vote_form_id input[name='investigation_range']").get(0).checked = true;
					     $("#edit_vote_start_at").removeAttr("disabled"); 
				         $("#edit_vote_end_at").removeAttr("disabled");
				   }else{
					   $("#edit_vote_form_id input[name='investigation_range']").get(1).checked = true;
				   }

				   validateInvestgationTitle('formGroupInputSmall','1','<?=Yii::t('frontend', '{value}_repeat',['value'=>Yii::t('frontend', 'vote_title')])?>','<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'vote_title')])?>','<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'vote_title')])?>',validation_edit_vote,msg.result.question_title);
				  
				   
			       var t2_templ_no_delete=_.template($("#t2_edit_no_delete").html(),{variable: 'data'})({datas:lists_no_delete});
				   var t2_templ=_.template($("#t2_edit").html(),{variable: 'data'})({datas:lists});
			       $("#edit_first_row").append(t2_templ_no_delete);
			       $("#edit_first_row").append(t2_templ);
			   }
			 });


  		  $("#editOptionId").click(function(){
  	  		 
  			 var lists2=[{option_title:''}];
  			 var t2_templ2=_.template($("#t2_edit").html(),{variable: 'data'})({datas:lists2});
  			 $("#edit_first_row").append(t2_templ2);
  	  	   }); 


  		

  	   
  		  $("#edit_vote_pub").click(function(){
  	  		  
  			
			  
  			edit_new_vote("<?=Url::toRoute(['investigation/edit-vote',])?>");
  			
  			//
  	  	   }); 


  		 $("#edit_vote_preview").click(function(){
             
        	   var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/vote-preview'])?>";

        	  
        	   window.pre_new_vote_datas=load_edit_vote_object();
        	   pre_new_vote_datas.votemotaiid='edit_vote';

        	   if(pre_new_vote_datas.options.length<2){
      		     
        		   app.showMsg("<?=Yii::t('frontend', 'more_than_2_option')?>");
      		     //app.alertSmall("#foo");	   
      		     return;
      		   }

        	   if(!compareDate(pre_new_vote_datas.start_at,pre_new_vote_datas.end_at)){
  		    	 return;
  			   }

        	   if(!validation_edit_vote.validate()){
        		   // $("#msm_alert_content").text("验证未通过");
        		   // app.alert("#foo");	 
                   
                   return;
      		   };
      		 $("#new_vote_preview_view").empty();
             $("#new_vote_read_preview_view").empty();
        	   FmodalLoadData("new_vote_preview_view", addChoiceUrl,pre_new_vote_datas);
        	  // $("#new_survey").modal('hide');

            });

  		
      	 //end
    }); 


    function deleteEditOption(node){
 	   
        $(node).parent().parent().parent().parent().remove();

    }

    function load_edit_vote_object(){

         var v_is_estimate_val=$("#edit_vote_form_id input[name='is_estimate']:checked").val();
   	 
    	 var v_question_type_val=$("#edit_vote_form_id input[name='question_type']:checked").val();
		 var v_answer_type_val=$("#edit_vote_form_id input[name='answer_type']:checked").val();
	     var v_question_title_val=app.clean($("#edit_vote_form_id input[name='question_title']").val());
	     var v_option_title_arrs=$("#edit_vote_form_id input[name='option_title']");
	     var v_start_at_val=$("#edit_vote_form_id input[name='start_at']").val();
	     var v_end_at_val=$("#edit_vote_form_id input[name='end_at']").val(); 

	    

	     var vote_obj={};

	     vote_obj.is_estimate=v_is_estimate_val;		    
	     vote_obj.question_type=v_question_type_val;
	     vote_obj.answer_type=v_answer_type_val;
	     vote_obj.question_title=v_question_title_val;
	     vote_obj.options=[];
	     vote_obj.start_at=v_start_at_val;
	     vote_obj.end_at=v_end_at_val;
	     vote_obj.id=$("#e_vote_investigation_id").val();
	     vote_obj.invest_quest_id=$("#e_vote_investigation_question_id").val();

	     for(var i=0;i<v_option_title_arrs.length;i++){
	    	vote_obj.options.push({option_title:app.clean(v_option_title_arrs[i].value)});
         }

	    

	     return  vote_obj;  
    }

    function edit_new_vote(url){

    		 var v_is_estimate_val=$("#edit_vote_form_id input[name='is_estimate']:checked").val();
       	 
    	     var v_question_type_val=$("#edit_vote_form_id input[name='question_type']:checked").val();
			 var v_answer_type_val=$("#edit_vote_form_id input[name='answer_type']:checked").val();
		     var v_question_title_val=$("#edit_vote_form_id input[name='question_title']").val();
		     var v_option_title_arrs=$("#edit_vote_form_id input[name='option_title']");
		     var v_start_at_val=$("#edit_vote_form_id input[name='start_at']").val();
		     var v_end_at_val=$("#edit_vote_form_id input[name='end_at']").val(); 

		     var v_investigation_range_val=$("#edit_vote_form_id input[name='investigation_range']:checked").val();


		     if(v_option_title_arrs.length<2){
			     
		    	 app.showMsg("<?=Yii::t('frontend', 'more_than_2_option')?>");
			    // app.alertSmall("#foo");	 
			     return;
			 }

		     if(!compareDate(v_start_at_val,v_end_at_val)){
		    	 return;
			 }	

		     var vote_obj={};

		     vote_obj.is_estimate=v_is_estimate_val;	
		     vote_obj.question_type=v_question_type_val;

		     vote_obj.investigation_range=v_investigation_range_val;
		     vote_obj.answer_type=v_answer_type_val;
		     vote_obj.question_title=v_question_title_val;
		     vote_obj.options=[];
		     vote_obj.start_at=v_start_at_val;
		     vote_obj.end_at=v_end_at_val;
		     vote_obj.id=$("#e_vote_investigation_id").val();
		     vote_obj.invest_quest_id=$("#e_vote_investigation_question_id").val();

		     for(var i=0;i<v_option_title_arrs.length;i++){
		    	vote_obj.options.push({option_title:v_option_title_arrs[i].value});
	         }


		     if(!validation_edit_vote.validate()){

	             
	            // $("#msm_alert_content").text("验证未通过");
	           //  app.alert("#foo");	 
	             return;
			  };

		    $.ajax({
			   type: "POST",
			   url: url,
			   data: vote_obj,
			   success: function(msg){
				 loadList();
				 //$("#edit_vote").modal('hide');
				  app.hideAlert("#edit_vote")
			   }
			 });

    }
    
    </script>     
   
   
    <script id="t2_edit_no_delete" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row">
                       <div class="col-md-12 col-sm-12">
                         <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?></label>
                            <div class="col-sm-9">
                            <input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option')])?>" class="form-control pull-left" value="<%=item.option_title%>" name="option_title" type="text" id="formGroupInputSmall123" placeholder="<?=Yii::t('frontend', 'input_content')?>" style="width:80%">
                          
                            </div>
                          </div>
                      </div>
       </div>
  <%});%>
    </script>                                	
                     	
   <script id="t2_edit" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row">
                       <div class="col-md-12 col-sm-12">
                         <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?></label>
                            <div class="col-sm-9">
                            <input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option')])?>" class="form-control pull-left" value="<%=item.option_title%>" name="option_title" type="text" id="formGroupInputSmall123" placeholder="<?=Yii::t('frontend', 'input_content')?>" style="width:80%">
                            <a  onclick="deleteEditOption(this)" class="btn btn-default btn-sm glyphicon glyphicon-remove deleteBtn"></a>
                            </div>
                          </div>
                      </div>
       </div>
  <%});%>
    </script>                   	