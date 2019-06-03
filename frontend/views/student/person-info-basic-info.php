<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/14
 * Time: 09:09
 */
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $content string */
?>
<div class=" panel-default scoreList">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id' => 'updateInfoForm',
            'method' => 'post',
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"controls col-md-10 col-sm-12\">{input}\n<p class=\"help-block\">{hint}</p></div>",
                'labelOptions' => ['class' => 'control-label col-md-2 col-sm-12'],
                'inputOptions' => ['class' => 'input-xlarge'],
                'options' => ['class' => 'control-group row'],
            ],
        ]); ?>
        <div class="panel-body courseInfoInput">
            <div class="row">
                <div class="col-md-6">
                    <h4><?= Yii::t('frontend', 'panel_basic_information') ?></h4>
                    <fieldset>
                        <?= $form->field($model, 'real_name')->textInput(['maxlength' => 255]) ?>
                        <?= $form->field($model, 'nick_name')->textInput(['maxlength' => 255]) ?>
                        <?= $form->field($model, 'gender')->dropDownList(ArrayHelper::map($genderModel, 'dictionary_value', 'dictionary_name_i18n'),
                            ['prompt' => Yii::t('common', 'select_more')]) ?>
                        <?= $form->field($model, 'birthday')->textInput(['data-type' => 'rili', 'readonly' => true]) ?>
                        <?= $form->field($model, 'email')->textInput(['maxlength' => 50, 'readonly' => true]) ?>
                        <?= $form->field($model, 'id_number')->textInput(['maxlength' => 50]) ?>
                        <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => 30]) ?>
                        <?= $form->field($model, 'home_phone_no')->textInput(['maxlength' => 30]) ?>
                        <div class="control-group row">
                            <label class="control-label col-md-2 col-sm-12"><?= Yii::t('frontend', 'personal_tag') ?></label>
                            <div class="controls col-md-10 col-sm-12">
                                <input style="width: 80%" type="text" id="tag" class="input-xlarge"
                                       value="<?= $userTags ?>" maxlength="30" readonly>
                                <a href="javascript:void(0)" onclick="tag_init()" style="margin-left: 10px"><?= Yii::t('frontend', 'config') ?></a>
                            </div>
                        </div>
                        <?= $form->field($model, 'theme')->dropDownList(ArrayHelper::map($themeModel, 'dictionary_value', 'dictionary_name_i18n'),
                            ['prompt' => Yii::t('common', 'select_more')]) ?>
                        <?= $form->field($model, 'language')->dropDownList(ArrayHelper::map($languageModel, 'dictionary_value', 'dictionary_name_i18n'),
                            ['prompt' => Yii::t('common', 'select_more')]) ?>
<!--                        --><?//= $form->field($model, 'timezone')->dropDownList(ArrayHelper::map($timezoneModel, 'dictionary_value', 'dictionary_name_i18n'),
//                            ['prompt' => Yii::t('common', 'select_more')]) ?>
                        <?= $form->field($model, 'location')->dropDownList(ArrayHelper::map($locationModel, 'dictionary_value', 'dictionary_name_i18n'),
                            ['prompt' => Yii::t('common', 'select_more')]) ?>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <h4><?= Yii::t('frontend', 'business_related') ?></h4>
                    <fieldset>
                        <div class="control-group row">
                            <!-- Text input-->
                            <label class="control-label col-md-2 col-sm-12" for="input01"><?= Yii::t('frontend', 'business_name') ?></label>
                            <div class="controls col-md-10 col-sm-12">
                                <input type="text" value="<?= $company ?>" class="input-xlarge" readonly />
                                <p class="help-block"></p>
                            </div>
                        </div>
                        <div class="control-group row">
                            <!-- Text input-->
                            <label class="control-label col-md-2 col-sm-12" for="input01"><?= Yii::t('frontend', 'belong_to_domain') ?></label>
                            <div class="controls col-md-10 col-sm-12">
                                <input type="text" value="<?= $domain ?>" class="input-xlarge" readonly />
                                <p class="help-block"></p>
                            </div>
                        </div>
                        <div class="control-group row">
                            <!-- Text input-->
                            <label class="control-label col-md-2 col-sm-12" for="input01"><?= Yii::t('frontend', 'organization_department') ?></label>
                            <div class="controls col-md-10 col-sm-12">
                                <input type="text" value="<?= $org ?>" class="input-xlarge" readonly />
                                <p class="help-block"></p>
                            </div>
                        </div>
                        <div class="control-group row">
                            <!-- Text input-->
                            <label class="control-label col-md-2 col-sm-12" for="input01"><?= Yii::t('frontend', 'position') ?></label>
                            <div class="controls col-md-10 col-sm-12">
                                <input type="text" value="<?= $position ?>" class="input-xlarge" readonly />
                                <p class="help-block"></p>
                            </div>
                        </div>
                        <div class="control-group row">
                            <!-- Text input-->
                            <label class="control-label col-md-2 col-sm-12" for="input01"><?= Yii::t('frontend', 'reporting_manager') ?></label>
                            <div class="controls col-md-10 col-sm-12">
                                <input type="text" value="<?= $reporting_manager ?>" class="input-xlarge" readonly />
                                <p class="help-block"></p>
                            </div>
                        </div>
                        <div class="control-group row">
                            <!-- Text input-->
                            <label class="control-label col-md-2 col-sm-12" for="input01"><?= Yii::t('frontend', 'search_range') ?></label>
                            <div class="controls col-md-10 col-sm-12">
                                <input type="text" value="<?= $searched_domain ?>" class="input-xlarge" readonly />
                                <p class="help-block"></p>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <hr/>
            <?=
            Html::submitButton(Yii::t('frontend', 'update'),
                ['id' => 'updateInfoBtn', 'class' => 'btn btn-success pull-right'])
            ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="ui modal" id="create_label">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'add_{value}',['value'=> Yii::t('frontend', 'personal_tag')]) ?></h4>
    </div>
    <div class="content">
        <div class="privateLabel">
            <div class="row">
            </div>
            <p class="tag_tip" style="text-align: center; color: red; display: none; margin-top: 10px; margin-bottom: -10px">
                <?= Yii::t('frontend', 'most_{value}_tag',['value'=> 20]) ?>
            </p>
        </div>
        <div class="publicLabel">
            <div class="searchLabel">
                <input type="text" class="searchArea"/>
                <a href="###" class="searchBtn"><?= Yii::t('frontend', 'top_search_text') ?></a>
                <a href="###" class="changeLabel" style="float: right;"><?= Yii::t('frontend', 'change_all') ?></a>
            </div>
            <div class="resultPanel">
                <div class="row">
                </div>
            </div>
        </div>
    </div>
    <div class="centerBtnArea groupAddMember">
        <a href="javascript:void(0);" class="btn centerBtn" onclick="saveSetTag()"><?= Yii::t('common', 'save') ?></a>
    </div>
</div>
<script src="/static/frontend/js/label.js" type="text/javascript"></script>
<script>
    app.genCalendar();
    $("#updateInfoForm").on("submit", function (event) {
        event.preventDefault();
        var $form = $(this);
        var validateResult = $form.data('yiiActiveForm').validated;
        if (validateResult == true) {
            //submitModalForm("", "updateInfoForm", "", true, false, null, null);
            // window.location.reload();
            submitModalForm.call({callback: function (){
                window.location.reload();
            }}, "", "updateInfoForm", "", true, false, null, null);
        }
    });

    function showTag() {
        tag_init();
    }
    var getTagDataUrl = "<?=Url::toRoute(['common/get-tag-data'])?>";

    function saveSetTag() {
        var strIds = new Array();//声明一个存放id的数组
        var strVals = "";
        for (var i = 0; i < privateLabel.length; i++) {
            strIds.push(privateLabel[i].id);
            if (i === privateLabel.length - 1) {
                strVals = strVals + privateLabel[i].val;
            }
            else {
                strVals = strVals + privateLabel[i].val + ", ";
            }
        }

        $.post("<?=Url::toRoute(['student/set-tag'])?>", {"tags": strIds, "time": new Date().getTime()},
            function (data) {
                if (data.result === 'success') {
                    app.hideAlert("#create_label");
                    if (checkPointResult(data.pointResult)) {
                        //score-Effect(data.point);
                        scorePointEffect(data.pointResult.show_point, data.pointResult.point_name, data.pointResult.available_point);
                    }
                    else {
                        app.showMsg('<?= Yii::t('common', 'operation_success') ?>', 1500);
                    }
                    $("#tag").val(strVals);
                }
            },
            "json");
    }
</script>