<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="panel-default scoreList " >
                <div class="panel-body courseInfo">
                
                 <div class="row">
                     <div class="actionBar">
                       <form class="form-inline pull-left">
                       <div class="form-group">
                          <div class="form-group ">
                                 <input class="form-control -table-report" style="width:100px "
                                   data-type="rili" id="begin_time" data-month-only="1" type="text" placeholder="<?= Yii::t('frontend', 'exam_kaishishijian') ?>"> <?= Yii::t('frontend', 'exam_to') ?>
                                 <input class="form-control -table-report"  style="width:100px "
                                   data-type="rili" id="end_time" data-month-only="1" type="text" placeholder="<?= Yii::t('frontend', 'end_time') ?>">
                                 </div>
                                  <div class="form-group "> 
                                    <select class="form-control" id="domain_id"   name="CourseService[course_type]">                                                             
                                    </select>
                                  </div>
                                  <div class="form-group "> 
                                    <select class="form-control" id="term_type_id"   name="CourseService[course_type]">  
                                      <option value="0"><?=Yii::t('frontend', 'rep_all')?></option>
		                              <option value="1"><?=Yii::t('frontend', 'rep_pc')?></option>
		                              <option value="2"><?=Yii::t('frontend', 'rep_weixin')?></option>
		                              <option value="3">APP</option>                                                           
                                    </select>
                                  </div>
                          </div>
                       </form>  
                      <form class="form-inline pull-right" style="margin-left:5px;">
                      <div class="form-group">
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
                      <td><?= Yii::t('frontend', 'month') ?></td>
                      <td><?= Yii::t('frontend', 'rep_login_num_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_reg_num_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_com_num_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_duration_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_certif_num_rate') ?></td>
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
    
      <?=Html::jsFile('/static/frontend/js/echarts.min.js')?>  
  <script type="text/javascript">
  
  
    $(function(){
//
    app.genCalendar();
    
 	$("#cvs_export").click(function(){
		    var iframe;
		    iframe = document.getElementById("hiddenDownloader");

		    var begin_time=$("#begin_time").val();

	     	if(!begin_time){
	     		app.showMsg("<?= Yii::t('frontend', 'exam_kaishishijian_buneng_null') ?>");
	     		return;
		    }
	     	
	 		var end_time=$("#end_time").val();

	 		if(!end_time){
	 			end_time=getCurrentMonth();
		 	}

	 		var arys1=begin_time.split('-');      
			var sdate=new Date(arys1[0],parseInt(arys1[1]-1));      
			var arys2=end_time.split('-');      
		    var edate=new Date(arys2[0],parseInt(arys2[1]-1));      
			if(sdate >= edate) {
				app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
				return;
			}    
	 		
	        var domain_id=$("#domain_id").val();
	        var type_val=$("#term_type_id").val();

	      
	        iframe.src = "<?=Url::toRoute(['report-new/export-active-degree',])?>"+"?domain_param="+domain_id+"&begin="+begin_time
	        +"&end="+end_time+"&type_param="+type_val;
			
		   
	  });

	     $.ajax({
        	   async: false,
			   url: "<?=Url::toRoute(['report-new/get-query-no-share',])?>",
			   data: {id:''},
			   success: function(msg){			  
				   for(var i=0;i<msg.domains.length;i++){
				    	 var tag=msg.domains[i];
				    	 
				    	 $("#domain_id").append("<option value='"+tag.kid+"'>"+tag.domain_name+"</option>");
				   }

				      //ajax end
				  
			   }
	    });


	     $("#query_cscd").click(function(){

	    	 var begin_time=$("#begin_time").val();

	     	if(!begin_time){
	     		app.showMsg("<?= Yii::t('frontend', 'exam_kaishishijian_buneng_null') ?>");
	     		return;
		    }
	     	
	 		var end_time=$("#end_time").val();

	 		if(!end_time){
	 			
	 			end_time=getCurrentMonth();
	 			
		 	}

	 		var arys1=begin_time.split('-');      
			var sdate=new Date(arys1[0],parseInt(arys1[1]-1));      
			var arys2=end_time.split('-');      
		    var edate=new Date(arys2[0],parseInt(arys2[1]-1));      
			if(sdate >= edate) {
				app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
				return;
			}      
	 		
	        var domain_id=$("#domain_id").val();
	        var type_val=$("#term_type_id").val();
	        
	        reflash(begin_time,end_time,domain_id,type_val);
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

    function reflash(begin_time_,end_time_,domain_param_,type_){
      var ajaxOpt = {

         url: '<?=Url::toRoute(['report-new/get-active-degree-data',])?>',
         data: {begin:begin_time_,end:end_time_,domain_param:domain_param_,type_param:type_},
         async: false,
         success: function(msg){

            $("#table_body tbody").empty();
             var lists_h=[1]
             var table_h_templ=_.template($("#table_h_id").html(),{variable: 'data'})({datas:lists_h});
             $("#table_body tbody").append(table_h_templ);

             var lists=msg.activeDegree;
          
             var table_tr_templ=_.template($("#table_tr").html(),{variable: 'data'})({datas:lists});
             $("#table_header").after(table_tr_templ);

             //
              $("#echarts").empty(); 
             
              var chart_arr=msg.chart;
              var month=[];
              var pc_login_nums=[];
              var weixin_login_nums=[];
              var app_login_nums=[];
              for(var i=0;i<chart_arr.length;i++){
                  var data_tmp= chart_arr[i];

                  month.push(data_tmp.op_time); 
                  pc_login_nums.push(parseInt(data_tmp.pc)); 
                  weixin_login_nums.push(parseInt(data_tmp.weixin)); 
                  app_login_nums.push(parseInt(data_tmp.app)); 
             
               }
              if(month){
              
            	  $("#echarts").height(350);
              }
              var myChart = echarts.init(document.getElementById('echarts'));
             
option = {
    title: {
        text: ' '
    },
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['<?= Yii::t('frontend', 'rep_pc_login_num') ?>','<?= Yii::t('frontend', 'rep_weixin_login_num') ?>','<?= Yii::t('frontend', 'rep_app_login_num') ?>']
    },
    toolbox: {
        feature: {
            saveAsImage: {}
        }
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis : [
        {
            type : 'category',
            boundaryGap : false,
            data : month
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [
        {
            name:'<?= Yii::t('frontend', 'rep_pc_login_num') ?>',
            type:'line',
            stack: ' ',
            areaStyle: {normal: {}},
            data:pc_login_nums
        },
        {
            name:'<?= Yii::t('frontend', 'rep_weixin_login_num') ?>',
            type:'line',
            stack: ' ',
            areaStyle: {normal: {}},
            data:weixin_login_nums
        },
        {
            name:'<?= Yii::t('frontend', 'rep_app_login_num') ?>',
            type:'line',
            stack: ' ',
            areaStyle: {normal: {}},
            data:app_login_nums
        }
        
    ]
};

myChart.setOption(option);
window.onresize = myChart.resize;
             //      
                    
                     
             }
        };
    	   $.ajax(ajaxOpt);
        }
    
    </script>  
    
      <script id="table_h_id" type="text/template">
<%_.each(data.datas, function(item) {%>
					  <tr id="table_header">
                      <td><?= Yii::t('frontend', 'month') ?></td>
                      <td><?= Yii::t('frontend', 'login_users') ?></td>
                      <td><?= Yii::t('frontend', 'login_users_rate') ?>(%)</td>
                      <td><?= Yii::t('frontend', 'rep_login_num') ?></td>
                    </tr>
 <%});%>
    </script>      
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					 <tr>
                      <td><%=item.op_time%></td>
                      <td><%=item.login_user_num%></td>
                      <td><%=item.login_user_num_rate%>%</td>
                      <td><%=item.login_num%></td>
                    </tr>
 <%});%>
    </script>                 

   