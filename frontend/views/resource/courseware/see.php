<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="courseInfo">
    <div role="tabpanel" class="tab-pane active" id="teacher_info">
        <div class=" panel-default scoreList">
            <div class="panel-body">
                <div class="infoBlock">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_code')?></label>
                                <div class="col-sm-9">
                                    <?=$model->courseware_code?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'filename')?></label>
                                <div class="col-sm-9">
                                    <?=$fileMod->file_title?>
                                    <?php
                                    if ($model->is_allow_download == \common\models\learning\LnCourse::IS_ALLOW_OVER_YES) {
                                    ?>
                                    <?= $model->getFileLink(Yii::t('common', 'down_originfile')) ?>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_name')?></label>
                                <div class="col-sm-9"><?=$model->courseware_name?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_type')?></label>
                                <div class="col-sm-9"><?=$component->title?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_time')?></label>
                                <div class="col-sm-9"><?=$model->courseware_time?> <?=Yii::t('frontend', 'time_minute')?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_default_credit')?></label>
                                <div class="col-sm-9"><?=$model->default_credit?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'relate_{value}', ['value'=>Yii::t('common','domain')])?></label>
                                <div class="col-sm-9">
                                    <?php
                                    foreach ($domain as $key=>$val){
                                        if (in_array($val->kid, $resource)) {
                                            $str[] = $val->domain_name;
                                        }
                                    }
                                    ?>
                                    <?=join('、',$str)?>
								</div>
                            </div>
                        </div>
					</div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_category')?></label>
                                <div class="col-sm-9"><?=$model->getCoursewareCategoryText()?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'time_validity')?></label>
                                <div class="col-sm-9">
                                    <?php
                                    if (empty($model->start_at)){
                                        echo Yii::t('frontend', 'long_term');
                                    }else if ($model->start_at > time()) {
                                        ?>
                                        <?=$model->start_at ?>
                                        <?=Yii::t('common','to2')?>
                                        <?= $model->end_at ? $model->end_at :Yii::t('common', 'forever')  ?>
                                    <?php
                                    }else{
                                        ?>
                                        <?= $model->end_at ? $model->end_at :Yii::t('common', 'forever')  ?>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'supplier')?></label>
                                <div class="col-sm-9"><?=$model->vendor ? $model->vendor :Yii::t('common', 'encrypt_mode_none') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'display_name')?></label>
                                <div class="col-sm-9">
								<?=$model->is_display_pc ? Yii::t('common', 'is_display_pc') : ''?>
            <?=$model->is_display_mobile ? Yii::t('common', 'is_display_mobile') : ''?>
								</div>
                            </div>
                        </div>
					</div>
                    <div class="row">
						<div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'download')?></label>
                                <div class="col-sm-9">
								<?=$model->is_allow_download ? Yii::t('frontend', 'yes') : Yii::t('frontend', 'no')?>
								</div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>