<?php
use yii\helpers\Html;
use app\assets\AppAsset;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = Yii::t('system','frontend_name');
?>

  <div class='form animated bounceIn'>
    <h2><?=Yii::t('common','welcome')?><?=Yii::t('system','frontend_name')?></h2>
   <?php $form = ActiveForm::begin(['id' => 'login-form','method' => 'post',]); ?>
   	  <?= $form->field($model, 'user_name')->textInput(["placeholder" => Yii::t('common','user_name'),'maxlength' => 255]); ?>
      <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::t('common','password'),'maxlength' => 255]); ?>
      
      <?= Html::submitButton(Yii::t('common','login'), ['class' => 'animated infinite pulse', 'onclick'=>"return changeTitle(this)" ,'name' => 'submit-button']) ?>
    <?php ActiveForm::end(); ?>
  </div>

<script type="text/javascript">

function changeTitle(obj){
	obj.innerHTML="<?= Yii::t('common', 'ogining')?>"
	return true;
}
</script>l