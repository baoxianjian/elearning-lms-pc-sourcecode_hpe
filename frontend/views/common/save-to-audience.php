<?php
/**
 * Created by PhpStorm.
 * User: liuc
 * Date: 2016/5/19
 * Time: 16:32
 */
use common\services\learning\ResourceService;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$resourceService = new ResourceService();
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    <h4 class="modal-title"><?= Yii::t('frontend', '{value}_save_course_regular_student_to_audience', ['value' => $count]) ?></h4>
</div>
<div class="content">
    <div class="infoBlock">
        <?php
        $form = ActiveForm::begin([
            'id' => 'saveAudienceForm',
            'method' => 'post',
            'action' => Url::to(['common/save-to-audience']),
        ]);
        ?>
        <input type="hidden" name="course_id" value="<?=$courseId?>" />
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'audience_kind') ?></label>
                    <div class="col-sm-10">
                        <select name="category_id" class="form-control"  data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('common', 'select_{value}',['value'=>Yii::t('common','category')]) ?>">
                            <option value=""><?= Yii::t('common', 'select_{value}',['value'=>Yii::t('common','category')]) ?></option>
                            <? echo $resourceService->getCategoryTree($catlog); ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'audience_name') ?></label>
                    <div class="col-sm-10">
                        <input value="【<?= $courseName ?>】<?= Yii::t('frontend', 'course_student') ?>" name="title" class="form-control" type="text" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('common', 'field_required') ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'audience_description') ?></label>
                    <div class="col-sm-10">
                        <textarea name="description" style="resize: none !important;"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <div class="col-md-12 col-sm-12 centerBtnArea">
            <a href="javascript:void(0);" class="btn btn-success btn-sm centerBtn" style="width:20%;" onclick="submitSaveAudience()"><?=Yii::t('common', 'save')?></a>
        </div>
    </div>
    <div class="c"></div>
</div>
<div class="c"></div>

<script>
    var validationSave = app.creatFormValidation($("#saveAudienceForm"));

    function submitSaveAudience() {
        if (!validationSave.validate()) {
            return false;
        }

        submitForm("saveAudienceForm");
    }
</script>