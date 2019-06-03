<?php


?>



                    <a  class="btn btn-sm pull-right editPanel_cancelBtn" id="deleteDySurveyChoiceMain"><?= Yii::t('common', 'delete_button') ?></a>
                    <form id="edit_dy_survey_form_id">
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                          <h5><?=Yii::t('frontend', 'modify_choose')?></h5>
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>
                          <div class="col-sm-9">
                            <input data-mode="COMMON"  data-condition="^(?!\s)(?!.*?\s$).{1,250}$" data-alert="<?=Yii::t('frontend', '{value}_limit_250_word_and_not_null',['value'=>Yii::t('frontend', 'question_title')])?>" class="form-control pull-left" name="question_title" value="<?=$result['question_title'] ?>" type="text" id="dy_formGroupInputSmall" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" style="width:80%">
                          </div>
                        </div>
                      </div>
                    </div>
                     <input type="hidden" value="<?=$result['id'] ?>" id="dy_edit_choice_id"/>
                     <div id="dy_choice_content_div">
                     <? foreach ($result['options'] as $opt): ?>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?></label>
                          <div class="col-sm-9">
                            <input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option')])?>" class="form-control pull-left"  name="option_title"  value="<?=$opt['option_title'] ?>" type="text"  placeholder="<?=Yii::t('frontend', 'input_content')?>" style="width:80%">
                            <a  onclick="delete_dy_edit_choice_option(this)" class="btn btn-default btn-sm glyphicon glyphicon-remove deleteBtn" title="<?= Yii::t('common', 'delete_button') ?>"></a>
                          </div>
                        </div>
                      </div>
                    </div>
                     
                     <? endforeach; ?>
                    
                  </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'radio_or_checkbox')?></label>
                          <div class="col-sm-9">
                            <div class="col-sm-9">
                              <div class="btn-group" data-toggle="buttons">
                                <label style="margin-right:68px;">
                                  <input type="radio" name="question_type" <?php if($result['question_type']==0) echo("checked");?> value="0"> <?=Yii::t('common', 'question_radio')?>
                                </label>
                                <label>  
                                  <input type="radio" name="question_type" <?php if($result['question_type']==1) echo("checked");?> value="1"> <?=Yii::t('common', 'question_checkbox')?>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12 centerBtnArea">
                        <a  id="editDySurveyChoiceMain" class="btn btn-default btn-sm centerBtn" style="width:30%"><?=Yii::t('frontend', 'be_sure')?></a>
                         <a  id="addDySurveyChoiceItem" class="btn btn-default btn-sm centerBtn" style="width:30%"><?=Yii::t('frontend', 'add_option')?></a>
                      </div>
                    </div>
</form>

<script id="dy_t5" type="text/template">
 <%_.each(data.datas, function(item) {%>
    <div class="row">
	   		<div class="col-md-12 col-sm-12">
	   		<div class="form-group form-group-sm">
	   		<label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?></label>
	   		<div class="col-sm-9">
	   		<input data-mode="COMMON"  data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option')])?>" class="form-control pull-left" type="text" name="option_title"  placeholder="<?=Yii::t('frontend', 'input_content')?>" style="width:80%">
	     	  <a  onclick="delete_dy_edit_choice_option(this)" class="btn btn-default btn-sm glyphicon glyphicon-remove deleteBtn" title="<?= Yii::t('common', 'delete_button') ?>"></a>
	     	</div>
	     	</div>
	     	</div>
    </div>


      <%});%>
</script> 

<script id="dy_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
      
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
      
  <%});%>
    </script>
    
<script id="dy_t8" type="text/template">
 <%_.each(data.datas, function(item) {%>
      
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
        
  <%});%>
    </script>  


  <script type="text/javascript">

  function delete_dy_edit_choice_option(node){
	  $(node).parent().parent().parent().parent().remove();
  }
  $(function(){


	 // window.validation_edit_survey_dy_edit_choice =app.creatFormValidation($("#edit_dy_survey_form_id"));  

    $("#deleteDySurveyChoiceMain").bind('click', function() {
       
        
        $(this).parent().remove();
        $("#edit_survey_lost_display").show();
        edit_one_choice_fun_flag=0;
	});


    $("#editDySurveyChoiceMain").click(function(){

    	 if(!validation_edit_survey.validate()){

    		// $("#msm_alert_content").text("验证未通过");
            // app.alert("#foo");	
             return;
		 };


	     var question_type_val=$("#edit_dy_survey_form_id input[name='question_type']:checked").val();
	     var question_title_val=app.clean($("#edit_dy_survey_form_id input[name='question_title']").val());
	     var option_title_arrs=$("#edit_dy_survey_form_id input[name='option_title']");


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
  	     lists6_obj.id=$("#dy_edit_choice_id").val();
	  	     
	  	     
		 for(var i=0;i<option_title_arrs.length;i++){
			 lists6_obj.options.push({option_title:app.clean(option_title_arrs[i].value)});
	     }

		 lists6.push(lists6_obj);
        
		 var iii=_.findWhere(edit_survey_objects, {id:$("#dy_edit_choice_id").val()}) ;
		 iii.question_title=lists6_obj.question_title;
		 iii.question_title=lists6_obj.question_title;
		 iii.question_type=lists6_obj.question_type;
		 iii.options=lists6_obj.options;
        
		 var pos= _.findIndex(edit_survey_objects,{id:$("#dy_edit_choice_id").val()});
		 //删除
	     edit_survey_objects.splice(pos,1);

		 console.log(iii);
			//插入
		 edit_survey_objects.splice(pos,0,iii);

		     if(question_type_val==0){
			 var dy_t6_templ=_.template($("#dy_t6").html(),{variable: 'data'})({datas:lists6});
			     $("#"+lists6_obj.id+"abc").empty();
	  	         $("#"+lists6_obj.id+"abc").append(dy_t6_templ);
	  	     }else{
	  	        var dy_t8_templ=_.template($("#dy_t8").html(),{variable: 'data'})({datas:lists6});
	  	        $("#"+lists6_obj.id+"abc").empty();
 	            $("#"+lists6_obj.id+"abc").append(dy_t8_templ);
	  	  	  }
			 
			
		     $("#"+lists6_obj.id+"xyxy").remove();
		     $("#edit_survey_lost_display").show();
		     edit_one_choice_fun_flag=0;
	  		 });


    $("#addDySurveyChoiceItem").click(function(){
			 var lists5_1=[1];
			 var dy_t5_templ2=_.template($("#dy_t5").html(),{variable: 'data'})({datas:lists5_1});
			 $("#dy_choice_content_div").append(dy_t5_templ2);
	 }); 

  });

  function deleteEditSurveyOption(node,id){

	  	var iiiiii= _.findIndex(edit_survey_objects,{id:id});
	  	edit_survey_objects.splice(iiiiii,1);
	      
	      $(node).parent().parent().parent().parent().remove();
	      $("#edit_survey_lost_display").show();
	      edit_one_choice_fun_flag=0;
	  }
  
  </script>