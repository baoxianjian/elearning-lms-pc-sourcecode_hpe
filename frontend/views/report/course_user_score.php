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
                          <select class="form-control"  id="year_id" name="CourseService[course_type]">
                            
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
                  
                  <div class="changeDailyReport2">
                    <table class="table table-bordered table-hover table-striped table-center" id="table_body">
                      <tr>
                        <td><?= Yii::t('frontend', 'register_name') ?></td>
                        <td><?= Yii::t('common', 'mobile_no') ?></td>
                        <td><?= Yii::t('frontend', 'department') ?></td>
                        <td><?= Yii::t('frontend', 'position') ?></td>
                        <td><?= Yii::t('frontend', 'signup_time')?></td>
                        <td><?=Yii::t('common', 'complete_end_at')?></td>
                        <td><?= Yii::t('common', 'examination_score') ?></td>
                      </tr>
                    
                     
                     
                    </table>
                  </div>
                 
                </div>
              </div>
        <iframe id="hiddenDownloader" src="" style="width: 10px;visibility:hidden;height:0px;  border: 0; position: absolute;">
    </iframe>          
 
   <style>
<!--
table#table_body .limit_class td{
    overflow: hidden;
    max-width: 100px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

-->
</style>           
 
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
	 	    
	    	if(course_id==''){
	    		 
	    		app.showMsg("<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','course_name')]) ?>");
	        } else{  	
	        	//var year_val=$("#year_id").val();
				//var month_val=$("#month_id").val();
		        //var domain_id=$("#domain_id").val();
		        //var course_id=obj.uid;
	            
	            iframe.src = "<?=Url::toRoute(['report/export-course-user-score',])?>"+"?domain_id="+domain_id+"&year="+year_val+"&month="+month_val+"&course_id="+course_id;
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

    	//reflash('2015','10','C8ECC4CC-7A96-4657-D5D5-C814E46F8945','6811AFCA-DAB3-3FB5-3AD1-BDA4AA8B93DC');
    	var obj=queryList.get();
	   
		if(_.isEmpty(obj)){
		    app.showMsg("<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','course_name')]) ?>");
	    } else{
	    	 year_val=$("#year_id").val();
			 month_val=$("#month_id").val();
	         domain_id=$("#domain_id").val();
	         course_id=obj.uid;
	        console.log(course_id);
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
		   url: "<?=Url::toRoute(['report/get-course-user-score-year',])?>",
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

	   $.get("<?=Url::toRoute(['report/get-course-user-score-month',])?>"+"?YEAR="+year_id_val,function(data){
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
			   
			   url: '<?=Url::toRoute(['report/get-course-user-score-data',])?>',
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
                        <td><?= Yii::t('frontend', 'register_name') ?></td>
 						<td><?= Yii::t('common', 'mobile_no') ?></td>
                        <td><?= Yii::t('frontend', 'department') ?></td>
                        <td><?= Yii::t('frontend', 'position') ?></td>
                        <td><?= Yii::t('frontend', 'signup_time')?></td>
                        <td><?=Yii::t('common', 'complete_end_at')?></td>
                        <td><?= Yii::t('common', 'examination_score') ?></td>
                    </tr>
 <%});%>
    </script>      
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					  <tr class="limit_class">
                      <td title="<%=item.user_id%>"><%=item.user_id%></td>
					  <td><%=item.mobile_no%></td>
                      <td><%=item.orgnization_name%></td>
 <td title="<%=item.position_name%>"><%=item.position_name%></td>
                      <td><%=item.reg_time%></td>
                      <td><%=item.comp_time%></td>
                      <td><%=item.score%></td>
                    </tr>
 <%});%>
    </script>               