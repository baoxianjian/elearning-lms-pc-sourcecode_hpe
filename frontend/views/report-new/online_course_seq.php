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
                                 <div class="form-group "> 
                                    <select class="form-control" id="day_type_id"   name="CourseService[course_type]">  
                                      <option value="day"><?=Yii::t('common', 'time_day')?></option>
		                              <option value="week"><?=Yii::t('frontend', 'rep_week')?></option>
		                              <option value="month"><?=Yii::t('frontend', 'month2')?></option>
		                                                                                
                                    </select>
                                  </div>
                                
                                 <input class="form-control -table-report"
                                   data-type="rili" id="begin_time"  type="text" style="width:100px " placeholder="<?= Yii::t('frontend', 'exam_kaishishijian') ?>"> <?= Yii::t('frontend', 'exam_to') ?>
                                 <input class="form-control -table-report"
                                   data-type="rili" id="end_time"  type="text" style="width:100px " placeholder="<?= Yii::t('frontend', 'end_time') ?>">
                          </div>
                          <div class="form-group "> 
                                    <select class="form-control" id="domain_id"   name="CourseService[course_type]">                                                             
                                    </select>
                                    
                                     
                          </div>
                                 
                          </div>
                       </form>  
                      <form class="form-inline pull-right" style="margin-left:5px;">
                      <div class="form-group">
                       <input type="text" id="course_name_id" class="form-control clickSearch" placeholder="<?= Yii::t('frontend','rep_input_course_name_or_code') ?>">
                      
                               <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="cvs_export"><?=Yii::t('common', 'export_button')?></button>
                     		    <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="query_cscd"><?=Yii::t('frontend', 'tag_query')?></button>
                       </div>
                    </form>   
                   
                    
                          
                        </div>    
                  </div>
                  <div class="row" id="canvas_div">
                   
                  <div id="echarts" style="margin: 10px auto; width:100%; height:auto;"></div>
                 
                  </div>
                  <div class="row">
                  </div>
                  <table class="table table-bordered table-hover table_teacher" id="table_body">
                  
                    <tr id="table_header">
                      <td><?= Yii::t('frontend', 'date_text') ?></td>
                      <td><?= Yii::t('frontend', 'rep_total_user') ?></td>
                      <td><?= Yii::t('frontend', 'register_number') ?></td>
                      <td><?= Yii::t('frontend', 'finish_number') ?></td>
                      <td><?= Yii::t('frontend', 'course_completion_rate') ?></td>
                      <td><?= Yii::t('frontend', 'grade_average') ?></td>
                    </tr>
                   
                    
                  </table>
                 
                </div>
              </div>
                <iframe id="hiddenDownloader" src="" style="width: 10px;visibility:hidden;height:0px;  border: 0; position: absolute;">
    </iframe>  
    
    <style>
<!--
.form-control.-table-report {
 float: none;
 margin-left: 1em;
}

 #g2Charts {
    display: block;
    width: 100%;
    height: 400px;
    float: left;
  }
  
  #g2Charts canvas {
    width: 100% !important;
  }

   
-->
</style>
      
  <script type="text/javascript">


  var course_id='';

  var queryList;
  function flash(){
		   var data_url_val="<?=Url::toRoute(['report-new/get-no-courses',])?>"+"?course_type=0";	

		   console.log(data_url_val);	
		   $("#course_name_id").attr("data-url",data_url_val);
		   queryList = app.queryList("#course_name_id"); 
  }
  
  
    $(function(){
//

     $('.clickSearch').bind('keydown', function(event) {
	    if (event.keyCode == "13") {
	      event.preventDefault()
	      $('#query_cscd').trigger("click");
	    }
	  });

    app.genCalendar();
    flash();

    $("#domain_id").change(function(){
    	queryList.reset();
    	flash();
    });
    
 	$("#cvs_export").click(function(){
		     var iframe;
		     iframe = document.getElementById("hiddenDownloader");

		     var obj=queryList.get();
	    	 var course_id_val='';
	    	 if(!obj.uid){
	    		 app.showMsg("<?= Yii::t('frontend', 'rep_input_course_name_or_code') ?>");
	    		 return;
		     }else{
		    	 course_id_val=obj.uid;
			 }
		    
		    var type_val=$("#day_type_id").val();

		    var begin_time=$("#begin_time").val();

	     	if(!begin_time){
	     		app.showMsg("<?= Yii::t('frontend', 'exam_kaishishijian_buneng_null') ?>");
	     		return;
		    }
	     	
	 		var end_time=$("#end_time").val();

	 		if(!end_time){
	 			if('month'==type_val){
	 				end_time=getCurrentMonth();
		 		}else{
		 			end_time=getCurrentDay();
			 	}
		 	}

	 		if('month'==type_val){
	 			var arys1=begin_time.split('-');      
				var sdate=new Date(arys1[0],parseInt(arys1[1]-1));      
				var arys2=end_time.split('-');      
			    var edate=new Date(arys2[0],parseInt(arys2[1]-1));      
				if(sdate >= edate) {
					app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
					return;
				}  		
		 	}else{
		 		var arys1=begin_time.split('-');      
				var sdate=new Date(arys1[0],parseInt(arys1[1]-1),arys1[2]);        
				var arys2=end_time.split('-');      
				var edate=new Date(arys2[0],parseInt(arys2[1]-1),arys2[2]);       
				if(sdate >= edate) {
					app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
					return;
				}      
		 		
			 }
	 		
	        var domain_id=$("#domain_id").val();
	      

	      
	        iframe.src = "<?=Url::toRoute(['report-new/export-online-course-seq',])?>"+"?domain_param="+domain_id+"&begin="+begin_time
	        +"&end="+end_time+"&circle="+type_val+"&course_id="+course_id_val;
			
		   
	  });

	     $.ajax({
        	   async: false,
			   url: "<?=Url::toRoute(['report-new/get-query-no-share',])?>",
			   data: {id:''},
			   success: function(msg){	

				   $("#domain_id").append("<option value='all'><?= Yii::t('frontend', 'all_domain') ?></option>");		  
				   for(var i=0;i<msg.domains.length;i++){
				    	 var tag=msg.domains[i];
				    	 
				    	 $("#domain_id").append("<option value='"+tag.kid+"'>"+tag.domain_name+"</option>");
				   }

				      //ajax end
				  
			   }
	    });

	     $("#day_type_id").change(function(){

	    	 $("#begin_time").val('');
    		 $("#end_time").val('');
	    	 if('month'==$("#day_type_id").val()){    		
		    	 $("#begin_time").attr("data-month-only",'1');
		    	 $("#end_time").attr("data-month-only",'1');
		    	 $("#begin_time").attr("placeholder",'<?= Yii::t('frontend', 'exam_kaishishijian') ?>');
		    	 $("#end_time").attr("placeholder",'<?= Yii::t('frontend', 'end_time') ?>');
		    	 app.genCalendar();
		     }else{
		    	 $("#begin_time").removeAttr("data-month-only");
		    	 $("#end_time").removeAttr("data-month-only");
		    	 $("#begin_time").attr("placeholder",'<?= Yii::t('frontend', 'exam_kaishishijian') ?>');
		    	 $("#end_time").attr("placeholder",'<?= Yii::t('frontend', 'end_time') ?>');
		    	 app.genCalendar();
			 }
	    	 queryList.reset();
		 });


	     $("#query_cscd").click(function(){

	    	 var obj=queryList.get();
	    	 var course_id_val='';
	    	 if(!obj.uid){
	    		 app.showMsg("<?= Yii::t('frontend', 'rep_input_course_name_or_code') ?>");
	    		 return;
		     }else{
		    	 course_id_val=obj.uid;
			 }

	    	 var type_val=$("#day_type_id").val();
	    	 var begin_time=$("#begin_time").val();

	     	if(!begin_time){
	     		app.showMsg("<?= Yii::t('frontend', 'exam_kaishishijian_buneng_null') ?>");
	     		return;
		    }
	     	
	 		var end_time=$("#end_time").val();

	 		if(!end_time){
	 			if('month'==type_val){
	 				end_time=getCurrentMonth();
		 		}else{
		 			end_time=getCurrentDay();
			 	}
	 			
		 	}

	 		if('month'==type_val){
	 			var arys1=begin_time.split('-');      
				var sdate=new Date(arys1[0],parseInt(arys1[1]-1));      
				var arys2=end_time.split('-');      
			    var edate=new Date(arys2[0],parseInt(arys2[1]-1));      
				if(sdate >= edate) {
					app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
					return;
				}  		
		 	}else{
		 		var arys1=begin_time.split('-');      
				var sdate=new Date(arys1[0],parseInt(arys1[1]-1),arys1[2]);        
				var arys2=end_time.split('-');      
				var edate=new Date(arys2[0],parseInt(arys2[1]-1),arys2[2]);       
				if(sdate >= edate) {
					app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
					return;
				}      
		 		
			 }
	 		
	        var domain_id=$("#domain_id").val();
	       
	        reflash(begin_time,end_time,domain_id,type_val,course_id_val);
	     });

	 

//
        });


    function getCurrentMonth(){

    	var myDate = new Date();
        
    	var yyyy=myDate.getFullYear();
    	var mm=myDate.getMonth()+1;   
    	if(mm<10){
    		mm="0"+mm;
        }

    	return yyyy+"-"+mm  
    }

    function getCurrentDay(){

    	var myDate = new Date();
        
    	var yyyy=myDate.getFullYear();
    	var mm=myDate.getMonth()+1;   
    	if(mm<10){
    		mm="0"+mm;
        }
    	var dd=myDate.getDate();    

    	return yyyy+"-"+mm+"-"+dd;  
    }



    function reflash(begin_time_,end_time_,domain_param_,type_,course_id_){
      var ajaxOpt = {

         url: '<?=Url::toRoute(['report-new/get-online-course-seq-data',])?>',
         data: {begin:begin_time_,end:end_time_,domain_param:domain_param_,circle:type_,course_id:course_id_},
         async: false,
         success: function(msg){

             $("#table_body tbody").empty();
             var lists_h=[1]
             var table_h_templ=_.template($("#table_h_id").html(),{variable: 'data'})({datas:lists_h});
             $("#table_body tbody").append(table_h_templ);

             var lists=msg.onlineCourseSeq;
          
             var table_tr_templ=_.template($("#table_tr").html(),{variable: 'data'})({datas:lists});
             $("#table_header").after(table_tr_templ);

             //
             var chart_obj=msg;
            
             //
              $("#echarts").empty(); 
            

var label_obj=chart_obj.label;
var reg_num_obj=chart_obj.reg_num;                  	 	
var com_num_obj=chart_obj.com_num;
if(label_obj){

	  $("#echarts").height("350px");
	  $("#echarts").css("float","left");
}
var myChart = echarts.init(document.getElementById('echarts'));

              option = {
          		    title: {
          		        text: ' '
          		    },
          		    tooltip: {
          		        trigger: 'axis'
          		    },
          		    legend: {
          		        data:['<?= Yii::t('frontend', 'rep_reg_num') ?>','<?= Yii::t('frontend', 'rep_com_num') ?>']
          		    },
          		    grid: {
          		        left: '7%',
          		        right: '7%',
          		        bottom: '3%',
          		        containLabel: true
          		    },
          		    toolbox: {
          		        feature: {
          		            saveAsImage: {}
          		        }
          		    },
          		    xAxis: {
          		        type: 'category',
          		        boundaryGap: false,
          		        data: label_obj
          		    },
          		    yAxis: {
          		        type: 'value'
          		    },
          		    series: [
          		        {
          		            name:'<?= Yii::t('frontend', 'rep_reg_num') ?>',
          		            type:'line',
          		            stack: '',
          		            data:reg_num_obj
          		        },
          		        {
          		            name:'<?= Yii::t('frontend', 'rep_com_num') ?>',
          		            type:'line',
          		            stack: '',
          		            data:com_num_obj
          		        }
          		    ]
          		};

             myChart.setOption(option);
window.onresize = myChart.resize;
             //  

             //      
                    
                     
             }
        };
    	   $.ajax(ajaxOpt);
        }
    
    </script>  
    
      <script id="table_h_id" type="text/template">
<%_.each(data.datas, function(item) {%>
					<tr id="table_header">
                      <td><?= Yii::t('frontend', 'date_text') ?></td>
                      <td><?= Yii::t('frontend', 'rep_total_user') ?></td>
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
                      <td><%=item.op_time%></td>
                      <td><%=item.total_user_num%></td>
                      <td><%=item.reg_num%></td>
                      <td><%=item.com_num%></td>
 					  <td><%=item.com_num_rate%>%</td>
 					  <td><%=item.score%></td>
                    </tr>
 <%});%>
    </script>                 

 