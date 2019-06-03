<?php

use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;



?>


   
    <!-- 预览试卷弹出页面 -->

        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="paper_id">
          </h4>
          
         
        </div>
        <div class="body">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                   
                   
                       <div id="pre_first_survey_row"></div>
                   
                    <hr>
                    <div class="row">
                      <div class="centerBtnArea">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    
   
  
   <script type="text/javascript">
         var pre_paper_obj=pre_new_paper_datas;
         $(function(){
        	 $("#paper_id").html(pre_paper_obj.title);
        	 query();
			
         });



         function query(){

        	 $.ajax({   	

        	   async: false,
        	   type: "POST",
			   url: "<?=Url::toRoute(['exam-paper-manage/preview-paper',])?>",
			   data: pre_paper_obj,
			   success: function(msg){

				 
			
				//$("#title_pre_id").text(msg.result.title);
				//$("#description_pre_id").text("简介:"+msg.result.description);
				 
      	  var lists_arrs=msg.result.question;
      	  var first_pagination_code=_.findIndex(lists_arrs, {examination_question_type:'<?= Yii::t('frontend', 'exam_pq_fenye') ?>'});

      	 
      	 
      	  
            var pre_page_data="";
            var next_page_data="";

            var div_id="";

            var preview_num_show=1;

			   for(var i=0;i<lists_arrs.length;i++){

				   var lists_tmp=lists_arrs[i];
				   if(i==0){
					   var lists=[];
					   lists.push({id:"xxxx-yyyy"});
					  // div_id=lists_tmp.id;
					   div_id="xxxx-yyyy";
					   pre_page_data="xxxx-yyyy";
					   next_page_data="xxxx-yyyy";
					   //分页div块
					   var fenye_t_templ=_.template($("#fenye_t").html(),{variable: 'data'})({datas:lists});
					   $("#pre_first_survey_row").append(fenye_t_templ);
				   }
				   
				   if(lists_tmp.examination_question_type=='<?= Yii::t('frontend', 'exam_panduan') ?>'){
					   var lists=[];
					   lists_tmp.preview_num_show=preview_num_show;
					   lists.push(lists_tmp);
					   var edit_t7_templ=_.template($("#pre_t7").html(),{variable: 'data'})({datas:lists});
				       $("#"+div_id).append(edit_t7_templ);
				       preview_num_show++;
				   }else if(lists_tmp.examination_question_type=='<?= Yii::t('frontend', 'exam_pq_fenye') ?>'){
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
				   }else if(lists_tmp.examination_question_type=='<?= Yii::t('frontend', 'exam_duoxuan') ?>'){
					   
					   var lists=[];
					   lists_tmp.preview_num_show=preview_num_show;
					   lists.push(lists_tmp);
					   var edit_t8_templ=_.template($("#pre_t8").html(),{variable: 'data'})({datas:lists});
					   $("#"+div_id).append(edit_t8_templ);
					   preview_num_show++;
				   }else if(lists_tmp.examination_question_type=='<?= Yii::t('frontend', 'exam_danxuan') ?>'){
					  
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
        	 
             }    

         function pre_page_fun(id,pid){

             $("#"+pid).hide();
             $("#"+id).show();
             app.refreshAlert("#new_exam_paper_ques_preview_ui");
            
         }

         function next_page_fun(id,nid){

        	 $("#"+id).hide();
        	 $("#"+nid).show();

        	 app.refreshAlert("#new_exam_paper_ques_preview_ui");

         }

     
         </script>   
        
   
   
    <script id="pre_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-12 control-label"><%=item.preview_num_show%>.【<?=Yii::t('frontend', 'exam_danxuan')?>】 <?=Yii::t('frontend', 'exam_wenti')?>:<%=item.title%> ( )</label>

	                       
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
	                        <label class="col-sm-12 control-label"><%=item.preview_num_show%>.【<?=Yii::t('frontend', 'exam_duoxuan')?>】 <?=Yii::t('frontend', 'exam_wenti')?>:<%=item.title%> ( )</label>

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
                          <label class="col-sm-12 control-label">                       
                          <%=item.preview_num_show%>. 【<?=Yii::t('frontend', 'exam_panduanti')?>】<%=item.title%>
                          </label>
                        </div>
                      </div>
                      <div class="col-md-12 col-sm-12">
                        <div class="options">
                          <label style="margin-right:40px;">
                            <input type="radio" value="1"> <?=Yii::t('frontend', 'exam_zhengque')?>
                          </label>
                        </div>
                        <div class="options">
                          <label style="margin-right:40px;">
                            <input type="radio" value="1"> <?=Yii::t('frontend', 'exam_wrong')?>
                          </label>
                        </div>
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
                      <a  class="btn btn-sm btn-success centerBtn" style="width:20%" onClick="next_page_fun('<%=item.id%>','<%=item.nid%>')"><?=Yii::t('frontend', 'exam_xiayibu')?></a>
                     </div>
 <%});%>
    </script> 
    
     
    
     <script id="pre_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">               
                      <a  class="btn btn-sm btn-success centerBtn" style="width:20%" onClick="pre_page_fun('<%=item.id%>','<%=item.pid%>')"><?= Yii::t('frontend', 'last_step') ?></a>
                     </div>
 <%});%>
    </script>         
    
     <script id="pre_next_page_t" type="text/template">
<%_.each(data.datas, function(item) {%>
					<hr/>
                    <div class=" centerBtnArea">                
                      <a  class="btn btn-sm btn-success centerBtn" style="width:20%" onClick="pre_page_fun('<%=item.id%>','<%=item.pid%>')"><?= Yii::t('frontend', 'last_step') ?></a>
                      <a  class="btn btn-sm btn-success centerBtn" style="width:20%" onClick="next_page_fun('<%=item.uid%>','<%=item.nid%>')"><?= Yii::t('frontend', 'next_step') ?></a>
                    </div>
 <%});%>
    </script>    
  
  