<?php

?>


    <a  class="btn btn-sm pull-right cancelBtn" id="deleteNewSurveyQuestionMain"><?= Yii::t('common', 'delete_button') ?></a>
    <form id="add_survey_add_question_form">
    <div class="row">
        <div class="col-md-12 col-sm-12">
        <h5><?=Yii::t('frontend', 'add_question')?></h5>
          <div class="form-group form-group-sm">
            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_stem')?></label>
            <div class="col-sm-9">
          	<input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_stem')])?>" class="form-control pull-left" type="text" id="formGroupInputSmall_id" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
          	</div>
          </div>
        </div>
     </div>
     <div class="row">
        <div class="col-md-12 col-sm-12">
          <div class="form-group form-group-sm">
            <label class="col-sm-3 control-label"><?=Yii::t('common', 'description')?></label>
            <div class="col-sm-9">
              <textarea data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'description')])?>" class="form-control pull-left" id="addQuestionContentId" type="text" style="width:80%"> </textarea>
            </div>
          </div>
        </div>
     </div>
     <div class="row">
        <div class="col-md-12 col-sm-12 centerBtnArea">
          <a  class="btn btn-default btn-sm centerBtn" id="addSurveyQuestionMain" style="width:30%"><?=Yii::t('frontend', 'be_sure')?></a>
        </div>
     </div>                
</form>

<script type="text/javascript">

var uuid= <?=  time() ?>+'xy1' ;   
    $(function(){
          // 
            //window.validation_add_survey_add_question =app.creatFormValidation($("#add_survey_add_question_form"));   
          

  		 $("#addSurveyQuestionMain").click(function(){


  			if(!validation_new_survey.validate()){

  				//$("#msm_alert_content").text("验证未通过");
               // app.alert("#foo");	
                return;
   		    };
  	  		 
  	  		 var formGroupInputSmall_val=$("#formGroupInputSmall_id").val();
  	  		 var addQuestionContentId_val=$("#addQuestionContentId").val();
             var lists7=[];

  			 var lists7_obj={};
  			 lists7_obj.question_title=app.clean(formGroupInputSmall_val);
  			 lists7_obj.question_description=app.clean(addQuestionContentId_val);
  			 lists7_obj.question_type='2';
  			 lists7_obj.id=uuid;
  			 
  			 lists7.push(lists7_obj);
			 save_survey_objects.push(lists7_obj);
  			 
  	         var t7_templ=_.template($("#t7").html(),{variable: 'data'})({datas:lists7});

  	         $("#first_survey_row").append(t7_templ);


  	        var addQuestionUrl="<?=Yii::$app->urlManager->createUrl(['investigation/addquestion'])?>";
		    FmodalLoad("addNewQuestion_id", addQuestionUrl,true);
		    $('.addNewQuestion').removeClass('hide');
  	  		 
  	     });
  		


  		$("#deleteNewSurveyQuestionMain").bind('click', function() {
  			 $(this).parent().addClass('hide');
  			 $(this).parent().empty();

  			 $("#new_survey_preview").attr("disabled", false);
             $("#new_survey_pub").attr("disabled", false);
             $("#new_survey_storage").attr("disabled", false);
  		});

  		
		  
      	 //end
    }); 


    function deleteSurveyQuest(node,id,event){
       
    	var iiiiii= _.findIndex(save_survey_objects,{id:id});
    	save_survey_objects.splice(iiiiii,1);
        
        $(node).parent().parent().parent().parent().remove();
        $("#"+id+"wewewewe").remove();
        add_edit_one_choice_fun_flag=0;
        
        
        $("#add_survey_lost_display").show();
        event.stopPropagation();
     }
  
    
</script> 


<script id="t7" type="text/template">
  <%_.each(data.datas, function(item) {%>
	<div class="row questionGroup_quest editTag questionTag" id="<%=item.id%>abcd" onClick="add_edit_one_question_fun('<%=item.id%>')">
       <div class="col-md-12 col-sm-12">
   		   <div class="form-group form-group-sm">
   		   <label class="col-sm-9 control-label"><?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
   		     <div class="col-sm-3">
   		      <a  onclick="deleteSurveyQuest(this,'<%=item.id%>',event)" class="btn btn-sm pull-right"><?= Yii::t('common', 'delete_button') ?></a>
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