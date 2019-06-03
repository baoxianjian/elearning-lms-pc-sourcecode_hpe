<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

?>


 
                    <a  class="btn btn-sm pull-right cancelBtn" id="deleteDyAddSurveyQuestionMain"><?= Yii::t('common', 'delete_button') ?></a>
                    <form id="dy_add_survey_edit_question">
                     <input type="hidden" value="<?=$result['id'] ?>" id="dy_edit_question_id"/>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <h5><?=Yii::t('frontend', 'modify_question')?></h5>
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_stem')?></label>
                          <div class="col-sm-9">
                            <input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_stem')])?>" id="formDyGroupInputSmall_id" value="<?=$result['question_title'] ?>"  class="form-control pull-left" type="text" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('common', 'description')?></label>
                          <div class="col-sm-9">
                            <textarea data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'description')])?>" id="addDyQuestionContentId" class="form-control pull-left" type="text" style="width:80%"><?=$result['question_description'] ?></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12 centerBtnArea">
                     
                        <a  id="addDySurveyQuestionMain" class="btn btn-default btn-sm centerBtn" style="width:30%"><?=Yii::t('frontend', 'be_sure')?></a>
                      </div>
                    </div>
                    
                    </form>
    <script type="text/javascript">


    $(function(){
          // 
          
          //  window.validation_add_survey_dy_edit_question =app.creatFormValidation($("#dy_add_survey_edit_question"));  

  		 $("#addDySurveyQuestionMain").click(function(){

  			if(!validation_new_survey.validate()){

  	            
  	           // $("#msm_alert_content").text("验证未通过");
  	           // app.alert("#foo");	 
  	            return;
  			 };
  	  		 
  	  		 var formGroupInputSmall_val=$("#formDyGroupInputSmall_id").val();
  	  		 var addQuestionContentId_val=$("#addDyQuestionContentId").val();
             var lists7=[];
              
  			 var lists7_obj={};
  			 lists7_obj.question_title=app.clean(formGroupInputSmall_val);
  			 lists7_obj.question_description=app.clean(addQuestionContentId_val);
  			 lists7_obj.question_type='2';
  			 lists7_obj.id=$("#dy_edit_question_id").val();

  			 
  			 
  			 lists7.push(lists7_obj);


  			 var iii=_.findWhere(save_survey_objects, {id:$("#dy_edit_question_id").val()}) ;
  			 iii.question_title=lists7_obj.question_title;
  			 iii.question_description=lists7_obj.question_description;
  			 iii.question_type=lists7_obj.question_type;
  			 

  			
  			 var pos= _.findIndex(save_survey_objects,{id:$("#dy_edit_question_id").val()});
  			//删除
  		     save_survey_objects.splice(pos,1);

  				//插入
  			 save_survey_objects.splice(pos,0,iii);
   			
			
  			 
  	         var dy_t7_templ=_.template($("#dy_add_t7").html(),{variable: 'data'})({datas:lists7});

  	       
  	         $("#"+lists7_obj.id+"abcd").empty();
	         $("#"+lists7_obj.id+"abcd").append(dy_t7_templ);


	         $("#"+lists7_obj.id+"wewewewe").remove();
  	         $("#add_survey_lost_display").show();
  	       add_edit_one_choice_fun_flag=0;
  	     });
  		


  		$("#deleteDyAddSurveyQuestionMain").bind('click', function() {
  			 
  			 $(this).parent().remove();
  			$("#add_survey_lost_display").show();
  			add_edit_one_choice_fun_flag=0;
  		});

  		
		  
      	 //end
    }); 


    function deleteDyAddSurveyQuest(node,id){
       
    	var iiiiii= _.findIndex(save_survey_objects,{id:id});
    	save_survey_objects.splice(iiiiii,1);
        
        $(node).parent().parent().parent().parent().remove();
        $("#add_survey_lost_display").show();
        add_edit_one_choice_fun_flag=0;
     }
  
    
</script> 


<script id="dy_add_t7" type="text/template">
  <%_.each(data.datas, function(item) {%>
	
       <div class="col-md-12 col-sm-12">
   		   <div class="form-group form-group-sm">
   		   <label class="col-sm-9 control-label"><?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
   		     <div class="col-sm-3">
   		      <a  onclick="deleteDyAddSurveyQuest(this,'<%=item.id%>')" class="btn btn-sm pull-right"><?= Yii::t('common', 'delete_button') ?></a>
   		     </div>
   		   </div>
   		   </div>
   	    	<div class="col-md-12 col-sm-12">
<%=item.question_description%>
   	 	<textarea ><?=Yii::t('frontend', 'my_answer_is')?>....</textarea>
   	  </div>
   	
      <%});%>
    </script>             