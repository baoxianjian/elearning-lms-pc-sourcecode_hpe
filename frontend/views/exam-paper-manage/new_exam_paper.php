<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 15/09/07
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>

<?= html::jsFile('/static/frontend/js/jquery.form.js') ?>
         <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'exam_new_paper')?></h4>
        </div>
        
      <form id="new_exam_paper_form" action="<?=Yii::$app->urlManager->createUrl(['exam-paper-manage/add-exam-paper-tmp'])?>" method="post">
        <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
         <input name="category_id" type="hidden" id="_csrf" value="<?=$cid ?>">
        <div class="content" style="padding:0;">
         
          <div class="infoBlock">
            <div class="row">
              <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                  <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'tag_mingcheng')?></label>
                  <div class="col-sm-9">
                    <input class="form-control" type="text" data-mode="COMMON" data-condition="^(?!\s)(?!.*?\s$).{1,25}$" data-alert="<?=Yii::t('frontend', 'exam_mingchengbunengweikong25')?>" name="title" id="title_id">
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                  <label class="col-sm-3 control-label"><?= Yii::t('common', 'description') ?></label>
                  <div class="col-sm-9">
                    <textarea data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'description')])?>" name="description" id="description_id"></textarea>
                  </div>
                </div>
              </div>
            </div>
            
            
            <div class="row">
              <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                  <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_shijuanleixing')?></label>
                  <div class="col-sm-9">
                    <div class="form-group field-courseservice-course_type">
                      <select id="examination_paper_type" class="form-control" name="examination_paper_type">
                        <option value="0"><?=Yii::t('frontend', 'exam_kaoshijuan')?></option>
                        <option value="1"><?=Yii::t('frontend', 'exam_lianxijuan')?></option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                  <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_nandu')?></label>
                  <div class="col-sm-9">
                    <div class="form-group field-courseservice-course_type">
                      <select id="examination_paper_level" class="form-control" name="examination_paper_level">
                         <?php
                                if (!empty($dictionary_list)){
                                    foreach ($dictionary_list as $item){
                                      ?>
                                        <option value="<?=$item->dictionary_value?>" ><?=$item->dictionary_name?></option>
                                      <?php
                                    }
                                }
                         ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="centerBtnArea">
            <a href="javascript:void(0)" id="new_exam_paper_id" class="btn btn-default btn-sm centerBtn"><?=Yii::t('frontend', 'exam_baocunbingbianji')?></a>
          </div>
        </div>
        </form>
        
    <script type="text/javascript">

    $(function(){
        // 
        window.validation_new_exam_paper =app.creatFormValidation($("#new_exam_paper_form"));   

        $("#new_exam_paper_id").click(function(){
        	var url="<?=Yii::$app->urlManager->createUrl(['exam-paper-manage/add-exam-paper-tmp'])?>";
        	save_new_exam_paper(url);

        });
        
        //
    });

    var submit_no=0;
    function save_new_exam_paper(url){
   	    if(!validation_new_exam_paper.validate()){
   		        return;
   	    };

        

   		  if(submit_no==0){
   	 		   submit_no++;
   	 		  
   	 		   $("#new_exam_paper_form").submit();
   		  }
   	}

    
   </script>   
    