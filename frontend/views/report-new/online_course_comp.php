<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="panel-default scoreList " >
                <div class="panel-body courseInfo">
                
                 <div >
                     <div class="actionBar">
                       <form class="form-inline pull-left">
                       <div class="form-group">
                              <div class="form-group "> 
                                    <select class="form-control" id="term_type_id"   name="CourseService[course_type]">  
                                      <option value="1"><?= Yii::t('common', 'reporting_manager') ?></option>
		                              <option value="3"><?=Yii::t('common', 'company')?></option>
		                              <option value="2"><?=Yii::t('common', 'domain')?></option>
		                                                                                     
                                    </select>
                                    
                                  
                               </div>
                          </div>
                       </form>  
                      <form class="form-inline pull-right" style="margin-left:5px;">
                      <div class="form-group">
                        <input type="text" id="course_name_id" class="form-control clickSearch" placeholder="<?= Yii::t('frontend','rep_input_course_name_or_code') ?>">
                      
                               <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="cvs_export"><?=Yii::t('common', 'export_button')?></button>
                     		    <button type="button" class="btn btn-primary pull-right clickBtn" style="margin-left:10px;" id="query_online"><?=Yii::t('frontend', 'tag_query')?></button>
                       </div>
                    </form>   
                   
                          
                        </div>    
                  </div>
                  <div class="row" id="canvas_div">
                   
                    <div id="g2Charts" style="margin: 10px auto;"></div>
                  </div>
                  <div class="row">
                  </div>
                  <table class="table table-bordered table-hover table-striped sortable table-center" id="table_body">
                     <thead>
                       <tr id="table_header">
	                      <th id="type_change_id"><?= Yii::t('common', 'reporting_manager') ?></th>
	                      <th><?= Yii::t('frontend', 'rep_total_user') ?></th>
	                      <th data-defaultsort="asc"><?= Yii::t('frontend', 'register_number') ?></th>
	                      <th><?= Yii::t('frontend', 'finish_number') ?></th>
	                      <th><?= Yii::t('frontend', 'rep_comp_rate') ?>(%)</th>
	                      <th><?= Yii::t('frontend', 'rep_avg_score') ?></th>
                   		 </tr>
                    </thead>
                     <tbody>
                     </tbody>
                  
                  </table>
                 
                </div>
              </div>
                <iframe id="hiddenDownloader" src="" style="width: 10px;visibility:hidden;height:0px;  border: 0; position: absolute;">
    </iframe>  
    
    <style type="text/css">
  .sortable th {
    text-align: center;
  }
  
  </style>
    
      
  <script type="text/javascript">

  var course_id='';

  var queryList;
  function flash(){
		   var data_url_val="<?=Url::toRoute(['report-new/get-courses',])?>"+"?course_type=0";	

		   console.log(data_url_val);	
		   $("#course_name_id").attr("data-url",data_url_val);
		   queryList = app.queryList("#course_name_id"); 
  }

  

	 
  
    $(function(){
//

	  $('.clickSearch').bind('keydown', function(event) {
	    if (event.keyCode == "13") {
	      event.preventDefault()
	      $('#query_online').trigger("click");
	    }
	  });

    app.genCalendar();

    flash();
    
 	$("#cvs_export").click(function(){
		     var iframe;
		     iframe = document.getElementById("hiddenDownloader");
 		
		     var obj=queryList.get();
	    	 var course_id_val='';
	    	 console.log(obj);
	    	 if(!obj.uid){
	    		 app.showMsg("<?= Yii::t('frontend', 'rep_input_course_name_or_code') ?>");
	    		 return;
		     }else{
		    	 course_id_val=obj.uid;
			 }
	    	 var type_val=$("#term_type_id").val();

	    	 //course_id_val='E279E8D3-6327-355F-D3B9-032F24740CE0';
	      
	         iframe.src = "<?=Url::toRoute(['report-new/export-online-course-comp',])?>"+"?course_id="+course_id_val+"&type_param="+type_val;
			
		   
	  });

	    


	     $("#query_online").click(function(){

	    	 var obj=queryList.get();
	    	 var course_id_val='';
	    	 console.log("---");
	    	 console.log(obj);
	    	 if(!obj.uid){
	    		 app.showMsg("<?= Yii::t('frontend', 'rep_input_course_name_or_code') ?>");
	    		 return;
		     }else{
		    	 course_id_val=obj.uid;
			 }
	    	
	        var type_val=$("#term_type_id").val();
	        if(type_val=='1'){
	        	$("#type_change_id").html("<?= Yii::t('common', 'reporting_manager') ?>");	        	
		    }else if(type_val=='2'){
		    	$("#type_change_id").html("<?= Yii::t('common', 'domain') ?>");	
			}else if(type_val=='3'){
				$("#type_change_id").html("<?= Yii::t('common', 'company') ?>");	
			}
	       
	        
	        //course_id_val='E279E8D3-6327-355F-D3B9-032F24740CE0';
	        reflash(course_id_val,type_val);
	     });

	 

//
        });



    function reflash(course_id_,type_){
      var ajaxOpt = {

         url: '<?=Url::toRoute(['report-new/get-online-course-comp-data',])?>',
         data: {course_id:course_id_,type_param:type_},
         async: false,
         success: function(msg){
              $("#table_body tbody").empty();
              var lists=msg.onlineCourseComp;        
              var table_tr_templ=_.template($("#table_tr").html(),{variable: 'data'})({datas:lists});
              $("#table_body tbody").append(table_tr_templ);

              $.bootstrapSortable();       
             }
        };
    	   $.ajax(ajaxOpt);
        }
    
    </script>  
    
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					 <tr>
                      <td><%=item.display_val%></td>
                      <td><%=item.total_user_num%></td>
                      <td><%=item.reg_num%></td>
                      <td><%=item.com_num%></td>
  					  <td><%=item.com_num_rate%></td>
                      <td><%=item.score%></td>
                    </tr>
 <%});%>
    </script>                 
 <?=Html::cssFile('/static/frontend/css/bootstrap-sortable.css')?>
       <?=Html::jsFile('/static/frontend/js/bootstrap-sortable.js')?>
    <?=Html::jsFile('/static/frontend/js/moment.js')?>
    
    