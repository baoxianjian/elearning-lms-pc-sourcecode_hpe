<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/27
 * Time: 17:42
 */
use yii\helpers\Url;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamQuestionCopy;
use common\models\learning\LnExamPaperQuestion;

?>
<div class="pages">
<?php
if (!empty($data)) {
    $key = 0;
    foreach ($data as $item) {
        if (!empty($item['options'])  && $item['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER) {
            if ($preview == 'add'){
                $model = LnExaminationQuestion::findOne($item['qu_kid']);
            }else{
                $model = LnExamQuestionCopy::findOne($item['qu_kid']);
            }
            if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO) {
                ?>
                <!-- //单选题 -->
                <div class="row questionGroup_quest">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">
                                <?= ( $key + 1) ?>
                                .【<?= $model->getExamQuestionCategoryName() ?>】<?= $item['title'] ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <?php
                        if ($item['options']) {
                            foreach ($item['options'] as $i => $val) {
                                ?>
                                <div class="options">
                                    <label style="margin-right:40px;">
                                        <input type="radio" value="1"> <?= chr(ord('A') + $i) ?> <?= $val['option_title'] ?>
                                    </label>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            } else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX) {
                ?>
                <!-- //多选题 -->
                <div class="row questionGroup_quest">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">
                                <?= ( $key + 1) ?>
                                .【<?= $model->getExamQuestionCategoryName() ?>】<?= $item['title'] ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <?php
                        if ($item['options']) {
                            foreach ($item['options'] as $i => $val) {
                                ?>
                                <div class="options">
                                    <label style="margin-right:40px;">
                                        <input type="checkbox" value="1"> <?= chr(ord('A') + $i) ?> <?= $val['option_title'] ?>
                                    </label>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            } else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_INPUT) {
                ?>
                <!-- //填空题 -->
                <div class="row questionGroup_quest">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">
                                <?= ($key + 1) ?>
                                .【<?= $model->getExamQuestionCategoryName() ?>】<?= $item['title'] ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="options">
                            <label style="margin-right:40px;">
                                (1)
                                <input type="text" class="fillInput">
                            </label>
                        </div>
                        <div class="options">
                            <label style="margin-right:40px;">
                                (2)
                                <input type="text" class="fillInput">
                            </label>
                        </div>
                        <div class="options">
                            <label style="margin-right:40px;">
                                (3)
                                <input type="text" class="fillInput">
                            </label>
                        </div>
                    </div>
                </div>
                <?php
            } else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE) {
                ?>
                <!-- //判断题 -->
                <div class="row questionGroup_quest">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">
                                <?= ( $key + 1) ?>
                                .【<?= $model->getExamQuestionCategoryName() ?>】<?= $item['title'] ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="options">
                            <label style="margin-right:40px;">
                                <input type="radio" value="1"> <?=Yii::t('frontend', 'exam_right')?>
                            </label>
                        </div>
                        <div class="options">
                            <label style="margin-right:40px;">
                                <input type="radio" value="1"> <?=Yii::t('frontend', 'exam_wrong')?>
                            </label>
                        </div>
                    </div>
                </div>
                <?php
            }
            $key ++;
        }
        else {
?>
    <hr>
    <div class="row">
        <div class="centerBtnArea">
            <a href="###" class="btn btn-sm btn-success centerBtn prevPage" style="width:20%"><?=Yii::t('frontend','last_page')?></a>
            <a href="###" class="btn btn-sm btn-success centerBtn nextPage" style="width:20%"><?=Yii::t('frontend','next_page')?></a>
        </div>
    </div>
</div>
<div class="pages hidden">
<?php
        }
    }
}
?>
</div>
<script>
$(function(){
    $("#questionList .pages:last").append('<hr><div class="row"><div class="centerBtnArea"><a href="###" class="btn btn-sm btn-success centerBtn prevPage" style="width:20%"><?=Yii::t('frontend', 'exam_lp')?></a>&nbsp;<a href="###" class="btn btn-sm btn-success centerBtn nextPage" style="width:20%"><?=Yii::t('frontend', 'exam_np')?></a></div></div>');

    $(".pages").eq(0).find(".prevPage").addClass('disabled');
    $("#questionList .pages:last").find(".nextPage").addClass('disabled');

    $(".prevPage").unbind('click').on('click', function(){
        var parent = $(this).parents('.pages');
        parent.prev().removeClass('hidden');
        parent.addClass('hidden');
        if ($("#new_exam").length){
            app.refreshAlert("#new_exam");
        }
    });

    $(".nextPage").unbind('click').on('click', function(){
        var parent = $(this).parents('.pages');
        parent.next().removeClass('hidden');
        parent.addClass('hidden');
        if ($("#new_exam").length){
            app.refreshAlert("#new_exam");
        }
    });
});
</script>
