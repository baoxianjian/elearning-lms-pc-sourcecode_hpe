         <div class="panel-body">
            <? if($courseType->course_type == 0){?>
            <div class="infoBlock">
                <h4 style=" margin-bottom: 5px; color: #0197d6; font-size: 1.6rem; "><?= Yii::t('frontend', 'direct_way') ?></h4>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6">*</strong><?= Yii::t('frontend', 'warning_for_derict') ?>.</p>
                    </div>
                    <? if(!empty($data['direct'])){?>
                        <? foreach($data['direct'] as $v){?>
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-7 control-label lessWord">[<?=$component[$v->component_id]?>]<? if(!empty($v->courseware_id)){echo $name['wares'][$v->courseware_id];}else{echo $name['activities'][$v->courseactivity_id];}?></label>
                                <div class="col-sm-5"><? if(!empty($v->pass_grade)){echo  Yii::t('frontend', 'minimum_pass_mark'). '：'.intval($v->pass_grade).Yii::t('frontend', 'point');}?></div>
                            </div>
                        </div>
                        <?}?>
                    <?}?>
                  </div>
            </div>
            <? }?>
            <div class="infoBlock">
                <h4 style=" margin-bottom: 5px; color: #0197d6; font-size: 1.6rem; "><?= Yii::t('frontend', 'nomal_way') ?></h4>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6">*</strong> <?= Yii::t('frontend', 'warning_for_derict') ?></p>
                    </div>
                    <? if(!empty($data['normal'])){?>
                    <? foreach($data['normal'] as $v){?>
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-7 control-label lessWord">[<?=$component[$v->component_id]?>]<? if(!empty($v->courseware_id)){echo $name['wares'][$v->courseware_id];}else{echo $name['activities'][$v->courseactivity_id];}?></label>
                                <div class="col-sm-5"><? if(!empty($v->score_scale)){echo Yii::t('frontend', 'weight_for_score').'：'.intval($v->score_scale).'% &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';}else{echo Yii::t('frontend', 'weight_for_score').'：'.Yii::t('frontend', 'do_not_score').' &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';}?><? if(!empty($v->pass_grade)){echo Yii::t('frontend', 'minimum_pass_mark').'：'.intval($v->pass_grade).Yii::t('frontend', 'point');}else{echo Yii::t('frontend', 'minimum_pass_mark').'：'.Yii::t('frontend', 'page_info_none');}?></div>
                            </div>
                        </div>
                    <?}?>
                    <?}?>
                </div>
            </div>
        </div>

