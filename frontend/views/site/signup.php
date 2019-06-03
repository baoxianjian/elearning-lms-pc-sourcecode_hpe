<?php
use components\widgets\TBreadcrumbs;
use components\widgets\TDatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\viewmodels\SignupForm */
$this->pageTitle = Yii::t('frontend','new_user_regist');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-8 col-md-offset-2">
            <div class="panel-default scoreList">
                <div class="panel-body courseInfoInput">
                    <?php $form = ActiveForm::begin(['id' => 'form-signup',
                        'method' => 'post',
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"controls col-md-10 col-sm-12\">{input}\n<p class=\"help-block\">{error}</p></div>",
                            'labelOptions' => ['class' => 'control-label col-md-2 col-sm-12'],
                            'inputOptions' => ['class' => 'input-xlarge'],
                            'options' => ['class'=>'control-group row'],
                        ],
                    ]); ?>
                    <div class="row">
                        <fieldset>
                            <?= $form->field($model, 'user_name')->textInput(['maxlength' => 255]) ?>
                            <?= $form->field($model, 'real_name')->textInput(['maxlength' => 255]) ?>
                            <?= $form->field($model, 'nick_name')->textInput(['maxlength' => 255]) ?>
                            <hr>
                            <?= $form->field($model, 'gender')->dropDownList(ArrayHelper::map($genderModel,'dictionary_value', 'dictionary_name_i18n'),
                                ['prompt'=> Yii::t('common','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
                            <input type="hidden" value="<?=$model->company_id?>" name="SignupForm[company_id]">
                            <?= $form->field($model, 'birthday')->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
                            <hr>
                            <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
                            <?= $form->field($model, 'email_repeat')->textInput(['maxlength' => 255]) ?>
                            <hr>
                            <?= $form->field($model, 'password')->passwordInput(['maxlength' => 255]) ?>
                            <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => 255]) ?>
                        </fieldset>
                    </div>
                    <hr/>
                    <div class="centerBtnArea">
                        <?= Html::submitButton(Yii::t('common','signup'),
                            ['class' => 'btn btn-success pull-right centerBtn', 'name' => 'signup-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>