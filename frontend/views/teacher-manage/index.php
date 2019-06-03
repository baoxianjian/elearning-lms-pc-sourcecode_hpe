<?php

use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = Yii::t('frontend','teacher_manage');
$this->params['breadcrumbs'][] = '';

?>
<?= html::jsFile('/static/frontend/js/Chart.js') ?>
<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>
<style type="text/css">
  /*使讲师评分日期不换行*/
  #course_rate_id_id .col-md-4.col-sm-4 .col-sm-12.control-label{
    position: absolute;
  }
  #course_rate_id_id .col-md-4.col-sm-4{
    height: 40px
  }
</style>
<div class="container">
    <div class="row">
     <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      <div class="col-md-12 col-sm-12">
        <div class="panel panel-default hotNews">
          <div class="panel-heading">
            <i class="glyphicon glyphicon-cloud-upload"></i><?= Yii::t('common', '{value}_list',['value'=> Yii::t('common', 'lecturer')] ) ?>
          </div>
          <div class="panel-body">
            <div class="actionBar">
           
               <a class="btn btn-success  pull-left" onclick="FmodalLoad(newTeacherId,newTeacherForm);"><?= Yii::t('frontend', 'add_new_teacher') ?></a>
               <!--  
              <div class="btn-group">
                <button type="button" class="btn btn-success uploadBtn">
                  批量添加
                </button>
                <div class="uploadPanel hide">
                  <a class="btn pull-left" href="#">下载模板</a>
                  <form>
                    <input class="upload pull-left" type="file" name="namelist" accept="xsl" value="浏览" />
                    <button type="submit" class="btn btn-info btn-xs pull-left" />上传</button>
                  </form>
                </div>
              </div>-->
              <form class="form-inline pull-right">
                <div class="form-group">
                  <input id="teacher_key_word" type="text" class="form-control" placeholder="<?= Yii::t('frontend', 'input_name_nick') ?>">
                  <!--                   <div class="form-group field-courseservice-course_type">
                    <select id="courseservice-course_type" class="form-control" name="CourseService[course_type]">
                      <option value="">请选择类型</option>
                      <option value="0">内部讲师</option>
                      <option value="1">外部讲师</option>
                    </select>
                    <div class="help-block"></div>
                  </div> -->
                  <div class="form-group field-courseservice-course_type">
                    <select class="form-control" id="search_teacher_level_id">
                        <option value="-1" >--<?=Yii::t('common','teacher_level')?>--</option>
                        <?php foreach($teacherLevels as $k=>$v) {?>
                            <option value="<?=$v['value']?>" ><?=$v['name']?></option>
                        <?php }?>
                    </select>
                        <div class="help-block"></div>
                  </div>

                 
                    <button type="button" id="teacher_index_clear" class="btn btn-primary pull-right" style="margin-left:10px;">
                    <?=Yii::t('frontend', 'reset')?>
                    </button>
                     <button type="button"  id="teacher_index_query" class="btn btn-primary pull-right" style="margin-left:10px;">
                     <?=Yii::t('common', 'search')?>
                     </button>
                </div>
              </form>
            </div>
            <div id="teacher_manage_list"></div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
  
  
  <div class="ui modal ipad" id="new_teacher" >
  </div>
  
  <div class="ui modal ipad" id="edit_teacher" >
  </div>
  
  <div class="ui modal" id="view_teacher_main" >
 </div>
 
  <!-- 消息弹出框 -->
   <div id="foo" class="ui modal">
		<div class="header">
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?= Yii::t('frontend', 'top_message_text') ?>
		</div>
		<div class="content">
			<p id="msm_alert_content"><?= Yii::t('frontend', 'issue_sucess') ?></p>
		</div>
		<div class="actions">
			
			<div class="btn btn-default ok"><?=Yii::t('frontend', 'be_sure')?></div>
			<div class="btn btn-default cancel"><?= Yii::t('frontend', 'page_info_good_cancel') ?></div>
		</div>
	</div>
  
  
    <script type="text/javascript">
   app.extend("alert");	//扩展弹出层库

   var newTeacherId='new_teacher';

   var newTeacherForm="<?=Yii::$app->urlManager->createUrl(['teacher-manage/new-teacher'])?>";


     function FmodalLoadData1(target, url)
	 {
	       if(url){
	           
	           $('#'+target).empty();
	           $('#'+target).load(url ,function (){
					
	               });
	       }
	  }
   
   

   $(function(){
//

loadList();

$("#teacher_index_query").click(function(){

	loadList();
	
});

$("#teacher_index_clear").click(function(){

	$("#teacher_key_word").val("");
	
});


//
   });


   function FmodalLoad(target, url, doAlert)
   {

	 
       if(url){
    	   $('#'+target).empty();
           $('#'+target).load(url, function (){
           		!doAlert && app.alertWide("#"+target,{
           			afterHide: function (){ 
           				$('#'+target).empty();
               	    }
       		    });
               });
          
       }
   }


   function loadList(){
       var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
       $('#certif_content_list').html(loadingDiv); // 设置页面加载时的loading图片
       var ajaxUrl = "<?=Url::toRoute(['teacher-manage/list'])?>";
       var inputdata = {keyword:$("#teacher_key_word").val(),tg:$("#search_teacher_level_id").val()};
//       alert(inputdata['keyword']);
       ajaxGet(ajaxUrl, "teacher_manage_list",null,inputdata);
   }

   function checkMobile(vid,validation) {
      var  re = /^1\d{10}$/;
      var mobile_val=$.trim($("#"+vid).val());
      if(mobile_val!=''){

    	  if (!re.test(mobile_val)) {
    		  validation.showAlert("#"+vid,"<?=Yii::t('frontend', '{value}_input_wrong',['value'=>Yii::t('common','mobile_no')])?>");
    		  return false;
          }else{
        	  validation.hideAlert("#"+vid);
        	  return true;
              }
      }
      return true;
      
  }

   function checkPhone(vid,validation){
      var re = /^0\d{2,3}-?\d{7,8}$/;
      var phone_val=$.trim($("#"+vid).val());
      if(phone_val!=''){

    	  if (!re.test(phone_val)) {
    		  validation.showAlert("#"+vid,"<?=Yii::t('frontend', '{value}_input_wrong',['value'=>Yii::t('common','telephone_no')])?>");
    		  return false;
          }else{
        	  validation.hideAlert("#"+vid);
        	  return true;
              }
      }
      return true;
   }

   function checkTeachYear(vid,validation){
	      var re = /^[0-9]+$/;
	      var year_val=$.trim($("#"+vid).val());
	      if(year_val!=''){

	    	  if (!re.test(year_val)) {
	    		  validation.showAlert("#"+vid,"<?=Yii::t('frontend', '{value}_input_wrong',['value'=>Yii::t('common','teach_year')])?>");
	    		  return false;
	          }else{
	        	  validation.hideAlert("#"+vid);
	        	  return true;
	        	  }
	      }
	      return true;
   }
   
   
	   </script>
	   
	   
	    <script>
  ! function($) {
    var hash = location.hash && location.hash.substr(1).split("@"),
      $_element, $_evt;
    if (hash && 2 === hash.length) {
      $_element = hash[0];
      $_evt = hash[1];
      try {
        $($_element)[$_evt]();
      } catch (e) {
        console.log(e.stack || e);
      }
    }
  }(jQuery);
  </script>