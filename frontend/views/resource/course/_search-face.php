<?php
use common\models\learning\LnCourse;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<style>
    .form-inline .form-group{float: left;}
</style>
<script>
    $(document).ready(function(){
        $("#searchForm").submit(function() {
            reloadForm();
            return false;
        });
    });
    function resetForm(){
        $("#searchText").val('');
        $("#is_display>option").attr("selected",false).eq(0).attr("selected",true);
        $("#courseservice-course_type>option").attr("selected",false).eq(0).attr("selected",true);
    }
</script>
<div class="form-inline pull-right">
    <?php $form = ActiveForm::begin([
        'id' => 'searchForm',
        'action'=>['list-face'],
        'method' => 'get',
    ]); ?>
    <input type="hidden" name="TreeNodeKid" id="TreeNodeKid" value="<?=$TreeNodeKid?>">
    <select name="visable" class="form-control" id="is_display">
        <option value=""><?= Yii::t('frontend', 'page_learn_path_tab_1') ?></option>
        <option value="is_display_pc" <?=$visable == 'is_display_pc' ? 'selected' : ''?>><?= Yii::t('common', 'position_pc') ?><?= Yii::t('common', 'course') ?></option>
        <option value="is_display_mobile" <?=$visable == 'is_display_mobile' ? 'selected' : ''?>><?= Yii::t('common', 'position_mobile') ?><?= Yii::t('common', 'course') ?></option>
    </select>
    <?= $form->field($model, 'course_name')->textInput(['id'=>'searchText','placeholder'=>Yii::t('common', 'list_code').'/'.Yii::t('common', 'audience_name').'/'.Yii::t('common', 'description')])->label(false) ?>
    <intpu type="hidden" name="CourseService[course_type]" value="<?=LnCourse::COURSE_TYPE_FACETOFACE?>"/>
    <?= Html::submitButton(Yii::t('common', 'search'), ['class' => 'btn btn-primary']) ?>
    <?= Html::Button(Yii::t('common', 'reset'), ['onclick'=>'resetForm()','class' => 'btn btn-default']) ?>
    <?php ActiveForm::end(); ?>
</div>