<?php
use common\models\learning\LnCourse;
use yii\helpers\Html;
use yii\helpers\Url;
use common\services\learning\ComponentService;
use common\models\learning\LnCourseware;
use common\models\framework\FwUser;
use common\models\learning\LnInvestigation;
use common\models\learning\LnExamination;
?>
<div class="courseInfo">
    <div role="tabpanel" class="tab-pane active" id="teacher_info">
        <div class=" panel-default scoreList">
            <div class="panel-body">
                <div class="infoBlock">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'course_code')?></label>
                                <div class="col-sm-9">
                                    <?=$model->course_code?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'course_name')?></label>
                                <div class="col-sm-9">
                                    <?=$model->course_name?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'course_desc')?></label>
                                <div class="col-sm-9">
                                    <?=$model->course_desc_nohtml?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'relate_{value}', ['value'=>Yii::t('common','domain')])?></label>
                                <div class="col-sm-9">
                                    <?=$model->getDomainNameByText()?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?=Yii::t('common', 'theme')?></label>
                                <div class="col-sm-9">
                                    <img src="<?= $model->theme_url ? $model->getCourseCover() : '/static/frontend/images/course_theme_small.png'?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'is_record_score')?></label>
                                <div class="col-sm-7">
                                    <?=$model->isRecordScore()?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common','category_id')?></label>
                                <div class="col-sm-7">
                                    <?=$model->getCourseCategoryText()?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'course_type')?></label>
                                <div class="col-sm-7">
                                    <?=$model->course_type==LnCourse::COURSE_TYPE_ONLINE? Yii::t('common', 'online'): Yii::t('common', 'face_to_face')?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'course_period')?></label>
                                <div class="col-sm-7">
                                    <?=$model->course_period?><?=$model->getCoursePeriodUnits($model->course_period_unit)?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'course_default_credit')?></label>
                                <div class="col-sm-7">
                                    <?=$model->default_credit?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'course_language')?></label>
                                <div class="col-sm-7">
                                    <?=$model->getDictionaryText('course_language',$model->course_language)?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'course_price')?></label>
                                <div class="col-sm-7">
                                    <?=$model->course_price?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'currency')?></label>
                                <div class="col-sm-7">
                                    <?=$model->getDictionaryText('currency',$model->currency)?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'time_validity')?></label>
                                <div class="col-sm-7">
                                    <?php
                                    if (empty($model->end_time)){
                                        echo Yii::t('frontend', 'forever');
                                    }else {
                                        if ($model->start_time > time()) {
                                    ?>
                                        <?= date('Y-m-d', $model->start_time) ?>
                                            <?= Yii::t('common', 'to2') ?>
                                        <?= !empty($model->end_time) ? date('Y-m-d', $model->end_time) : Yii::t('frontend', 'forever') ?>
                                    <?php
                                    } else {
                                    ?>
                                        <?= !empty($model->end_time) ? date('Y-m-d', $model->end_time) : Yii::t('frontend', 'forever') ?>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'display_name')?></label>
                                <div class="col-sm-7">
                                    <?=$model->is_display_pc?Yii::t('common', 'is_display_pc').'<br />':''?>
                                    <?=$model->is_display_mobile? Yii::t('common', 'is_display_mobile'):''?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?=Yii::t('common', 'course_max_attempt')?></label>
                                <div class="col-sm-7">
                                    <?=$model->max_attempt?$model->max_attempt.Yii::t('frontend', 'times'):Yii::t('common', 'not_limit')?>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($model->course_type == LnCourse::COURSE_TYPE_ONLINE) {
                        ?>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-5 control-label"><?= Yii::t('common', 'course_level') ?></label>
                                <div class="col-sm-7">
                                    <?= $model->getDictionaryText('course_level', $model->course_level) ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-12 control-label"><?=Yii::t('common', 'resource')?></label>
                                <div class="col-sm-12">
                                    <?php
                                    if (!empty($modules)) {
                                        foreach ($modules as $mods) {
                                            ?>
                                            <ul class="resourcePanel">
                                                <strong><?=Yii::t('common', 'mod_name')?>:</strong> <?= $mods['mod_name'] ?> <br/>
                                                <strong><?=Yii::t('frontend', 'module_description')?>:</strong> <?= str_replace(array("\r\n", "\r\t", "\n", "\t"), '', trim($mods['mod_desc'])) ?>
                                                <br/>
                                                <strong><?=Yii::t('frontend','mod_res')?>:</strong>
                                                <div style="padding-left: 80px; margin-top: -25px;min-height: 25px;">
                                                    <?php
                                                    if (!empty($mods['courseitems'])) {
                                                    $componentService = new ComponentService();
                                                        foreach ($mods['courseitems'] as $courseware) {
                                                            $component = $componentService->getCompoentByComponentKid($courseware['componentId']);
                                                            $icon = !empty($component->icon) ? $component->icon : '';
                                                            if ($courseware['isCourseware']) {
                                                    ?>
                                                            <li>
                                                    <?php
                                                        if ($courseware['item']->is_allow_download==LnCourseware::ALLOW_DOWNLOAD_YES){
                                                    ?>
                                                                <a href="<?=Url::toRoute(['resource/courseware/view','id'=>$courseware['item']->kid,'download'=>true])?>" target="_blank"><?=$icon?>&nbsp;<?=$courseware['item']->courseware_name?></a>
                                                                <?php
                                                                }else{
                                                                ?>
                                                                <?=$icon?>&nbsp;<?=$courseware['item']->courseware_name?>
                                                                <?php
                                                                }
                                                                ?>
                                                            </li>
                                                        <?php
                                                        }else{
                                                                //$user = FwUser::findIdentity($courseware['item']->created_by);
                                                                if ($courseware['component_code'] == 'investigation'){
                                                        ?>
                                                            <li>
                                                                <font><?=$icon?>&nbsp;<?=$courseware['item']->title?></font>&nbsp;<font><?=$courseware['item']->investigation_type == LnInvestigation::INVESTIGATION_TYPE_SURVEY ? Yii::t('common', 'investigation') : Yii::t('frontend', 'vote')?></font><!--&nbsp;<font><?//=$user->real_name .'('.$user->email.')'?></font>-->
                                                        <?php

                                                            }else if ($courseware['component_code'] == 'examination'){
                                                            ?>
                                                            <li><font><?=$icon?>&nbsp;<?=$courseware['item']->title?></font>&nbsp;<font><?=$courseware['item']->examination_mode == LnExamination::EXAMINATION_MODE_TEST ? Yii::t('frontend', 'test') : Yii::t('frontend', 'practice')?></font><!--&nbsp;<font><?//=$user->real_name .'('.$user->email.')'?></font>--></li>
                                                            <?php
                                                            }else{
                                                            ?>
                                                            <li><?=$icon?>&nbsp;<?=$courseware['item']->title?></li>
                                                            <?php
                                                                }
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </ul>
                                        <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>