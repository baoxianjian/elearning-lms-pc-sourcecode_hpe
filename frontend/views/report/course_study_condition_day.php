<?php
use yii\helpers\Url;
?>


<div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="actionBar">
                    <form class="form-inline pull-left">
                      <div class="form-group">
                        
                        <label><?= Yii::t('frontend', 'time_area') ?>:</label>
                        <div class="form-group ">
                          <select class="form-control" id="year_id" name="CourseService[course_type]">
                           
                          </select>
                        </div>
                        <div class="form-group ">
                          <select class="form-control" id="month_id" name="CourseService[course_type]">
                            
                          </select>
                        </div>&nbsp;&nbsp;
                        <label><?=Yii::t('common', 'domain')?>:</label>
                        <div class="form-group ">
                          <select class="form-control" name="CourseService[course_type]" id="domain_id">
                           
                          </select>
                        </div>
                      </div>
                    </form>
                    <form class="form-inline pull-right" style="margin-left:5px;">
                      <label><?= Yii::t('common', 'course_name') ?>:</label>
                      <div class="form-group">
                        <input type="text" id="course_name_id" class="form-control" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','course_name')]) ?>">
                           <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="cvs_export"><?=Yii::t('common', 'export_button')?></button>
                     
                        <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="query_cscd"><?=Yii::t('common', 'search')?></button>
                      </div>
                    </form>
                  </div>
                  <div class="changeDailyReport1">
                    <table class="table table-bordered table-hover table-striped table-center" id="table_body">
                      <tr>
                        <td><?= Yii::t('common', 'time') ?></td>
                        <td><?= Yii::t('frontend', 'register_number') ?></td>
                        <td><?= Yii::t('frontend', 'finish_number') ?></td>
                        <td><?= Yii::t('frontend', 'course_completion_rate') ?></td>
                        <td><?= Yii::t('frontend', 'grade_average') ?></td>
                      </tr>
                      
                     
                    
                    </table>
                  </div>
                  
                 
                </div>
 </div>
  <iframe id="hiddenDownloader" src="" style="width: 10px;visibility:hidden;height:0px;  border: 0; position: absolute;">
    </iframe>   
 
    <script type="text/javascript"> 

    var year_val;
	var month_val;
    var domain_id;
    var course_id='';
    
 $(function(){
//

 	$("#cvs_export").click(function(){
		    var iframe;
		    iframe = document.getElementById("hiddenDownloader");
		    var obj=queryList.get();
	 	    console.log(course_id);
	    	if(course_id==''){
	    		 
	    		 app.showMsg("<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','course_name')]) ?>");
	        } else{  	
	        	
	            iframe.src = "<?=Url::toRoute(['report/export-course-study-condition-day',])?>"+"?domain_id="+domain_id+"&year="+year_val+"&month="+month_val+"&course_id="+course_id;
	        }
			
		   
	  });
   
    $("#domain_id").change(function(){
    	queryList.reset();
    	flash($("#domain_id").val());
    });


    $("#year_id").change(function(){
    	getMonthList($("#year_id").val());   	
    });
   
    $("#query_cscd").click(function(){
    	var obj=queryList.get();
    	   
    	if(_.isEmpty(obj)){
    		 app.showMsg("<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','course_name')]) ?>");
        } else{
        	 year_val=$("#year_id").val();
    		 month_val=$("#month_id").val();
             domain_id=$("#domain_id").val();
             course_id=obj.uid;

           
            console.log(course_id);
        	//reflash('2015','10','C8ECC4CC-7A96-4657-D5D5-C814E46F8945','6811AFCA-DAB3-3FB5-3AD1-BDA4AA8B93DC');
        	reflash(year_val,month_val,domain_id,course_id);
        }
    	
    });

	$.ajax({
        	   async: false,
			   url: "<?=Url::toRoute(['report/get-query',])?>",
			   data: {id:''},
			   success: function(msg){					
				   for(var i=0;i<msg.domains.length;i++){
				    	 var tag=msg.domains[i];				    	 
				    	 $("#domain_id").append("<option value='"+tag.kid+"'>"+tag.domain_name+"</option>");
				   }
				   var xxxx=msg.domains[0];
				  
				   flash(xxxx.kid);	  
			   }
			 });



	 $.ajax({
		   async: false,
		   url: "<?=Url::toRoute(['report/get-course-study-condition-day-year',])?>",
		   data: {id:''},
		   success: function(msg){	

			   				
			   for(var i=0;i<msg.length;i++){
			    	 var yyyy=msg[i];				    	 
			    	 $("#year_id").append("<option value='"+yyyy.YEAR+"'>"+yyyy.YEAR+"</option>");
			   }

			   if(msg){
				    
				   getMonthList(msg[0].YEAR) ;
			    }
			  
		   }
		 });
	//
	});


	function getMonthList(year_id_val){

		   $.get("<?=Url::toRoute(['report/get-course-study-condition-day-month',])?>"+"?YEAR="+year_id_val,function(data){
			   for(var i=0;i<data.length;i++){
		    	 var mm=data[i];				    	 
		    	 $("#month_id").append("<option value='"+mm.MONTH+"'>"+mm.MONTH+"</option>");
		       }

			});

		}


    var queryList;
    function flash(kid){
		   var data_url_val="<?=Url::toRoute(['report/get-courses',])?>"+"?domain_id="+kid;	

		   console.log(data_url_val);	
		   $("#course_name_id").attr("data-url",data_url_val);
		   queryList = app.queryList("#course_name_id"); 
    }


    function reflash(year_,month_,domain_id_,course_id_){


    	$.ajax({
			   
			   url: '<?=Url::toRoute(['report/get-course-study-condition-day-data',])?>',
			   data: {year:year_,month:month_,domain_id:domain_id_,course_id:course_id_},
			   async: false,
			   success: function(msg){
				  $("#table_body tbody").empty();
				   var lists_h=[1]
				   var table_h_templ=_.template($("#table_h_id").html(),{variable: 'data'})({datas:lists_h});
				   $("#table_body tbody").append(table_h_templ);
				   var lists=msg;			  
				   var table_tr_templ=_.template($("#table_tr").html(),{variable: 'data'})({datas:lists});
			       $("#table_header").after(table_tr_templ);

			   }
	      });
    	
        }
    
    </script>  
    
      <script id="table_h_id" type="text/template">
<%_.each(data.datas, function(item) {%>
					  <tr id="table_header">
                        <td><?= Yii::t('common', 'time') ?></td>
                        <td><?= Yii::t('frontend', 'register_number') ?></td>
                        <td><?= Yii::t('frontend', 'finish_number') ?></td>
                        <td><?= Yii::t('frontend', 'course_completion_rate') ?></td>
                        <td><?= Yii::t('frontend', 'grade_average') ?></td>

                    </tr>
 <%});%>
    </script>      
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					 <tr>
                      <td><%=item.time%></td>
                      <td><%=item.reg_user_num%></td>
                      <td><%=item.comp_user_num%></td>
                      <td><%=item.comp_rate%></td>
                      <td><%=item.score%></td>
                    </tr>
 <%});%>
    </script>  