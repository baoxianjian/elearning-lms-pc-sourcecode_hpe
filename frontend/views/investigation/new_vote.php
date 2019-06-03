<?php
use yii\helpers\Url;

?>


    <!--div class="modal-dialog modal-md">
     <div class="modal-content">-->
      <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','build_investigation')?> (<?= Yii::t('frontend', 'vote') ?>)</h4>
       </div>
      <div class="content">
       <div class="courseInfo">
        <div role="tabpanel" class="tab-pane active" id="teacher_info">
         <div class=" panel-default scoreList">
           <div class="panel-body">
           <form id="new_vote_form_id">
                  <div class="infoBlock" id="infoBlock_content_id">
                    <div class="row" >
	                    <div class="col-md-12 col-sm-12">
	                    		<div class="form-group form-group-sm">
	                    		<label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>
	                    		<div class="col-sm-9">
	                    		<input  data-mode="COMMON" data-condition="^(?!\s)(?!.*?\s$).{1,250}$" data-delay="1" data-alert="<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'question_title')])?>" class="form-control pull-left" name="question_title" type="text" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
	                    		</div>
	                            </div>
	                    </div>
                    </div>
                    <!-- 选项 -->
                    <div id="first_row"></div>
                    
                    
                    
                   <div class="row">
                     <div class="col-md-12 col-sm-12 centerBtnArea">
                     <a  class="btn btn-default btn-sm centerBtn" id="addOptionId" style="width:30%"><?=Yii::t('frontend', 'add_option')?></a>
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
                                    <input type="radio" name="investigation_range" value="1" checked="checked"/> <?= Yii::t('frontend', 'course_call') ?>
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
                              <input readonly="readonly" data-mode="COMMON"  data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'action_start_at')])?>" class="form-control pull-left" id="new_vote_start_at" name="start_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%; margin-right:6%;">
                              <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('common', 'to2')?></span>
                              <input readonly="readonly" data-mode="COMMON"  data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')])?>" class="form-control pull-left" id="new_vote_end_at" name="end_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%;margin-left:6% ;margin-right:6%;">
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
      					        <input type="radio" name="answer_type" value="0" checked="checked"/> <?= Yii::t('frontend', 'vote_real_name') ?>
      					      </label>
      					     <label>
      					       <input type="radio" name="answer_type" value="1" /> <?= Yii::t('frontend', 'vote_private_name') ?>
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
           									<input type="radio" name="question_type" value="0" checked="checked"/> <?=Yii::t('common', 'question_radio')?>
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
                     	<a  class="btn btn-default btn-sm centerBtn" id="new_vote_preview" style="width:20%; background:#46c0f3"><?= Yii::t('common', 'preview_button') ?></a>
                     	<a  class="btn btn-success btn-sm centerBtn " style="width:20%; margin:10px 15px;" id="new_vote_pub"><?= Yii::t('common', 'art_publish') ?></a>
                     	<a  class="btn btn-default btn-sm centerBtn " style="width:20%; background:#46c0f3" id="new_vote_storage"><?= Yii::t('common', 'save_temp') ?></a>
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
                     	<!--</div>
                     	</div>-->
                     	
                     	
    <script type="text/javascript">
    app.genCalendar();
   
    $(function(){
          // 
           window.validation =app.creatFormValidation($("#new_vote_form_id"));  
         
         $("#new_vote_start_at").attr("disabled","disabled");
         $("#new_vote_end_at").attr("disabled","disabled");


         $("#new_vote_form_id input[name='investigation_range']").click(function(){
        	console.log( $("#new_vote_form_id input[name='investigation_range']:checked").val());
        	var is_investigation_range_check=$("#new_vote_form_id input[name='investigation_range']:checked").val();
        	    if(is_investigation_range_check=='0'){
        	    	
        		    $("#new_vote_start_at").removeAttr("disabled");
                    $("#new_vote_end_at").removeAttr("disabled");
                    
                    $("#new_vote_start_at").attr("data-condition","required");
                    $("#new_vote_end_at").attr("data-condition","required");
            	}else{
            		 $("#new_vote_start_at").attr("disabled","disabled");
                     $("#new_vote_end_at").attr("disabled","disabled");
                     $("#new_vote_start_at").val('');
                     $("#new_vote_end_at").val('');

         		     $("#new_vote_start_at").removeAttr("data-condition");
                     $("#new_vote_end_at").removeAttr("data-condition");


                     validation.hideAlert("#new_vote_start_at");
                     validation.hideAlert("#new_vote_end_at");
                }
          });
          
          
		
		 validateInvestgationTitle('formGroupInputSmall','1','<?=Yii::t('frontend', '{value}_repeat',['value'=>Yii::t('frontend', 'vote_title')])?>','<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'vote_title')])?>','<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'vote_title')])?>',validation);
		 var lists_no_delete=[];
         var lists=[];
         
          lists_no_delete.push({id:'a'+Math.random().toString().substr(2)});
          lists_no_delete.push({id:'a'+Math.random().toString().substr(2)});
          lists.push({id:'a'+Math.random().toString().substr(2)});
          lists.push({id:'a'+Math.random().toString().substr(2)});

         
          var t2_templ_delete=_.template($("#t2_no_delete").html(),{variable: 'data'})({datas:lists_no_delete});
          var t2_templ=_.template($("#t2").html(),{variable: 'data'})({datas:lists});

          $("#first_row").append(t2_templ_delete);
          
  		  $("#first_row").append(t2_templ);

  		  $("#addOptionId").click(function(){
  	  		 
  			 var lists2=[1];
  			 var t2_templ2=_.template($("#t2").html(),{variable: 'data'})({datas:lists2});
  			 $("#first_row").append(t2_templ2);
  			 //validation.add("input[name=option_title]");
  	  	   }); 


  		 $("#new_vote_preview").click(function(){
             
      	   var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/vote-preview'])?>";

      	  
      	   window.pre_new_vote_datas=load_new_vote_object("1");
      	   pre_new_vote_datas.votemotaiid='new_vote';

      	   if(pre_new_vote_datas.options.length<2){

      		 app.showMsg("<?=Yii::t('frontend', 'more_than_2_option')?>");
       	     //app.alertSmall("#foo");	   
  		     
  		     return;
  		   }


	      	 if(!compareDate(pre_new_vote_datas.start_at,pre_new_vote_datas.end_at)){
		    	 return;
			 }

      	   if(!validation.validate()){

      		 //$("#msm_alert_content").text("验证未通过");
       	    // app.alert("#foo");	   
             
             return;
		   };
		   $("#new_vote_preview_view").empty();
	         $("#new_vote_read_preview_view").empty();
      	   FmodalLoadData("new_vote_preview_view", addChoiceUrl,pre_new_vote_datas);
      	  // $("#new_survey").modal('hide');

          });

    		  
  		

  	     $("#new_vote_storage").click(function(){

  		     save_new_vote("0","<?=Url::toRoute(['investigation/save-vote',])?>");

  	  	  });

  		  $("#new_vote_pub").click(function(){

  			save_new_vote("1","<?=Url::toRoute(['investigation/save-vote',])?>");
  			
  			//
  	  	   }); 

  		
      	 //end
    }); 


    function deleteOption(node){
 	   
        $(node).parent().parent().parent().parent().remove();

    }

    function load_new_vote_object(status){
	     var v_is_estimate_val=$("#new_vote_form_id input[name='is_estimate']:checked").val();
	     var v_question_type_val=$("#new_vote_form_id input[name='question_type']:checked").val();
		 var v_answer_type_val=$("#new_vote_form_id input[name='answer_type']:checked").val();
	     var v_question_title_val=app.clean($("#new_vote_form_id input[name='question_title']").val());
	     var v_option_title_arrs=$("#new_vote_form_id input[name='option_title']");
	     var v_start_at_val=$("#new_vote_form_id input[name='start_at']").val();
	     var v_end_at_val=$("#new_vote_form_id input[name='end_at']").val(); 
	     var v_investigation_range_val=$("#new_vote_form_id input[name='investigation_range']:checked").val();

	  
	     var vote_obj={};

	     vote_obj.is_estimate=v_is_estimate_val;
	     vote_obj.question_type=v_question_type_val;
	     vote_obj.answer_type=v_answer_type_val;
	     vote_obj.question_title=v_question_title_val;
	     vote_obj.options=[];
	     vote_obj.start_at=v_start_at_val;
	     vote_obj.end_at=v_end_at_val;
	     vote_obj.status=status;
	     vote_obj.investigation_range=v_investigation_range_val;

	     for(var i=0;i<v_option_title_arrs.length;i++){
	    	vote_obj.options.push({option_title:app.clean(v_option_title_arrs[i].value)});
         }

	    

	     return  vote_obj;  
	}

    function save_new_vote(status,url){

    	 	 var v_is_estimate_val=$("#new_vote_form_id input[name='is_estimate']:checked").val();
    	     var v_question_type_val=$("#new_vote_form_id input[name='question_type']:checked").val();
			 var v_answer_type_val=$("#new_vote_form_id input[name='answer_type']:checked").val();
		     var v_question_title_val=$("#new_vote_form_id input[name='question_title']").val();
		     var v_option_title_arrs=$("#new_vote_form_id input[name='option_title']");
		     var v_start_at_val=$("#new_vote_form_id input[name='start_at']").val();
		     var v_end_at_val=$("#new_vote_form_id input[name='end_at']").val(); 

		     var v_investigation_range_val=$("#new_vote_form_id input[name='investigation_range']:checked").val();

 
		     if(!compareDate(v_start_at_val,v_end_at_val)){
		    	 return;
			 }

			

		     if(v_option_title_arrs.length<2){
		    	 app.showMsg("<?=Yii::t('frontend', 'more_than_2_option')?>");
	       	     //app.alertSmall("#foo");	   
			     return;
			 }
			 
		     var vote_obj={};

		     vote_obj.is_estimate=v_is_estimate_val;
		     vote_obj.question_type=v_question_type_val;
		     vote_obj.answer_type=v_answer_type_val;
		     vote_obj.question_title=v_question_title_val;
		     vote_obj.options=[];
		     vote_obj.start_at=v_start_at_val;
		     vote_obj.end_at=v_end_at_val;
		     vote_obj.status=status;
		     vote_obj.investigation_range=v_investigation_range_val;

		     for(var i=0;i<v_option_title_arrs.length;i++){
		    	vote_obj.options.push({option_title:v_option_title_arrs[i].value});
	         }

		     if(!validation.validate()){

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
				// $("#new_vote").modal('hide');
				 app.hideAlert("#new_vote");
			   }
			 });

    }
    
    </script>     
       
       
   <script id="t2_no_delete" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row">
                       <div class="col-md-12 col-sm-12">
                         <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?></label>
                            <div class="col-sm-9">
                            <input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option')])?>" class="form-control pull-left" name="option_title" type="text" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_content')?>" style="width:80%">
                             </div>
                          </div>
                      </div>
       </div>
  <%});%>
    </script>                          	
                     	
   <script id="t2" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row">
                       <div class="col-md-12 col-sm-12">
                         <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?></label>
                            <div class="col-sm-9">
                            <input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option')])?>" class="form-control pull-left" name="option_title" type="text" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_content')?>" style="width:80%">
                            <a  onclick="deleteOption(this)" class="btn btn-default btn-sm glyphicon glyphicon-remove deleteBtn" title="<?= Yii::t('common', 'delete_button') ?>"></a>
                            </div>
                          </div>
                      </div>
       </div>
  <%});%>
    </script>                   	