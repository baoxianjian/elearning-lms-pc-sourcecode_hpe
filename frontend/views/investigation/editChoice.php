<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

?>



  <a  class="btn btn-sm pull-right cancelBtn" id="deleteNewSurveyChoiceMain"><?= Yii::t('common', 'delete_button') ?></a>
  <form id="addChoiceFormId">
  
     	   <div class="row">
     	   <div class="col-md-12 col-sm-12">
     	    <h5><?=Yii::t('frontend', 'add_choose')?></h5>
     	    <div class="form-group form-group-sm">
     	     <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>
     	      <div class="col-sm-9">
     	      <input data-mode="COMMON"  data-condition="^\S{1,250}$" data-alert="<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'question_title')])?>" class="form-control pull-left" type="text" name="question_title" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
     	      </div>
     	    </div>
     	   </div>
     	   </div>
     	  
     	<div id="ed_choice_content_div"></div>
     	
     	
     	<div class="row">
	     	<div class="col-md-12 col-sm-12">
		       <div class="form-group form-group-sm" id="addChoice_question_type">
		     	<label class="col-sm-3 control-label"><?=Yii::t('frontend', 'radio_or_checkbox')?></label>
		     	<div class="col-sm-9">
		     	<div class="col-sm-9">
		     	<div class="btn-group" data-toggle="buttons">
		     	<label style="margin-right:68px;">
		     	<input type="radio" name="question_type" value="0" checked="checked"> <?=Yii::t('common', 'question_radio')?>
		     	</label>
		     	<label>
		     	<input type="radio" name="question_type" value="1"> <?=Yii::t('common', 'question_checkbox')?>
		     	</label>
		     	</div>
		     	</div>
		     	</div>
	     	   </div>
	     	</div>
     	</div>
     	<div class="row">
          <div class="col-md-12 col-sm-12 centerBtnArea">
     	     <a  id="addSurveyChoiceItem" class="btn btn-default btn-sm centerBtn" style="width:30%"><?=Yii::t('frontend', 'add_option')?></a>
     	     <a  class="btn btn-default btn-sm centerBtn" id="editSurveyChoiceMain" style="width:30%"><?=Yii::t('frontend', 'be_sure')?></a>
     	  </div>
     	</div>                          																										
</form>


<script type="text/javascript">

var uuid= <?=  time() ?>+"wew" ;   

    $(function(){
          // 
        // window.validation_edit_survey_edit_choice =app.creatFormValidation($("#addChoiceFormId"));   
          var lists5=[1,2,3,4];
          var ed_t5_templ=_.template($("#ed_t5").html(),{variable: 'data'})({datas:lists5});

          $("#ed_choice_content_div").append(ed_t5_templ);


  		  $("#addSurveyChoiceItem").click(function(){
  			 var lists5_1=[1];
  			 var ed_t5_templ2=_.template($("#ed_t5").html(),{variable: 'data'})({datas:lists5_1});
  			 $("#ed_choice_content_div").append(ed_t5_templ2);
  	  	   }); 

  		 $("#editSurveyChoiceMain").click(function(){

  			if(!validation_edit_survey.validate()){

               
              //  $("#msm_alert_content").text("验证未通过");
    	       // app.alert("#foo");
                return;
   		    };


  		     var question_type_val=$("#edit_survey_form_id input[name='question_type']:checked").val();
  		     var question_title_val=app.clean($("#edit_survey_form_id input[name='question_title']").val());
  		     var option_title_arrs=$("#edit_survey_form_id input[name='option_title']");


  		     if(option_title_arrs.length<2){
    		     
  		    	app.showMsg("<?=Yii::t('frontend', 'more_than_2_option')?>");
     	         //app.alertSmall("#foo");
    		     return;
    		 }

  		     var lists6=[];
  	  	     var lists6_obj={};
  	  	     lists6_obj.question_title=question_title_val;
  	  	     lists6_obj.question_type=question_type_val;
  	  	     lists6_obj.options=[];
  	  	     lists6_obj.id=uuid;
  	  	     
  	  	     
			 for(var i=0;i<option_title_arrs.length;i++){
				 lists6_obj.options.push({option_title:app.clean(option_title_arrs[i].value)});
		     }

			 lists6.push(lists6_obj);

		    

			 edit_survey_objects.push(lists6_obj);

  		     if(question_type_val==0){
				 var ed_t6_templ=_.template($("#ed_t6").html(),{variable: 'data'})({datas:lists6});

  	  	         $("#edit_first_survey_row").append(ed_t6_templ);
  	  	     }else{
  	  	         var ed_t8_templ=_.template($("#ed_t8").html(),{variable: 'data'})({datas:lists6});

	  	         $("#edit_first_survey_row").append(ed_t8_templ);
  	  	  	  }
  			 
  	  		
  		   var addChoiceUrl="<?=Yii::$app->urlManager->createUrl(['investigation/add-edit-choice'])?>";
    	   FmodalLoad("editNewChoice_id", addChoiceUrl,true);
    	   $('.editNewChoice').removeClass('hide');

  	      
  	  		 
  	  		 });
  		


  		$("#deleteNewSurveyChoiceMain").bind('click', function() {
  	           $(this).parent().addClass('hide');
  	         $(this).parent().empty();



      	   $("#edit_survey_preview").attr("disabled", false);
             $("#edit_survey_pub").attr("disabled", false);
             
  		});
		  
      	 //end
    }); 


    function deleteSurveyChoiceOption(node){
 	   
        $(node).parent().parent().parent().parent().remove();
        
    }

    function deleteEditSurveyOption(node,id){

    	var iiiiii= _.findIndex(edit_survey_objects,{id:id});
    	edit_survey_objects.splice(iiiiii,1);
       
        $(node).parent().parent().parent().parent().remove();
        $("#edit_survey_lost_display").show();
    }
    
</script>  

<script id="ed_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest editTag" id="<%=item.id%>abc" onClick="edit_one_choice_fun('<%=item.id%>')">
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
    
<script id="ed_t8" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest editTag" id="<%=item.id%>abc"  onClick="edit_one_choice_fun('<%=item.id%>')">
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

<script id="ed_t5" type="text/template">
 <%_.each(data.datas, function(item) {%>
    <div class="row">
	   		<div class="col-md-12 col-sm-12">
	   		<div class="form-group form-group-sm">
	   		<label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?></label>
	   		<div class="col-sm-9">
	   		<input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option')])?>" class="form-control pull-left" type="text" name="option_title" id="formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_content')?>" style="width:80%">
	     	  <a  onclick="deleteSurveyChoiceOption(this)" class="btn btn-default btn-sm glyphicon glyphicon-remove deleteBtn"></a>
	     	</div>
	     	</div>
	     	</div>
    </div>	

      <%});%>
</script>     