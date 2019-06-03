<?php
use yii\helpers\Url;
?>

 
   
     
        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'teacher_detail') ?></h4>
        </div>
        <div class="content">
          <div class="courseInfo">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
              <li role="presentation" class="active"><a href="#teacher_info" id="teacher_info_id" aria-controls="teacher_info" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'personal_detail') ?></a></li>
              <li role="presentation"><a href="#course_info" aria-controls="course_info" id="course_info_id" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'teacher_detail') ?></a></li>
              <li role="presentation"><a href="#course_rate" aria-controls="course_rate" id="course_rate_id" role="tab" data-toggle="tab" ><?=Yii::t('frontend', 'give_a_mark')?></a></li>
            </ul>
            <div class="tab-content">
             
             <div role="tabpanel" class="tab-pane active" id="teacher_info">
             </div>
             
             <div role="tabpanel" class="tab-pane" id="course_info">
              </div>
             
              <div role="tabpanel" class="tab-pane" id="course_rate">
               </div>
            </div>
          </div>
          
          <div class="c"></div>
        </div>
     
    <script type="text/javascript">
	    $(function(){

			FmodalLoadData1("teacher_info","<?=Url::toRoute(['teacher-manage/view'])?>"+"?id=<?=$id?>");


			$("#teacher_info_id").unbind("click").click(function(){


				FmodalLoadData1("teacher_info","<?=Url::toRoute(['teacher-manage/view'])?>"+"?id=<?=$id?>");
			});

			$("#course_info_id").unbind("click").click(function(){

				FmodalLoadData1("course_info","<?=Url::toRoute(['teacher-manage/course-info'])?>"+"?id=<?=$id?>");
				
			});

			$("#course_rate_id").unbind("click").click(function(){

				FmodalLoadData1("course_rate","<?=Url::toRoute(['teacher-manage/course-rate'])?>"+"?id=<?=$id?>");
				
			});

        });
    </script>
