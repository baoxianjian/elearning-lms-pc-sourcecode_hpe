<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/23
 * Time: 11:22
 */
use yii\helpers\Url;
use common\models\learning\LnExamination;
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?=Yii::t('frontend', 'exam_kaoshixinxi')?> <?=Yii::t('frontend', 'exam_shijuan')?>(<?=$paper->examination_question_number?><?=Yii::t('frontend', 'exam_ti')?>)</h4>
</div>
<div class="content" style="padding:0;">
    <div class="infoBlock">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_moshi')?></label>
                    <div class="col-sm-9">
                        <?=$model->examination_mode==LnExamination::EXAMINATION_MODE_TEST?Yii::t('frontend', 'exam_ceshimoshi'):Yii::t('frontend', 'exam_lianximoshi')?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'tag_mingcheng')?></label>
                    <div class="col-sm-9">
                        <?=$model->title?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_kaoshimiaoshu')?></label>
                    <div class="col-sm-9">
                        <?=$model->description?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_kaoqianmiaoshu')?></label>
                    <div class="col-sm-9">
                       <?=$model->pre_description?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_kaohoumiaoshu')?></label>
                    <div class="col-sm-9">
                       <?=$model->after_description?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_shijuan')?></label>
                    <div class="col-sm-9">
                        <?=$paper->title?> <span style="color: #337ab7;"><?=Yii::t('frontend', 'exam_gong')?><?=$paper->examination_question_number?><?=Yii::t('frontend', 'exam_ti')?>/<?=Yii::t('frontend', 'exam_gong')?><?=$paper->default_total_score?><?=Yii::t('frontend', 'exam_fenshu')?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?= Yii::t('common', 'investigation_range_') ?></label>
                    <div class="col-sm-9">
                        <div class="btn-group" data-toggle="buttons">
                            <?=$model->examination_range==LnExamination::EXAMINATION_RANGE_SELF?Yii::t('frontend', 'exam_dulishiyong'):Yii::t('frontend', 'exam_kechengneibu')?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="mode_0" class="examType <?=$model->examination_mode == LnExamination::EXAMINATION_MODE_TEST?'':'hidden'?>">
            <div class="row <?=$model->examination_range==LnExamination::EXAMINATION_RANGE_COURSE?'hidden':''?>">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_ruchangshijian')?></label>
                        <div class="col-sm-9">
                            <?=!empty($model->start_at) ? date('Y-m-d H:i:s', $model->start_at) : ''?>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=Yii::t('common', 'to2')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?=!empty($model->end_at)?date('Y-m-d H:i:s', $model->end_at):''?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_xianzhishichang')?></label>
                        <div class="col-sm-9">
                            <?=$model->limit_time?$model->limit_time.Yii::t('frontend', 'exam_time_m') : Yii::t('frontend', 'exam_na')?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_daanjiexi')?></label>
                        <div class="col-sm-9">
                            <div class="btn-group" data-toggle="buttons">
                                <label style="margin-right:68px;">
                                    <input type="radio" <?=$model->answer_view==LnExamination::ANSWER_VIEW_YES?'checked':''?> onclick="return false;" /> <?=Yii::t('frontend', 'exam_yunxuchakan')?>
                                </label>
                                <label>
                                    <input type="radio" <?=$model->answer_view!=LnExamination::ANSWER_VIEW_YES?'checked':''?> onclick="return false;" /> <?=Yii::t('frontend', 'exam_buyunxuchakan')?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('common', 'random_mode')?></label>
                        <div class="col-sm-9">
                            <?=$model->random_mode == LnExamination::RANDOM_MODE_YES?Yii::t('common', 'yes'):Yii::t('common','no')?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if ($model->random_mode == LnExamination::RANDOM_MODE_NO){
            ?>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_shitipaixu')?></label>
                        <div class="col-sm-9">
                            <div class="btn-group" data-toggle="buttons">
                                <label style="margin-right:68px;">
                                    <input type="checkbox" <?=$model->question_disorder == LnExamination::QUESTION_DISORDER_YES ? 'checked' :'' ?> onclick="return false;"> <?=Yii::t('frontend', 'exam_timuluanxu')?>
                                </label>
                                <label>
                                    <input type="checkbox"  <?=$model->option_disorder == LnExamination::OPTIOIN_DISORDER_YES ? 'checked' :'' ?> onclick="return false;"> <?=Yii::t('frontend', 'exam_xuanxiangluanxu')?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }else{
            ?>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_suijixianshi')?></label>
                        <div class="col-sm-9">
                            <?=$model->random_number?><?=Yii::t('frontend', 'exam_ti')?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_meiyexianshi')?></label>
                        <div class="col-sm-9">
                            <?=$model->each_page_number?><?=Yii::t('frontend', 'exam_ti')?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
            <div class="row <?=$model->examination_range==LnExamination::EXAMINATION_RANGE_COURSE?'hidden':''?>">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_t_times')?></label>
                        <div class="col-sm-9">
                            <?=!empty($model->limit_attempt_number)?$model->limit_attempt_number.Yii::t('frontend', 'exam_times'):Yii::t('frontend', 'exam_na')?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_pingfenmoshi')?></label>
                        <div class="col-sm-9">
                            <div class="form-group field-courseservice-course_type">
                                 <?php
                                 if ($model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_TOP){
                                     echo Yii::t('common', 'exam_attempt_strategy_0');
                                 }else if ($model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_LAST) {
                                     echo Yii::t('common', 'exam_attempt_strategy_1');
                                 }else if ($model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_AVG) {
                                     echo Yii::t('common', 'exam_attempt_strategy_2');
                                 }else if ($model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_FIRST) {
                                     echo Yii::t('common', 'exam_attempt_strategy_3') ;
                                 }
                                 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_jige')?></label>
                        <div class="col-sm-9">
                            <?=$model->pass_grade?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="mode_1" class="examType  <?=$model->examination_mode == LnExamination::EXAMINATION_MODE_EXERCISE?'':'hide'?>">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_suijixianshi')?></label>
                        <div class="col-sm-9">
                            <?=$model->random_number?><?=Yii::t('frontend', 'exam_ti')?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_meiyexianshi')?></label>
                        <div class="col-sm-9">
                            <?=$model->each_page_number?><?=Yii::t('frontend', 'exam_ti')?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>
    </div>
    <div class="c"></div>
</div>
