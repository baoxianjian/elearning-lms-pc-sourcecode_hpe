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
    		<h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','build_investigation')?> (<?=Yii::t('frontend','questionnaire')?>)</h4>
        </div>
        <div class="content">
        <div class="courseInfo">
        <div role="tabpanel" class="tab-pane active" id="teacher_info">
        <div class=" panel-default scoreList">
        		<div class="panel-body">
        		<form id="new_survey_form_id">
        		<div class="infoBlock">
        		<div class="row">
	        		<div class="col-md-12 col-sm-12">
	        				<div class="form-group form-group-sm">
	        				<label class="col-sm-3 control-label"><?= Yii::t('frontend', 'questionnaire_title') ?></label>
	        				<div class="col-sm-9">
	        						<input data-delay="1" data-mode="COMMON" data-condition="^(?!\s)(?!.*?\s$).{1,250}$" data-alert="<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'question_title')])?>" class="form-control pull-left" name="title" type="text" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
	        			     </div>
	        				</div>
	        		 </div>
        		</div>
        		<div class="row">
        			<div class="col-md-12 col-sm-12">
        				<div class="form-group form-group-sm">
        				  <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'brief') ?></label>
        				  <div class="col-sm-9">
        					<textarea  id="survey_description_id" name="description" class="form-control" type="text" style="width:80%"></textarea>
        				   </div>
        				 </div>
                      </div>
                 </div>
                 <div id="first_survey_row">
                 </div>
                 
                 <!-- 
                 <div id="second_survey_row">
                 </div>
                 -->
                 <hr/>
                      
                      <div class="row addNewChoice hide" id="addNewChoice_id">
                      </div>    																									
                  
                    
                 <div class="row addNewQuestion hide" id="addNewQuestion_id">
                 </div>
                 
                   <div id="add_survey_lost_display">
                    <div class="col-md-12 col-sm-12 centerBtnArea">
                      <div class="btn-group" style="width:20%">
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width:100%">
                            <?= Yii::t('frontend', 'add_qu') ?> <span class="caret stBtn"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a  class="btnaddNewChoice" id="btnaddNewChoice"><?= Yii::t('frontend', 'choose') ?></a></li>
                          <li><a  class="btnaddNewQuestion" id="btnaddNewQuestion"><?= Yii::t('frontend', 'question_answer2') ?></a></li>
                        </ul>
                      </div>
                      <a  class="btn btn-default btn-sm centerBtn" id="add_new_survey_pagination" style="width:20%;font-size:13px;"><?= Yii::t('frontend', 'add2_{value}',['value'=>Yii::t('frontend','page_break')]) ?></a>
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
                              <input readonly="readonly" data-mode="COMMON" id="new_surv_start_at" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'action_start_at')])?>" class="form-control pull-left" name="start_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%; margin-right:6%;">
                              <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('common', 'to2')?></span>
                              <input readonly="readonly" data-mode="COMMON" id="new_surv_end_at"  data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')])?>" class="form-control pull-left" name="end_at" type="text" data-type="rili" placeholder="yyyy-mm-dd" style="width:32%;margin-left:6% ;margin-right:6%;">
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
                        <div class="col-md-12 col-sm-12 centerBtnArea">
                          <a  id="new_survey_preview" class="btn btn-default btn-sm centerBtn" style="width:20%"><?= Yii::t('common', 'preview_button') ?></a>
                          <a  id="new_survey_pub" class="btn btn-success btn-sm centerBtn" style="width:20%; margin:10px 15px;"><?= Yii::t('common', 'art_publish') ?></a>
                          <a  id="new_survey_storage" class="btn btn-default btn-sm centerBtn" style="width:20%"><?= Yii::t('common', 'save_temp') ?></a>
                        </div>
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
   var p_uuid=1;

   var save_survey_objects=[];

   
   
    $(function(){
//
 // 新建增加交换顺序脚本
    	 $('#first_survey_row').sortable().disableSelection();
        
        //
        window.validation_new_survey =app.creatFormValidation($("#new_survey_form_id"));  



        $("#new_surv_start_at").attr("disabled","disabled");
        $("#new_surv_end_at").attr("disabled","disabled"); 

        $("#new_survey_form_id input[name='investigation_range']").click(function(){
        	
        	var is_investigation_range_check=$("#new_survey_form_id input[name='investigation_range']:checked").val();
        	    if(is_investigation_range_check=='0'){
        	    	
        		    $("#new_surv_start_at").removeAttr("disabled");
                    $("#new_surv_end_at").removeAttr("disabled");
                    
                    $("#new_surv_start_at").attr("data-condition","required");
                    $("#new_surv_end_at").attr("data-condition","required");
            	}else{
            		 $("#new_surv_start_at").attr("disabled","disabled");
                     $("#new_surv_end_at").attr("disabled","disabled");
                     $("#new_surv_start_at").val('');
                     $("#new_surv_end_at").val('');

         		     $("#new_surv_start_at").removeAttr("data-condition");
                     $("#new_surv_end_at").removeAttr("data-condition");


                     validation_new_survey.hideAlert("#new_surv_start_at");
                     validation_new_survey.hideAlert("#new_surv_end_at");
                }
          });

        validateInvestgationTitle('formGroupInputSmall','0','<?=Yii::t('frontend', '{value}_repeat',['value'=>Yii::t('frontend', 'questionnaire_title')])?>','<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'questionnaire_title')])?>','<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'questionnaire_title')])?>',validation_new_survey);
             

        $("#new_survey_storage").click(function(){

    	 save_new_survey('0',"<?=Url::toRoute(['investigation/save-survey',])?>");

         });

       $("#new_survey_pub").click(function(){
         
    	  save_new_survey('1',"<?=Url::toRoute(['investigation/save-survey',])?>");

       });


       $("#new_survey_preview").click(function(){

    	   if(!validation_new_survey.validate()){

               
               // $("#msm_alert_content").text("验证未通过");
    	         //  app.alert("#foo");
                return;
   		   };
           
    	   var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/survey-preview'])?>";

    	  
    	  
    	   window.pre_new_survey_datas=load_new_survey_object("1");

    	   pre_new_survey_datas.surveymotaiid='new_survey';

    	  

    	 

  		 if(!compareDate(pre_new_survey_datas.start_at,pre_new_survey_datas.end_at)){
	    	 return;
		 }

  		 if(!check_pagination_pos()){

 		    return;
        }
  		 $("#new_survey_preview_view").empty();
  		 $("#new_survey_read_preview_view").empty();
    	   FmodalLoadData("new_survey_preview_view", addChoiceUrl,pre_new_survey_datas);
    	  // $("#new_survey").modal('hide');

        });


       
   

       $("#btnaddNewChoice").click(function(){
           var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/addchoice'])?>";
    	   FmodalLoad("addNewChoice_id", addChoiceUrl,true);
    	   $('.addNewChoice').removeClass('hide');


           $("#new_survey_preview").attr("disabled", true);
           $("#new_survey_pub").attr("disabled", true);
           $("#new_survey_storage").attr("disabled", true);
    	   

    	   
           });


	$("#btnaddNewQuestion").click(function(){
	       var addQuestionUrl="<?=Yii::$app->urlManager->createUrl(['investigation/addquestion'])?>";
		   FmodalLoad("addNewQuestion_id", addQuestionUrl,true);
		   $('.addNewQuestion').removeClass('hide');


		   $("#new_survey_preview").attr("disabled", true);
           $("#new_survey_pub").attr("disabled", true);
           $("#new_survey_storage").attr("disabled", true);
	    });


	$("#add_new_survey_pagination").click(function(){
		var lists9=[];
		var first_new_sur_obj=_.first(save_survey_objects);
		if(!first_new_sur_obj){
			
			app.showMsg("<?=Yii::t('frontend','page_break_not_first')?>");
 	        //app.alertSmall("#foo");
			return false;
		}
		
		p_uuid++;

		var lists9_obj={};
		lists9_obj.question_title='<?=Yii::t('frontend','next_page')?>';
		lists9_obj.question_type='3';
		lists9_obj.id=p_uuid;
		lists9.push(lists9_obj);
		save_survey_objects.push(lists9_obj);
		
		var t9_templ=_.template($("#t9").html(),{variable: 'data'})({datas:lists9});

	         $("#first_survey_row").append(t9_templ);

		});
	


   	 //end
    }); 


    var is_pagination_adjacent=[];


    function check_pagination_pos(){
    	is_pagination_adjacent=[];
        for(var i=0;i<save_survey_objects.length;i++){
            var s_obj_tmep=save_survey_objects[i];
            
        	if(s_obj_tmep.question_type=='3'){
        		if(is_pagination_adjacent.length>0){
                	var vali_pag_temp=is_pagination_adjacent[0];
                	
                	if((i-vali_pag_temp.num)==1){
                		app.showMsg("<?=Yii::t('frontend','page_break_not_nearby')?>");
             	        //app.alertSmall("#foo");
            			return false;	
                    }else{
                    	is_pagination_adjacent=[];
                    }
                	
                }
        		is_pagination_adjacent.push({num:i});
            }
        }

    	var first_new_sur_obj=_.first(save_survey_objects);
    	if(!first_new_sur_obj){

    		
    		app.showMsg("<?=Yii::t('frontend','{value}_not_null',['value'=>Yii::t('frontend','questionnaire_option')])?>");
 	        //app.alertSmall("#foo");
			return false;
        }
		if(first_new_sur_obj.question_type=='3'){
			app.showMsg("<?=Yii::t('frontend','page_break_not_first')?>");
 	       // app.alertSmall("#foo");
			
			return false;
		}

		
		var last_new_sur_obj=_.findLastIndex(save_survey_objects,{question_type:'3'});
		if(last_new_sur_obj==(save_survey_objects.length-1)){
			
			app.showMsg("<?=Yii::t('frontend','page_break_not_end')?>");
 	        //app.alertSmall("#foo");
			return false;
		}

		return true;

    }



    function delete_new_survey_pagination(node,id){
    	var iiiiii= _.findIndex(save_survey_objects,{id:id});
    	save_survey_objects.splice(iiiiii,1);
        $(node).parent().parent().remove();

    }

   function load_new_survey_object(status){

	  var v_is_estimate_val=$("#new_survey_form_id input[name='is_estimate']:checked").val();
      var s_answer_type_val=$("#new_survey_form_id input[name='answer_type']:checked").val();
      var s_start_at_val=$("#new_survey_form_id input[name='start_at']").val();
	  var s_end_at_val=$("#new_survey_form_id input[name='end_at']").val(); 
	  var s_title_val=app.clean($("#new_survey_form_id input[name='title']").val());
	  var s_description_val=app.clean($("#survey_description_id").val());


	  var survey_obj={};

	  survey_obj.is_estimate=v_is_estimate_val;
	  survey_obj.answer_type=s_answer_type_val;
	  survey_obj.title=s_title_val;
	  survey_obj.description=s_description_val;
	  survey_obj.question=save_survey_objects;

	  var new_array=sortArrays(survey_obj.question);

	  survey_obj.question=new_array;
	  save_survey_objects=new_array;
	  survey_obj.start_at=s_start_at_val;
	  survey_obj.end_at=s_end_at_val;
	  survey_obj.status=status;

	 
	  return survey_obj;
   }  

    function save_new_survey(status,url){

    	 if(!validation_new_survey.validate()){

			 // $("#msm_alert_content").text("验证未通过");
  	       //   app.alert("#foo");
              return;
 		   };

    	 

    	 
 		  var v_is_estimate_val=$("#new_survey_form_id input[name='is_estimate']:checked").val();
          var s_answer_type_val=$("#new_survey_form_id input[name='answer_type']:checked").val();
          var s_start_at_val=$("#new_survey_form_id input[name='start_at']").val();
		  var s_end_at_val=$("#new_survey_form_id input[name='end_at']").val(); 
		  var s_title_val=$("#new_survey_form_id input[name='title']").val();
		  var s_description_val=$("#survey_description_id").val();

		  var v_investigation_range_val=$("#new_survey_form_id input[name='investigation_range']:checked").val();

		  var survey_obj={};

		  survey_obj.is_estimate=v_is_estimate_val;
		  survey_obj.answer_type=s_answer_type_val;
		  survey_obj.title=s_title_val;
		  survey_obj.description=s_description_val;

		  var new_array=sortArrays(save_survey_objects);
		  survey_obj.question=new_array;
		  save_survey_objects=new_array;
		  
		  survey_obj.start_at=s_start_at_val;
		  survey_obj.end_at=s_end_at_val;
		  survey_obj.status=status;
		  survey_obj.investigation_range=v_investigation_range_val;

		  if(!check_pagination_pos()){

	 		    return;
	         }
	 	  

		  if(!compareDate(s_start_at_val,s_end_at_val)){
		    	 return;
			 }		

		 

		  $.ajax({
			   type: "POST",
			   url: url,
			   data: survey_obj,
			   success: function(msg){
				 loadList();
				 //$("#new_survey").modal('hide');
				 app.hideAlert("#new_survey");
			   }
		  });
        }

    window.add_edit_one_choice_fun_flag=0;
    function add_edit_one_choice_fun(id){
    	console.log("add_edit_one_choice_fun");

      if(add_edit_one_choice_fun_flag!=0){
				app.showMsg("<?=Yii::t('frontend','modify_box_can_open_only_one')?>！");
				return ;
      }
    	
   	 var iii=_.findWhere(save_survey_objects, {id:id}) ;
   	 var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/dy-add-choice'])?>";

   	 var div_tag_str_id=id+"xyxyxy";
   	 $("#"+div_tag_str_id).remove();
   	 var div_tag_str="<div id="+div_tag_str_id+" class='editChoice editPanel' style='z-index:2000;position: static;'></div>";
   	 var editTag = $("#"+id+"abcd");
   	 editTag.after(div_tag_str);
        var editTagHeight = editTag.height();
        var editTagTop = editTag.position().top + editTagHeight + 20;
   	 
   	 $("#"+div_tag_str_id).css('top', editTagTop).removeClass('hide');
   	 FmodalLoadData1(div_tag_str_id, addChoiceUrl,iii);

        $("#add_survey_lost_display").hide();
        add_edit_one_choice_fun_flag=1;
   }


    function add_edit_one_question_fun(id){

      if(add_edit_one_choice_fun_flag!=0){
				app.showMsg("<?=Yii::t('frontend','modify_box_can_open_only_one')?>！");
				return ;
       } 
   	 var iii=_.findWhere(save_survey_objects, {id:id}) ;
   	 var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/dy-add-question'])?>";


   	 var div_tag_str_id=id+"wewewewe";
   	 $("#"+div_tag_str_id).remove();
   	 var div_tag_str="<div id="+div_tag_str_id+" class='editQuestion editPanel2' style='z-index:2000;position: static;'></div>";
   	 
   	 var editTag = $("#"+id+"abcd");
   	 editTag.after(div_tag_str);
        var editTagHeight = editTag.height();
        var editTagTop = editTag.position().top + editTagHeight + 20;

   	 $("#"+div_tag_str_id).css('top', editTagTop).removeClass('hide');
   	 FmodalLoadData1(div_tag_str_id, addChoiceUrl,iii);

   	 $("#add_survey_lost_display").hide();
   	 add_edit_one_choice_fun_flag=1;
  }


    function sortArrays(question_){

      var new_array_order={};
   	  $("#first_survey_row .questionGroup_quest").each(function(num) {
   		   var div_id=$(this).attr("id");
   		   if(div_id.indexOf('abcd')!=-1){
   			  div_id=div_id.substring(0,(div_id.length-4));
   		   }
   		   new_array_order[div_id]=num;		 
         });

   	    var new_array_=_.sortBy(question_, function(num){ 
        	 return  parseInt(new_array_order[num.id]) ;
         });

        return  new_array_;   
     }
        
   </script>   
          
    <script id="t9" type="text/template">
 <%_.each(data.datas, function(item) {%>
	<div class="row questionGroup_quest" id="<%=item.id%>">
       <div class="col-md-12 col-sm-12">
 <button type="button" class="btn btn-info btn-sm "  aria-haspopup="true" aria-expanded="false" style="width:90%">
                          <%=item.question_title%> <span class="caret stBtn"></span>
                        </button>
   		  <a  onclick="delete_new_survey_pagination(this,<%=item.id%>)" class="btn btn-sm pull-right"><?= Yii::t('common', 'delete_button') ?></a>
   	  </div>
   	</div>
      <%});%>
    </script>       
  
  
  
    
  