<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->pageTitle = Yii::t('frontend', 'course_task_push');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;

?>
<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>
<?= html::jsFile('/vendor/bower/jquery-ui/jquery-ui.min.js') ?>
<?= html::cssFile('/vendor/bower/jquery-ui/themes/smoothness/jquery-ui.min.css') ?>

<div class="container">
    <div class="row">
   
    
      <?= TBreadcrumbs::widget([
            'tag' => 'ol',
           
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
    <!--  
        <ol class="breadcrumb">
            <li>
                <h2>任务推送</h2></li>
            <li><a href="#">首页</a></li>
            <li><a href="#">学习管理员推送</a></li>
        </ol> -->
        <div class="col-md-12">
            <div class="panel panel-default hotNews pull-left" style="width:100%">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('frontend', 'push_table')?>
                </div>
                <?php $form = ActiveForm::begin([
                    'id' => 'pushForm',
                	
                    'method' => 'post',
                    'enableAjaxValidation' => false,
                    'enableClientValidation' => true,
                    'action' => Yii::$app->urlManager->createUrl('message/ms-admin-push-save'),
//                                'validateOnSubmit' => true
                ]); ?>
                <div class="modal-body" id="newTask" >
                    <div class="col-md-8 col-sm-8">
                        <label><?=Yii::t('frontend', 'task_list')?></label>
                       
                         <a href="#" class="btn btn-sm btn-default pull-right selectBtn"><?=Yii::t('frontend', 'add_task')?></a>
                        <input type="hidden" name="TaskPushForm[courses]" value=""/>
                        <input type="hidden" name="TaskPushForm[users]" value=""/>
                        <!-- tab begin -->
               <?php  echo $this->render('task-choice'); ?>
                        <!-- tab end -->
                        
                        <ul class="taskList" id="taskList" style="margin:15px 0; ">
                           
                        </ul>
                    </div>
                    <div class="col-md-4 col-sm-4 myGroupList_mini">
                        <div class="input-group " style="width:100%">
			                <input type="text" id="search_people_id" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend', 'input_position_audience')?>">
			                
			              </div>
			              <!-- 
			              <ul class="list_people hide">
			                <li>全域所有人</li>
			                <li>基层经理</li>
			                <li>中层经理</li>
			                <li>营销部分部一</li>
			                <li>市场部</li>
			                <li>采购部</li>
			                <li>渠道部</li>
			              </ul> -->
                        <ul class="thumbList" id="userList">
                          
                        </ul>
                      
                    </div>
                </div>
                  <a href="#" class="btn btn-sm btn-success pull-right" style="margin: 0 20px 20px 0;" onclick="submitPreModalForm();"><?=Yii::t('frontend', 'push_task')?></a>
                <!--  
                <input name="push" value="推送" alt="推送" type="button"
                       onclick="submitModalForm('none', 'pushForm', 'none',  true, false, null, null);"/>-->
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function pushCourseToUser() {
        var obj = document.getElementsByName("courses1");//选择所有name="courses"的对象，返回数组
        var s = '';//如果这样定义var s;变量s中会默认被赋个null值
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].checked) //取到对象数组后，我们来循环检测它是不是被选中
                s += obj[i].value + ',';   //如果选中，将value添加到变量s中
        }
        if (s == '') {
            NotyWarning('<?=Yii::t('frontend', 'select_null')?>！');
            return;
        }
        //$("#course_value").val(s);
        var chk_value = [];//定义一个数组
        $('input[name="users1"]:checked').each(function () {//遍历每一个名字为users的复选框，其中选中的执行函数
            chk_value.push($(this).val());//将选中的值添加到数组chk_value中
        })
        if (chk_value.length == 0) {
            NotyWarning('<?=Yii::t('frontend', 'select_null')?>！');
            return;
        }
        //$("#user_value").val(chk_value);
        submitModalForm('none', 'pushForm', 'none', true, false, null, null);

    }
    function reloadForm()
    {
        setTimeout("window.location.reload()", 1500);
    }


    function submitPreModalForm(){

        var courses_val=$("input[name='TaskPushForm[courses][]']:checked");
        var users_val=$("input[name='TaskPushForm[users][]']:checked");
        
        if(courses_val.length==0){
            //alert("推送的课程不能为空");
            NotyWarning('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'push_course')])?>', 'center', 1000);
            return;
        }

        if(users_val.length==0){
        	// alert("推送的对象不能为空");
        	 NotyWarning('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'push_object')])?>', 'center', 1000);
             return;
         }
    	submitModalForm('none', 'pushForm', 'none', true, false, null, null);

    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
        NotyWarning('<?=Yii::t('common', 'operation_success')?>','center',1500);
        reloadForm();
    }

    var uuid= <?=  time() ?> ;
   
    var user_uuid= <?=  time() ?>+'u' ;
    
    $(function(){

    	 //任务添加 begin
    	 var selectBtn = $(".selectBtn"),
         selectPanel = $(".selectPanel"),
         btnComfirm = $(".btnComfirm")

       selectBtn.bind("click", function() {
         if (selectPanel.hasClass("hide")) {
           selectPanel.removeClass("hide")
         } else {
           selectPanel.addClass("hide")
         }
       });

       btnComfirm.bind("click", function() {
         if (selectPanel.hasClass("hide")) {
           selectPanel.removeClass("hide")
         } else {
           selectPanel.addClass("hide")
         }
       });
       //任务添加 end

       
       //人员搜索begin
       /*
       $('.search_people').bind('focus', function(){
    	      $('.list_people').removeClass('hide').css({
    	        width: $('.search_people').width()
    	      })
       });

       $(document).bind('dblclick', function(){
    	   $('.list_people').addClass('hide')
      });*/

        var url = "<?=Yii::$app->urlManager->createUrl('message/get-users')?>";
       $( "#search_people_id" ).autocomplete({
    	   source: function( request, response ) {
               $.ajax({
                   url: url,
                   dataType: "json",
                   data:{
                       searchDbInforItem: request.term,
                       user_uuid:user_uuid,
                       company_id:'<?=$company_id?>'
                   },
                   success: function( data ) {
                       response( $.map( data, function( item ) {
                           var type_label="";
                           if('Domain'==item.object_type){
                        	   type_label="[<?=Yii::t('common', 'domain')?>]";
                           }else if('Organization'==item.object_type){
                        	   type_label="[<?=Yii::t('frontend', 'organization')?>]";
                               }else if('Position'==item.object_type){
                            	   type_label="[<?= Yii::t('frontend', 'position') ?>]";
                                   }else if('User'==item.object_type){
                                	   type_label="[<?=Yii::t('common', 'user_{value}',['value'=>''])?>]"+"["+item.email+"]";
                                       }
                           return {label:item.object_name+type_label,value:item.object_id};
                       }));
                   }
               });
           },
           minLength: 1,
           select: function( event, ui ) {
        	   $.get("<?=Url::toRoute(['message/selected',])?>"+"?mission_id="+user_uuid+"&select_id="+ui.item.value,function(data){

        		   var user_tmp = [
    					           {
    					                   kid:ui.item.value,
    					                   real_name:ui.item.label
    					           }];
                   addUsersArrays(user_tmp);
                  

              });
        	  return false;
           }
       });
       //人员搜索end   
       
    }); 

   function addTaskArrays(lists){
	   $("#taskList").append( _.template($("#t2").html(),{variable: 'data'})({datas:lists}));
   }

   function addUsersArrays(lists){
	   $("#userList").append( _.template($("#t3").html(),{variable: 'data'})({datas:lists}));
   }


   function deleteNode(node,id){
	   $.get("<?=Url::toRoute(['message/delete-selected',])?>"+"?mission_id="+uuid+"&select_id="+id);
       $(node).parent().parent().parent().remove();

   }
  
</script>


<!--ace-template demo-->        
    <script id="t2" type="text/template">
 <%_.each(data.datas, function(item) {%>
          <li>
             <input type="checkbox" name="TaskPushForm[courses][]" value="<%=item.kid%>"
                                           checked="checked"/>

            <div class="taskLine">
             <h5><%=item.course_name%> <a href="##" onclick="deleteNode(this,'<%=item.kid%>')" class="btn btn-default btn-xs"><?= Yii::t('common', 'delete_button') ?></a></h5>
<!--
             <p>完成要求:<%=item.course_desc%></p>
-->
            
             </div>
          </li>
  <%});%>
    </script>
    
     <script id="t3" type="text/template">
 <%_.each(data.datas, function(item) {%>
           <li>
                 <input type="checkbox" value="<%=item.kid%>" name="TaskPushForm[users][]"
                     checked="checked"/>
<!-- 
                 <img src="/staticcommon/images/woman.jpeg" alt="scoreList1" width="99"
                      height="99"/>-->

                 <p class="name"> <%=item.real_name%>
                 </p>

                
           </li>
  <%});%>
    </script>
</body>

</html>
