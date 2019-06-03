<?php
use yii\helpers\Url;
?>


              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="actionBar">
                    <form class="form-inline pull-left">
                      <div class="form-group">
                       
                        <label><?=Yii::t('common', 'domain')?>:</label>
                        <div class="form-group ">
                          <select class="form-control" name="CourseService[course_type]" id="domain_id">
                            
                          </select>
                        </div>
                      </div>
                    </form>
                    <form class="form-inline pull-right" style="margin-left:5px;">
                      <label class="nameInput"><?= Yii::t('common', 'real_name') ?>:</label>
                      <div class="form-group">
                        
                          <input type="text" data-mult="1" id="user_name_id" class="form-control nameInput " placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('common', 'real_name')])?>">
                          <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="cvs_export"><?=Yii::t('common', 'export_button')?></button>
                        <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="query_cscd"><?=Yii::t('common', 'search')?></button>
                         
                      </div>
                    </form>
                  </div>
                 
                  <div class="changeLearningReport2">
                    <table class="table table-bordered table-hover table-striped table-center" id="table_body">
                      <tr>
                        <td><?= Yii::t('common', 'real_name') ?></td>
                        <td><?= Yii::t('frontend', 'department') ?></td>
                        <td><?= Yii::t('frontend', 'position') ?></td>
                        <td><?= Yii::t('frontend', 'login_times_total') ?></td>
                        <td><?= Yii::t('frontend', 'login_last_time') ?></td>
                        <td><?= Yii::t('frontend', 'learning_time_total') ?></td>
                        <td><?= Yii::t('frontend', 'register_course_number') ?></td>
                        <td><?= Yii::t('frontend', 'register_finish') ?></td>
                        <td><?= Yii::t('frontend', 'course_must_rate') ?></td>
                        <td><?= Yii::t('frontend', 'course_must_average') ?></td>
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

   var queryList;
   function flash(kid){
		   var data_url_val="<?=Url::toRoute(['report/get-users',])?>"+"?domain_id="+kid;	

		   console.log(data_url_val);	
		   $("#user_name_id").attr("data-url",data_url_val);
		   queryList = app.queryList("#user_name_id"); 
   }
   
 $(function(){
//

      var is_flash_flag=0;

     $("#cvs_export").click(function(){
		    var iframe;
		    iframe = document.getElementById("hiddenDownloader");

		    var obj=queryList.get();
	 	    
	 	    var domain_id=$("#domain_id").val();
	    	if(_.isEmpty(obj)){
	    		 
	    		 iframe.src = "<?=Url::toRoute(['report/export-user-study-condition-gather',])?>"+"?domain_id="+domain_id+"&user_id=";
	        } else{  	
	            var user_id="";
	            for(var i=0,len=obj.length;i<len;i++){
	            	user_id=user_id+"'"+obj[i].uid+"',";
	            }
	            user_id=user_id.substring(0,user_id.length-1)
	            
	            iframe.src = "<?=Url::toRoute(['report/export-user-study-condition-gather',])?>"+"?domain_id="+domain_id+"&user_id="+user_id;
	        }
			
		   
	  });

    $("#domain_id").change(function(){
    	queryList.reset();
    	flash($("#user_name_id").val());
    });

    $("#query_cscd").click(function(){

    	var obj=queryList.get();
 	    
 	    var domain_id=$("#domain_id").val();
    	if(_.isEmpty(obj)){
    		reflash(domain_id,"");
        } else{  	
            var user_id="";
            for(var i=0,len=obj.length;i<len;i++){
            	user_id=user_id+"'"+obj[i].uid+"',";
            }
            user_id=user_id.substring(0,user_id.length-1)
            
            //reflash('C8ECC4CC-7A96-4657-D5D5-C814E46F8945','B63D0811-EB6A-CCE0-2C58-65B1AC8A672B');
            reflash(domain_id,user_id);
        }
    });

	$.ajax({
        	   async: false,
			   url: "<?=Url::toRoute(['report/get-query',])?>",
			   data: {id:''},
			   success: function(msg){	
				   console.log(msg);				
				   for(var i=0;i<msg.domains.length;i++){
					   
				    	 var tag=msg.domains[i];	
				    	 if(tag.share_flag!='1'){
				    		 $("#domain_id").append("<option value='"+tag.kid+"'>"+tag.domain_name+"</option>");

				    		 if(is_flash_flag==0){
				    			 var xxxx=msg.domains[i];						  
								 flash(xxxx.kid);
								 is_flash_flag=is_flash_flag+1;	 
					    	  }	 
						 }			    	 
				    	
				   }
				  		  
			   }
			 });
//
        });



    function reflash(domain_id_,user_id_){


    	$.ajax({
			   
			   url: '<?=Url::toRoute(['report/get-user-study-condition-gather-data',])?>',
			   data: {domain_id:domain_id_,user_id:user_id_},
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
                       
                        <td><?= Yii::t('common', 'real_name') ?></td>
                        <td><?= Yii::t('frontend', 'department') ?></td>
                        <td><?= Yii::t('frontend', 'position') ?></td>
                        <td><?= Yii::t('frontend', 'login_times_total') ?></td>
                          <td><?= Yii::t('frontend', 'login_last_time') ?></td>
                        <td><?= Yii::t('frontend', 'learning_time_total') ?></td>
                        <td><?= Yii::t('frontend', 'register_course_number') ?></td>
                        <td><?= Yii::t('frontend', 'register_finish_number') ?></td>
                        <td><?= Yii::t('frontend', 'course_must_rate') ?></td>
                        <td><?= Yii::t('frontend', 'course_must_average') ?></td>
                    
                    </tr>
 <%});%>
    </script>      
    
    
    
     <script id="table_tr" type="text/template">
<%_.each(data.datas, function(item) {%>
					 <tr class="limit_class">
                      <td title="<%=item.user_id%>"><%=item.user_id%></td>
                      <td><%=item.orgnization_name%></td>
                      <td><%=item.position_name%></td>
                      <td><%=item.login_number%></td>
                      <td title="<%=item.last_login_at%>"><%=item.last_login_at%></td>
                      <td><%=item.acc_study_time%></td>
					  <td><%=item.reg_course_num%></td>
					  <td><%=item.comp_course_num%></td>
					  <td><%=item.obliga_course_comp_rate%></td>
					  <td><%=item.obliga_course_score%></td>
                    </tr>
 <%});%>
    </script>               