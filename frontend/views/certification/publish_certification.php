<?php


use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

?>

<style>
<!--
.-query-list{
 display: inline-block;
    float: left;
}
#pub_user_certification_id{
margin-left: 250px;
    float: left;
}
-->
</style>

<!-- 颁发证书的弹出窗口 -->
    
        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'award_certificate')?></h4>
        </div>
        <div class="content">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <h4><?= Yii::t('frontend', 'tab_basic_information') ?></h4>
                    <hr/>
                    <input type="hidden" id="p_cert_kid" value="<?=$certi['kid'] ?>"/>
                    <div class="row">
                      <div class="col-md-8">
                        <div class="row">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'certification_name') ?></label>
                            <div class="col-sm-9">
                              <input value="<?=$certi['certification_name'] ?>" class="form-control" type="text" placeholder="<?=Yii::t('frontend', 'graduation_certificate')?>" disabled="disable">
                            </div>
                          </div>
                        </div>
                          <div class="row">
                              <div class="form-group form-group-sm">
                                  <label class="col-sm-3 control-label"><?= Yii::t('common', 'certification_display_name') ?></label>
                                  <div class="col-sm-9">
                                      <textarea disabled="disable"><?=$certi['certification_display_name'] ?></textarea>
                                  </div>
                              </div>
                          </div>
                        <div class="row">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('common', 'certification_description')?></label>
                            <div class="col-sm-9">
                              <textarea disabled="disable"><?=$certi['description'] ?></textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                          <a href="<?= Yii::$app->urlManager->createUrl(['certification/preview','id'=>$certi['kid']]) ?>" target="_blank">
                              <img id="imgTemplateUrl" width="280" height="195" src="<?=$certi['file_path'] . "preview.png" ?>" alt=""/>
                          </a>
                      </div>
                    
                    </div>
                    <div class="popTable">
                      <div class="actionBar">
                        
                         <div class="form-group pull-left">
                           <input data-type="search"  type="text" data-mult="1"  id="search_people_id" class="form-control" placeholder="<?=Yii::t('common', 'add_person')?>" aria-describedby="basic-addon2">
                           <a href="#" class="btn btn-success selectBtn" id="pub_user_certification_id"><?=Yii::t('frontend', 'award_certificate')?></a>
                       
                         </div>
                         
                           <!-- bengin -->
                      <div class="form-group" style="width: 130px; float: left;">
                          <select style="width: 120px" class="form-control" name="valid_status" id="valid_status_id">
                              <option value="1"><?=Yii::t('frontend', 'effective_certifi')?></option>
                              <option value="0"><?=Yii::t('frontend', 'history_certifi')?></option>
                          </select>
                        </div>
                        <div class="form-group" style="width: 130px; float: left; margin-left: 10px;">
                          <select style="width: 120px" class="form-control" name="created_channel" id="created_channel_id">
                              <option value="2"><?=Yii::t('frontend', 'all_certifi')?></option>
                              <option value="1"><?=Yii::t('frontend', 'course_award')?></option>
                              <option value="0"><?=Yii::t('frontend', 'manual_award')?></option>
                          </select>
                        </div>
                      
                      <!-- end -->
                        
                      
                        <form class="form-inline pull-right">
                          <div class="form-group">
                            <input id="certi_pub_key_word" type="text" class="form-control" placeholder="<?=Yii::t('frontend', 'input_name')?>">
                          
                            <button type="button"  id="cert_publish_query" class="btn btn-primary pull-right" style="margin-left:10px;">
                            <?=Yii::t('common', 'search')?>
                            </button>
                          </div>
                        </form>
                      </div>
                      
                      
                      <div id="certif_users_content_list"></div>
                      
                     
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="c"></div> 
        </div>
        <input type="hidden" value="<?=$p_page ?>" id="pub_p_page_num_id"/>
      <script>

      
//   var
//     selectBtn = $(".selectBtn"),
//     selectPanel = $(".selectPanel"),
//     btnComfirm = $(".btnComfirm");

 // selectBtn.bind("click", function() {
  //  if (selectPanel.hasClass("hide")) {
     // selectPanel.removeClass("hide")
  //  } else {
     // selectPanel.addClass("hide")
 //   }
//  })

//   btnComfirm.bind("click", function() {
//     if (selectPanel.hasClass("hide")) {
//       selectPanel.removeClass("hide")
//     } else {
//       selectPanel.addClass("hide")
//     }

   
   // $("#search_people_id").val("");
    
 // });

   var user_uuid= <?=  time() ?>+'u' ;
   var url_pub_list= "<?=Url::toRoute(['certification/pub-list'])?>"+"?certification_id=<?=$certi['kid'] ?>";
   


   function addUsersArrays(obj){
	  
	   var tttt=obj.title;
 	   var lists5=[{title:tttt,kid:obj.kid}];
 	   var t5_templ=_.template($("#t5").html(),{variable: 'data'})({datas:lists5});
 	  
       $("#peopleTag_id").append(t5_templ);
       $("#search_people_id").val("");
	   
   }

   function canelCertificationUser(iid,url) {
       console.log(iid);
       console.log(url);
       url = url + "&mission_id=" + user_uuid;
       $.get(url, function () {
           app.showMsg("<?=Yii::t('frontend', 'cancel_sucess')?>");
           var inputdata = {keyword: $("#certi_key_word").val()};
           ajaxGet("<?=Url::toRoute(['certification/list'])?>" + "?page=" + $("#pub_p_page_num_id").val(), "certif_content_list",null,inputdata);
           load_pub_list();
       });

   }

   function deleteNode(node,id){
	   $.get("<?=Url::toRoute(['message/delete-selected',])?>"+"?mission_id="+user_uuid+"&select_id="+id);
	   var iiiiii= _.findIndex(pub_user_list_tmp,{select_id:id});
	   pub_user_list_tmp.splice(iiiiii,1);
	   
       $(node).parent().remove();

   }

   var pub_user_list_tmp=[];
   

   function doAction(obj){
 	    console.log(obj.title);
 	    console.log(obj.uid);
 	    $.get("<?=Url::toRoute(['message/selected',])?>"+"?mission_id="+user_uuid+"&select_id="+obj.uid,function(data){

		   var user_tmp = 
				           {
				                   kid:obj.uid,
				                   title:obj.title
				           };
		   pub_user_list_tmp.push({select_id:obj.uid});
           addUsersArrays(user_tmp);
          

      });
   }

   var queryList;
  

  $(function(){
	    //app.genSearch();
 		//console.log(user_uuid);
	    //setTimeout( function (){
    	 //   app.searchResults("#search_people_id", "/certification/get-users.html?certification_id=<?=$certi['kid'] ?>"+"&user_uuid="+user_uuid, doAction);}
	    //, 300);
	    
	    var data_url_val="/certification/get-users.html?certification_id=<?=$certi['kid'] ?>"+"&user_uuid="+user_uuid;
	   $("#search_people_id").attr("data-url",data_url_val);
	   setTimeout(function(){
		   queryList = app.queryList("#search_people_id"); 
		   },600);
	   
	    
	
//
load_pub_list();

$("#cert_publish_query").click(function(){

	load_pub_list();
});


$("#certif_users_content_list").css("padding-bottom",'200px');

var pub_user_certification_flg=0;

$("#pub_user_certification_id").click(function(){

	
  var url="<?=Yii::$app->urlManager->createUrl(['certification/publish-certification-users'])?>";
 
  var user_certification_param={};
  user_certification_param.mission_id=user_uuid;
  user_certification_param.certification_id=$("#p_cert_kid").val();

  var nodeList=queryList.get();	
 
  console.log(nodeList);
  if(nodeList.length>0){
	  user_certification_param.all_users_chk='0';
	 

	  var pub_user_list_tmp=[];
	  
	  for(var i=0,len=nodeList.length;i<len;i++){
		  pub_user_list_tmp.push({select_id:nodeList[i].uid});
      }
	  user_certification_param.pub_user_list_tmp=pub_user_list_tmp;

	  if(pub_user_certification_flg==0){
		  pub_user_certification_flg=1;
		  $.ajax({
			   type: "POST",
			   url: url,
			   data: user_certification_param,
			   success: function(msg){
				   queryList.reset();
				  
		           var inputdata = {keyword:$("#certi_key_word").val()};
				   ajaxGet("<?=Url::toRoute(['certification/list'])?>"+"?page=<?=$p_page ?>", "certif_content_list",null,inputdata);
				   load_pub_list();				  				  
				   pub_user_certification_flg=0;
			   }
		      });
	  }
	  
	 
  } 

  pub_user_list_tmp=[];
  console.log("------");
  $("#peopleTag_id").empty();
  $("#search_people_id").val("");

  $("#all_users_chk_id").attr("checked",false);
});



//
	  });

 
   function load_pub_list(){
       var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
       $('#certif_users_content_list').html(loadingDiv); // 设置页面加载时的loading图片
       var ajaxUrl =url_pub_list

//       var inputdata = {keyword:$("#certi_pub_key_word").val().trim()};
//       ajaxGet(ajaxUrl, "certif_users_content_list",null,inputdata);

       load(ajaxUrl, 'certif_users_content_list', true);
   }


  function load(ajaxUrl, container, is_bind) {
      $("#" + container).empty();
      if (is_bind) {
          $("#list_loading").removeClass('hide');
          var inputdata = {
                  keyword:$("#certi_pub_key_word").val().trim(),
                  valid_status:$("#valid_status_id").val(),
                  created_channel:$("#created_channel_id").val()
                  };
          ajaxGet(ajaxUrl, container, bind,inputdata);
      }
      else {
    	  var inputdata = {keyword:$("#certi_pub_key_word").val().trim()};
          ajaxGet(ajaxUrl, container, null,inputdata);
      }
  }
  function bind(target, data) {
      $("#list_loading").addClass('hide');
      $("#" + target).html(data);
      $("#" + target + ' .pagination a').bind('click', function () {
          var url = $(this).attr('href');
          load(url, target, true);
          return false;
      });
  }
  
  
  </script>
  
<script id="t5" type="text/template">
 <%_.each(data.datas, function(item) {%>  
    <span class="tags taged"><%=item.title%> 
   <a href="#" onclick="deleteNode(this,'<%=item.kid%>')" class="tagCancel">x</a>
   </span>    
      <%});%>
</script>
 