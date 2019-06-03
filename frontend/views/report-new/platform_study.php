<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="panel-default scoreList">
                <div class="panel-body courseInfo">
                
                 <div >
                     <div class="actionBar">
                       <form class="form-inline pull-left">
                       <div class="form-group">
                          <div class="form-group ">
                               <select class="form-control" id="time_id"   name="CourseService[course_type]">
                                                                
                                </select>
                                 </div>
                                  <div class="form-group "> 
                                 <select class="form-control" id="domain_id"   name="CourseService[course_type]">
                                                                
                                </select>
                                
                                  </div>
                          </div>
                       </form>  
                      <form class="form-inline pull-right" style="margin-left:5px;">
                      <div class="form-group">
                                <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="cvs_export"><?=Yii::t('common', 'export_button')?></button>
                       </div>
                    </form>   
                    
                          
                        </div>    
                  </div>
                  <div class="row" id="canvas_div">
                   <div id="echarts" style="margin: 10px auto; width:100%; min-height:350px; height:auto;"></div>
                    
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
 #g2Charts {
    display: block;
    width: 100%;
    height: 400px;
    float: left;
  }
  
  #g2Charts canvas {
    width: 100% !important;
  }
  
  #echarts{pointer-events:none;}
#echarts>div{pointer-events:auto;}
-->
</style>
          <?=Html::jsFile('/static/frontend/js/echarts.min.js')?>
  <script type="text/javascript">
  
  
    $(function(){
//

 	$("#cvs_export").click(function(){
		    var iframe;
		    iframe = document.getElementById("hiddenDownloader");

		    var year_val=$("#time_id").val();
	        var domain_id=$("#domain_id").val();
	 	    
	        iframe.src = "<?=Url::toRoute(['report-new/export-platform-study',])?>"+"?domain_param="+domain_id+"&time_param="+year_val;
			
		   
	  });


   $("#domain_id").change(function(){
	  
		var domain_id_val=$("#domain_id").val();
		var time_id_val=$("#time_id").val();

		console.log("domain_id");
		  reflash(time_id_val,domain_id_val);
	});


   $("#time_id").change(function(){
	  // var domain_id_val=$("#domain_id").val();
	//	var time_id_val=$("#time_id").val();
	//	reflash(time_id_val,domain_id_val);
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

				   for(var j=0;j<msg.years.length;j++){
				    	 var tag=msg.years[j];
				    	 
				    	 $("#time_id").append("<option value='"+tag.YEAR+"'>"+tag.YEAR+"年</option>");
				    	
				   }


				   var time_param_=$("#time_id").val();
				   var domain_param_=$("#domain_id").val();
				   
				   reflash(time_param_,domain_param_);
				      //ajax end
				  
			   }
			 });


	 

//
        });



    function reflash(time_param_,domain_param_){
      var ajaxOpt = {

         url: '<?=Url::toRoute(['report-new/get-platform-study-data',])?>',
         data: {time_param:time_param_,domain_param:domain_param_},
         async: false,
         success: function(msg){

          $("#table_body tbody").empty();

           var lists_h=[1]
           var table_h_templ=_.template($("#table_h_id").html(),{variable: 'data'})({datas:lists_h});
           $("#table_body tbody").append(table_h_templ);

          
           var lists=msg.platformStudy;
          
           var table_tr_templ=_.template($("#table_tr").html(),{variable: 'data'})({datas:lists});
             $("#table_header").after(table_tr_templ);

                     var chart_obj=msg;

                     //
                     $("#echarts").empty();
                     var myChart = echarts.init(document.getElementById('echarts'));
                    
                     var label_obj=chart_obj.label;
                	 var reg_num_obj=chart_obj.reg_num;                  	
                	 var login_num_obj=chart_obj.login_num;
                	 var com_num_obj=chart_obj.com_num;
                	 var certif_num_obj=chart_obj.certif_num;
                 
                       option = {
                    		    title: {
                    		        text: ' '
                    		    },
                    		    tooltip: {
                    		        trigger: 'axis'
                    		    },
                    		    legend: {
                    		        data:['<?= Yii::t('frontend', 'rep_login_num') ?>','<?= Yii::t('frontend', 'rep_reg_num') ?>','<?= Yii::t('frontend', 'rep_com_num') ?>','<?= Yii::t('frontend', 'rep_certif_num') ?>']
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
                    		            name:'<?= Yii::t('frontend', 'rep_login_num') ?>',
                    		            type:'line',
                    		            stack: '',
                    		            data:login_num_obj
                    		        },
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
                    		        },
                    		        {
                    		            name:'<?= Yii::t('frontend', 'rep_certif_num') ?>',
                    		            type:'line',
                    		            stack: '',
                    		            data:certif_num_obj
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
                      <td><?= Yii::t('frontend', 'rep_login_num_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_reg_num_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_com_num_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_duration_rate') ?></td>
                      <td><?= Yii::t('frontend', 'rep_certif_num_rate') ?></td>
                    </tr>
 <%});%>
    </script>      
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					 <tr>
                      <td><%=item.month%><?= Yii::t('frontend', 'month2') ?></td>
                      <td><%=item.login_num%>(<%=item.login_num_rate%>)</td>
                      <td><%=item.reg_num%>(<%=item.reg_num_rate%>)</td>
                      <td><%=item.com_num%>(<%=item.com_num_rate%>)</td>
                      <td><%=item.duration%>(<%=item.duration_rate%>)</td>
                      <td><%=item.certif_num%>(<%=item.certif_num_rate%>)</td>
                    </tr>
 <%});%>
    </script>                 
