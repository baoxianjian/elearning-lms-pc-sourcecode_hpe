<?php
use yii\helpers\Url;

?>

<form id="course_rate_form_id">
 <input type="hidden" id="year_choice_id" />
                         
                <div class=" panel-default scoreList">
                  <div class="panel-body">
                    <div class="row">
                      <div class="btn-group timeScope pull-left">
                        <button id="btn_dropdown" class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"><?= Yii::t('frontend', 'all_time_area') ?> &nbsp;<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                         <? foreach ($times as $time): ?>
                          <li><a href="javascript:void(0)" onclick="selectTemplate('course_rate_form_id',this,'<?=$time['YEAR'] ?>')"><?=$time['YEAR'] ?><?= Yii::t('common', 'time_year') ?></a></li>
                        
                         <? endforeach; ?>
                        </ul>
                      </div>
                    </div>
                    <div class="infoBlock" id="course_rate_id_id">
                    
                    </div>
                  </div>
                </div>
           
  </form>         
           <script type="text/javascript">


           function selectTemplate(formId, obj, duration) {
        	   console.log("1111");
               $("#" + formId + " #btn_dropdown").html($(obj).html() + ' &nbsp;<span class="caret">');
               $("#" + formId + " #year_choice_id").val(duration);
               $("#course_rate_id_id").empty();
               do_course_rate(duration);
           }
                   

           $(function(){
               //
           do_course_rate();
  //
           });


           function do_course_rate(time){

        	   $.ajax({
              	   async: false,
      			   url: "<?=Url::toRoute(['teacher-manage/courses',])?>",
      			   data: {id:'<?=$id?>',time:time},
      			   success: function(msg){

      				 console.log(msg.result);
      				 var lists2=msg.result;
      	  			 var t2_templ2=_.template($("#t5").html(),{variable: 'data'})({datas:lists2});
      	  			 $("#course_rate_id_id").append(t2_templ2); 

      	  			 for(var i=0;i<msg.result.length;i++){
                        var chart_obj=msg.result[i];
                        console.log("111height");
                        console.log(chart_obj.chart_label.length);
                        
                            if(chart_obj.chart_label.length>0){
                            	       var lineChartData = {
                      	   	  		      labels: chart_obj.chart_label,
                      	   	  		      datasets: [{
                      	   	  		        label: "My First dataset",
                      	   	  		        fillColor: "rgba(220,220,220,0.2)",
                      	   	  		        strokeColor: "rgba(220,220,220,1)",
                      	   	  		        pointColor: "rgba(220,220,220,1)",
                      	   	  		        pointStrokeColor: "#fff",
                      	   	  		        pointHighlightFill: "#fff",
                      	   	  		        pointHighlightStroke: "rgba(220,220,220,1)",
                      	   	  		        data:chart_obj.chart_data
                      	   	  		      }]
                      	   	  		    }

                      	   	  		    var ctx1 = document.getElementById(chart_obj.kid).getContext("2d");

                      	   	  		  
                      	   	  		    new Chart(ctx1).Line(lineChartData, {
                      	   	  		      responsive: true
                      	   	  		    });

                            }else{
                                  console.log("height");
                                  $("#"+chart_obj.kid).height(10);
                                

                            }
      	  			  


      	  	  	     }
      	  		    
      				
    			   }
    			 }); 

            }
  </script>
  
  
  <script id="t5" type="text/template">
 <%_.each(data.datas, function(item) {%>
    <div class="row">
                        <div class="col-md-5 col-sm-5">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label"><%if(item.course_type == "1") {%>[<?=Yii::t('common', 'face_to_face')?>]<%}%>  <%if(item.course_type == "0") {%>[<?=Yii::t('common', 'online')?>]<%}%><%=item.course_name%></label>
                          </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label"><%=item.open_start_time%>-<%=item.open_end_time%></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label"><?=Yii::t('frontend', 'score_by_sutdent')?>:<%=item.per_marks%></label>
                          </div>
                        </div>
                        <div class="col-md-12 col-sm-12">
                          <canvas id="<%=item.kid%>" height="100" width="600"></canvas>
                        </div>
    </div>

      <%});%>
</script>     