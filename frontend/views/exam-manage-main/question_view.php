<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/18
 * Time: 9:34
 */
use yii\helpers\Url;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionOption;

//选择题
if ($new_exam_question == 'select') {
?>
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'exam_viewpanduan')?></h4>
    </div>
<div class="content">
    <div class="infoBlock">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_type')?></label>
                    <div class="col-sm-9">
                        <div class="form-group field-courseservice-course_type" style="width:98%">
                           <?
                           if ($model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX ){
                               echo Yii::t('frontend', 'exam_duoxuan');
                           }else if( $model->examination_question_type == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
                               echo Yii::t('frontend', 'exam_danxuan');
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
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>
                    <div class="col-sm-9">
                        <?= $model->title ?>
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
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_option')?><span><?=$item->sequence_number?></span></label>
                        <div class="col-sm-9">
                            <input class="form-control pull-left checkBox" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" value="<?=$item->option_title?>" readonly>
                            <label style="margin-left:10px;">
                                <input type="checkbox" value="1" onclick="return false;" class="checkBoxAnswer" <?=$item->is_right_option==LnExamQuestionOption::IS_RIGHT_OPTION_YES?'checked':''?>> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            ?>
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
                        <div class="col-sm-9 clearfix">
                            <input class="form-control pull-left checkRadio" type="text" placeholder="<?=Yii::t('frontend', 'exam_type_opt_content')?>" style="width:75%" value="<?=$item->option_title?>" readonly>
                            <input type="text" placeholder='<?=Yii::t('frontend', 'exam_defen')?>' class="form-control pull-left checkRadioAnswer" value="<?=(float)$item->default_score?>" style="width:14%; margin-left:1%;" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            ?>
        </div>
        <div class="row">
            <hr>
            <div class="infoBlock">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_zhishidian')?></label>

                            <div class="col-sm-9">
                                <?=join(' ', $tags)?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_nandu')?></label>
                            <div class="col-sm-9">
                                <div class="form-group field-courseservice-course_type" style="width:98%">
                                    <?php
                                        if (!empty($dictionary_list)){
                                        foreach ($dictionary_list as $item){
                                            if ($model->examination_question_level==$item->dictionary_value) {
                                        ?>
                                            <?= $item->dictionary_name ?>
                                        <?php
                                            }
                                        }
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
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_morenfen')?></label>
                            <div class="col-sm-9">
                                <?=(float)$model->default_score?> <?=Yii::t('frontend', 'exam_fenshu')?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_jiexi')?></label>
                            <div class="col-sm-9">
                                <?=$model->answer?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="c"></div>
</div>
<?php
}else if ($new_exam_question == 'judge') {/*判断题*/
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'exam_viewxuanze')?></h4>
</div>
<div class="content">
    <div class="infoBlock">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'question_title')?></label>
                    <div class="col-sm-9">
                        <?=$model->title?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="display: none;">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_miaoshu')?></label>
                    <div class="col-sm-9">
                        <?=$model->description?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_right')?>/<?=Yii::t('frontend', 'exam_wrong')?></label>
                    <div class="col-sm-9">
                        <div class="btn-group" data-toggle="buttons">
                            <label for="is_right_option_1" style="margin-right:68px;">
                                <input type="radio" onclick="return false;" <?=(!empty($model->kid)&&$option_list[0]->option_stand_result==LnExamQuestionOption::JUDGE_OPTION_RESULT_RIGHT)?'checked':''?> /> <?=Yii::t('frontend', 'exam_zhengque')?>
                            </label>
                            <label for="is_right_option_0">
                                <input type="radio" onclick="return false;" <?=(!empty($model->kid)&&$option_list[0]->option_stand_result==LnExamQuestionOption::JUDGE_OPTION_RESULT_WRONG)?'checked':''?> /> <?=Yii::t('frontend', 'exam_wrong')?>
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
                            <?=join(' ', $tags)?>
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
                                <?php
                                if (!empty($dictionary_list)){
                                    foreach ($dictionary_list as $item){
                                        if ($model->examination_question_level==$item->dictionary_value) {
                                ?>
                                <?= $item->dictionary_name ?>
                                <?php
                                        }
                                    }
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
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_morenfen')?></label>
                        <div class="col-sm-9">
                            <?=(float)$model->default_score?> <?=Yii::t('frontend', 'exam_fenshu')?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_jiexi')?></label>
                        <div class="col-sm-9">
                            <?=$model->answer?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="c"></div>
</div>
<?php
}else if ($new_exam_question == 'input'){
?>
<!-- 新建填空题 -->

<?php
}
?>
<div class="actions" style="text-align: center;">
    <button type="button" class="btn btn-default" onclick="app.hideAlert('#new_exam_question');"><?=Yii::t('common','close')?></button>
</div>
