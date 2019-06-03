<?php

use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','exam_paper_management'),'url'=>['/exam-paper-manage/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','exam_paper_edit'),];
$this->params['breadcrumbs'][] = Yii::t('common','exam_paper_management');
$this->params['breadcrumbs'][] = '';

?>

<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>
<?= html::jsFile('/static/frontend/js/uuid.js') ?>


     <input name="category_id" type="hidden" id="category_id" value="<?=$examPaper['category_id'] ?>">
     <input name="title" type="hidden" id="title" value="<?=$examPaper['title'] ?>">
     <input name="description" type="hidden" id="description" value="<?=$examPaper['description'] ?>">
     <input name="examination_paper_level" type="hidden" id="examination_paper_level" value="<?=$examPaper['examination_paper_level'] ?>">
     <input name="examination_paper_type" type="hidden" id="examination_paper_type" value="<?=$examPaper['examination_paper_type'] ?>">
     
     
     <style>
<!--
.-query-list{
	width: 150px;
	float: left;
	margin-right: 5px;
}

-->
</style>
   
   <div class="container">
    <div class="row">
      <?= TBreadcrumbs::widget([
          'tag' => 'ol',
          'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
      ]) ?>
      <div class="col-md-6 col-sm-12 bd">
        <h5><?=Yii::t('frontend', 'exam_tiku')?></h5>
        <hr>
        <div class="panel-body lessonMachine lessonMachine_closing">
        <div  class="blockScreen hide">
                        <span class="glyphicon glyphicon-ok"></span>
                        <p><?=Yii::t('frontend', 'exam_pq_chengxuchulizhongqingdengdai')?></p>
        </div>
          <h5><?=Yii::t('frontend', 'exam_pq_suijidaoru')?></h5>
          <hr style="margin-bottom:20px;"/>
            <p><strong><?=Yii::t('frontend', 'exam_pq_anshumu')?>:</strong> <?=Yii::t('frontend', 'exam_pq_xuanze_special_00_start')?>
              <input type="text" class="scroeInput" id="exam_question_input" placeholder="1<?=Yii::t('frontend', 'exam_ti')?>"> <?=Yii::t('frontend', 'exam_pq_xuanze_special_00_end')?> <a id="exam_question_exec" href="javascript:void(0)" class="btn btn-sm btn-default pull-right"><?=Yii::t('frontend', 'exam_pq_zhixing')?></a></p>
            <p><strong><?=Yii::t('frontend', 'exam_pq_anzongfen')?>:</strong> <?=Yii::t('frontend', 'exam_pq_xuanze_special_01_start')?>
              <input type="text" class="scroeInput" id="exam_question_score_input" placeholder="100<?= Yii::t('frontend', 'point') ?>"> <?=Yii::t('frontend', 'exam_pq_xuanze_special_01_end')?> <a id="exam_question_score_exec" href="javascript:void(0)" class="btn btn-sm btn-default pull-right"><?=Yii::t('frontend', 'exam_pq_zhixing')?></a></p>
            <p><strong><?=Yii::t('frontend', 'exam_pq_annandu')?>:</strong> <?=Yii::t('frontend', 'exam_pq_jiandan')?>
              <input type="text" id="easy_input" class="scroeInput" placeholder="1<?=Yii::t('frontend', 'exam_ti')?>"> <?=Yii::t('frontend', 'exam_pq_zhongdeng')?>
              <input type="text" id="intermediate_input" class="scroeInput" placeholder="1<?=Yii::t('frontend', 'exam_ti')?>"> <?=Yii::t('frontend', 'exam_pq_kunnan')?>
              <input type="text" id="hard_input" class="scroeInput" placeholder="1<?=Yii::t('frontend', 'exam_ti')?>"> <a id="exam_question_level_exec" href="javascript:void(0)" class="btn btn-sm btn-default pull-right"><?=Yii::t('frontend', 'exam_pq_zhixing')?></a></p>
            <p><strong><?=Yii::t('frontend', 'exam_pq_antixing')?>:</strong> <?=Yii::t('common', 'question_radio')?>
              <input type="text" class="scroeInput" id="exam_question_type_danxuan_input" placeholder="1<?=Yii::t('frontend', 'exam_ti')?>"> <?=Yii::t('common', 'question_checkbox')?>
              <input type="text" class="scroeInput" id="exam_question_type_duoxuan_input" placeholder="1<?=Yii::t('frontend', 'exam_ti')?>"> <?=Yii::t('frontend', 'exam_panduan')?>
              <input type="text" class="scroeInput" id="exam_question_type_panduan_input" placeholder="1<?=Yii::t('frontend', 'exam_ti')?>"> 
              <a href="javascript:void(0)" id="exam_question_type_exec" class="btn btn-sm btn-default pull-right"><?=Yii::t('frontend', 'exam_pq_zhixing')?></a></p>

            <div class="paperListController">
              <a href="###" class="btn btn-sm closePanel"><?=Yii::t('frontend', 'exam_pq_shouqimianban')?></a>
            </div>
        </div>
        <div class="lessonMachine_outer hide"></div>
        <div class="panel-body lessonStorage">
          <div class="row paperListStatu">
            <form class="form-inline pull-left" action="" method="post" onsubmit="return false;">
              <div class="form-group">
                <div class="form-group">
                  <input type="text" id="search_question_cat" class="form-control" placeholder="<?=Yii::t('frontend', 'exam_pq_xuanzetiku')?>" style="width:150px;float:none" data-url="<?=Url::toRoute(['exam-paper-manage/get-question-category',])?>">
                </div>
                <div class="form-group">
                  <select id="choice_type" class="form-control" name="">
                    <option value=""><?=Yii::t('frontend', 'exam_pq_xuanzetixing')?></option>
                    <option value="999"><?=Yii::t('frontend', 'exam_pq_quanbutixing')?></option>
                    <option value="0"><?=Yii::t('frontend', 'exam_pq_danxuanti')?></option>
                    <option value="1"><?=Yii::t('frontend', 'exam_pq_duoxuanti')?></option>
                    <option value="3"><?=Yii::t('frontend', 'exam_pq_panduanti')?></option>
                  </select>
                </div>
                <input type="text" class="form-control" id="search_tag" placeholder="<?=Yii::t('frontend', 'exam_pq_souzhishidiantigan')?>" style="width:150px;float:none"  data-url="<?=Url::toRoute(['exam-paper-manage/get-tags',])?>">
                <button type="button" id="questions_left_id" class="btn btn-primary pull-right" style="margin-left:10px;"><?= Yii::t('frontend', 'top_search_text') ?></button>
              </div>
            </form>
          </div>
          <div class="row paperList">
            <ul id="left_questions_ul" class="paperListUl paperStore" style="height:563px; overflow-y:scroll;">
            
              <div class="centerBtnArea noData"  id="question_no_datas">
                <i class="glyphicon glyphicon-calendar"></i>
                <p><?=Yii::t('frontend', 'exam_pq_note_1')?></p>
              </div>
               
            </ul>
           
            <a href="javascript:void(0)" class="btn btn-sm pull-left selectAll"><?=Yii::t('frontend', 'exam_pq_quanxuan')?></a>
            <a href="javascript:void(0)" class="btn btn-sm pull-left unselectAll"><?=Yii::t('frontend', 'exam_pq_quxiaosuoxuan')?></a>
            <div class="paperListController">
              <a href="javascript:void(0)" id="addToRight" class="btn btn-sm removeList"><?=Yii::t('frontend', 'exam_pq_tianjiadaoshijuan')?> >> </a>
             
              <a href="###" class="btn btn-sm removeList lessonMachine_open" style="background: #337ab7; color:#fff;"><?=Yii::t('frontend', 'exam_pq_suijidaoru')?></a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-sm-12 bd">
        <h5>【<?=Yii::t('frontend', 'exam_shijuan')?>】<?=$examPaper['title'] ?></h5>
        <hr>
        <div class="panel-body">
          <div class="row paperListStatu">
            <form class="form-inline pull-left" action="" method="post" onsubmit="return false;">
              <div class="form-group">
                <div class="form-group">
                  <select class="form-control" name="" id="right_category_id" style="max-width:120px">
                    <option value=""><?=Yii::t('frontend', 'exam_pq_guolvtiku')?></option>
                    <option value="999"><?=Yii::t('frontend', 'exam_pq_quanbutiku')?></option>
                  </select>
                </div>
                <div class="form-group">
                  <select class="form-control" name="" id="right_choice_type">
                   
                    <option value="999"><?=Yii::t('frontend', 'exam_pq_quanbutiku')?></option>
                    <option value="0"><?=Yii::t('frontend', 'exam_pq_danxuanti')?></option>
                    <option value="1"><?=Yii::t('frontend', 'exam_pq_duoxuanti')?></option>
                    <option value="3"><?=Yii::t('frontend', 'exam_pq_panduanti')?></option>
                  </select>
                </div>
                <input type="text" id="right_search_tag" class="form-control" placeholder="<?=Yii::t('frontend', 'exam_pq_souzhishidian')?>" style="width:150px;float:none" data-url="<?=Url::toRoute(['exam-paper-manage/get-tags',])?>">
                <button type="button" id="question_right_id" class="btn btn-primary pull-right" style="margin-left:10px;"><?= Yii::t('frontend', 'top_search_text') ?></button>
              </div>
            </form>
          </div>
          <div class="row paperList paperListId">
            <ul class="paperListUl" style="height:563px;" id="right_questions_ul">
              <div class="centerBtnArea noData" id="question_right_no_datas">
                <i class="glyphicon glyphicon-calendar"></i>
                <p><?=Yii::t('common', 'no_data')?></p>
              </div>
            </ul>
            <a href="javascript:void(0)" class="btn btn-sm pull-left selectAll"><?=Yii::t('frontend', 'exam_pq_quanxuan')?></a>
            <a href="javascript:void(0)" class="btn btn-sm pull-left unselectAll" ><?=Yii::t('frontend', 'exam_pq_quxiaosuoxuan')?></a>
            <a href="javascript:void(0)" class="btn btn-sm pull-right" id="addRightCenter"><?=Yii::t('frontend', 'exam_pq_addfenyefu')?></a>

            <div class="paperListController">
              <a href="javascript:void(0)" class="btn btn-sm removeList" id="removeRightQuestion">
                << <?=Yii::t('frontend', 'exam_pq_yichu')?></a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-12 bd" style="margin-bottom:50px;">
        <div class="row paperListStatu" style="background:#fff; letter-spacing:5px;">
          <p id="total_stat"><?=Yii::t('frontend', 'exam_pq_note_2')?>.</p>
        </div>
        <div class="centerBtnArea">
          <a href="###" class="btn btn-sm centerBtn" style="width:10%" id="new_exam_paper_submit"><?=Yii::t('frontend', 'exam_submit')?></a>
          <a href="###" class="btn btn-sm centerBtn" style="width:10%" id="preview_id"><?=Yii::t('frontend', 'exam_preview')?></a>

        </div>
        
      </div>
    </div>
  </div>
  
  
  <!-- 预览的试卷-->
 <div class="ui modal" id="new_exam_paper_ques_preview_ui" >
 </div>
 
 <!-- 提交 -->
  <div class="ui modal" id="new_exam_paper_submit_ui" >
 </div>
 
 
  <div id="random_choice_show" class="ui modal">
		<div class="header">
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<?=Yii::t('frontend', 'exam_pq_zujuantishi')?>
		</div>
		<div class="content" id="random_choice_show_content">
			
		</div>
		
	</div>
   
    <script type="text/javascript">

    
  
    var to_left_content=true;;
    var loading = false;
    var total_question_num=0;
    var total_question_score=0;

    var page_size=15;
    var question_page = 1;
    var question_url = "<?=Url::toRoute(['exam-paper-manage/get-left-questions',])?>" + "?page=";
    var question_end = false;
    
    var question_new = false;
    var current_time=Date.parse(new Date())/1000;

    var mission_id=guid();
    var validate_right_list_num=500;   
    var right_category_datas;

    function on_more_datas(node){        
            question_page++;
            var url=question_url + question_page+"&current_time="+current_time
            loadTab(url);
    }

     var query_flag=false;

     function queryRightList(){

    	 console.log("queryRightList");
 	   	 var right_choice_mark;
 	   	 var right_choice_val=$("#right_choice_type").val();
 	   	 if(right_choice_val=='999'){
 	   		right_choice_mark="all";
 		 }
 		 if(right_choice_val=='0'){
 			right_choice_mark="<?=Yii::t('common', 'question_radio')?>";
 	     }
 		 if(right_choice_val=='1'){
  			right_choice_mark="<?=Yii::t('common', 'question_checkbox')?>";
  	     }
 		 if(right_choice_val=='3'){
  			right_choice_mark="<?= Yii::t('frontend', 'exam_panduan') ?>";
  	     }

    	 $("#right_questions_ul li").each(function(index, domEle) {
    		 $(this).show();
             if($(this).find("hr").length==1){
            	 if(right_choice_mark!='all'||$("#right_category_id").val()!='999'||$("#right_search_tag").val()!=''){
            		 $(this).hide();
                 }
            	 
             }else{

                 var obj={};
                 var category_id=$(this).find("input[type=checkbox]").attr("data-id5");
                 var tag_id=$(this).find("input[type=checkbox]").attr("data-id6");
                 var examination_question_type=$(this).find("input[type=checkbox]").attr("data-id2");
                
                 if(right_choice_mark!='all'&&right_choice_mark!=examination_question_type){
                	 $(this).hide();    
                 }
                
                 if($("#right_category_id").val()!='999'&&$("#right_category_id").val()!=category_id){
                	 $(this).hide();    
          		 }

                 if($("#right_search_tag").val()){
         		 	 var taglist=queryList2.get();	

         		 	 if(tag_id.indexOf(taglist.uid)){
         		 		$(this).hide();  
             		 }	 	 			     
         	     }	  
                
             }
    	
         });    	
     	
     }

    function queryLeftList(){ 
    	 query_flag=false;   	
	   	 var params_obj={};
	   	 params_obj.mission_id=mission_id;
	   	 current_time=Date.parse(new Date())/1000;
	   	 var url=question_url+1+"&current_time="+current_time;

	   	 if($("#search_tag").val()){
	   		var taglist=queryList1.get();	
	   		if(_.isEmpty(taglist)){
	   		     params_obj.tag_select_id= $("#search_tag").val();
			     query_flag=true;
		   	} else{
		   	     params_obj.tag_select_id= taglist.uid;	
			     query_flag=true;  	
			}
	   	  
		 }

		 if($("#search_question_cat").val()){
			
		 	 var categlist=queryList.get();		 	
			 params_obj.category_select_id= categlist.uid;	
			 query_flag=true;		     
	     }	  
	   	 
	   	 var choice_type=$("#choice_type").val();
	   	 if(choice_type){
	   		 params_obj.examination_question_type= choice_type;
	   		 query_flag=true;	
		 }

		 if(!query_flag){
			 app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_3')?>");
			 return ;	
		  }
	   	
	   	 $.ajax({
				   type: "POST",
				   url: url,
				   data: params_obj,
				   success: function(msg){
					 $("#left_questions_ul").empty();
					 question_page=1;					 
					 var t1_temp=_.template($("#t1").html(),{variable: 'data'})({datas:msg.result});
					 $("#question_no_datas").hide();					
			         $("#left_questions_ul").append(t1_temp);

			         var t4_temp=_.template($("#t4").html(),{variable: 'data'})({datas:[1]});
			         $("#left_questions_ul").append(t4_temp);
			         
			        
					 if(parseInt(msg.result.length)<page_size){					 
						 $("#more_datas").hide();
				     }

					 if(msg.result.length==0){
			        	 var t6_temp=_.template($("#t6").html(),{variable: 'data'})({datas:[1]});		    		 
			             $("#left_questions_ul").append(t6_temp);	
				     }

					 paperList_bind_event();
				     
				   }
		     });

     }

    function loadTab(ajaxUrl) {
    	 var params_obj={};
	   	 params_obj.mission_id=mission_id;

	   	 if($("#search_tag").val()){
		   		var taglist=queryList1.get();	   	
			    params_obj.tag_select_id= taglist.uid;	
			    query_flag=true;	    
			 }

		 if($("#search_question_cat").val()){
			 	 var categlist=queryList.get();		 	
				 params_obj.category_select_id= categlist.uid;	
				 query_flag=true;		     
		 }	  
		   	 
		 var choice_type=$("#choice_type").val();
		 if(choice_type){
		   		 params_obj.examination_question_type= choice_type;
		   		 query_flag=true;	
	     }
	   	
	   	 
	   	 $.ajax({
				   type: "POST",
				   url: ajaxUrl,
				   data: params_obj,
				   success: function(msg){
					
					 if(msg.result.length<page_size){
							 question_end = true;
							 $("#more_datas").hide();
					 }
					
					 var t1_temp=_.template($("#t1").html(),{variable: 'data'})({datas:msg.result});
					 $("#question_no_datas").hide();
			         $("#more_datas").before(t1_temp);
			         paperList_bind_event();
				   }
		     });
     }
     
    var queryList;
    var queryList1;
    var queryList2;
    var shuiji_num=100;

    function validate_right_list_num_fun(num){
    	if((parseInt($("#right_questions_ul li").length)+parseInt(num))>validate_right_list_num){
    		 app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_special_1_start')?>"+validate_right_list_num+"<?=Yii::t('frontend', 'exam_pq_note_special_1_end')?>");
    		 return false;
        }else{
             return true;
        }
    }

    function exam_question_exec_fun(){
    	console.log("exam_question_exec_fun");
         var exam_question_input_val=$("#exam_question_input").val();
         if(!exam_question_input_val){
        	 app.showMsg("<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'number')])?>");
         }else{
        	 if(!validate_number("exam_question_input","<?=Yii::t('frontend', 'exam_pq_note_5')?>")){
        		 return ;
             }

        	 if(!validate_right_list_num_fun(exam_question_input_val)){
        		 return ;
             };

             if(exam_question_input_val>shuiji_num){
            	 app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_special_2_start')?>"+shuiji_num+"<?=Yii::t('frontend', 'exam_pq_note_special_2_end')?>");
            	 return ;
              }

             showBlockScreen();
             var categlist_val="";
             if($("#search_question_cat").val()){
     			
    		 	 var categlist=queryList.get();		 	
    		 	categlist_val= categlist.uid;	
    			     
    	     }	  
             
             var url="<?=Url::toRoute(['exam-paper-manage/get-exam-question-exec-data',])?>"+"?mission_id="+mission_id+"&question_num="+exam_question_input_val+"&categlist_val="+categlist_val;
        	 $.get(url,function(msg){
        		 hideBlockScreen();
                 if(msg.result=='false'){
                     $("#random_choice_show_content").empty();
                     var num1='<font color="green">'+0+'</font>';
                     var num2='<font color="red">'+0+'</font>';
                     var dat={total:exam_question_input_val,num1:0,num2:0};
                	 var t7_temp=_.template($("#t7").html(),{variable: 'data'})({datas:[dat]});		    		 
		             $("#random_choice_show_content").append(t7_temp);	
                	 app.alert("#random_choice_show")
                 }else{
                	 $("#random_choice_show_content").empty();
                	 var num2=parseInt(exam_question_input_val)-parseInt(msg.result.message);
                	 var num1='<font color="green">'+msg.result.message+'</font>';
                	 num2='<font color="red">'+num2+'</font>';
                	 var dat={total:exam_question_input_val,num1:num1,num2:num2};
                	 var t7_temp=_.template($("#t7").html(),{variable: 'data'})({datas:[dat]});		    		 
		             $("#random_choice_show_content").append(t7_temp);	
                	 app.alert("#random_choice_show")
                	 get_exec_datas(msg.result.list);              	 
                 }

             });
         }
    }

    function exam_question_score_exec_fun(){
    	var exam_question_score_input_val=$("#exam_question_score_input").val();
    	if(!exam_question_score_input_val){
       	  app.showMsg("<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'courseware_default_credit')])?>");
        }else{
       	    if(!validate_number("exam_question_score_input","<?=Yii::t('frontend', 'exam_pq_note_7')?>")){
       		 return ;
            }


       	  if(!validate_right_list_num_fun(exam_question_score_input_val)){
    		 return ;
          };

       	   if(exam_question_score_input_val>shuiji_num){
        	 app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_special_3_start')?>"+shuiji_num+"<?=Yii::t('frontend', 'exam_pq_note_special_3_end')?>");

        	 return ;
           }
       	   showBlockScreen();

       	 var categlist_val="";
         if($("#search_question_cat").val()){
 			
		 	 var categlist=queryList.get();		 	
		 	categlist_val= categlist.uid;	
			     
	     }	  
       	   
            var url="<?=Url::toRoute(['exam-paper-manage/get-exam-score-exec-data',])?>"+"?mission_id="+mission_id+"&question_score="+exam_question_score_input_val+"&categlist_val="+categlist_val;
       	 $.get(url,function(msg){
       		    hideBlockScreen();
                if(msg.result=='false'){
                	$("#random_choice_show_content").empty();
                	var num1='<font color="green">'+0+'</font>';
                    var num2='<font color="red">'+0+'</font>';
                    var dat={total:exam_question_score_input_val,num1:num1,num2:num2};
               	    var t8_temp=_.template($("#t8").html(),{variable: 'data'})({datas:[dat]});		    		 
		            $("#random_choice_show_content").append(t8_temp);	
               	    app.alert("#random_choice_show")
                }else{
                	 $("#random_choice_show_content").empty();
                	
                	 var num2=parseFloat(exam_question_score_input_val)-parseFloat(msg.result.message);
                	 var num1='<font color="green">'+msg.result.message+'</font>';
                     var num2='<font color="red">'+num2+'</font>';
                	 var dat={total:exam_question_score_input_val,num1:num1,num2:num2};
                	 var t8_temp=_.template($("#t8").html(),{variable: 'data'})({datas:[dat]});		    		 
		             $("#random_choice_show_content").append(t8_temp);	
                	 app.alert("#random_choice_show")
                	 get_exec_datas(msg.result.list);                 	 
                }

            });
        }
    	
    }

    function exam_question_level_exec_fun(){
    	console.log("exam_question_level_exec_fun");
       var question_level_exec_flag=3;
       var question_level_exec_flag1=3;
      
       var easy_input_val=$("#easy_input").val();
       var hard_input_val=$("#hard_input").val();
       var intermediate_input_val=$("#intermediate_input").val();

       var categlist_val="";
       if($("#search_question_cat").val()){
			
		 	 var categlist=queryList.get();		 	
		 	categlist_val= categlist.uid;	
			     
	     }	  
       
       var url="<?=Url::toRoute(['exam-paper-manage/get-exam-question-level-data',])?>"+"?mission_id="+mission_id+"&categlist_val="+categlist_val;
       var total_submit_num=0;

   	
       if(easy_input_val!=""){
    	   if(!validate_number("easy_input","<?=Yii::t('frontend', 'exam_pq_note_8')?>")){
    		   question_level_exec_flag=question_level_exec_flag-1;
    		   easy_input_val="---";
    		   question_level_exec_flag1=question_level_exec_flag1-1;
           }else{
        	   url=url+"&&easy_input_val="+easy_input_val;
        	   total_submit_num=total_submit_num+parseInt(easy_input_val);
           }
    	 
       }else{
    	   
    	   question_level_exec_flag=question_level_exec_flag-1;
       }

       if(hard_input_val!=""){
    	   if(!validate_number("hard_input","<?=Yii::t('frontend', 'exam_pq_note_9')?>")){
    		   question_level_exec_flag=question_level_exec_flag-1;
    		   hard_input_val="---";
    		   question_level_exec_flag1=question_level_exec_flag1-1;
           }else{
        	   url=url+"&&hard_input_val="+hard_input_val;
        	   total_submit_num=total_submit_num+parseInt(hard_input_val);
           }
    	   
       }else{
    	   question_level_exec_flag=question_level_exec_flag-1;
       }

       if(intermediate_input_val!=""){
    	   if(!validate_number("intermediate_input","<?=Yii::t('frontend', 'exam_pq_note_10')?>")){
        	  
    		   question_level_exec_flag=question_level_exec_flag-1;
    		   intermediate_input_val="---";
    		   question_level_exec_flag1=question_level_exec_flag1-1;
           }else{
    	      url=url+"&&intermediate_input_val="+intermediate_input_val;
    	      total_submit_num=total_submit_num+parseInt(intermediate_input_val);
           }
       }else{
    	   question_level_exec_flag=question_level_exec_flag-1;
       }

       if(question_level_exec_flag1!=3){
            return;    
       }


       if(!validate_right_list_num_fun(total_submit_num)){
  		 return ;
       };
     
       if(total_submit_num>shuiji_num){
    	   app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_special_4_start')?>"+shuiji_num+"<?=Yii::t('frontend', 'exam_pq_note_special_4_end')?>");
    	   return;
       }

       if(question_level_exec_flag==0){
    	   setTimeout(function(){
   			    app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_11')?>")
     		 }, 2000);  
       }else{
    	   showBlockScreen();
    	   $.get(url,function(msg){
    		   hideBlockScreen();
    		   var dat={};
               var fanhuijieguo=3;
               var fanhuijieguo_msg="";
               if(msg.result.fault.easy_input=='false'){
            	   dat.jd='<font color="red">'+0+'</font>';
            	   fanhuijieguo=fanhuijieguo-1;
               }else{
                   if(parseInt(easy_input_val)>parseInt(msg.result.fault.easy_input)){
                	   dat.jd='<font color="red">'+msg.result.fault.easy_input+'</font>';
                   }else{
                       if(_.isUndefined(msg.result.fault.easy_input)){
                    	   dat.jd='---';
                        }else{
                        	   dat.jd='<font color="green">'+msg.result.fault.easy_input+'</font>';
                        }
                	
                   }
            	  
                };
              
               if(msg.result.fault.intermediate_input=='false'){
            	   dat.zd='<font color="red">'+0+'</font>';
            	   fanhuijieguo=fanhuijieguo-1;
               }else{
            	   if(parseInt(intermediate_input_val)>parseInt(msg.result.fault.intermediate_input)){
            	  	   dat.zd='<font color="red">'+msg.result.fault.intermediate_input+'</font>';
            	   }else{
            		   if(_.isUndefined(msg.result.fault.intermediate_input)){
                    	   dat.zd='---';
                        }else{
                        	 dat.zd='<font color="green">'+msg.result.fault.intermediate_input+'</font>';
                        }
            		  
                   }
               }
               if(msg.result.fault.hard_input=='false'){
            	   dat.kn='<font color="red">'+0+'</font>';
            	   fanhuijieguo=fanhuijieguo-1;
               }else{
            	   if(parseInt(hard_input_val)>parseInt(msg.result.fault.hard_input)){
            	  	  dat.kn='<font color="red">'+msg.result.fault.hard_input+'</font>';
            	   }else{
            		   if(_.isUndefined(msg.result.fault.hard_input)){
                    	   dat.kn='---';
                        }else{
                        	 dat.kn='<font color="green">'+msg.result.fault.hard_input+'</font>';
                        }
            		  
                    }
               };

                   if(easy_input_val){
                	   dat.total_jd=easy_input_val;
                   }else{
                       dat.total_jd="---";
                       dat.jd="---";
                   }

                   if(intermediate_input_val){
                	   dat.total_zd=intermediate_input_val;
                   }else{
                	   dat.total_zd="---";
                	   dat.zd="---";
                   }

                   if(hard_input_val){
                	   dat.total_kn=hard_input_val;
                   }else{
                	   dat.total_kn="---";
                	   dat.kn="---";
                   }
         	     
                   $("#random_choice_show_content").empty();
         	     var t10_temp=_.template($("#t10").html(),{variable: 'data'})({datas:[dat]});		    		 
	             $("#random_choice_show_content").append(t10_temp);	
         	     app.alert("#random_choice_show")

               if(fanhuijieguo==0){
					return;
               }
               
               get_exec_datas(msg.result.result);    

           });
       }
    }

    function exam_question_type_exec_fun(){
        console.log("exam_question_type_exec_fun");
    	var question_type_exec_flag=3;
    	var question_type_exec_flag1=3;
        var exam_question_type_danxuan_input_val=$("#exam_question_type_danxuan_input").val();
        var exam_question_type_duoxuan_input_val=$("#exam_question_type_duoxuan_input").val();
        var exam_question_type_panduan_input_val=$("#exam_question_type_panduan_input").val();

        var categlist_val="";
        if($("#search_question_cat").val()){
 			
 		 	 var categlist=queryList.get();		 	
 		 	categlist_val= categlist.uid;	
 			     
 	     }	  
        
        var url="<?=Url::toRoute(['exam-paper-manage/get-exam-question-type-data',])?>"+"?mission_id="+mission_id+"&categlist_val="+categlist_val;
        var total_submit_num=0;
    	
        if(exam_question_type_danxuan_input_val!=""){
        	 if(!validate_number("exam_question_type_danxuan_input","<?=Yii::t('frontend', 'exam_pq_note_12')?>")){

        		 question_type_exec_flag=question_type_exec_flag-1;
        		 exam_question_type_danxuan_input_val="---";
        		 question_type_exec_flag1=question_type_exec_flag1-1;
             }else{
     	   		url=url+"&&exam_question_type_danxuan_input_val="+exam_question_type_danxuan_input_val;
     	   	    total_submit_num=total_submit_num+parseInt(exam_question_type_danxuan_input_val);
             }
        }else{
        	question_type_exec_flag=question_type_exec_flag-1;
        }

        if(exam_question_type_duoxuan_input_val!=""){
        	 if(!validate_number("exam_question_type_duoxuan_input","<?=Yii::t('frontend', 'exam_pq_note_13')?>")){
        		 question_type_exec_flag=question_type_exec_flag-1;
        		 exam_question_type_duoxuan_input_val="---";
        		 question_type_exec_flag1=question_type_exec_flag1-1;
              }else{
     	  		 url=url+"&&exam_question_type_duoxuan_input_val="+exam_question_type_duoxuan_input_val;
     	  		 total_submit_num=total_submit_num+parseInt(exam_question_type_duoxuan_input_val) ;
             }
        }else{
        	question_type_exec_flag=question_type_exec_flag-1;
        }

        if(exam_question_type_panduan_input_val!=""){
        	 if(!validate_number("exam_question_type_panduan_input","<?=Yii::t('frontend', 'exam_pq_note_14')?>")){
        		 question_type_exec_flag=question_type_exec_flag-1;
        		 exam_question_type_panduan_input_val="---";
        		 question_type_exec_flag1=question_type_exec_flag1-1;
              }else{
     	 		  url=url+"&&exam_question_type_panduan_input_val="+exam_question_type_panduan_input_val;
     	 		  total_submit_num=total_submit_num+parseInt(exam_question_type_panduan_input_val) ;
             }
        }else{
        	question_type_exec_flag=question_type_exec_flag-1;
        }

        if(question_type_exec_flag1!=3){
        	return ;
        }


        if(!validate_right_list_num_fun(total_submit_num)){
     		 return ;
        };

        if(total_submit_num>shuiji_num){
        	app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_special_5_start')?>"+shuiji_num+"<?=Yii::t('frontend', 'exam_pq_note_special_5_end')?>");
        	return;
        }

        if(question_type_exec_flag==0){
        	  setTimeout(function(){
  	    	    app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_15')?>");
  	   		 }, 2000);  
        }else{

        	showBlockScreen(); 
     	   $.get(url,function(msg){
     		    hideBlockScreen();
     		    var dat={};
                var fanhuijieguo=3;
                var fanhuijieguo_msg="";
                if(msg.result.fault.danxuan_input=='false'){
                   dat.dx='<font color="red">'+0+'</font>';
             	   fanhuijieguo=fanhuijieguo-1;
                }else{
                	if(parseInt(exam_question_type_danxuan_input_val)>parseInt(msg.result.fault.danxuan_input)){
                		dat.dx='<font color="red">'+msg.result.fault.danxuan_input+'</font>';
                	}else{
                		 if(_.isUndefined(msg.result.fault.danxuan_input)){
                        	   dat.dx='---';
                            }else{
                          	  dat.dx='<font color="green">'+msg.result.fault.danxuan_input+'</font>';
                          }
                     }
                };
               
                if(msg.result.fault.duoxuan_input=='false'){
                   dat.duox='<font color="red">'+0+'</font>';
             	   fanhuijieguo=fanhuijieguo-1;
                }else{
                	if(parseInt(exam_question_type_duoxuan_input_val)>parseInt(msg.result.fault.duoxuan_input)){
                	     dat.duox='<font color="red">'+msg.result.fault.duoxuan_input+'</font>';
                	}else{
                		 if(_.isUndefined(msg.result.fault.duoxuan_input)){
                        	   dat.duox='---';
                            }else{
                          	  dat.duox='<font color="green">'+msg.result.fault.duoxuan_input+'</font>';
                            }
                     } 
                }
                
                if(msg.result.fault.panduan_input=='false'){
                	dat.pd='<font color="red">'+0+'</font>';
             	    fanhuijieguo=fanhuijieguo-1;
                }else{
                	if(parseInt(exam_question_type_panduan_input_val)>parseInt(msg.result.fault.panduan_input)){
                    	dat.pd='<font color="red">'+msg.result.fault.panduan_input+'</font>';
                    }else{
                    	 if(_.isUndefined(msg.result.fault.panduan_input)){
                        	   dat.pd='---';
                            }else{
                          	  dat.pd='<font color="green">'+msg.result.fault.panduan_input+'</font>';
                         }
                    }
                	
                };

                $("#random_choice_show_content").empty();
           	    
           	      if(exam_question_type_danxuan_input_val){
           	    	 dat.total_dx=exam_question_type_danxuan_input_val;
               	  }else{
               		dat.total_dx="---";
               		dat.dx="---";
                 }
           	     if(exam_question_type_duoxuan_input_val){
           	    	    dat.total_duox=exam_question_type_duoxuan_input_val;
               	 }else{
               	    	dat.total_duox="---";
               	    	dat.duox="---";
                 }

           	     if(exam_question_type_panduan_input_val){
           	    	    dat.total_pd=exam_question_type_panduan_input_val;
               	 }else{
               	    	dat.total_pd="---";
               	    	dat.pd="---";
                 }
           	    
           	    
           	     var t9_temp=_.template($("#t9").html(),{variable: 'data'})({datas:[dat]});		    		 
	             $("#random_choice_show_content").append(t9_temp);	
           	     app.alert("#random_choice_show")
           	   
                if(fanhuijieguo==0){
 					return;
                }
                
                get_exec_datas(msg.result.result);    

            });
        }
    }

    function paperList_bind_event(){
    	 // 点击li元素选中checkbox,并加上背景
    	 $('.paperList li').unbind();
        $('.paperList li').bind('click', function() {
          var thisLi = $(this);
          if (thisLi.hasClass('checkbox_checked')) {
            thisLi.removeClass('checkbox_checked')
            thisLi.find('.checkbox').removeAttr("checked")
          } else {
            thisLi.addClass('checkbox_checked')
            thisLi.find('.checkbox').prop("checked", true)
          }
        });

    }

    function paperList_bind_event1(){
   	 // 点击li元素选中checkbox,并加上背景
   	 $('.paperListId li').unbind();
       $('.paperListId li').bind('click', function() {
         var thisLi = $(this)
         if (thisLi.hasClass('checkbox_checked')) {
           thisLi.removeClass('checkbox_checked')
           thisLi.find('.checkbox').removeAttr("checked")
         } else {
           thisLi.addClass('checkbox_checked')
           thisLi.find('.checkbox').prop("checked", true)
         }
       });

   }

    function showBlockScreen(){
    	$(".blockScreen").removeClass('hide');
    }

    function hideBlockScreen(){
    	$(".blockScreen").addClass('hide');
    }
  
    $(document).ready(function(){

    	
    	 
         $("#exam_question_exec").click(function(){
        	 exam_question_exec_fun();
         });

         $("#exam_question_score_exec").click(function(){
        	 exam_question_score_exec_fun();
         });


         $("#exam_question_level_exec").click(function(){
        	 exam_question_level_exec_fun();
         });


         $("#exam_question_type_exec").click(function(){
        	 exam_question_type_exec_fun();
         });

    	 $('.closePanel').bind('click', function() {
    	      $('.lessonMachine').addClass('lessonMachine_closing');
    	      $('.lessonMachine_outer').addClass('hide');
    	      to_left_content=true;
    	    })

    	  $('.lessonMachine_open').bind('click', function() {
    	      $('.lessonMachine').removeClass('lessonMachine_closing');
    	      $('.lessonMachine_outer').removeClass('hide');
    	      to_left_content=false;
    	    });

    	initCategory();

    	

    	queryList1 = app.queryList("#search_tag");

    	queryList2 = app.queryList("#right_search_tag");
        
    	 $('.selectAll').bind('click', function() {
    	      $(this).parent().find('.checkbox').prop("checked", true)
    	 });
    	    
    	 $('.unselectAll').bind('click', function() {
    	      $(this).parent().find('.checkbox').removeAttr("checked")
    	 });

    	 // 新建试卷列表增加交换顺序脚本
    	 $('.paperListId ul').sortable().disableSelection();
    	
         $("#questions_left_id").click(function(){
        	 queryLeftList();
         });

         $("#question_right_id").click(function(){
        	 queryRightList();
         });

         $("#new_exam_paper_submit").click(function(){
        	 var params= {};
        	 params=new_exam_paper_submit();

        	var category_id= $("#category_id").val();
        	var title= $("#title").val();
        	var description= $("#description").val();
        	var examination_paper_level= $("#examination_paper_level").val();
        	var examination_paper_type= $("#examination_paper_type").val();
        	var exam={};
        	exam.category_id=category_id;
        	exam.title=title;
        	exam.description=description;
        	exam.examination_paper_level=examination_paper_level;
        	exam.examination_paper_type=examination_paper_type;
        	params.exam=exam;
        	params.mission_id=mission_id;
        	//console.log(params);
        	 window.new_paper_submit_data=params;
        	 var url="<?=Yii::$app->urlManager->createUrl(['exam-paper-manage/new-exam-paper-submit'])?>";
        	 FmodalLoadData1("new_exam_paper_submit_ui",url,params);
        	 
             });

         $("#preview_id").click(function(){
        	 var params= {};
        	 params=new_exam_paper_submit();
        	 var title= $("#title").val();
        	 params.title=title;      	
        	 window.pre_new_paper_datas=params;
        	 var url="<?=Yii::$app->urlManager->createUrl(['exam-paper-manage/exam-paper-question-preview'])?>";
        	 FmodalLoadData("new_exam_paper_ques_preview_ui",url);        	 
         });

         $("#addRightCenter").click(function(){
        	 var t3_temp=_.template($("#t3").html(),{variable: 'data'})({datas:[1]});
        	 $("#question_right_no_datas").hide();
	         $("#right_questions_ul").append(t3_temp);	
         });

         $("#addToRight").click(function(){
        	 if($("input[name='left_checkboxs']:checked").length==0){
				 app.showMsg("<?= Yii::t('frontend', '{value}_not_choose',['value'=>Yii::t('frontend','question_bank')]) ?>");
				 return ;	
    		 }

        	 if(!validate_right_list_num_fun($("input[name='left_checkboxs']:checked").length)){
         		 return ;
             };
    		 
    		 var result_obj={};
    		 var result_obj2={};
    		 question_page=0;
    		 var result_obj_arr=[];
    		 var result_obj_arr2=[];
			 $("input[name='left_checkboxs']:checked").each(function() { //由于复选框一般选中的是多个,所以可以循环输出
				 var result_obj_tmp_={};
				 var result_obj_tmp_2={};
				 var kid=$(this).val();
				 var title=$(this).attr("data-id1");
				 var is_allow_change_score=$(this).attr("data-id2");
				 var default_score=$(this).attr("data-id3");
				 var examination_question_type=$(this).attr("data-id4");
				 var category_id=$(this).attr("data-id5");
				 var tag_id=$(this).attr("data-id6");
				 var tag_value=$(this).attr("data-id7");
				 result_obj2.kid=kid;
				 result_obj.kid=kid;
				 result_obj.title=title;
				 result_obj.default_score=default_score;
				 result_obj.examination_question_type=examination_question_type;
				 total_question_num=total_question_num+1;
				 total_question_score=parseFloat(total_question_score)+parseFloat(default_score);			 
				 result_obj.is_allow_change_score=is_allow_change_score;
				 result_obj.category_id=category_id;
				 result_obj.tag_id=tag_id;	
				 result_obj.tag_value=tag_value;			 
				 result_obj_tmp_=_.clone(result_obj) ;
				 result_obj_tmp_2=_.clone(result_obj2) ;
				 result_obj_arr.push(result_obj_tmp_);	
				 result_obj_arr2.push(result_obj_tmp_2);	
				 $("#"+kid).parent().remove();
			  });

			 total_stat_show(total_question_num,total_question_score);
			// $("#right_questions_ul").empty();
			 var t2_temp=_.template($("#t2").html(),{variable: 'data'})({datas:result_obj_arr});
			 $("#question_right_no_datas").hide();
	         $("#right_questions_ul").append(t2_temp);			 
			 var add_left_params={};
			 add_left_params.result=result_obj_arr2;			
			 add_left_params.mission_id=mission_id;
			 var url="<?=Url::toRoute(['exam-paper-manage/add-left-questions-tmp',])?>";
        	 $.ajax({
				   type: "POST",
				   url: url,
				   data: add_left_params,
				   success: function(msg){					 
					   toRightCategory();
					   paperList_bind_event1();
				   }
		     });  
             });

         $("#removeRightQuestion").click(function(){
           	 
           	     if($("input[name='right_checkboxs']:checked").length==0){

        			 app.showMsg("<?= Yii::t('frontend', '{value}_not_choose',['value'=>Yii::t('frontend','question2')]) ?>");
       			 return ;
        		 }

           	     var result_obj={};
        		 var result_obj_arr=[];
        		 console.log("input[name='right_checkboxs']:checked");
        		 $("input[name='right_checkboxs']:checked").each(function() { //由于复选框一般选中的是多个,所以可以循环输出
            		
            		 var next_nodes=$(this).nextAll();
            		
        			 var result_obj_tmp_={};
        			 var kid=$(this).val();	
        			
        			 $("#right_questions_ul #"+kid).parent().remove();
					 //var score=$(this).attr("data-id1");
					 var score=next_nodes[2].value;
					 var examination_question_type=$(this).attr("data-id2");
					 var title=$(this).attr("data-id3");
					 var is_allow_change_score=$(this).attr("data-id4");
					 var category_id=$(this).attr("data-id5");
					 var tag_id=$(this).attr("data-id6");
					 var tag_value=$(this).attr("data-id7");
        			 total_question_num=total_question_num-1;
        	         total_question_score=parseFloat(total_question_score)-parseFloat(score);       	         
        	         total_stat_show(total_question_num,total_question_score);        			 			
        			 result_obj.kid=kid;
        			 result_obj.default_score=score;
        			 result_obj.examination_question_type=examination_question_type;
        			 result_obj.title=title;
        			 result_obj.is_allow_change_score=is_allow_change_score;
        			 result_obj.category_id=category_id;
        			 result_obj.tag_id=tag_id; 
        			 result_obj.tag_value=tag_value;       			 
        			 result_obj_tmp_=_.clone(result_obj) ;
        			 result_obj_arr.push(result_obj_tmp_);	
        		  });


        		 if(to_left_content){
        			 var t1_temp=_.template($("#t1").html(),{variable: 'data'})({datas:result_obj_arr});        			
                     $("#left_questions_ul").append(t1_temp);          		 
                 }
                
        		 $("#more_datas").hide();
        		 $("#question_no_datas").hide();
        		 
        		
        		 var add_right_params={};
        		 add_right_params.result=result_obj_arr;			
        		 add_right_params.mission_id=mission_id;
        		 var url="<?=Url::toRoute(['exam-paper-manage/remove-right-questions-tmp',])?>";
           	     $.ajax({
        			   type: "POST",
        			   url: url,
        			   data: add_right_params,
        			   success: function(msg){        				 
        				   //queryLeftList();
        			   }
        	     });       		 
           	 
                });
    	       
    });


    function new_exam_paper_submit(){
    	 var ul_size=$("#right_questions_ul li").length;
         var params={};
         var result=[];
         var submit_flag=true;
         if(ul_size==0){
        	 app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_17')?>");
        	 return;
         }
     
    	 $("#right_questions_ul li").each(function(index, domEle) {
        	 if(index==0){
        		 if($(this).find("hr").length==1){
        			 app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_18')?>");
        			 submit_flag=false;
                 }
             }
        	 if(index==(ul_size-1)){
        		 if($(this).find("hr").length==1){
        			 app.showMsg("<?=Yii::t('frontend', 'exam_pq_note_19')?>");
        			 submit_flag=false;
                 }
             }

             if($(this).find("hr").length==1){
            	 var obj={};
            	 obj.relation_type=1;
            	 obj.examination_question_type="<?=Yii::t('frontend', 'exam_pq_fenye')?>";
            	 result.push(obj);
             }else{

                 var obj={};
                 var category_id=$(this).find("input[type=checkbox]").attr("data-id5");
                 var tag_id=$(this).find("input[type=checkbox]").attr("data-id6");
                 obj.relation_type=0;
                 obj.kid=$(this).find("input[type=checkbox]").val();
                 obj.examination_question_type=$(this).find("input[type=checkbox]").attr("data-id2");
                 obj.category_id=category_id;
                 obj.tag_id=tag_id;
                 obj.title=$(this).find("input[type=checkbox]").attr("data-id3");
                 obj.default_score=$(this).find("input[type=text]").val();
            	 result.push(obj);
             }
    	
        	 });    	
    	 //console.log(category_id_stats_list);
    	// console.log(tag_id_stats_list);    	 

    	 if(!submit_flag){

    		 return;
         }
    	 params.result=result;    	
         return params;
    }


    function total_stat_show(num,score){
    	score=Math.round(score*100)/100;
		var show="<?=Yii::t('frontend', 'exam_pq_note_special_6_start')?>"+num+"<?=Yii::t('frontend', 'exam_pq_note_special_6_end')?>, <?=Yii::t('frontend', 'exam_pq_note_special_7_start')?>"+score+"<?=Yii::t('frontend', 'exam_pq_note_special_7_end')?>";
    	$("#total_stat").html(show);
    }

    function change_total_stat_show(){
    	total_question_score=0;
    	 $("input[name='right_input']").each(function() { //			
			 var score=$(this).val();	
			 if(!validate_float_number(this,"<?=Yii::t('frontend', 'exam_pq_note_20')?>")){
				    $(this).val($(this).attr("data-id1"));
				    score=$(this).attr("data-id1");
					
			 }			  		
	         total_question_score=parseFloat(total_question_score)+parseFloat(score);	
	         console.log( total_question_score );         
	         total_stat_show(total_question_num,total_question_score);			
		  });       

    }

    function delete_right_question(node,id,title,is_allow_change_score,default_score,examination_question_type,category_id,tag_id,tag_value){

         var arrs=[];
         var arr={};
         arr.kid=id;
         arr.title=title;
         arr.is_allow_change_score=is_allow_change_score;
         arr.default_score=default_score;
         arr.examination_question_type=examination_question_type;
         arr.category_id=category_id;
         arr.tag_id=tag_id; 
         arr.tag_value=  tag_value;     
         arrs.push(arr);

         if(to_left_content){
        	 var t1_temp=_.template($("#t1").html(),{variable: 'data'})({datas:arrs});		
             $("#left_questions_ul").append(t1_temp);           		 
         }
         
         $("#more_datas").hide();
    		
         $(node).parent().remove();
         var score=$(node).next().val();

         total_question_num=total_question_num-1;
         total_question_score=parseFloat(total_question_score)-parseFloat(score);
         total_stat_show(total_question_num,total_question_score);
         var params_obj={};
         var result=[];
         console.log("-----");
         result.push({kid:id});
	   	 params_obj.mission_id=mission_id;
	   	 params_obj.result=result;
	   	 //return;
	   	 var url="<?=Url::toRoute(['exam-paper-manage/remove-right-questions-tmp',])?>";
	   	 $.ajax({
				   type: "POST",
				   url: url,
				   data: params_obj,
				   success: function(msg){					
					  // queryLeftList();
				   }
		     });

    }

    function  delete_right_center(node){
    	 $(node).parent().parent().remove();
    }


    function FmodalLoadData(target, url)
	{
   	
   	 if(url){
     	   $('#'+target).empty();
            $('#'+target).load(url, function (){
            		 app.alertWide("#"+target,{
            			afterHide: function (){ 
            				$('#'+target).empty();
                	    }
        		    });
                });
           
        }
	  }

    function FmodalLoadData1(target, url,data)
	 {  	
  	 if(url){
    	   $('#'+target).empty();
           $('#'+target).load(url,data, function (){
           		 app.alert("#"+target,{
           			afterHide: function (){ 
           				$('#'+target).empty();
               	    }
       		    });
               });
          
       }
	  }

    function initCategory(){
    	$.get("<?=Url::toRoute(['exam-paper-manage/get-all-question-categorys',])?>",function(msg){
			right_category_datas=msg.results;

 			var titles="";
			titles=JSON.stringify(right_category_datas);
			
			$("#search_question_cat").attr("data-resource",titles);
			queryList = app.queryList("#search_question_cat");
        });
    }

    function toRightCategory(){
        var right_category_list=[];
       

   	    $("input[name='right_checkboxs']").each(function() { //由于复选框一般选中的是多个,所以可以循环输出		
			 var category_id=$(this).attr("data-id5");
			 if(!_.contains(right_category_list, category_id)){
				 right_category_list.push(category_id);
		     }	 
		  });

   	    var tmp_data=[];
   	    for(var i=0;i<right_category_datas.length;i++){
				var cate=right_category_datas[i];
				if(_.contains(right_category_list, cate.uid)){
					tmp_data.push(cate);
			    }
        }
      
   	    $("#right_category_id").empty();
  	    var t5_temp=_.template($("#t5").html(),{variable: 'data'})({datas:tmp_data});
        $("#right_category_id").append(t5_temp);
   }

    function get_exec_datas(datas){

   	     var result_obj={};
		 var result_obj2={};
		 question_page=0;
		 var result_obj_arr=[];
		 var result_obj_arr2=[];
		 console.log(datas.length);
		 for(var i=0;i<datas.length;i++) { //由于复选框一般选中的是多个,所以可以循环输出
			 var data=datas[i];
			 var result_obj_tmp_={};
			 var result_obj_tmp_2={};
			 var kid=data.kid;
			 var title=data.title;
			 var is_allow_change_score=data.is_allow_change_score;
			 var default_score=data.default_score;
			 var examination_question_type=data.examination_question_type;
			 var category_id=data.category_id;
			 var tag_id=data.tag_id;
			 var tag_value=data.tag_value;
			 result_obj2.kid=kid;
			 result_obj.kid=kid;
			 result_obj.title=title;
			 result_obj.default_score=default_score;
			 result_obj.examination_question_type=examination_question_type;
			 total_question_num=total_question_num+1;
			 total_question_score=parseFloat(total_question_score)+parseFloat(default_score);			 
			 result_obj.is_allow_change_score=is_allow_change_score;
			 result_obj.category_id=category_id;
			 result_obj.tag_id=tag_id;	
			 result_obj.tag_value=tag_value;			 
			 result_obj_tmp_=_.clone(result_obj) ;
			 result_obj_tmp_2=_.clone(result_obj2) ;
			 result_obj_arr.push(result_obj_tmp_);	
			 result_obj_arr2.push(result_obj_tmp_2);	
			
		  }

		 total_stat_show(total_question_num,total_question_score);
		// $("#right_questions_ul").empty();
		 var t2_temp=_.template($("#t2").html(),{variable: 'data'})({datas:result_obj_arr});
		 $("#question_right_no_datas").hide();
         $("#right_questions_ul").append(t2_temp);			 
		 var add_left_params={};
		 add_left_params.result=result_obj_arr2;			
		 add_left_params.mission_id=mission_id;
		 toRightCategory();
		 paperList_bind_event1();

   }

    function validate_number(id,msg){

    	var val=$("#"+id).val();
    	if(!(/^[0-9]+$/gi.test(val))){
    		app.showMsg(msg);
    		$("#"+id).val("");
    		return false;
        }
    	return true;

    }

    function validate_float_number(node,msg){
    	var val=$(node).val();
    	//if(!(/^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/.test(val))){
    	//console.log(/^\d+(?:\.\d{1,2})?$/.test(val));
    	if(!(/^\d+(?:\.\d{1,2})?$/.test(val))){
    		app.showMsg(msg);
    		$(node).val("");
    		return false;
        }
    	return true;

    }

    function hide_show(){
    	hideBlockScreen();
		app.hideAlert("#random_choice_show");
    }
    
    
     </script>
     
     
  
   <script id="t1" type="text/template">
 <%_.each(data.datas, function(item) {%>
    <li>
        <input id="<%=item.kid%>" type="checkbox" data-id7="<%=item.tag_value%>" data-id6="<%=item.tag_id%>" data-id5="<%=item.category_id%>" data-id4="<%=item.examination_question_type%>" data-id3="<%=item.default_score%>" data-id2="<%=item.is_allow_change_score%>" data-id1="<%=item.title%>" name="left_checkboxs" class="checkbox" value="<%=item.kid%>"/>
        <label title="<%=item.title%>">[<%=item.examination_question_type%>]<%=item.title%></label>
    </li>
             	
 <%});%>
    </script> 
    
    
      <script id="t2" type="text/template">
 <%_.each(data.datas, function(item) {%>
   <li>
       <input type="checkbox" data-id1="<%=item.default_score%>" data-id2="<%=item.examination_question_type%>" data-id3="<%=item.title%>" data-id4="<%=item.is_allow_change_score%>" data-id5="<%=item.category_id%>" data-id6="<%=item.tag_id%>" data-id7="<%=item.tag_value%>"  class="checkbox" value="<%=item.kid%>" name="right_checkboxs" id="<%=item.kid%>"/>
       <label title="[<%=item.examination_question_type%>,<?=Yii::t('frontend', 'exam_zhishidian')?>:<%=item.tag_value%>]<%=item.title%>">[<%=item.examination_question_type%>]<%=item.title%></label>
       <a href="javascript:void(0)" onclick="delete_right_question(this,'<%=item.kid%>','<%=item.title%>','<%=item.is_allow_change_score%>','<%=item.default_score%>','<%=item.examination_question_type%>','<%=item.category_id%>','<%=item.tag_id%>','<%=item.tag_value%>')" class="btn btn-xs"><?= Yii::t('common', 'delete_button') ?></a>
       <input type="text" data-id1="<%=item.default_score%>" name="right_input" onblur="change_total_stat_show(this)" class="scroeInput" <%=item.is_allow_change_score%> placeholder="1<?=Yii::t('frontend', 'point')?>" value="<%=item.default_score%>">
   </li>	
 <%});%>
    </script>   
    
   <script id="t3" type="text/template">
 <%_.each(data.datas, function(item) {%>
    <li>
      <hr id="fenye-id"/>
      <div class="centerBtnArea"><?=Yii::t('frontend', 'exam_pq_fenyefu')?><a onclick="delete_right_center(this)" href="javascript:void(0)" class="btn btn-xs"><?= Yii::t('common', 'delete_button') ?></a></div>
    </li> 
              
 <%});%>
    </script>   
    
 <script id="t4" type="text/template"> 
 <%_.each(data.datas, function(item) {%>
    <div class="centerBtnArea" id="more_datas" onclick="on_more_datas(this)" ><a class="btn btn-sm btn-default"><?=Yii::t('frontend', 'exam_pq_loadmore')?></a></div> 
 <%});%> 
 </script>      
 
  <script id="t5" type="text/template"> 
 <option value="999"><?=Yii::t('frontend', 'exam_pq_quanbutiku')?></option>
 <%_.each(data.datas, function(item) {%>
    <option value="<%=item.uid%>"><%=item.title%></option>
 <%});%> 
 </script>                           
                          
     
   <script id="t6" type="text/template"> 
 <%_.each(data.datas, function(item) {%>
       <div class="centerBtnArea noData"  id="question_no_datas">
                <i class="glyphicon glyphicon-calendar"></i>
                <p><?=Yii::t('frontend', 'exam_pq_note_1')?></p>
        </div> 
 <%});%> 
 </script>     
  
 <script id="t7" type="text/template"> 
 <%_.each(data.datas, function(item) {%>
  <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="choiceType1">
                      <div class="infoBlock">
                        <div class="row">
                          <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                              <p><?=Yii::t('frontend', 'exam_pq_suijizujuanrule')?> ： <?=Yii::t('frontend', 'exam_pq_yaoqiutimu')?> ：<%=item.total%><?=Yii::t('frontend', 'exam_ti')?> </p>
 							  <p> <?=Yii::t('frontend', 'exam_pq_yiwanchengshumu')?> ：<%=item.num1%> <?=Yii::t('frontend', 'exam_ti')?>    <?=Yii::t('frontend', 'exam_pq_queshao')?> <%=item.num2%><?=Yii::t('frontend', 'exam_ti')?> </p>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 centerBtnArea">
                          <a href="###" onclick="hide_show()" class="btn btn-success btn-sm centerBtn"><?= Yii::t('common', 'close') ?></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
 <%});%> 
   </script>  
   
   <script id="t8" type="text/template"> 
 <%_.each(data.datas, function(item) {%>
  <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="choiceType1">
                      <div class="infoBlock">
                        <div class="row">
                          <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                              <p><?=Yii::t('frontend', 'exam_pq_suijizujuanrule')?> ： <?=Yii::t('frontend', 'exam_pq_yaoqiufenshu')?> ：<%=item.total%><?= Yii::t('frontend', 'point') ?> </p>
 							  <p> <?=Yii::t('frontend', 'exam_pq_yiwanchengfenshu')?> ：<%=item.num1%> <?= Yii::t('frontend', 'point') ?>    <?=Yii::t('frontend', 'exam_pq_queshao')?><%=item.num2%><?= Yii::t('frontend', 'point') ?> </p>

                            </div>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 centerBtnArea">
                          <a href="###" onclick="hide_show()" class="btn btn-success btn-sm centerBtn"><?= Yii::t('common', 'close') ?></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
 <%});%> 
   </script>  
   
   <script id="t9" type="text/template"> 
 <%_.each(data.datas, function(item) {%>
  <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="choiceType1">
                      <div class="infoBlock">
                        <div class="row">
                          <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                               <p><?=Yii::t('frontend', 'exam_pq_suijirule_tixing')?> <?=Yii::t('common', 'question_radio')?><%=item.total_dx%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('common', 'question_checkbox')?><%=item.total_duox%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('frontend', 'exam_pq_panduanti')?><%=item.total_pd%><?=Yii::t('frontend', 'exam_ti')?> </p>
 							   <p><?=Yii::t('frontend', 'exam_pq_yiwanchengtixing')?> ：  <?=Yii::t('common', 'question_radio')?><%=item.dx%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('common', 'question_checkbox')?><%=item.duox%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('frontend', 'exam_pq_panduanti')?><%=item.pd%><?=Yii::t('frontend', 'exam_ti')?> </p>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 centerBtnArea">
                          <a href="###" onclick="hide_show()" class="btn btn-success btn-sm centerBtn"><?= Yii::t('common', 'close') ?></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
 <%});%> 
   </script> 
   
   <script id="t10" type="text/template"> 
 <%_.each(data.datas, function(item) {%>
  <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="choiceType1">
                      <div class="infoBlock">
                        <div class="row">
                          <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <p><?=Yii::t('frontend', 'exam_pq_suijirule_diff')?> <?=Yii::t('frontend', 'exam_pq_jiandan')?><%=item.total_jd%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('frontend', 'exam_pq_zhongdeng')?><%=item.total_zd%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('frontend', 'exam_pq_kunnan')?><%=item.total_kn%><?=Yii::t('frontend', 'exam_ti')?> </p>
 							   <p> <?=Yii::t('frontend', 'exam_pq_yiwanchengnandu')?> ：  <?=Yii::t('frontend', 'exam_pq_jiandan')?><%=item.jd%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('frontend', 'exam_pq_zhongdeng')?><%=item.zd%><?=Yii::t('frontend', 'exam_ti')?> <?=Yii::t('frontend', 'exam_pq_kunnan')?><%=item.kn%><?=Yii::t('frontend', 'exam_ti')?> </p>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12 centerBtnArea">
                          <a href="###" onclick="hide_show()" class="btn btn-success btn-sm centerBtn"><?= Yii::t('common', 'close') ?></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
 <%});%> 
   </script>               