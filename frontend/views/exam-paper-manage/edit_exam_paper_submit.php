<?php

use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;



?>


   
 
        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'exam_shijuangaikuang')?></h4>
        </div>
        <div class="body">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="row paperListStatu">
                    <span class="listStatu"><?=Yii::t('frontend', 'exam_anzhaotixing')?>: </span>              
                    <? foreach ($result['type_res'] as $r): ?>
                          <a ><?=$r['name'] ?>(<?=$r['num'] ?>)</a>
                    <? endforeach; ?>
                  </div>
                  <div class="row paperListStatu">
                    <span class="listStatu"><?=Yii::t('frontend', 'exam_anzhaotiku')?>: </span>
                    <? foreach ($result['category_res'] as $r): ?>
                          <a ><?=$r['name'] ?>(<?=$r['num'] ?>)</a>
                    <? endforeach; ?>
                  </div>
                  <div class="row paperListStatu">
                    <span class="listStatu"><?=Yii::t('frontend', 'exam_anzhishidian')?>: </span>
                    <? foreach ($result['tag_res'] as $r): ?>
                          <a ><?=$r['name'] ?>(<?=$r['num'] ?>)</a>
                    <? endforeach; ?>
                  </div>
                  <div class="centerBtnArea">
                    <span><?=Yii::t('frontend', 'exam_shitizongshi')?>: <?=$result['examination_question_number'] ?><?=Yii::t('frontend', 'ti')?></span>
                    <span><?=Yii::t('frontend', 'exam_morenzongfen')?>: <?=$result['default_total_score'] ?><?=Yii::t('frontend', 'exam_fenshu')?></span>
                  </div>
                  <div class="col-md-12 col-sm-12 centerBtnArea" style="margin-top: 20px;">
                    <a href="javascript:void(0)" id="edit_exam_final_submit_id" class="btn btn-success btn-sm centerBtn" style="width:40%;"><?=Yii::t('frontend', 'exam_bianjibingfanhuiliebiao')?></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    
   
   
  
   <script type="text/javascript">
   var exam_paper_final_obj=new_paper_submit_data;
   $(function(){
       // 
      
      $("#edit_exam_final_submit_id").click(function(){
    	  var url="<?=Yii::$app->urlManager->createUrl(['exam-paper-manage/edit-exam-paper-final-submit'])?>";


    	  $.ajax({
			   type: "POST",
			   url: url,
			   data: exam_paper_final_obj,
			   success: function(msg){					 
				   window.location = "<?=Yii::$app->urlManager->createUrl(['exam-paper-manage/index'])?>"
				   
			   }
	     });  
      });
       
       //
   });
   </script>        
  
  