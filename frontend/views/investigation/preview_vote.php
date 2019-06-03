
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
          <h4 class="modal-title" id="myModalLabel_pre_vote"><?=Yii::t('common','investigation_view')?>(<?= Yii::t('frontend', 'vote') ?>)</h4>
        </div>
        <div class="content">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="row questionGroup_quest">
                      <p><?= Yii::t('common', 'time_validity') ?>：<label id="start_at_pre_id"></label>-<label id="end_at_pre_id"></label></p>
                      
                      
                    </div>
                     <div id="pre_pre_first_vote_row"></div>
                    <hr/>
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

     

     $(function(){


    	
         
         $.ajax({
       	   async: false,
			   url: "<?=Url::toRoute(['investigation/get-vote',])?>",
			   data: {id:'<?=$id?>'},
			   success: function(msg){
				   
				 
				var pre_vote_obj=msg.result;

				if(msg.result.answer_type=='0'){
 					 $("#myModalLabel_pre_vote").html("<?=Yii::t('common','investigation_view')?>(<?= Yii::t('frontend', 'vote_real_name') ?>)");
 	  				 }  else{
 	  				 $("#myModalLabel_pre_vote").html("<?=Yii::t('common','investigation_view')?>(<?= Yii::t('frontend', 'vote_private_name') ?>)");

 	  	  		 }

				if(msg.result.start_at){
					$("#start_at_pre_id").text(toYYYYMMDD(msg.result.start_at));
					$("#end_at_pre_id").text(toYYYYMMDD(msg.result.end_at));
				}else{
					$("#start_at_pre_id").text("--");
					$("#end_at_pre_id").text("--");
				}
				

				if(pre_vote_obj.question_type=='0'){

		        	   var lists=[];
					   lists.push(pre_vote_obj);
					   var edit_t6_templ=_.template($("#pre_pre_vote_t6").html(),{variable: 'data'})({datas:lists});
					   $("#pre_pre_first_vote_row").append(edit_t6_templ);
		        }else{

		        	   var lists=[];
					   lists.push(pre_vote_obj);
					   var edit_t8_templ=_.template($("#pre_pre_vote_t8").html(),{variable: 'data'})({datas:lists});
					   $("#pre_pre_first_vote_row").append(edit_t8_templ);
		        }
						   

					  
			   }
			 });
       

       
      });
     </script>   
     
  
    <script id="pre_pre_vote_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[<?=Yii::t('common', 'question_radio')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                       <div class="col-sm-3">
                            </div>
	                      </div>
                     </div>
                     <div class="col-md-12 col-sm-12">
 <%_.each(item.options, function(item_option) {%>
                       <div class="col-md-6 col-sm-6">
                          <label style="margin-right:40px;">
                            <input type="radio" name="addChoiceOptionTemp6" value="1"> <%=item_option.option_title%>
                          </label>
                        </div>
 <%});%>
                       
                        
                      </div>
        </div>
  <%});%>
    </script>
    
<script id="pre_pre_vote_t8" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[<?=Yii::t('common', 'question_checkbox')?>] <?=Yii::t('frontend', 'question')?>:<%=item.question_title%></label>
	                        <div class="col-sm-3">
                            </div>
	                      </div>
                     </div>
                     <div class="col-md-12 col-sm-12">
 <%_.each(item.options, function(item_option) {%>
                       <div class="col-md-6 col-sm-6">
                          <label style="margin-right:40px;">
                            <input type="checkbox" name="addChoiceOptionTemp8" value="1"> <%=item_option.option_title%>
                          </label>
                        </div>
 <%});%>
                       
                        
                      </div>
        </div>
  <%});%>


    </script>