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
                      <div class="form-group">
                          <label class="nameInput hide"><?= Yii::t('common', 'real_name') ?>:</label>
                          <input type="text" class="form-control nameInput hide" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('common', 'real_name')])?>">
                              <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="cvs_export"><?=Yii::t('common', 'export_button')?></button>
                        <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="query_cscd"><?=Yii::t('common', 'search')?></button>
                      </div>
                    </form>
                  </div>
                  <div class="changeLearningReport1">
                    <table class="table table-bordered table-hover table-striped table-center" id="table_body">
                      <tr>
                        <td><?= Yii::t('common', 'time') ?></td>
                        <td><?= Yii::t('frontend', 'login_times2_today') ?></td>
                        <td><?= Yii::t('frontend', 'login_times2_today_rate') ?></td>
                        <td><?= Yii::t('frontend', 'learning_time_total_today') ?></td>
                        <td><?= Yii::t('frontend', 'course_rate_higest') ?></td>
                        <td><?= Yii::t('frontend', 'course_learn_most') ?></td>
                      </tr>
                     
                    </table>
                  </div>
                  
                 
                </div>
              </div>
            <iframe id="hiddenDownloader" src="" style="width: 10px;visibility:hidden;height:0px;  border: 0; position: absolute;">
    </iframe>      
  <style>
<!--
table#table_body td{
    overflow: hidden;
    max-width: 100px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

-->
</style>      

               <script type="text/javascript"> 
 $(function(){
//

    $("#cvs_export").click(function(){
		    var iframe;
		    iframe = document.getElementById("hiddenDownloader");

		    var year_val=$("#year_id").val();
			var month_val=$("#month_id").val();
	        var domain_id=$("#domain_id").val();
	 	    
	        iframe.src = "<?=Url::toRoute(['report/export-study-condition-day',])?>"+"?domain_id="+domain_id+"&year="+year_val+"&month="+month_val;
			
		   
	  });

    $("#year_id").change(function(){
    	getMonthList($("#year_id").val());   	
    });

    $("#query_cscd").click(function(){

    	//reflash('2015','10','C8ECC4CC-7A96-4657-D5D5-C814E46F8945');

    	var year_val=$("#year_id").val();
		var month_val=$("#month_id").val();
        var domain_id=$("#domain_id").val();
        
    	//reflash('2015','10','C8ECC4CC-7A96-4657-D5D5-C814E46F8945','6811AFCA-DAB3-3FB5-3AD1-BDA4AA8B93DC');
    	reflash(year_val,month_val,domain_id);
    });

	$.ajax({
        	   async: false,
			   url: "<?=Url::toRoute(['report/get-query',])?>",
			   data: {id:''},
			   success: function(msg){					
				   for(var i=0;i<msg.domains.length;i++){
				    	 var tag=msg.domains[i];	
				    	 if(tag.share_flag!='1'){			    	 
				    		 $("#domain_id").append("<option value='"+tag.kid+"'>"+tag.domain_name+"</option>");
				    	 }
				   }
				      //ajax end			  
			   }
			 });


	 $.ajax({
		   async: false,
		   url: "<?=Url::toRoute(['report/get-study-condition-day-year',])?>",
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

	   $.get("<?=Url::toRoute(['report/get-study-condition-day-month',])?>"+"?YEAR="+year_id_val,function(data){
		   for(var i=0;i<data.length;i++){
	    	 var mm=data[i];				    	 
	    	 $("#month_id").append("<option value='"+mm.MONTH+"'>"+mm.MONTH+"</option>");
	       }

		});

	}


    function reflash(year_,month_,domain_id_){


    	$.ajax({
			   
			   url: '<?=Url::toRoute(['report/get-study-condition-day-data',])?>',
			   data: {year:year_,month:month_,domain_id:domain_id_},
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
                        <td><?= Yii::t('frontend', 'login_times2_today') ?></td>
                        <td><?= Yii::t('frontend', 'login_times2_today_rate') ?></td>
                        <td><?= Yii::t('frontend', 'learning_time_total_today') ?></td>
                        <td><?= Yii::t('frontend', 'course_rate_higest') ?></td>
                        <td><?= Yii::t('frontend', 'course_learn_most') ?></td>
                    </tr>
 <%});%>
    </script>      
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					 <tr>
                      <td><%=item.time%></td>
                      <td><%=item.log_user_num%></td>
                      <td><%=item.log_user_rate%></td>
                      <td><%=item.acc_study_time%></td>
                      <td align="left" title="<%=item.max_acc_comment_course%>"><span class="preivew"><%=item.max_acc_comment_course%></span></td>
 					  <td align="left" title="<%=item.max_acc_study_course%>"><span class="preivew"><%=item.max_acc_study_course%></span></td>
                    </tr>
 <%});%>
    </script>  