<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

?>
<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>
<div class="container">
    <div class="row">
        <ol class="breadcrumb">
            <li>
                <h2><?=Yii::t('frontend', 'task_push')?></h2></li>
            <li><a href="#"><?=Yii::t('common', 'home')?></a></li>
            <li><a href="#"><?=Yii::t('frontend', 'direct_manager_push')?></a></li>
        </ol>
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
                    'action' => Yii::$app->urlManager->createUrl('message/ms-task-push-save'),
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
                        <label><?=Yii::t('frontend', 'list_of_{value}',['value'=>Yii::t('frontend','student')])?></label>
                       
                         
                        <ul class="thumbList">
                            <?php foreach ($users as $FwUser): ?>
                                <li>
                                    <input type="checkbox" value="<?= $FwUser[kid] ?>" name="TaskPushForm[users][]"
                                           checked="checked"/>
                                    <img src="/staticcommon/images/woman.jpeg" alt="scoreList1" width="99"
                                         height="99"/>

                                    <p class="name"> <?= Html::encode("{$FwUser[real_name]} ") ?>
                                    </p>

                                    <p><?= Html::encode("{$FwUser[position_name]} ") ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                       
                    </div>
                </div>
                  <a href="#" class="btn btn-sm btn-success pull-right" onclick="submitModalForm('none', 'pushForm', 'none',  true, false, null, null);"><?=Yii::t('frontend', 'appoint_task')?></a>
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

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
        NotyWarning('<?=Yii::t('common', 'operation_success')?>','center',1500);
        reloadForm();
    }

    var uuid= <?=  time() ?> ;
   

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
    }); 

   function addTaskArrays(lists){
	   $("#taskList").append( _.template($("#t2").html(),{variable: 'data'})({datas:lists}));
   }
  
</script>


<!--ace-template demo-->        
    <script id="t2" type="text/template">
 <%_.each(data.datas, function(item) {%>
          <li>
             <input type="checkbox" name="TaskPushForm[courses][]" value="<%=item.kid%>"
                                           checked="checked"/>

            <div class="taskLine">
             <h5><%=item.course_name%></h5>

             <p><?=Yii::t('frontend', 'completion_requirement')?>:<%=item.course_desc%></p>
             </div>
          </li>
  <%});%>
    </script>
    
   
</body>

</html>
