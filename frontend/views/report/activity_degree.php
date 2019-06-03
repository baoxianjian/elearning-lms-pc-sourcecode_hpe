<?php
use yii\helpers\Url;
?>

<div class="panel-default scoreList">
                <div class="panel-body">
                 
                 
                  <div class="row">
                   <div class="actionBar">
                      <form class="form-inline pull-left">
                        <div class="form-group">
                          <div class="form-group ">
                               <select class="form-control" id="time_id"  style="width: 150px">
                                                                
                                </select>
                           </div>
                          <div class="form-group ">
                                 <select class="form-control" id="domain_id"  style="width: 150px">
                                                                
                                </select>
                            </div>
                        </div>
                      </form> 
                       <div class="chartExample pull-right">
                        <label>
                          <input type="checkbox" checked="checked" value="registed"><?= Yii::t('frontend', 'login_times') ?>
                          <i style="background:red"></i>
                        </label>
                        <label>
                          <input type="checkbox" checked="checked" value="registed"><?= Yii::t('frontend', 'login_users') ?>
                          <i style="background:blue"></i>
                        </label>
                        <label>
                          <input type="checkbox" checked="checked" value="registed"><?= Yii::t('frontend', 'login_users_active') ?>
                          <i style="background:yellow"></i>
                        </label>
                      </div>
                      </div>
                  </div>
                  
                  <div class="row" id="canvas_div">
                   
                    <canvas id="canvas" height="100px" width="450px"></canvas>
                  </div>
                 
                 <div class="row">
                  </div>
                  
                  <table id="table_body" class="table table-bordered table-hover table_teacher">
                    <tr id="table_header">
                      <td><?= Yii::t('frontend', 'month') ?></td>
                      <td><?= Yii::t('frontend', 'login_times') ?></td>
                      <td><?= Yii::t('frontend', 'login_users') ?></td>
                      <td><?= Yii::t('frontend', 'login_users_active') ?></td>
                     
                    </tr>
                   
                  
                   
                  </table>
                 
                </div>
              </div>
              
              
               <script type="text/javascript">
  
  
    $(function(){
//

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
			   url: "<?=Url::toRoute(['report/get-query',])?>",
			   data: {id:''},
			   success: function(msg){
					
				   for(var i=0;i<msg.domains.length;i++){
				    	 var tag=msg.domains[i];
				    	 var tag=msg.domains[i];	
				    	 if(tag.share_flag!='1'){
				    		 $("#domain_id").append("<option value='"+tag.kid+"'>"+tag.domain_name+"</option>");
				    	 }
				   }

				   for(var j=0;j<msg.years.length;j++){
				    	 var tag=msg.years[j];
				    	 
				    	 $("#time_id").append("<option value='"+tag.YEAR+"'>"+tag.YEAR+"<?= Yii::t('common', 'time_year') ?></option>");
				    	
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


    	$.ajax({
			   
			   url: '<?=Url::toRoute(['report/get-activity-degree-data',])?>',
			   data: {time_param:time_param_,domain_param:domain_param_},
			   async: false,
			   success: function(msg){

				  $("#table_body tbody").empty();

				   var lists_h=[1]
				   var table_h_templ=_.template($("#table_h_id").html(),{variable: 'data'})({datas:lists_h});
				   $("#table_body tbody").append(table_h_templ);

				   console.log(msg.statisticalAnalysis);
				   var lists=msg.statisticalAnalysis;
				  
				   var table_tr_templ=_.template($("#table_tr").html(),{variable: 'data'})({datas:lists});
			       $("#table_header").after(table_tr_templ);

                     var chart_obj=msg;
                     
                     $("#canvas_div").empty();
                     $("#canvas_div").append('<canvas id="canvas" height="100px" width="450px"></canvas>');
                     
                         if(chart_obj.label.length>0){
                         	       var lineChartData = {
                   	   	  		      labels: chart_obj.label,
                   	   	  		      datasets: [{
		                      	   	        label: "My First dataset",
		                      	   	        fillColor: "rgba(220,220,220,0.2)",
		                      	   	        strokeColor: "red",
		                      	   	        pointColor: "red",
		                      	   	        pointStrokeColor: "red",
		                      	   	        pointHighlightFill: "red",
		                      	   	        pointHighlightStroke: "red",
                   	   	  		        data:chart_obj.login_number
                   	   	  		      },
                   	   	  		      {
                   	   	  		        label: "My Second dataset",
                   	   	                fillColor: "rgba(151,187,205,0.2)",
		                      	   	        strokeColor: "blue",
		                      	   	        pointColor: "blue",
		                      	   	        pointStrokeColor: "blue",
		                      	   	        pointHighlightFill: "blue",
		                      	   	        pointHighlightStroke: "blue",
	                      	   	  		        data:chart_obj.login_user_num
	                      	   	  		      },
	                      	   	  		   {
		                      	   	  		        label: "My First dataset",
		                      	   	  		        fillColor: "rgba(120,120,120,0.2)",
		                      	   	  		        strokeColor: "yellow",
		                      	   	  		        pointColor: "yellow",
		                      	   	  		        pointStrokeColor: "yellow",
		                      	   	  		        pointHighlightFill: "yellow",
		                      	   	  		        pointHighlightStroke: "yellow",
		                      	   	  		        data:chart_obj.active_user
		                      	   	  		      }]
                   	   	  		    }

                         	       
                   	   	  		    var ctx1 = document.getElementById("canvas").getContext("2d");

                   	   	  		  
                   	   	  		    var myChart = new Chart(ctx1).Line(lineChartData, {
                     	   	  		        responsive: true,
                                        bezierCurve: false
                     	   	  		      })
                                  ,   cachedDatasets = myChart.datasets.concat()
                                  ,   $checkbox = $(".chartExample input[type=checkbox]")
                                  ;
                                  $checkbox.click(function ()
                                  {
                                    var L = $checkbox.length
                                    ,   index = $checkbox.index($(this))
                                    ,   i = 0
                                    ;
                                    myChart.datasets = [];
                                    for(; i<L; i++)
                                    {
                                      $checkbox.eq(i).is(":checked") && myChart.datasets.push(cachedDatasets[i]);
                                    }
                                    myChart.update();
                                  });

                         }else{
                              
                             

                         }
   	  			  


   	  	  	     
			   }
	      });
    	
        }
    
    </script>  
    
      <script id="table_h_id" type="text/template">
<%_.each(data.datas, function(item) {%>
					  <tr id="table_header">
                      <td><?= Yii::t('frontend', 'month') ?></td>
                      <td><?= Yii::t('frontend', 'login_times') ?></td>
                      <td><?= Yii::t('frontend', 'login_users') ?></td>
                      <td><?= Yii::t('frontend', 'login_users_active') ?></td>
                     
                    </tr>
 <%});%>
    </script>      
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					 <tr>
                      <td><%=item.month%><?= Yii::t('frontend', 'month2') ?></td>
                      <td><%=item.login_number%></td>
                      <td><%=item.login_user_num%></td>
                      <td><%=item.active_user%></td>
                     
                    </tr>
 <%});%>
    </script>  