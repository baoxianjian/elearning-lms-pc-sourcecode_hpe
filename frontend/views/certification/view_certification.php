<?php


?>

   
        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'certificate_detail') ?></h4>
        </div>
        <div class="content">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <h4><?= Yii::t('frontend', 'tab_basic_information') ?></h4>
                    <hr/>
                    <div class="row">
                      <div class="col-md-8">
                        <div class="row">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'certification_name') ?></label>
                            <div class="col-sm-9">
                              <?=$certi['certification_name'] ?>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'certification_display_name') ?></label>
                            <div class="col-sm-9">
                              <?=$certi['certification_display_name'] ?>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('common', 'certification_description')?></label>
                            <div class="col-sm-9">
                              <?=$certi['description'] ?>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('common', 'certification_template')?></label>
                            <div class="col-sm-9">
                              <?=$certi['template_name'] ?>
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
                  </div>
                  <h4><?= Yii::t('frontend', 'issue_related') ?></h4>
                  <hr/>
                  <div class="infoBlock">
                    <div class="row">
                      <div class="col-md-4 col-sm-4">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-6 control-label nowrap"><?= Yii::t('frontend', 'notification_mail') ?></label>
                          <div class="col-sm-6"> <?=$certi['is_email'] ?> </div>
                        </div>
                      </div>
                      <div class="col-md-4 col-sm-4">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-6 control-label nowrap"><?= Yii::t('frontend', 'print_score') ?> </label>
                          <div class="col-sm-6"><?=$certi['is_print_score'] ?></div>
                        </div>
                      </div>
                      
                       <div class="col-md-4 col-sm-4">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-6 control-label nowrap"><?= Yii::t('frontend', 'auto_push') ?></label>
                          <div class="col-sm-6"><?=$certi['is_auto_certify'] ?></div>
                        </div>
                      </div>
                      
                      <div class="col-md-4 col-sm-4">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-4 control-label nowrap"><?= Yii::t('common', 'time_validity') ?></label>
                          <div class="col-sm-8"><?=$certi['expire_time'] ?></div>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-12 col-sm-12 centerBtnArea">
                    <a id="view_cert_close" class="btn btn-success btn-sm centerBtn" style="width:20%;"><?= Yii::t('common', 'close') ?></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="c"></div> 
        </div>
        
        
<script type="text/javascript">
        
      
        $(function(){

        	$("#view_cert_close").click(function(){
console.log("view_cert_close");
            	
            	app.hideAlert("#view_certification")
            	});

            });
</script>
   