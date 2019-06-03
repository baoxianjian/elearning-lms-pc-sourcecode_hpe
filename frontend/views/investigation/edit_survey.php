<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

?>


  
    		<div class="header">
    		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    		<h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'modify_investigation') ?>(<?= Yii::t('frontend', 'questionnaire') ?>)</h4>
        </div>
        <div class="content">
        <div class="courseInfo">
        <div role="tabpanel" class="tab-pane active" id="teacher_info">
        <div class=" panel-default scoreList">
        		<div class="panel-body">
        		<form id="edit_survey_form_id">
        		 <input type="hidden" id="e_survey_investigation_id" value="<?=$id?>">
        		<div class="infoBlock" style="position: relative;" >
        		<div class="row">
	        		<div class="col-md-12 col-sm-12">
	        				<div class="form-group form-group-sm">
	        				<label class="col-sm-3 control-label"><?= Yii::t('frontend', 'questionnaire_title') ?></label>
	        				<div class="col-sm-9">
	        						<input data-delay="1" data-mode="COMMON" data-condition="^(?!\s)(?!.*?\s$).{1,250}$" data-alert="<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'questionnaire_title')])?>" class="form-control pull-left" name="title" type="text" id="formGroupInputSmall10251" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'questionnaire_title')])?>" style="width:80%">
	        			     </div>
	        				</div>
	        		 </div>
        		</div>
        		<div class="row">
        			<div class="col-md-12 col-sm-12">
        				<div class="form-group form-group-sm">
        				  <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'brief') ?></label>
        				  <div class="col-sm-9">
        					<textarea  id="edit_survey_description_id" name="description" class="form-control pull-left" type="text" style="width:80%"></textarea>
        				   </div>
        				 </div>
                      </div>
                 </div>
                 <div id="edit_first_survey_row">
                 </div>
                 
                 <!-- 
                 <div id="second_survey_row">
                 </div>
                 -->
                 <hr/>
                      
                      <div class="row addNewChoice hide" id="editNewChoice_id">
                      </div>    																									
                  
                    
                 <div class="row addNewQuestion hide" id="editNewQuestion_id">
                 </div>
                 
                   <div id="edit_survey_lost_display">
                    <div class="col-md-12 col-sm-12 centerBtnArea">
                      <div class="btn-group" style="width:20%">
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width:100%">
							<?= Yii::t('frontend', 'add_qu') ?> <span class="caret stBtn"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a  class="btnaddEditChoice" id="btnaddEditChoice"><?= Yii::t('frontend', 'choose') ?></a></li>
                          <li><a  class="btnaddEditQuestion" id="btnaddEditQuestion"><?= Yii::t('frontend', 'question_answer2') ?></a></li>
                        </ul>
                      </div>
                      
                      
                      <a  class="btn btn-default btn-sm centerBtn" id="add_edit_survey_pagination" style="width:20%"><?= Yii::t('frontend', 'add2_{value}',['value'=>Yii::t('frontend','page_break')]) ?></a>
                    </div>
                    <div class="infoBlock" style="margin-top:30px;">
                    
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
                              <input readonly="readonly" data-mode="COMMON" id="edit_surv_start_at" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'action_start_at')])?>" class="form-control pull-left" name="start_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%; margin-right:6%;">
                              <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('common', 'to2')?></span>
                              <input readonly="readonly" data-mode="COMMON" id="edit_surv_end_at"  data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')])?>" class="form-control pull-left" name="end_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%;margin-left:6% ;margin-right:6%;">
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
                                    <input type="radio" name="answer_type" value="1" /> <?= Yii::t('frontend', 'vote_private_name') ?>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12 col-sm-12 centerBtnArea" >
                          <a  class="btn btn-default btn-sm centerBtn" id="edit_survey_preview" style="width:20%"><?= Yii::t('common', 'preview_button') ?></a>
                          <a  id="edit_survey_pub" class="btn btn-success btn-sm centerBtn" style="width:20%; margin:10px 15px;"><?= Yii::t('frontend', 'modify') ?></a>
                         
                        </div>
                      </div>
                      </div>
                    </div>
                    
                    <!-- 8888 -->
                    <div class="editChoice editPanel hide"  id="dy_edit_choice_panels" style="position: static;">
                     
                    </div>
                    <div class="editQuestion editPanel2 hide" id="dy_edit_question_panels" style="position: static;">
                      
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
   var p_uuid=1;

   var edit_survey_objects=[];
   
    $(function(){

    	 $('#edit_first_survey_row').sortable().disableSelection();

    	  window.validation_edit_survey =app.creatFormValidation($("#edit_survey_form_id"));

    	  $("#edit_surv_start_at").attr("disabled","disabled");
          $("#edit_surv_end_at").attr("disabled","disabled");

          $("#edit_survey_form_id input[name='investigation_range']").click(function(){
          	
          	var is_investigation_range_check=$("#edit_survey_form_id input[name='investigation_range']:checked").val();
          	    if(is_investigation_range_check=='0'){
          	    	
          		    $("#edit_surv_start_at").removeAttr("disabled");
                      $("#edit_surv_end_at").removeAttr("disabled");
                      
                      $("#edit_surv_start_at").attr("data-condition","required");
                      $("#edit_surv_end_at").attr("data-condition","required");
              	}else{
              		 $("#edit_surv_start_at").attr("disabled","disabled");
                       $("#edit_surv_end_at").attr("disabled","disabled");
                       $("#edit_surv_start_at").val('');
                       $("#edit_surv_end_at").val('');

           		     $("#edit_surv_start_at").removeAttr("data-condition");
                       $("#edit_surv_end_at").removeAttr("data-condition");


                       validation_edit_survey.hideAlert("#edit_surv_start_at");
                       validation_edit_survey.hideAlert("#edit_surv_end_at");
                  }
            });

     	 $("#formGroupInputSmall10251").mouseout(function(){

     		$("#formGroupInputSmall10251").trigger("blur");
         	 });
     
     $.ajax({
        	   async: false,
			   url: "<?=Url::toRoute(['investigation/get-survey',])?>",
			   data: {id:'<?=$id?>'},
			   success: function(msg){

				   
				   
				  
				   $("#edit_survey_form_id input[name='start_at']").val(msg.result.start_at);
				   $("#edit_survey_form_id input[name='end_at']").val(msg.result.end_at);
				   $("#edit_survey_description_id").val(msg.result.description);
				   $("#edit_survey_form_id input[name='title']").val(msg.result.title);

				   validateInvestgationTitle('formGroupInputSmall10251','0','<?=Yii::t('frontend', '{value}_repeat',['value'=>Yii::t('frontend', 'questionnaire_title')])?>','<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'questionnaire_title')])?>','<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'questionnaire_title')])?>',validation_edit_survey,msg.result.title);

				   
				   
				   if(msg.result.answer_type==0){
					   $("#edit_survey_form_id input[name='answer_type']").get(0).checked = true;
				   }else{
					   $("#edit_survey_form_id input[name='answer_type']").get(1).checked = true;
				   }

				   if(msg.result.is_estimate=='1'){
					   $("#edit_survey_form_id input[name='is_estimate']").get(0).checked = true;
				   }else{
					   $("#edit_survey_form_id input[name='is_estimate']").get(1).checked = true;
				   }

				   if(msg.result.investigation_range=='0'){
					   $("#edit_survey_form_id input[name='investigation_range']").get(0).checked = true;
					   $("#edit_surv_start_at").removeAttr("disabled");
				       $("#edit_surv_end_at").removeAttr("disabled");
				   }else{
					   $("#edit_survey_form_id input[name='investigation_range']").get(1).checked = true;
				   }
				  
				   
				   var lists_arrs=msg.result.question;
				   edit_survey_objects=lists_arrs;

				   for(var i=0;i<lists_arrs.length;i++){

					   var lists_tmp=lists_arrs[i];
					   
					   if(lists_tmp.question_type=='2'){
						   
						   var lists=[];
						   lists.push(lists_tmp);
						   var edit_t7_templ=_.template($("#edit_t7").html(),{variable: 'data'})({datas:lists});
					       $("#edit_first_survey_row").append(edit_t7_templ);
					   }else if(lists_tmp.question_type=='3'){
						   
						   var lists=[];
						   lists.push(lists_tmp);
						   var edit_t9_templ=_.template($("#edit_t9").html(),{variable: 'data'})({datas:lists});
					       $("#edit_first_survey_row").append(edit_t9_templ);
					   }else if(lists_tmp.question_type=='1'){
						   
						   var lists=[];
						   lists.push(lists_tmp);
						   var edit_t8_templ=_.template($("#edit_t8").html(),{variable: 'data'})({datas:lists});
					       $("#edit_first_survey_row").append(edit_t8_templ);
						  
					   }else if(lists_tmp.question_type=='0'){
						   
						   var lists=[];
						   lists.push(lists_tmp);
						   var edit_t6_templ=_.template($("#edit_t6").html(),{variable: 'data'})({datas:lists});
					       $("#edit_first_survey_row").append(edit_t6_templ);
					   }
				  }
				  
				  
			      
			   }
			 });

       

       $("#edit_survey_pub").click(function(){
    	 
         
    	  edit_new_survey("<?=Url::toRoute(['investigation/edit-survey',])?>");

       });
   

       $("#btnaddEditChoice").click(function(){
           
           var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/add-edit-choice'])?>";
    	   FmodalLoad("editNewChoice_id", addChoiceUrl,true);
    	   $('#editNewChoice_id').removeClass('hide');

    	   $("#edit_survey_preview").attr("disabled", true);
           $("#edit_survey_pub").attr("disabled", true);
          
    	   
           });


	$("#btnaddEditQuestion").click(function(){
		
	       var addQuestionUrl="<?=Yii::$app->urlManager->createUrl(['investigation/add-edit-question'])?>";
		   FmodalLoad("editNewQuestion_id", addQuestionUrl,true);
		   $('#editNewQuestion_id').removeClass('hide');



    	   $("#edit_survey_preview").attr("disabled", true);
           $("#edit_survey_pub").attr("disabled", true);
		   
	    });


	$("#add_edit_survey_pagination").click(function(){
		var lists9=[];
		var first_new_sur_obj=_.first(edit_survey_objects);
		if(!first_new_sur_obj){
			
			app.showMsg("<?=Yii::t('frontend','page_break_not_first')?>");
	        //app.alertSmall("#foo");	 
			return false;
		}
		
		p_uuid++;

		var lists9_obj={};
		lists9_obj.question_title='<?=Yii::t('frontend','next_page')?>';
		lists9_obj.question_type='3';
		lists9_obj.id=p_uuid+'wrt';
		lists9.push(lists9_obj);
		edit_survey_objects.push(lists9_obj);
		
		var t9_templ=_.template($("#edit_t9").html(),{variable: 'data'})({datas:lists9});

	         $("#edit_first_survey_row").append(t9_templ);

		});



	 $("#edit_survey_preview").click(function(){

		   if(!validation_edit_survey.validate()){

		         
		         //$("#msm_alert_content").text("验证未通过");
			         //  app.alert("#foo");
		         return;
			   };
         
  	   var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/survey-preview'])?>";

	  
	  
  	   window.pre_new_survey_datas=load_edit_survey_object();

  		if(!check_pagination_pos()){
  		
		    return;
		}

  	   pre_new_survey_datas.surveymotaiid='edit_survey';


  	 if(!compareDate(pre_new_survey_datas.start_at,pre_new_survey_datas.end_at)){
    	 return;
	 }
 

  	 $("#new_survey_preview_view").empty();
	 $("#new_survey_read_preview_view").empty();
  	   
  	   FmodalLoadData("new_survey_preview_view", addChoiceUrl,pre_new_survey_datas);
  	  // $("#new_survey").modal('hide');

      });


	

	

	 //resetEditPanel();


   	 //end
    }); 

    var is_pagination_adjacent=[];

    function check_pagination_pos(){
    	is_pagination_adjacent=[];
    	for(var i=0;i<edit_survey_objects.length;i++){
            var s_obj_tmep=edit_survey_objects[i];
            
        	if(s_obj_tmep.question_type=='3'){
        		if(is_pagination_adjacent.length>0){
                	var vali_pag_temp=is_pagination_adjacent[0];
                	
                	if((i-vali_pag_temp.num)==1){
                		app.showMsg("<?=Yii::t('frontend','page_break_not_nearby')?>");
             	       // app.alertSmall("#foo");
            			return false;	
                    }else{
                    	is_pagination_adjacent=[];
                    }
                	
                }
        		is_pagination_adjacent.push({num:i});
            }
        }

    	var first_new_sur_obj=_.first(edit_survey_objects);
    	if(!first_new_sur_obj){

    		
    		app.showMsg("<?=Yii::t('frontend','{value}_not_null',['value'=>Yii::t('frontend','questionnaire_option')])?>");
	       // app.alertSmall("#foo");
			return false;
        }
		if(first_new_sur_obj.question_type=='3'){
			
			app.showMsg("<?=Yii::t('frontend','page_break_not_first')?>");
	        //app.alertSmall("#foo");
			return false;
		}

		
		var last_new_sur_obj=_.findLastIndex(edit_survey_objects,{question_type:'3'});
		if(last_new_sur_obj==(edit_survey_objects.length-1)){
			
			app.showMsg("<?=Yii::t('frontend','page_break_not_end')?>");
	        //app.alertSmall("#foo");
			return false;
		}

		return true;

    }
    

    function resetEditPanel() {
        $('.editPanel').addClass('hide')
        $('.editPanel2').addClass('hide')
      }

    function delete_new_survey_pagination(node,id){
    	var iiiiii= _.findIndex(edit_survey_objects,{id:id});
    	edit_survey_objects.splice(iiiiii,1);
        $(node).parent().parent().remove();

    }


    function load_edit_survey_object(){
      

      var v_is_estimate_val=$("#edit_survey_form_id input[name='is_estimate']:checked").val();	
      var s_answer_type_val=$("#edit_survey_form_id input[name='answer_type']:checked").val();
      var s_start_at_val=$("#edit_survey_form_id input[name='start_at']").val();
	  var s_end_at_val=$("#edit_survey_form_id input[name='end_at']").val(); 
	  var s_title_val=app.clean($("#edit_survey_form_id input[name='title']").val());
	  var s_description_val=app.clean($("#edit_survey_description_id").val());


	  var survey_obj={};

	  survey_obj.is_estimate=v_is_estimate_val;
	  survey_obj.answer_type=s_answer_type_val;
	  survey_obj.title=s_title_val;
	  survey_obj.description=s_description_val;
	  survey_obj.question=edit_survey_objects;
	  var new_array=sortArrays(survey_obj.question);
	  survey_obj.question=new_array;
	  edit_survey_objects=new_array;
	  
	  survey_obj.start_at=s_start_at_val;
	  survey_obj.end_at=s_end_at_val;
	  survey_obj.id=$("#e_survey_investigation_id").val();

	  return survey_obj;
    }

    function edit_new_survey(url){

    	 if(!validation_edit_survey.validate()){

	         
		        // $("#msm_alert_content").text("验证未通过");
			       //    app.alert("#foo");
		         return;
		  };

    	

		  var v_is_estimate_val=$("#edit_survey_form_id input[name='is_estimate']:checked").val();
          var s_answer_type_val=$("#edit_survey_form_id input[name='answer_type']:checked").val();
          var s_start_at_val=$("#edit_survey_form_id input[name='start_at']").val();
		  var s_end_at_val=$("#edit_survey_form_id input[name='end_at']").val(); 
		  var s_title_val=$("#edit_survey_form_id input[name='title']").val();
		  var s_description_val=$("#edit_survey_description_id").val();

		  var v_investigation_range_val=$("#edit_survey_form_id input[name='investigation_range']:checked").val();
		  
		  var survey_obj={};

		  survey_obj.is_estimate=v_is_estimate_val;
		  survey_obj.answer_type=s_answer_type_val;
		  survey_obj.title=s_title_val;
		  survey_obj.description=s_description_val;
		  survey_obj.question=edit_survey_objects;

		  var new_array=sortArrays(survey_obj.question);
		  survey_obj.question=new_array;
		  edit_survey_objects=new_array;
		  
		  survey_obj.start_at=s_start_at_val;
		  survey_obj.end_at=s_end_at_val;
		  survey_obj.investigation_range=v_investigation_range_val;
		  survey_obj.id=$("#e_survey_investigation_id").val();

		  if(!check_pagination_pos()){

	  		    return;
	         }
	  	  

		  if(!compareDate(s_start_at_val,s_end_at_val)){
		    	 return;
			 }	

		 
		  
		  console.log(survey_obj);
		  $.ajax({
			   type: "POST",
			   url: url,
			   data: survey_obj,
			   success: function(msg){
				 loadList();
				 //$("#edit_survey").modal('hide');
				 app.hideAlert("#edit_survey")
			   }
		  });
        }

    function deleteEditSurveyQuest(node,id){
        
    	var iiiiii= _.findIndex(edit_survey_objects,{id:id});
    	edit_survey_objects.splice(iiiiii,1);
        
        $(node).parent().parent().parent().parent().remove();
        $("#edit_survey_lost_display").show();
        edit_one_choice_fun_flag=0;
     }

    function deleteEditSurveyOption(node,id){

    	var iiiiii= _.findIndex(edit_survey_objects,{id:id});
    	edit_survey_objects.splice(iiiiii,1);
        
        $(node).parent().parent().parent().parent().remove();
        $("#edit_survey_lost_display").show();
        edit_one_choice_fun_flag=0;
    }

    window.edit_one_choice_fun_flag=0;
    function edit_one_choice_fun(id){

    	
    	 if(edit_one_choice_fun_flag!=0){
				app.showMsg("<?=Yii::t('frontend','modify_box_can_open_only_one')?>！");
				return ;
         }
    	 var iii=_.findWhere(edit_survey_objects, {id:id}) ;
    	 var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/dy-edit-choice'])?>";

    	 var div_tag_str_id=id+"xyxy";

    	 $("#"+div_tag_str_id).remove();
    	 
    	 var div_tag_str="<div id="+div_tag_str_id+" class='editChoice editPanel' style='z-index:2000;position: static;'></div>";
    	 var editTag = $("#"+id+"abc");
    	 editTag.after(div_tag_str);
         var editTagHeight = editTag.height();
         var editTagTop = editTag.position().top + editTagHeight + 20;
    	 
    	 $("#"+div_tag_str_id).css('top', editTagTop).removeClass('hide');
    	 
    	 FmodalLoadData1(div_tag_str_id, addChoiceUrl,iii);

         $("#edit_survey_lost_display").hide();
         edit_one_choice_fun_flag=1;
        

    }

    function edit_one_question_fun(id){
        
    	 var iii=_.findWhere(edit_survey_objects, {id:id}) ;
    	 var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/dy-edit-question'])?>";
    	 if(edit_one_choice_fun_flag!=0){
				app.showMsg("<?=Yii::t('frontend','modify_box_can_open_only_one')?>！");
				return ;
         }

    	 var div_tag_str_id=id+"wewewe";

    	 $("#"+div_tag_str_id).remove();
    	 
    	 var div_tag_str="<div id="+div_tag_str_id+" class='editQuestion editPanel2' style='z-index:2000;position: static;'></div>";
    	 
    	 var editTag = $("#"+id+"abc");
    	 editTag.after(div_tag_str);
         var editTagHeight = editTag.height();
         var editTagTop = editTag.position().top + editTagHeight + 20;

    	 $("#"+div_tag_str_id).css('top', editTagTop).removeClass('hide');
    	 FmodalLoadData1(div_tag_str_id, addChoiceUrl,iii);

    	 $("#edit_survey_lost_display").hide();
    	 edit_one_choice_fun_flag=1;
   }


    function sortArrays(question_){

        var new_array_order={};
     	  $("#edit_first_survey_row .questionGroup_quest").each(function(num) {
     		   var div_id=$(this).attr("id");
     		   if(div_id.indexOf('abc')!=-1){
     			  div_id=div_id.substring(0,(div_id.length-3));
     		   }
     		  if(div_id.indexOf('fenye')!=-1){
     			  div_id=div_id.substring(0,(div_id.length-5));
     		   }
     		   new_array_order[div_id]=num;		 
           });
          console.log(new_array_order);

     	    var new_array_=_.sortBy(question_, function(num){ 
     	    	 console.log(num.id);
          	 return  parseInt(new_array_order[num.id]) ;
           });
     	  console.log(new_array_);
          return  new_array_;   
       }

   
        
   </script>   
          
    <script id="edit_t9" type="text/template">
 <%_.each(data.datas, function(item) {%>
	<div class="row questionGroup_quest" id="<%=item.id%>fenye">
       <div class="col-md-12 col-sm-12">
 <button type="button" class="btn btn-info btn-sm "  aria-haspopup="true" aria-expanded="false" style="width:90%">
                          <%=item.question_title%> <span class="caret stBtn"></span>
                        </button>
   		  <a  onclick="delete_new_survey_pagination(this,'<%=item.id%>')" class="btn btn-sm pull-right"><?= Yii::t('common', 'delete_button') ?></a>
   	  </div>
   	</div>
      <%});%>
    </script>       
  
  <script id="edit_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest editTag" onClick="edit_one_choice_fun('<%=item.id%>')" id="<%=item.id%>abc">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[<?=Yii::t('common', 'question_radio')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                        <div class="col-sm-3">
	                         <a  onclick="deleteEditSurveyOption(this,'<%=item.id%>')" class="btn btn-sm pull-right"><?= Yii::t('common', 'delete_button') ?></a>
	                        </div>
	                      </div>
                     </div>
                     <div class="col-md-12 col-sm-12">
 <%_.each(item.options, function(item_option) {%>
                      <div class="options" style="display: inline-block; float: left; text-indent: 8px;">
                          <label style="margin-right:40px;">
                            <input type="radio" name="addChoiceOptionTemp6" value="1"> <%=item_option.option_title%>
                          </label>
                        </div>
 <%});%>
                       
                        
                      </div>
        </div>
  <%});%>
    </script>
    
<script id="edit_t8" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest editTag" onClick="edit_one_choice_fun('<%=item.id%>')" id="<%=item.id%>abc">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[<?=Yii::t('common', 'question_checkbox')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                        <div class="col-sm-3">
	                         <a  onclick="deleteEditSurveyOption(this,'<%=item.id%>')" class="btn btn-sm pull-right"><?= Yii::t('common', 'delete_button') ?></a>
	                        </div>
	                      </div>
                     </div>
                     <div class="col-md-12 col-sm-12">
 <%_.each(item.options, function(item_option) {%>
                      <div class="options" style="display: inline-block; float: left; text-indent: 8px;">
                          <label style="margin-right:40px;">
                            <input type="checkbox" name="addChoiceOptionTemp8" value="1"> <%=item_option.option_title%>
                          </label>
                        </div>
 <%});%>
                       
                        
                      </div>
        </div>
  <%});%>
    </script>
  
 <script id="edit_t7" type="text/template">
  <%_.each(data.datas, function(item) {%>
	<div class="row questionGroup_quest editTag questionTag" id="<%=item.id%>abc" onClick="edit_one_question_fun('<%=item.id%>')">
       <div class="col-md-12 col-sm-12">
   		   <div class="form-group form-group-sm">
   		   <label class="col-sm-9 control-label"><?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
   		     <div class="col-sm-3">
   		      <a  onclick="deleteEditSurveyQuest(this,'<%=item.id%>')" class="btn btn-sm pull-right"><?= Yii::t('common', 'delete_button') ?></a>
   		     </div>
   		   </div>
   		   </div>
   	    	<div class="col-md-12 col-sm-12">
            <%=item.question_description%>
   	 	    <textarea ><?=Yii::t('frontend', 'my_answer_is')?>....</textarea>
   	  </div>
   	</div>
      <%});%>
    </script>        
  