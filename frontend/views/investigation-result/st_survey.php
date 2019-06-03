<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

?>

<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>



<!-- 预览问卷的弹出窗口 -->
 <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel_id"></h4>
  </div>
   <div class="content">
  <div class="courseInfo">
                <div role="tabpanel" class="tab-pane active" id="teacher_info">
                  <div class=" panel-default scoreList">
                    <div class="panel-body">
  					
 			      <div  class="blockScreen hide">
                        <span class="glyphicon glyphicon-ok"></span>
                        <p><?= Yii::t('frontend', 'finish_investigation') ?></p>
                   </div>
                  <div class="infoBlock">
                     
                    <div class="row questionGroup_quest ">
                      <div class="hide">
                      <p><?= Yii::t('common', 'time_validity') ?>：<label id="start_at_pre_id"></label>-<label id="end_at_pre_id"></label></p>
                      </div>
                      <h4 id="title_pre_id"></h4>
                     <h4 id="description_pre_id"></h4>
                    </div>
                    
                      <div id="pre_first_survey_row"></div>
                   
                  
                
                    
                    <div class="col-md-12 col-sm-12 addNewChoice hide" id="course_play_survey_result_id">
                         
                        </div>
                  </div>
            
           </div>
        </div>
       </div>
      </div> 
     </div>    
         <script type="text/javascript">

     

         function get_play_survey_result(){
        	get_play_survey_result_show();
         }

         function get_play_survey_result_show(){

        	 $.get("<?=Url::toRoute(['investigation-result/get-single-sub-survey-result',])?>"+"?id=<?=$id?>&user_id=<?=$user_id?>",function(msg){


        		 $("#title_pre_id").text(msg.result.title);
        		 $("#myModalLabel_id").text(msg.result.title);
        		 
    		     $("#description_pre_id").text("<?= Yii::t('frontend', 'brief') ?>:"+msg.result.description);
        		 $("#pre_first_survey_row").empty();

        		 var lists_arrs=msg.result.question;
           	  survey_submit_get_obj=msg.result;
           	  var first_pagination_code=_.findIndex(lists_arrs, {question_type:'3'}); 

                 var pre_page_data="";
                 var next_page_data="";

                 var div_id="";

                 var preview_num_show=1;

   			   for(var i=0;i<lists_arrs.length;i++){

   				   var lists_tmp=lists_arrs[i];
   				   console.log(lists_tmp);
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

   	  				 $("#survey_play_submit_a").hide();
   				  
   			  }
           		 

        		 //end
            	 });
         }

         /*
         function toYYYYMMDD(val_date){
	    	 var ooo=val_date.replace('-','年');
				ooo=ooo.replace('-','月');
				ooo=ooo+"日";
				return ooo;
	      }
        */
         function toYYYYMMDD(val_date){
          return val_date;
        }
	      var survey_submit_get_obj;
	      var survey_submit_count=0;
	      
	      
	   
	      
         $(function(){


        	 
        	 get_play_survey_result();
           

        	 
				  
        	 
         });

         function survey_play_load(){
        	  console.log("----------");
        	    $('.addNewChoice').removeClass('hide');

        	    var url_result_1="<?=Url::toRoute(['investigation/single-course-play-survey-result',])?>"+"?id=<?=$id?>&investigation_id=";
        	 	  
        	    $('#course_play_survey_result_id').empty();
 	            $('#course_play_survey_result_id').load(url_result_1,function (){
 					
 	               });

             }

       

         function pre_page_fun(id,pid){

             $("#"+pid).hide();
             $("#"+id).show();
         }

         function next_page_fun(id,nid){

        	 $("#"+id).hide();
        	 $("#"+nid).show();

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
                            <input type="radio" <%=item_option.isCheck%> name="<%=item.id%>danxuanti" value="<%=item_option.kid%>" result="<%=item_option.option_title%>"> <%=item_option.option_title%>
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
                            <input type="checkbox" <%=item_option.isCheck%> name="<%=item.id%>duoxuanti" value="<%=item_option.kid%>" result="<%=item_option.option_title%>"> <%=item_option.option_title%>
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
   	 	    <textarea id="<%=item.id%>wendati"><%=item.option_result%></textarea>
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
             
    <script id="survey_submit_btn_id" type="text/template"> 
<%_.each(data.datas, function(item) {%> 
<div class="row">
    <div class="col-md-12 col-sm-12 centerBtnArea">
     <a onClick="survey_play_submit()" id="survey_play_submit_a" class="btn btn-info btn-sm centerBtn" style="width:30%" data-dismiss="modal" aria-label="Close"><?= Yii::t('frontend', 'exam_submit') ?></a>
   <a onClick="survey_play_load()" class="btn btn-default btn-sm centerBtn btnaddNewChoice" style="width:30%"><?=Yii::t('frontend', 'view_result')?></a>
 </div>
</div>
 <%});%>
 </script>