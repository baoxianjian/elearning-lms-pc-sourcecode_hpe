<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <p class="modal-title"><?=Yii::t('common', 'import_button')?></p>
        </div>
        <div class="content">
          <div class="infoBlock">
            <div class="row">
              <div class="col-md-12">
                <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6
  ">*</strong> <?=Yii::t('frontend', 'exam_note1')?>(.xls)，<a href="/upload/examination-question-template/exam_import.xls"><?= Yii::t('frontend', 'click_here') ?></a><?= Yii::t('frontend', 'download_mode_file') ?>.</p>
                <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6
  ">*</strong> <?=Yii::t('frontend', 'exam_note2')?>.</p>
                <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6
  ">*</strong> <?=Yii::t('frontend', 'exam_note3')?>.</p>
                <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6
  ">*</strong> <?=Yii::t('frontend', 'exam_note4')?>.</p>
                <hr>
              </div>
              <div class="col-md-12">
                <div class="row">
                  <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                      <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_mubiaoshitiku')?></label>
                      <div class="col-sm-9"><?=$categoryName?></div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                      <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_tikumuban')?></label>
                      <div class="col-sm-9">
                      	<input type="hidden" id="categoryId" value="<?=$categoryId?>" />
                        <input type="text" class="form-control" id="tmp" readonly/>
                      </div>
                    </div>
                  </div>
                  <div class="row">
		              <div class="col-md-12 col-sm-12 centerBtnArea">
		                <input type="button" id="upload" class="btn btn-success btn-sm centerBtn" value="<?=Yii::t('common', 'select_files')?>" style="width: 20%; zoom:1">
                    	<a href="###" class="btn btn-default btn-sm centerBtn" style="width:20%;" id="import"><?=Yii::t('common', 'import')?></a>
		              </div>
		            </div>
                </div>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-md-12" id="errmsg"></div>
            </div>
          </div>
        </div>
        <?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
        <script>
        var ajaxUploadUrl = "<?=Url::toRoute(['/exam-manage-main/import-file'])?>";
        new AjaxUpload("#upload", {
            action: ajaxUploadUrl,
            type: "POST",
            name: 'myfile',
            data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
            onComplete: function (file, response) {
                var jsonData = eval("("+response+")");
                if (jsonData.result == 'success'){
					$("#tmp").val(jsonData.basename).attr('data-src', jsonData.errmsg);
                }else{
                	$("#tmp").val('');
                    app.showMsg(jsonData.errmsg);
                }
            }
        });
        $(function(){
            $("#import").on('click', function(){
                if ($(this).attr('data-disabled') == 'disabled') return false;
                var fileName = $("#tmp").val();
                var file = $("#tmp").attr('data-src');
                if ($("#tmp").val() == "" ){
                    app.showMsg('<?=Yii::t('common', 'please_select_import_file')?>');
                    return false;
                }
                var categoryId = $("#categoryId").val();
                $(this).attr('data-disabled', 'disabled');
                $.post('<?=Url::toRoute(['/exam-manage-main/import-submit'])?>', {categoryId: categoryId, file: file, fileName: fileName}, function(e){
                    if (e.result == 'success'){
                        var errmsg = e.errmsg;
                        if ((typeof errmsg['select'] != 'undefined' && typeof errmsg['select']['err'] != 'undefined') || (typeof errmsg['judge'] != 'undefined' &&typeof errmsg['judge']['err'] != 'undefined')){
                            var html = '<h5><?=Yii::t('frontend', 'exam_cuowutishi')?>:</h5>';
                            if (typeof errmsg['select'] != 'undefined' && typeof errmsg['select']['err'] != 'undefined'){
                                var err = errmsg['select']['err'];
                                var err_number = err.length;
                                for (var i = 0; i < err_number; i++){
                                    if (typeof err[i]['col'] == 'undefined'){
                                        html += '<p><?=Yii::t('frontend', 'exam_panduanti')?>:<?=Yii::t('frontend', 'exam_the')?>'+err[i]['row']+'<?=Yii::t('frontend', 'exam_line_err')?></p>';
                                    }else{
                                        html += '<p><?=Yii::t('frontend', 'exam_panduanti')?>:<?=Yii::t('frontend', 'exam_the')?>'+err[i]['row']+'<?=Yii::t('frontend', 'exam_line')?>，<?=Yii::t('frontend', 'exam_the')?>'+ err[i]['col']+'<?=Yii::t('frontend', 'exam_col_err')?></p>';
                                    }
                                }
                            }
                            if (typeof errmsg['judge'] != 'undefined' && typeof errmsg['judge']['err'] != 'undefined'){
                                var err = errmsg['judge']['err'];
                                var err_number = err.length;
                                for (var i = 0; i < err_number; i++){
                                    if (typeof err[i]['col'] == 'undefined'){
                                        html += '<p><?=Yii::t('frontend', 'exam_panduanti')?>:<?=Yii::t('frontend', 'exam_the')?>'+err[i]['row']+'<?=Yii::t('frontend', 'exam_line_err')?></p>';
                                    }else{
                                        html += '<p><?=Yii::t('frontend', 'exam_panduanti')?>:<?=Yii::t('frontend', 'exam_the')?>'+err[i]['row']+'<?=Yii::t('frontend', 'exam_line')?>，<?=Yii::t('frontend', 'exam_the')?>'+ err[i]['col']+'<?=Yii::t('frontend', 'exam_col_err')?></p>';
                                    }
                                }
                            }
                            $("#errmsg").html('<div style="color: #ff3366">'+html+'</div>');
                        }else{
                        	var html = '<h5><?=Yii::t('frontend', 'exam_import_ok')?></h5>';
                        	html += '<p><?=Yii::t('frontend', 'exam_imports')?> '+errmsg['total']+' <?=Yii::t('frontend', 'exam_ti')?></p>';
                        	if (typeof errmsg['select_right_number'] != 'undefined'){
                        		html += '<p><?= Yii::t('frontend', 'choose') ?>（'+errmsg['select_right_number']+'）</p>';
                            }
                        	if (typeof errmsg['judge_right_number'] != 'undefined'){
                        		html += '<p><?=Yii::t('frontend', 'exam_panduanti')?>（'+errmsg['judge_right_number']+'）</p>';
                            }
                            $("#errmsg").html('<div style="color: #008000">'+html+'</div>');
                            $("#import").attr('data-disabled', 'false');
                            $("#tmp").val('').attr('data-src','');
                            loadTree();
                            loadList();
                        }
                    }else{
                        app.showMsg(e.errmsg);
                        return false;
                    }
                },'json');
            });
        });
        </script>