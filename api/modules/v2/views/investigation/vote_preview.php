
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
          <h4 class="modal-title" id="myModalLabel"><?=$result['answer_type_show'] ?></h4>
        </div>
        <div class="content">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="row questionGroup_quest">
                      <p>有效期：<?=$result['start_at'] ?>-<?=$result['end_at'] ?></p>
                      
                      
                    </div>
                     <div id="pre_first_vote_row"></div>
                    <hr/>
                    <div class=" centerBtnArea">
                      <a  class="btn btn-default btn-sm centerBtn" id="vote_motaikuang_return" style="width:20%">返回</a>
                     </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          
          <div class="c"></div>
        </div>
        
     <script type="text/javascript">
     var pre_vote_obj=pre_new_vote_datas;

     $(function(){

    	  
        if(pre_vote_obj.question_type=='0'){

        	   var lists=[];
			   lists.push(pre_vote_obj);
			   var edit_t6_templ=_.template($("#pre_vote_t6").html(),{variable: 'data'})({datas:lists});
			   $("#pre_first_vote_row").append(edit_t6_templ);
        }else{

        	   var lists=[];
			   lists.push(pre_vote_obj);
			   var edit_t8_templ=_.template($("#pre_vote_t8").html(),{variable: 'data'})({datas:lists});
			   $("#pre_first_vote_row").append(edit_t8_templ);
        }


        $("#vote_motaikuang_return").click(function(){
            console.log(pre_vote_obj.votemotaiid);
            
             app.hideAlert("#new_vote_preview_view");
             app.alert("#"+pre_vote_obj.votemotaiid)
             
        });
      });
     </script>   
     
  
    <script id="pre_vote_t6" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[单选] 问题:<%=item.question_title%></label>
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
    
<script id="pre_vote_t8" type="text/template">
 <%_.each(data.datas, function(item) {%>
       <div class="row questionGroup_quest">
                     <div class="col-md-12 col-sm-12">
	                      <div class="form-group form-group-sm">
	                        <label class="col-sm-9 control-label">[多选] 问题:<%=item.question_title%></label>
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