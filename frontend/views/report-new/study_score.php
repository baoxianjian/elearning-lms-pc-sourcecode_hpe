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
                                   <select class="form-control" id="domain_id"   name="CourseService[course_type]">                                                             
                                    </select>
                                    
                                   
                      
                               </div>
                          </div>
                       </form>  
                      <form class="form-inline pull-right" style="margin-left:5px;">
                      <div class="form-group">
                      
                                    <input type="text" data-url="<?=Url::toRoute(['report-new/get-ext-infos',])?>" id="object_id" class="form-control clickSearch" placeholder="<?= Yii::t('frontend','rep_input_ext_info') ?>">
                                     <input type="text" style="width:200px" id="user_id" class="form-control clickSearch" placeholder="<?= Yii::t('frontend','rep_input_user_info') ?>">
                      
                                    <input type="text" style="width:200px" id="course_name_id" class="form-control clickSearch" placeholder="<?= Yii::t('frontend','rep_input_course_name_or_code') ?>">
                      
                               <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="cvs_export"><?=Yii::t('common', 'export_button')?></button>
                     		    <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="query_online"><?=Yii::t('frontend', 'tag_query')?></button>
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
	                      <th id="type_change_id"><?= Yii::t('common', 'real_name') ?></th>
	                      <th><?= Yii::t('frontend', 'top_mail_text') ?></th>
	                      <th><?= Yii::t('common', 'department') ?></th>
	                      <th><?= Yii::t('common', 'position') ?></th>
	                      <th><?= Yii::t('common', 'reporting_manager') ?></th>
	                      <th><?= Yii::t('common', 'examination_score') ?></th>
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
  
  
table#table_body .limit_class td{
    overflow: hidden;
    max-width: 100px;
    text-overflow: ellipsis;
    white-space: nowrap;
}


  
  </style>
    
      
  <script type="text/javascript">

  var course_id='';

  var queryList;
  var queryUserList;

  var queryTabs;
  
  function flash(){
	  //课程
		   var data_url_val="<?=Url::toRoute(['report-new/get-courses',])?>"+"?course_type=0"+"&domain_id="+$("#domain_id").val();
		   $("#course_name_id").attr("data-url",data_url_val);
		   queryList = app.queryList("#course_name_id"); 

           //用户
		   var user_data_url_val="<?=Url::toRoute(['report-new/get-users',])?>"+"?domain_id="+$("#domain_id").val();	
		   $("#user_id").attr("data-url",user_data_url_val);
		   queryUserList = app.queryList("#user_id"); 

		   queryTabs = app.queryTabs("#object_id")
		   
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

  

    $("#domain_id").change(function(){
    	queryList.reset();
    	queryUserList.reset();
    	flash();
    });
 	

 	 $.ajax({
  	   async: false,
		   url: "<?=Url::toRoute(['report-new/get-query',])?>",
		   data: {id:''},
		   success: function(msg){	

			  	  
			   for(var i=0;i<msg.domains.length;i++){
			    	 var tag=msg.domains[i];
			    	 
			    	 $("#domain_id").append("<option value='"+tag.kid+"'>"+tag.domain_name+"</option>");
			   }

			   flash();
			      //ajax end
			  
		   }
  });

	    


	     $("#query_online").click(function(){

	    	 var obj=queryList.get();
	    	 var course_id_val='';
	    
	    	 if(!obj.uid){
	    		 app.showMsg("<?= Yii::t('frontend', 'rep_input_course_name_or_code') ?>");
	    		 return;
		     }else{
		    	 course_id_val=obj.uid;
			 }
	    	

	    	 var userObj=queryUserList.get();
	    	 var user_id_val='';
	    	 if(userObj.uid){
	    		 user_id_val=userObj.uid;
		     }

	    	 
		    var tabObj=queryTabs.get();
            var extObjVal="";

            console.log(tabObj);
           
	    	 if(tabObj){
	    		 extObjVal= tabObj.id;
		     }

	    	 if($("#object_id").val()==""){
		    	 extObjVal="";
			 }
	        
	         reflash(course_id_val,user_id_val,extObjVal);
	     });


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

	    	 var userObj=queryUserList.get();
	    	 var user_id_val='';
	    	 if(userObj.uid){
	    		 user_id_val=userObj.uid;
		     }

	    	 var tabObj=queryTabs.get();
	    	
	         var extObjVal="";

	    	 if(tabObj){
	    		 extObjVal= tabObj.id;
		     }

	    	 if($("#object_id").val()==""){
		    	 extObjVal="";
			 }
	    	
	    	
	         iframe.src = "<?=Url::toRoute(['report-new/export-study-score',])?>"+"?course_id="+course_id_val+"&user_id="+user_id_val+"&ext_id="+extObjVal+"&domain_param="+$("#domain_id").val();
			
		   
	    });

	 

//
        });



    function reflash(course_id_,user_id_,extObj_){
      var ajaxOpt = {

         url: '<?=Url::toRoute(['report-new/get-study-score-data',])?>',
         data: {course_id:course_id_,user_id:user_id_,ext_id:extObj_,domain_param:$("#domain_id").val()},
         async: false,
         success: function(msg){
              $("#table_body tbody").empty();
              var lists=msg.studyScore;        
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
					 <tr class="limit_class">
                      <td><%=item.real_name%></td>
                      <td><%=item.email%></td>
                      <td><%=item.orgnization_name%></td>
<td title="<%=item.position_name%>"><%=item.position_name%></td>
  					  <td><%=item.reporting_manager_name%></td>
                      <td><%=item.score%></td>
                    </tr>
 <%});%>
    </script>                 
 <?=Html::cssFile('/static/frontend/css/bootstrap-sortable.css')?>
       <?=Html::jsFile('/static/frontend/js/bootstrap-sortable.js')?>
    <?=Html::jsFile('/static/frontend/js/moment.js')?>
    
    