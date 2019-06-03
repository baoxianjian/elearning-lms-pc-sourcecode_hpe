<?php
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;

?>
<div class="selectPanel selectPanel_task hide">
<div class="panel panel-default">

<div class="input-group">
<input type="text" class="form-control" placeholder="<?=Yii::t('frontend', 'input_keyword')?>" aria-describedby="basic-addon2" id="keyword">
<a class="btn input-group-addon" id="basic-addon2" onclick="searchCourses()"><?= Yii::t('frontend', 'top_search_text') ?></a>
</div>
<div role="tabpanel">
<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#addNewCourse" aria-controls="addNewCourse" role="tab" data-toggle="tab"><?= Yii::t('common', 'course') ?></a></li>
				<li role="presentation"><a href="#addNewExam" aria-controls="addNewExam" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'exam') ?></a></li>
						<li role="presentation"><a href="#addNewQuestion" aria-controls="addNewQuestion" role="tab" data-toggle="tab"><?=Yii::t('common', 'investigation_questionnaire')?></a></li>
						</ul>
						<!-- Tab panes -->
						<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="addNewCourse">
						<div class="AddtaskList" id="courseList">
						 
						 		</div>
						 		</div>
						 		<div role="tabpanel" class="tab-pane" id="addNewExam">...</div>
						 		<div role="tabpanel" class="tab-pane" id="addNewQuestion">...</div>
						 		</div>
						 		</div>
						 		
						 				<div class="panel-footer">
						 				<label><?=Yii::t('common', 'select_{value}',['value'=>Yii::t('frontend','task')])?></label>
						 			 <a href="#" class="btn btn-xs btn-success pull-right btnComfirm"><?= Yii::t('common', 'close') ?></a>
						 				</div>
						 				</div>
						 				</div>


<!-- 加载滑动幻灯片区域的脚本参数 -->
<script type="text/javascript">


    $(document).ready(function () {
        loadTab("<?=Url::toRoute(['message/get-course',])?>"+"?uuid="+uuid, 'courseList');
       // loadTab("<?=Url::toRoute(['lesson/study','direcions'=>'all'])?>", 'tab2');
        //loadTab("<?=Url::toRoute(['lesson/done','direcions'=>'all'])?>", 'tab3');
        //loadTab("<?=Url::toRoute(['lesson/directions',])?>", 'directions');

       // $('#myTab a:first').tab('show');

    });


    function searchCourses(){
    	
    	loadTab("<?=Url::toRoute(['message/get-course',])?>"+"?uuid="+uuid, 'courseList');
     }

    function loadTab(ajaxUrl, container) {
        var inputdata = {keyword:$("#keyword").val()};
        ajaxGet(ajaxUrl, container, bind, inputdata);
    }


    function bind(target, data) {
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            loadTab(url, target);
            return false;
        });
    }
    </script>