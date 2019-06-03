<?php

?>
 <a  class="btn btn-sm pull-right cancelBtn" id="deleteEdiSurveyQuestionMain"><?= Yii::t('common', 'delete_button') ?></a>
   <form id="edit_survey_edit_question_form">
   
   
    <div class="row">
        <div class="col-md-12 col-sm-12">
          <h5><?=Yii::t('frontend', 'add_question')?></h5>
          <div class="form-group form-group-sm">
            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_stem')?></label>
            <div class="col-sm-9">
          	<input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_stem')])?>" class="form-control pull-left" type="text" id="edi_formGroupInputSmall_id" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
          	</div>
          </div>
        </div>
     </div>
     <div class="row">
        <div class="col-md-12 col-sm-12">
          <div class="form-group form-group-sm">
            <label class="col-sm-3 control-label"><?=Yii::t('common', 'description')?></label>
            <div class="col-sm-9">
              <textarea data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'description')])?>" class="form-control pull-left" id="edi_addQuestionContentId" type="text" style="width:80%"> </textarea>
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

var uuid= <?=  time() ?> +"wew" ; ;   
    $(function(){

        
          // 
          
          //window.validation_edit_survey_edit_question =app.creatFormValidation($("#edit_survey_edit_question_form"));   

  		 $("#addSurveyQuestionMain").click(function(){


  			if(!validation_edit_survey.validate()){

  				//$("#msm_alert_content").text("验证未通过");
     	       // app.alert("#foo");
                return;
   		    };
  	  		 
  	  		 var formGroupInputSmall_val=$("#edi_formGroupInputSmall_id").val();
  	  		 var addQuestionContentId_val=$("#edi_addQuestionContentId").val();
             var lists7=[];

  			 var lists7_obj={};
  			 lists7_obj.question_title=app.clean(formGroupInputSmall_val);
  			 lists7_obj.question_description=app.clean(addQuestionContentId_val);
  			 lists7_obj.question_type='2';
  			 lists7_obj.id=uuid;
  			 
  			 lists7.push(lists7_obj);
			 edit_survey_objects.push(lists7_obj);
  			 
  	         var t7_templ=_.template($("#t7").html(),{variable: 'data'})({datas:lists7});

  	         $("#edit_first_survey_row").append(t7_templ);


  	       var addQuestionUrl="<?=Yii::$app->urlManager->createUrl(['investigation/add-edit-question'])?>";
		   FmodalLoad("editNewQuestion_id", addQuestionUrl,true);
		   $('.editNewQuestion').removeClass('hide');
  	  		 
  	     });
  		


  		$("#deleteEdiSurveyQuestionMain").bind('click', function() {
  			 $(this).parent().addClass('hide');
  			 $(this).parent().empty();

  			 $("#edit_survey_preview").attr("disabled", false);
             $("#edit_survey_pub").attr("disabled", false);
             
  		});

  		
		  
      	 //end
    }); 


    function deleteEditSurveyQuest(node,id){
        
    	var iiiiii= _.findIndex(edit_survey_objects,{id:id});
    	edit_survey_objects.splice(iiiiii,1);
        
        $(node).parent().parent().parent().parent().remove();
        $("#edit_survey_lost_display").show();
     }
  
    
</script> 


<script id="t7" type="text/template">
  <%_.each(data.datas, function(item) {%>
	<div class="row questionGroup_quest questionTag" id="<%=item.id%>abc"  onClick="edit_one_question_fun('<%=item.id%>')">
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