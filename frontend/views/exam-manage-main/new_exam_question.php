<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/18
 * Time: 9:34
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionOption;

//选择题
if ($new_exam_question == 'select') {
?>
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=!empty($model->kid)?Yii::t('frontend', 'editor_text'):Yii::t('frontend', 'build')?><?=Yii::t('frontend', 'exam_opt')?></h4>
    </div>
<div class="content">
    <div class="infoBlock">
        <form id="form2" name="form2">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <input type="hidden" id="question_kid" value="<?= $model->kid ?>"/>
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_type')?></label>

                    <div class="col-sm-9">
                        <div class="form-group field-courseservice-course_type" style="width:98%">
                            <select class="form-control" id="examination_question_types">
                                <option value="1" <?= $model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX ? 'selected' : '' ?>>
                                    <?=Yii::t('frontend', 'exam_duoxuan')?>
                                </option>
                                <option value="0" <?= $model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO ? 'selected' : '' ?>>
                                    <?=Yii::t('frontend', 'exam_danxuan')?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>

                    <div class="col-sm-9">
                        <input class="form-control pull-left" type="text" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" id="question_title" style="width:75%" value="<?= $model->title ?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_title')])?>">
                    </div>
                </div>
            </div>
        </div>
        <!-- 多选 -->
        <div class="choiceType <?=!(!empty($model->kid) && $model->examination_question_type==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO) ? '' : 'hide'?>" id="choiceType1">
            <?php
            if (!empty($model->kid)) {
                foreach ($option_list as $item){
            ?>
            <div class="row choiceType1_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'option')?><span><?=$item->sequence_number?></span></label>
                        <div class="col-sm-9">
                            <input class="form-control pull-left checkBox" type="text" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'option_content')])?>" style="width:75%" value="<?=$item->option_title?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'option_content')])?>">
                            <label style="margin-left:10px;">
                                <input type="checkbox" value="1" class="checkBoxAnswer" <?=$item->is_right_option==LnExamQuestionOption::IS_RIGHT_OPTION_YES?'checked':''?>> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                            <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
            <div class="row choiceType1_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>1</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkBox" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <label style="margin-left:10px;">
                                <input type="checkbox" value="1" class="checkBoxAnswer"> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                            <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row choiceType1_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>2</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkBox" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <label style="margin-left:10px;">
                                <input type="checkbox" value="1" class="checkBoxAnswer"> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                            <a href="###" class="delOption"
                               style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row choiceType1_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>3</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkBox" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <label style="margin-left:10px;">
                                <input type="checkbox" value="1" class="checkBoxAnswer"> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                            <a href="###" class="delOption"
                               style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row choiceType1_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>4</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkBox" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <label style="margin-left:10px;">
                                <input type="checkbox" value="1" class="checkBoxAnswer"> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                            <a href="###" class="delOption"
                               style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
            <script type="text/html" id="choiceTpl_1">
                <div class="row choiceType1_option">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>[number]</span></label>
                            <div class="col-sm-9">
                                <input class="form-control pull-left checkBox" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                                <label style="margin-left:10px;">
                                    <input type="checkbox" value="1" class="checkBoxAnswer"> <?=Yii::t('frontend', 'exam_zhengque')?>
                                </label>
                                <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </script>
            <div class="row">
                <div class="col-md-12 col-sm-12 centerBtnArea">
                    <a href="###" class="btn btn-default btn-sm addOption" style="width:30%"><?=Yii::t('frontend', 'add_option')?></a>
                </div>
            </div>
        </div>
        <!-- 单选 -->
        <div class="choiceType <?=(!empty($model->kid) && $model->examination_question_type==LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO) ? '' : 'hide'?>" id="choiceType0">
            <?php
            if (!empty($model->kid)){
                foreach ($option_list as $item){
            ?>
            <div class="row choiceType0_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span><?=$item->sequence_number?></span></label>
                        <div class="col-sm-9">
                            <input class="form-control pull-left checkRadio" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" value="<?=$item->option_title?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <input type="text" placeholder='<?=Yii::t('frontend', 'exam_defen')?>' class="form-control pull-left checkRadioAnswer" value="<?=(float)$item->default_score?>" style="width:14%; margin-left:1%;" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>">
                            <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            }else{
            ?>
            <div class="row choiceType0_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>1</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkRadio" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <input type="text" placeholder='<?=Yii::t('frontend', 'exam_defen')?>' class="form-control pull-left checkRadioAnswer" style="width:14%; margin-left:1%;" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>">
                            <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row choiceType0_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>2</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkRadio" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <input type="text" placeholder='<?=Yii::t('frontend', 'exam_defen')?>' class="form-control pull-left checkRadioAnswer" style="width:14%; margin-left:1%;" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>" >
                            <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row choiceType0_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>3</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkRadio" type="text"  placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <input type="text" placeholder='<?=Yii::t('frontend', 'exam_defen')?>' class="form-control pull-left checkRadioAnswer" style="width:14%; margin-left:1%;" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>">
                            <a href="###" class="delOption"  style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row choiceType0_option">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>4</span></label>

                        <div class="col-sm-9">
                            <input class="form-control pull-left checkRadio" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                            <input type="text" placeholder='<?=Yii::t('frontend', 'exam_defen')?>' class="form-control pull-left checkRadioAnswer"  style="width:14%; margin-left:1%;" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>">
                            <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
            <script id="choiceTpl_0" type="text/html">
                <div class="row choiceType0_option">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span>[number]</span></label>

                            <div class="col-sm-9">
                                <input class="form-control pull-left checkRadio" type="text"  placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_opt_content_null')?>">
                                <input type="text" placeholder='<?=Yii::t('frontend', 'exam_defen')?>' class="form-control pull-left checkRadioAnswer" style="width:14%; margin-left:1%;" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>">
                                <a href="###" class="delOption" style="position: absolute;top: 0;right: -10px;font-size: 12px;"><?=Yii::t('frontend', 'tag_del')?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </script>
            <div class="row">
                <div class="col-md-12 col-sm-12 centerBtnArea">
                    <a href="###" class="btn btn-default btn-sm addOption" style="width:30%"><?=Yii::t('frontend', 'add_option')?></a>
                </div>
            </div>
        </div>
        <div class="row">
            <hr>
            <div class="infoBlock">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_zhishidian')?></label>

                            <div class="col-sm-9">
                                <input class="form-control" id="tags" type="text" style="width:100%; float:left;" data-url="<?=Url::toRoute(['/student/get-tag','cate_code'=>'examination_question-knowledge-point','format' => 'new'])?>" data-mult="1" data-option="1" autocomplete="off" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_zhishidian')?><?=Yii::t('frontend', 'exam_not_null')?>" data-delay="1">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_nandu')?></label>
                            <div class="col-sm-9">
                                <div class="form-group field-courseservice-course_type"
                                     style="width:98%">
                                    <select class="form-control" id="examination_question_level">
                                        <?php
                                        if (!empty($dictionary_list)){
                                        foreach ($dictionary_list as $item){
                                        ?>
                                        <option value="<?=$item->dictionary_value?>" <?=$model->examination_question_level==$item->dictionary_value?'selected':''?>><?=$item->dictionary_name?></option>
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
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_morenfen')?></label>
                            <div class="col-sm-9">
                                <input class="form-control" type="text" name="default_score" id="default_score"  style="width:75%"  value="<?=!empty($model->kid) ? (float)$model->default_score : 1?>" <?=(!empty($model->kid) && ($model->is_allow_change_score==LnExaminationQuestion::IS_ALLOW_CHANGE_SCORE_NO || $model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO))?'readonly':''?> data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_jiexi')?></label>
                            <div class="col-sm-9">
                                <textarea id="answer_text"><?=$model->answer?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 centerBtnArea">
                <a href="###" class="btn btn-success btn-sm centerBtn" id="saveBtn"><?=Yii::t('frontend', 'tag_save')?></a>
            </div>
        </div>
        </form>
    </div>
    <div class="c"></div>
</div>
<?php
}else if ($new_exam_question == 'judge') {/*判断题*/
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=!empty($model->kid)?Yii::t('frontend', 'editor_text'):Yii::t('frontend', 'build')?><?=Yii::t('frontend', 'exam_panduanti')?></h4>
</div>
<div class="content">
    <div class="infoBlock">
        <form id="form2" name="form2">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <input type="hidden" id="question_kid" value="<?=$model->kid?>" />
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>
                    <div class="col-sm-9">
                        <input class="form-control pull-left" id="question_title" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_title')?>" value="<?=$model->title?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'question_title')?><?=Yii::t('frontend', 'exam_not_null')?>" >
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="display: none;">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_miaoshu')?></label>
                    <div class="col-sm-9">
                        <textarea id="question_description" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_miaoshu')?><?=Yii::t('frontend', 'exam_not_null')?>"><?=$model->description?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_zhengque')?>/<?=Yii::t('frontend', 'exam_wrong')?></label>
                    <div class="col-sm-9">
                        <div class="btn-group" data-toggle="buttons">
                            <label for="is_right_option_1" style="margin-right:68px;">
                                <input type="radio" id="is_right_option_1" name="is_right_option" value="1" <?=(!empty($model->kid)&&$option_list[0]->option_stand_result==LnExamQuestionOption::JUDGE_OPTION_RESULT_RIGHT)?'checked':''?> /> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                            <label for="is_right_option_0">
                                <input type="radio" id="is_right_option_0" name="is_right_option" value="0" <?=(!empty($model->kid)&&$option_list[0]->option_stand_result==LnExamQuestionOption::JUDGE_OPTION_RESULT_WRONG)?'checked':''?> /> <?=Yii::t('frontend', 'exam_wrong')?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="infoBlock">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_zhishidian')?></label>
                        <div class="col-sm-9">
                            <input class="form-control" id="tags" type="text" style="width:100%; float:left;" data-url="<?=Url::toRoute(['/student/get-tag','cate_code'=>'examination_question-knowledge-point','format' => 'new'])?>" data-mult="1" data-option="1" autocomplete="off" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_zhishidian')?><?=Yii::t('frontend', 'exam_not_null')?>" data-delay="1">
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
                                <select id="examination_question_level" class="form-control" name="">
                                    <?php
                                    if (!empty($dictionary_list)){
                                        foreach ($dictionary_list as $item){
                                            ?>
                                            <option value="<?=$item->dictionary_value?>" <?=$model->examination_question_level==$item->dictionary_value?'selected':''?>><?=$item->dictionary_name?></option>
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
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_morenfen')?></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="default_score" id="default_score" value="<?=!empty($model->kid) ? (float)$model->default_score:1?>" type="text" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', 'exam_type_i_or_f')?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_jiexi')?></label>
                        <div class="col-sm-9">
                            <textarea id="answer_text"><?=$model->answer?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12 centerBtnArea">
                    <a href="###" class="btn btn-success btn-sm centerBtn" id="saveJudge"><?=Yii::t('common', 'save')?></a>
                </div>
            </div>
        </div>
        </form>
    </div>
    <div class="c"></div>
</div>
<?php
}else if ($new_exam_question == 'input'){
?>
<!-- 新建填空题 -->
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'exam_new_tiankong')?></h4>
</div>
<div class="content">
    <div class="infoBlock">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_stem')?></label>
                    <div class="col-sm-9">
                        <textarea></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label">(1)</label>
                    <div class="col-sm-9">
                        <input class="form-control pull-left" type="text" id="formGroupInputSmall">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label">(2)</label>
                    <div class="col-sm-9">
                        <input class="form-control pull-left" type="text" id="formGroupInputSmall">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label">(3)</label>
                    <div class="col-sm-9">
                        <input class="form-control pull-left" type="text" id="formGroupInputSmall">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 centerBtnArea">
                <a href="###" class="btn btn-default btn-sm" style="width:30%"><?=Yii::t('frontend', 'exam_yigekongbaixiang')?></a>
            </div>
        </div>
        <hr>
        <div class="infoBlock">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_zhishidian')?></label>
                        <div class="col-sm-9">
                            <input class="form-control" type="text" style="width:80%; float:left;">
                            <a href="###" class="btn btn-sm pull-left"><?=Yii::t('frontend', 'select')?></a>
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
                                <select id="courseservice-course_type" class="form-control" name="">
                                    <option value="0"><?=Yii::t('frontend', 'exam_rongyi')?></option>
                                    <option value="0"><?=Yii::t('frontend', 'exam_zhongdeng')?></option>
                                    <option value="0"><?=Yii::t('frontend', 'exam_kunnan')?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_morenfen')?></label>
                        <div class="col-sm-9">
                            <input class="form-control" type="text">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_jiexi')?></label>
                        <div class="col-sm-9">
                            <textarea style="width:80%"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12 centerBtnArea">
                    <a href="###" class="btn btn-success btn-sm centerBtn"><?=Yii::t('common', 'save')?></a>
                </div>
            </div>
        </div>
    </div>
    <div class="c"></div>
</div>
<?php
}
?>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script>
    $(function() {
        var  validation = app.creatFormValidation($("#form2"));
        $("#new_exam_question").on('change', "#examination_question_types", function () {
            var choiceType = $(this).val();
            $(".choiceType").addClass('hide');
            $("#choiceType" + choiceType).removeClass('hide');
            if (choiceType == 0) {
                $("#default_score").attr('readonly', true);
            } else {
                $("#default_score").attr('readonly', false);
            }
        });
        $("#new_exam_question").on('click', '.result', function(){
            validation.hideAlert($("#tags"));
        });
        $("#new_exam_question").on('click', '.-query-add-list', function(){
            validation.hideAlert($("#tags"));
        });
        $("#saveBtn").click(function () {
            var kid = $("#question_kid").val();
            var title = $("#question_title").val().replace(/(^\s*)|(\s*$)/g,'');
            if (title.length == 0) {
                validation.showAlert($("#question_title"));
                return false;
            }
            if (app.stringLength(title) > 1500){
                validation.showAlert($("#question_title"),"<?=Yii::t('frontend', 'exam_biaotizuiduo_500')?>");
                return false;
            }
            var xss_title = filterXSS(title);
            if (title != xss_title){
                validation.showAlert($("#question_title"),"<?=Yii::t('frontend', 'exam_biaoti_ill')?>");
                return false;
            }
            var examination_question_type = $("#examination_question_types").val();
            var answer = $("#choiceType" + examination_question_type).find('.choiceType' + examination_question_type + '_option').length;
            if (answer < 2) {
                app.showMsg('<?=Yii::t('frontend', 'more_than_2_option')?>');
                return false;
            }
            var options = [];
            var answer = [];
            var err = 0;
            var max = 0;
            if (examination_question_type == 0) {
                var score = 0;
                var format = 0;
                $(".choiceType0_option").each(function () {
                    var checkTitle = $(this).find('.checkRadio').val().replace(/(^\s*)|(\s*$)/g, '');
                    if (checkTitle.length == 0) {
                        validation.showAlert($(this).find('.checkRadio'));
                        err++;
                        return false;
                    }
//                    var xss_checkTitle = filterXSS(checkTitle);
//                    if (checkTitle != xss_checkTitle){
//                        err++;
//                        validation.showAlert($(this).find('.checkRadio'),"选项<?=Yii::t('frontend', 'exam_biaoti_ill')?>");
//                        return false;
//                    }
                    var de_score = $(this).find('.checkRadioAnswer').val();
                    if (parseInt(de_score) > 0) {
                        score++;
                    }
                    if (!/^[0-9]*(\.[0-9]{1,2})?$/.test(de_score)){
                        validation.showAlert($(this).find('.checkRadioAnswer'));
                        format ++;
                        err++;
                        return false;
                    }
                    if (parseFloat(de_score) > 500){
                        validation.showAlert($(this).find('.checkRadioAnswer'),'<?=Yii::t('frontend', 'exam_max_500')?>');
                        return false;
                    }
                    options.push(checkTitle);
                    de_score = de_score > 0 ? de_score : 0;
                    answer.push(de_score);
                    if (de_score > max){
                        max = de_score;
                    }
                });
                if (format > 0){
                    return false;
                }
                if (err > 0) {
                    return false;
                }
                if (score == 0) {
                    app.showMsg('<?=Yii::t('frontend', 'exam_opt_more_than_0')?>');
                    err++;
                    return false;
                }
            } else if (examination_question_type == 1) {
                $(".choiceType1_option").each(function () {
                    var checkTitle = $(this).find('.checkBox').val().replace(/(^\s*)|(\s*$)/g, '');
                    if (checkTitle.length == 0) {
                        //app.showMsg('选项为必填字段');
                        validation.showAlert($(this).find('.checkBox'));
                        err++;
                        return false;
                    }
//                    var xss_checkTitle = filterXSS(checkTitle);
//                    if (checkTitle != xss_checkTitle){
//                        err++;
//                        validation.showAlert($(this).find('.checkBox'),"选项<?=Yii::t('frontend', 'exam_biaoti_ill')?>");
//                        return false;
//                    }
                    options.push(checkTitle);
                    if ($(this).find(".checkBoxAnswer").is(':checked')) {
                        answer.push(1);
                    } else {
                        answer.push(0);
                    }
                });
                if (err > 0) {
                    return false;
                }
                if ($(".checkBoxAnswer:checked").length < 2) {
                    app.showMsg('<?=Yii::t('frontend', 'exam_answers_need_2')?>');
                    err++;
                    return false;
                }
            }
            var tags = [];
            var tag_json = common_tags.get();
            if (typeof tag_json != 'undefined') {
                var tag_length = tag_json.length;
                if (tag_length > 0) {
                    for (var i = 0; i < tag_length; i++) {
                        tags.push(tag_json[i]['title']);
                    }
                }
            }
            if (tags.length == 0) {
                validation.showAlert($("#tags"));
                return false;
            }
            var examination_question_level = $("#examination_question_level").val();
            var default_score = $("#default_score").val().replace(/(^\s*)|(\s*$)/g, '');
            $("#default_score").val(default_score);
            if (default_score == 0 || default_score == "") {
                validation.showAlert($("#default_score"));
                return false;
            }
            if (!/^[0-9]*(\.[0-9]{1,2})?$/.test(default_score)){
                validation.showAlert($("#default_score"));
                return false;
            }
            if (parseFloat(default_score) > 500){
                validation.showAlert($("#default_score"),'<?=Yii::t('frontend', 'exam_default_500')?>');
                return false;
            }
            var question_answer = $("#answer_text").val().replace(/(^\s*)|(\s*$)/g, '');
            if (question_answer != "" && app.stringLength(question_answer) > 600){
                app.showMsg('<?=Yii::t('frontend', 'exam_no_more_200')?>');
                return false;
            }
            var xss_question_answer = filterXSS(question_answer);
            if (question_answer != xss_question_answer){
                app.showMsg("<?=Yii::t('frontend', 'exam_char_ill')?>");
                return false;
            }
            var url = "<?=Url::toRoute(['/exam-manage-main/question-add'])?>";
            $.post(url, {
                kid: kid,
                category_id: '<?=$category_id?>',
                examination_question_type: examination_question_type,
                title: title,
                options: options,
                answer: answer,
                tags: tags,
                examination_question_level: examination_question_level,
                default_score: default_score,
                question_answer: question_answer
            }, function (data) {
                if (data.result == 'success') {
                    reloadForm();
                    loadTree();
                    app.showMsg('<?=Yii::t('frontend', 'exam_done_succeed')?>');
                    app.hideAlert($("#new_exam_question"));
                } else {
                    app.showMsg(data.errmsg);
                    return false;
                }
            }, 'json');
        });
        $(".choiceType").on('click', '.delOption', function () {
            var options = $(this).parents('.choiceType').find('.row');
            if (options.length <= 3) {
                app.showMsg('<?=Yii::t('frontend', 'more_than_2_option')?>');
                return false;
            }
            var options = $(this).parents('.choiceType').find('.row');
            $(this).parents('.row').remove();
            options.each(function () {
                $(this).find('span').html($(this).index() + 1);
            });
        });
        $(".addOption").click(function () {
            var type = $("#examination_question_types").val();
            var html = $("#choiceTpl_" + type).html();
            var length = $(".choiceType" + type + "_option").length;
            html = html.replace('[number]', length + 1);
            $("#choiceTpl_" + type).before(html);
        });
        $("#choiceType0").on('keyup blur', '.checkRadioAnswer', function () {
            if (!/^[0-9]*(\.[0-9]{1,2})?$/.test($(this).val())){
                return false;
            }
            var score = parseFloat($(this).val());
            if (parseFloat(score) > 500){
                validation.showAlert($(this),'<?=Yii::t('frontend', 'exam_max_500')?>');
                return false;
            }
            var score_list = $("#choiceType0 .checkRadioAnswer").map(function(e){
                if (isNaN(parseFloat($(this).val()))){
                    return 0;
                }else{
                    return parseFloat($(this).val());
                }
            }).get();
            var max = Math.max.apply(null, score_list);
            $("#default_score").val(max);
        });
        $("#saveJudge").click(function () {
            var kid = $("#question_kid").val();
            var title = $("#question_title").val().replace(/(^\s*)|(\s*$)/g, '');
            if (title.length == 0) {
                //app.showMsg('标题不能为空');
                validation.showAlert($("#question_title"));
                return false;
            }
            if (app.stringLength(title) > 750){
                validation.showAlert($("#question_title"),"<?=Yii::t('frontend', 'exam_title_max_200')?>");
                return false;
            }
            var xss_title = filterXSS(title);
            if (title != xss_title){
                validation.showAlert($("#question_title"),"<?=Yii::t('frontend', 'exam_biaoti_ill')?>");
                return false;
            }
            var question_description = $("#question_description").val().replace(/(^\s*)|(\s*$)/g,'');
            /*if (question_description.length == 0) {
                validation.showAlert($("#question_description"));
                return false;
            }
            var xss_question_description = filterXSS(question_description);
            if (question_description != xss_question_description){
                validation.showAlert($("#question_description"), "描述含有非法字符");
                return false;
            }*/
            var is_right_option = $("input[name='is_right_option']:checked").val();
            if (typeof is_right_option == 'undefined') {
                app.showMsg('<?=Yii::t('frontend', 'exam_choose_rw')?>');
                return false;
            }
            //var tags = $("#tags").val();
            var tags = [];
            var tag_json = common_tags.get();
            if (typeof tag_json != 'undefined') {
                var tag_length = tag_json.length;
                if (tag_length > 0) {
                    for (var i = 0; i < tag_length; i++) {
                        tags.push(tag_json[i]['title']);
                    }
                }
            }
            if (tags.length == 0) {
                //app.showMsg('<?=Yii::t('frontend', 'exam_zhishidian')?>为必填项');
                validation.showAlert($("#tags"));
                return false;
            }
            var examination_question_level = $("#examination_question_level").val();
            var default_score = $("#default_score").val().replace(/(^\s*)|(\s*$)/g, '');
            $("#default_score").val(default_score);
            if (default_score == 0 || default_score == "") {
                validation.showAlert($("#default_score"));
                //app.showMsg('默认分为必填字段');
                return false;
            }
            if (!/^[0-9]*(\.[0-9]{1,2})?$/.test(default_score)){
                validation.showAlert($("#default_score"));
                return false;
            }
            if (parseFloat(default_score) > 500){
                validation.showAlert($("#default_score"),'<?=Yii::t('frontend', 'exam_default_500')?>');
                return false;
            }
            var question_answer = $("#answer_text").val().replace(/(^\s*)|(\s*$)/g, '');
            if (question_answer != "" && app.stringLength(question_answer) > 600){
                app.showMsg('<?=Yii::t('frontend', 'exam_no_more_200')?>');
                return false;
            }
            var xss_question_answer = filterXSS(question_answer);
            if (question_answer != xss_question_answer){
                app.showMsg("<?=Yii::t('frontend', 'exam_char_ill')?>");
                return false;
            }
            var url = "<?=Url::toRoute(['/exam-manage-main/question-judge-add'])?>";
            $.post(url, {
                kid: kid,
                category_id: '<?=$category_id?>',
                title: title,
                description: question_description,
                is_right_option: is_right_option,
                tags: tags,
                examination_question_level: examination_question_level,
                default_score: default_score,
                question_answer: question_answer
            }, function (data) {
                if (data.result == 'success') {
                    reloadForm();
                    loadTree();
                    app.showMsg('<?=Yii::t('frontend', 'exam_done_succeed')?>');
                    app.hideAlert($("#new_exam_question"));
                } else {
                    app.showMsg(data.errmsg);
                    return false;
                }
            }, 'json');
        });
    });
    <?php
    if (!empty($model->kid)) {
    ?>
    window.common_modify_tags = '<?=$tags?>';
    <?php
    }
    ?>
</script>
