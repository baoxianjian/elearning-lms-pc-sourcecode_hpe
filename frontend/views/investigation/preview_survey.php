<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

?>


<!-- 预览问卷的弹出窗口 2-->
 
   
        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel_preview_survey"><?=Yii::t('common','investigation_view')?>(<?=Yii::t('frontend','questionnaire')?>)</h4>
        </div>
        <div class="content">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="row questionGroup_quest">
                      <p><?= Yii::t('common', 'time_validity') ?>：<label id="start_at_pre_id1228"></label>-<label id="end_at_pre_id1228"></label></p>
                      <h4 id="title_pre_id"></h4>
                     <p style="text-align:left" id="description_pre_id"></p>
                    </div>
                    
                      <div id="pre_first_survey_row"></div>
                   
                  
                    <div class=" centerBtnArea">
                     
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          	<div class="c"></div>
        </div>
        
         <script type="text/javascript">
        // var pre_survey_obj=pre_new_survey_datas.question;
         $(function(){


        	 $.ajax({
          	   async: false,
  			   url: "<?=Url::toRoute(['investigation/get-survey',])?>",
  			   data: {id:'<?=$id?>'},
  			   success: function(msg){



  				 if(msg.result.answer_type=='0'){
  					 $("#myModalLabel_preview_survey").html("<?=Yii::t('common','investigation_view')?>(<?=Yii::t('frontend','questionnaire_real_name')?>)");
  	  				 }  else{
  	  				 $("#myModalLabel_preview_survey").html("<?=Yii::t('common','investigation_view')?>(<?=Yii::t('frontend','questionnaire_pritave_name')?>)");

  	  	  		 }

  				 
 	  				
                 if(msg.result.start_at){
                	
                	  $("#start_at_pre_id1228").text(toYYYYMMDD(msg.result.start_at));
      				  $("#end_at_pre_id1228").text(toYYYYMMDD(msg.result.end_at));
                     
                     }else{
                    	 $("#start_at_pre_id1228").text("--");
         				  $("#end_at_pre_id1228").text("--");

                 }
  				

 				$("#title_pre_id").text(msg.result.title);
 				$("#description_pre_id").text("<?= Yii::t('frontend', 'brief') ?>:"+msg.result.description);
 				 
        	  var lists_arrs=msg.result.question;
        	  var first_pagination_code=_.findIndex(lists_arrs, {question_type:'3'}); 

        	 
        	 
        	  
              var pre_page_data="";
              var next_page_data="";

              var div_id="";

              var preview_num_show=1;

			   for(var i=0;i<lists_arrs.length;i++){

				   var lists_tmp=lists_arrs[i];
				   if(i==0){
					   var lists=[];
					   lists.push({id:lists_tmp.id});
					   div_id=lists_tmp.id;
					   pre_page_data=lists_tmp.id;
					   next_page_data=lists_tmp.id;
					   //分页div块
					   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
					   $("#pre_first_survey_row").append(fenye_t_templ);
				   }
				   
				   if(lists_tmp.question_type=='2'){
					   var lists=[];
					   lists_tmp.preview_num_show=preview_num_show;
					   lists.push(lists_tmp);
					   var edit_t7_templ=_.template($("#pre_t7").html(),{variable: 'data'})({datas:lists});
				       $("#"+div_id).append(edit_t7_templ);
				       preview_num_show++;
				   }else if(lists_tmp.question_type=='3'){
					  if(first_pagination_code==i){

						  next_page_data=lists_tmp.id;
						  //下步按钮
						   var lists_p=[{id:pre_page_data,nid:next_page_data}];
						   var next_page_t_templ=_.template($("#next_page_t").html(),{variable: 'data'})({datas:lists_p});
						  
						   $("#"+div_id).append(next_page_t_templ);

						   var lists=[];
						   lists.push({id:lists_tmp.id,hide:'hidden="hidden"'});
						   div_id=lists_tmp.id;
						 //分页div块
						   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
						   $("#pre_first_survey_row").append(fenye_t_templ);	 
					  }else{
						  if(lists_arrs.length!=3){
							  var lists_p=[{id:pre_page_data,uid:next_page_data,pid:next_page_data,nid:lists_tmp.id}];
							   pre_page_data=next_page_data;
							   next_page_data=lists_tmp.id;
							  //上下步按钮
							 
							   var pre_next_page_t_templ=_.template($("#pre_next_page_t").html(),{variable: 'data'})({datas:lists_p});
							   
							  
							   $("#"+div_id).append(pre_next_page_t_templ);
							 
							   var lists=[];
							   lists.push({id:lists_tmp.id,hide:'hidden="hidden"'});
							   div_id=lists_tmp.id;
							 //分页div块
							   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
							   $("#pre_first_survey_row").append(fenye_t_templ);

						  }
						  
					  }
				   }else if(lists_tmp.question_type=='1'){
					   
					   var lists=[];
					   lists_tmp.preview_num_show=preview_num_show;
					   lists.push(lists_tmp);
					   var edit_t8_templ=_.template($("#pre_t8").html(),{variable: 'data'})({datas:lists});
					   $("#"+div_id).append(edit_t8_templ);
					   preview_num_show++;
				   }else if(lists_tmp.question_type=='0'){
					  
					   var lists=[];
					   lists_tmp.preview_num_show=preview_num_show;
					   lists.push(lists_tmp);
					   var edit_t6_templ=_.template($("#pre_t6").html(),{variable: 'data'})({datas:lists});
					   $("#"+div_id).append(edit_t6_templ);
					   preview_num_show++;
				   }

				   if(i==lists_arrs.length-1&&i!=0&&first_pagination_code!=-1){
					   //上步按钮
					   var lists_p=[{id:pre_page_data,pid:next_page_data}];
					   var pre_page_t_templ=_.template($("#pre_page_t").html(),{variable: 'data'})({datas:lists_p});
						  
					   $("#"+div_id).append(pre_page_t_templ);
					   
				   }
			  }



  			  }
			 });   	  
			  
				  
        	 
         });

         function pre_page_fun(id,pid){

             $("#"+pid).hide();
             $("#"+id).show();
             app.refreshAlert("#new_survey_read_preview_view");
            
         }

         function next_page_fun(id,nid){

        	 $("#"+id).hide();
        	 $("#"+nid).show();

        	 app.refreshAlert("#new_survey_read_preview_view");

         }

     
         </script>   
        
   
   
    <script id="pre_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label"><%=item.preview_num_show%>.[<?=Yii::t('common', 'question_radio')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                       
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
    
<script id="pre_t8" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label"><%=item.preview_num_show%>.[<?=Yii::t('common', 'question_checkbox')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                        
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
  
 <script id="pre_t7" type="text/template">
  <%_.each(data.datas, function(item) {%>
	<div class="row questionGroup_quest">
       <div class="col-md-12 col-sm-12">
   		   <div class="form-group form-group-sm">
   		   <label class="col-sm-9 control-label"><%=item.preview_num_show%>. <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
   		     
   		   </div>
   		   </div>
   	    	<div class="col-md-12 col-sm-12">
            <%=item.question_description%>
   	 	    <textarea ><?=Yii::t('frontend', 'my_answer_is')?>....</textarea>
   	  </div>
   	</div>
      <%});%>
    </script>  
    
    <script id="fenye_t" type="text/template">
  <%_.each(data.datas, function(item) {%>
	<div id="<%=item.id%>" <%=item.hide%> >
      
   	</div>
      <%});%>
    </script>  
    
    <script id="next_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">
                      <a  class="btn btn-sm btn-success pull-right" onClick="next_page_fun('<%=item.id%>','<%=item.nid%>')"><?= Yii::t('frontend', 'next_step') ?></a>
                     </div>   
 <%});%>
    </script> 
    
     
    
     <script id="pre_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">               
                      <a  class="btn btn-sm btn-success pull-left" onClick="pre_page_fun('<%=item.id%>','<%=item.pid%>')"><?= Yii::t('frontend', 'last_step') ?></a>
                     </div>  
 <%});%>
    </script>         
    
     <script id="pre_next_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">                
                      <a  class="btn btn-sm btn-success pull-left" onClick="pre_page_fun('<%=item.id%>','<%=item.pid%>')"><?= Yii::t('frontend', 'last_step') ?></a>
                      <a  class="btn btn-sm btn-success pull-right" onClick="next_page_fun('<%=item.uid%>','<%=item.nid%>')"><?= Yii::t('frontend', 'next_step') ?></a>
                    </div>  
 <%});%>
    </script>          
    