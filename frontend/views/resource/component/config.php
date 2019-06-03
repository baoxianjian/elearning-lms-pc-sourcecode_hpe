<?php 
use common\models\learning\LnCourse;
?>
            <div class="header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <p class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'resources') ?><?= Yii::t('frontend', 'configuration') ?>: [<?=$component_name?>] <?=$title?></p>
            </div>
            <div class="content">
                <div class="infoBlock">
                    <div class="row">
                    	<?php 
                    	$isCourseType = $params['isCourseType'];
                    	if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                    	?>
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?= Yii::t('frontend', 'tip_for_direct') ?>:</label>
                                <div class="col-sm-7">
                                    <div class="btn-group" data-toggle="buttons">
                                        <label style="margin-right:68px;">
                                            <input id="isyes" name="isfinish" type="radio"  value="1"> <?= Yii::t('frontend', 'yes') ?>
                                        </label>
                                        <label>
                                            <input id="isno" name="isfinish" type="radio" checked='checked' value="0"> <?= Yii::t('frontend', 'no') ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <p style="padding-left:15px; color:#999999; font-size:12px;"><?= Yii::t('frontend', 'warning_for_compelet_course') ?></p>
                        </div>
                        <div class="col-md-12">&nbsp;</div>
                        <?php
                        if($view){
                        ?>
                        <div class="col-md-12 col-sm-12">
                            <label class="col-sm-5 control-label"><?= Yii::t('frontend', 'use_mini_pass_score') ?>:</label>
                            <div class="col-sm-7">
                                <? if($component_code){?>
                                <input class="form-control pull-left" type="text" id="formGroupInputSmall" value="" onkeyup="if (this.value==this.value2) return; if (this.value.search(/^\d*(?:\.\d{0,2})?$/)==-1) this.value=(this.value2)?this.value2:'';else this.value2=this.value;" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','score')]) ?>">
                                <? }else{?>
                                 <input class="form-control pull-left" type="text" id="formGroupInputSmall" value="" onkeyup="if (this.value==this.value2) return; if (this.value.search(/^\d*(?:\.\d{0,2})?$/)==-1) this.value=(this.value2)?this.value2:'';else this.value2=this.value;" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','score')]) ?>">
                                <? }?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <? if(!empty($pass_grade)){?>
                            <p style="padding-left:15px; color:#999999; font-size:12px;"><?= Yii::t('frontend', 'warning_for_passgrade_{value}',['value'=>$pass_grade]) ?></p>
                            <? }?>
                            <p style="padding-left:15px; color:#999999; font-size:12px;"><?= Yii::t('frontend', 'warning_for_score_course') ?></p>
                            <p style="padding-left:15px; color:#999999; font-size:12px;"><?= Yii::t('frontend', 'warning_for_score_zero') ?></p>
                        </div>
                        <?php }
                        }?>
                        
                        
                        
                        <?php
                        if($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){?>
                        <div class="col-md-12 col-sm-12">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'res_time') ?>:</label>
                            <div class="col-sm-7">
                                <input type="text" maxlength="8" id="formResTime" value="" style="width: 250px;" onkeyup="if (this.value==this.value2) return; if (this.value.search(/^\d*(?:\.\d{0,2})?$/)==-1) this.value=(this.value2)?this.value2:'';else this.value2=this.value;" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','res_time')]) ?>"> <?=Yii::t('frontend', 'point')?>
                            </div>
                        </div>
                        <?php }?>
                        
                        
                        
                        
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 centerBtnArea">
                            <a href="###" class="btn btn-success btn-sm centerBtn" onclick="saveConfig();" style="width:20%;"><?= Yii::t('common', 'save') ?></a>
                        </div>
                    </div>
                </div>
            </div>
<input type="hidden" id="passgrade" value="<?=$pass_grade?>">
        <script>
            if(document.getElementById('con_<?=$mod_num?>_<?=$kid?>')){
                if($('#con_<?=$mod_num?>_<?=$kid?>').attr('data-isfinish') == 1){
                    $('#isyes').attr('checked','checked');
                    $('#isno').removeAttr('checked');
                }else{
                    $('#isyes').removeAttr('checked');
                    $('#isno').attr('checked','checked');
                }
                if($('#con_<?=$mod_num?>_<?=$kid?>').attr('data-score') != 'undefined'){
                    $('#formGroupInputSmall').val($('#con_<?=$mod_num?>_<?=$kid?>').attr('data-score'));
                }
                
                if($('#con_<?=$mod_num?>_<?=$kid?>').attr('data-res-time') != 'undefined'){
                    $('#formResTime').val($('#con_<?=$mod_num?>_<?=$kid?>').attr('data-res-time'));
                }
                

            }
    
            function saveConfig(){
                
                if($('#formGroupInputSmall').val() > 100){
                    app.showMsg('<?= Yii::t('frontend', 'warning_for_passgrade') ?>');
                    return false;
                }
                var passgrade = $('#passgrade').val();
                if($('#formGroupInputSmall').val() < parseInt(passgrade)  && passgrade !='' && $('#formGroupInputSmall').val()!=''){
                    app.showMsg('<?= Yii::t('frontend', 'warning_for_passgrade_beyond') ?>');
                    return false;
                }
                
                /*
                if($('#formResTime').val() > 100){
                    app.showMsg('<?= Yii::t('frontend', 'warning_for_passgrade') ?>');
                    return false;
                }
                */
                
                
                
               var html;
               var score = $('#formGroupInputSmall').val();
               var isfinish = $("input[name=isfinish]:checked").val();
               var resTime = $('#formResTime').val();
               
               if (typeof score == 'undefined'){
                   score = 0;
               }
               if (typeof isfinish == 'undefined'){
            	   isfinish = 0;
               }
               if (typeof resTime == 'undefined'){
                   resTime = 0;
               }

                html = "<input value='{\"kid\":\"<?=$kid?>\",\"title\":\"<?=urlencode($title)?>\",\"score\":\""+score+"\",\"res_time\":\""+resTime+"\",\"isfinish\":\""+isfinish+"\",\"componet\":\"<?=urlencode($component_name)?>\",\"iscore\":\"<?=$is_score?>\"}' id=\"con_<?=$mod_num?>_<?=$kid?>\" name=\"resource[<?=$mod_num?>][config][<?=$kid?>]\" data-name=\"config\" data-title=\"<?=$title?>\" data-isfinish=\""+isfinish+"\" data-score=\""+score+"\" data-res-time=\""+resTime+"\" data-componet=\"<?=$component_name?>\" data-iscore=\"<?=$is_score?>\" data-kid=\"<?=$kid?>\"/>";
                if(parseInt(isfinish) == 1){
                    html += "<input id=\"dir_<?=$mod_num?>_<?=$kid?>\" name=\"direct\" type=\"hidden\" value=\"<?=$kid?>\" >";
                }
               if(document.getElementById('con_<?=$mod_num?>_<?=$kid?>')){
                    $("#con_<?=$mod_num?>_<?=$kid?>").remove();
                    $("#dir_<?=$mod_num?>_<?=$kid?>").remove();
               }
               $("#configlist").append(html);
   
               
               $("#isconfigandfinalscore").attr('value','1'); 
               app.hideAlert("#addModal");
            }
        </script>