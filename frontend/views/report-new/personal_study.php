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
                                 <select class="form-control" id="domain_id"   name="CourseService[course_type]">                                                             
                                    </select>
                          
                               
                             <div class="form-group ">  
                             
                                  
                                   
                             </div>
                          </div>
                       </form>  
                      <form class="form-inline pull-right" style="margin-left:5px;">
                      <div class="form-group">
                        <input class="form-control -table-report"
                                   data-type="rili" id="begin_time"  type="text" placeholder="<?= Yii::t('frontend', 'exam_kaishishijian') ?>"  style="width:100px "> 
                                  
                                 <input class="form-control -table-report"
                                   data-type="rili" id="end_time"  type="text" placeholder="<?= Yii::t('frontend', 'end_time') ?>"  style="width:100px ">
                           
                             <input type="text" data-url="<?=Url::toRoute(['report-new/get-ext-infos',])?>" id="object_id" class="form-control clickSearch" placeholder="<?= Yii::t('frontend','rep_input_ext_info') ?>">
                                     <input type="text" id="user_id" class="form-control clickSearch" placeholder="<?= Yii::t('frontend','rep_input_user_info') ?>">
                    
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
	                      <th><?= Yii::t('frontend', 'department') ?></th>
	                      <th><?= Yii::t('common', 'position') ?></th>
	                      <th><?= Yii::t('common', 'reporting_manager') ?></th>
	                      <th><?= Yii::t('frontend', 'learning_time_total') ?></th>
	                      <th><?= Yii::t('frontend', 'register_course_number') ?></th>
	                      <th><?= Yii::t('frontend', 'register_finish_number') ?></th>
	                      <th><?= Yii::t('frontend', 'rep_certification_num') ?></th>
                   		 </tr>
                    </thead>
                     <tbody>
                     </tbody>
                  
                  </table>
                 
                </div>
              </div>
              
               <div class="ui modal" id="view_course_info" >
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


  function FmodalLoad(target, user_id, type,doAlert)
  {
	  console.log(target);
	  
	  console.log(type);
      var url="<?=Yii::$app->urlManager->createUrl(['report-new/course-infos'])?>"+"?user_id="+user_id+"&type="+type+"&begin="+$("#begin_time").val()
       +"&end="+$("#end_time").val()+"&domain_id="+$("#domain_id").val();
	  
      if(url){
      	   $('#'+target).empty();
      	   $('#'+target).load(url, function (){
     		!doAlert && app.alert("#"+target,{
     			afterHide: function (){ 
     				$('#'+target).empty();
         	    }
 		    });
          });
 	      return;

      }
  }

  var course_id='';

  var queryUserList;
  var queryTabs;
  
  function flash(){	 
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

 	$("#cvs_export").click(function(){
	     var iframe;
	     iframe = document.getElementById("hiddenDownloader");
	
	
	   	 var userObj=queryUserList.get();
	   	 var user_id_val='';
	   	 if(userObj.uid){
	   		 user_id_val=userObj.uid;
		 }


	   	 var begin_time=$("#begin_time").val();
	     if(!begin_time){
	     		app.showMsg("<?= Yii::t('frontend', 'exam_kaishishijian_buneng_null') ?>");
	     		return;
		 }		     	
	 	 var end_time=$("#end_time").val();
	     if(!end_time){
	    	 end_time=getCurrentDay();
		 }	
		 var arys1=begin_time.split('-');      
		 var sdate=new Date(arys1[0],parseInt(arys1[1]-1),arys1[2]);        
		 var arys2=end_time.split('-');      
		 var edate=new Date(arys2[0],parseInt(arys2[1]-1),arys2[2]);       
		 if(sdate >= edate) {
				app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
				return;
		 } 

		 var domain_id=$("#domain_id").val(); 
	
	   	 var tabObj=queryTabs.get();
	   	 var extObjVal="";	
	   	 if(tabObj){
	   		 extObjVal= tabObj.id;
		 }
	   	 if($("#object_id").val()==""){
	    	 extObjVal="";
		 }
	
	   	 var extObjVal="";
	   	
	      iframe.src = "<?=Url::toRoute(['report-new/export-personal-study',])?>"+
	      "?begin="+begin_time+"&end="+end_time+"&user_id="+user_id_val+"&ext_id="+extObjVal+"&domain_param="+$("#domain_id").val();
			
	   
  		 });


	     $("#query_online").click(function(){
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

	    	 var begin_time=$("#begin_time").val();
		     if(!begin_time){
		     		app.showMsg("<?= Yii::t('frontend', 'exam_kaishishijian_buneng_null') ?>");
		     		return;
			 }		     	
		 	 var end_time=$("#end_time").val();
		     if(!end_time){
		    	 end_time=getCurrentDay();
			 }	
			 var arys1=begin_time.split('-');      
			 var sdate=new Date(arys1[0],parseInt(arys1[1]-1),arys1[2]);        
			 var arys2=end_time.split('-');      
			 var edate=new Date(arys2[0],parseInt(arys2[1]-1),arys2[2]);       
			 if(sdate >= edate) {
						app.showMsg("<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
						return;
			 }      
			
			 var domain_id=$("#domain_id").val();
	         reflash(begin_time,end_time,domain_id,user_id_val,extObjVal);
	     });


//
        });

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



    function reflash(begin_time_,end_time_,domain_id_,user_id_,extObj_){
      var ajaxOpt = {

         url: '<?=Url::toRoute(['report-new/get-personal-study-data',])?>',
         data: {begin:begin_time_,end:end_time_,domain_param:domain_id_,user_id:user_id_,ext_id:extObj_},
         async: false,
         success: function(msg){
              $("#table_body tbody").empty();
              var lists=msg.personalStudy;        
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
                      <td><%=item.orgnization_name%></td>
                      <td title="<%=item.position_name%>"><%=item.position_name%></td>
  					  <td><%=item.reporting_manager_name%></td>
                      <td><%=item.duration%></td>
 					  <td><a onclick="FmodalLoad('view_course_info','<%=item.user_id%>','reg')"><%=item.reg_num%></a></td>
                      <td><a onclick="FmodalLoad('view_course_info','<%=item.user_id%>','com')"><%=item.com_num%></a></td>
				      <td><%=item.certification%></td>
                    </tr>
 <%});%>
    </script>                 
 <?=Html::cssFile('/static/frontend/css/bootstrap-sortable.css')?>
       <?=Html::jsFile('/static/frontend/js/bootstrap-sortable.js')?>
    <?=Html::jsFile('/static/frontend/js/moment.js')?>
    
    