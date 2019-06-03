<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDictionary */
/* @var $form yii\widgets\ActiveForm */
?>

<script>

    var operation = '';

    var formId = "clientform-work-place-domain";

    function FormSubmit()
    {
        submitModalForm("",formId,"updateModal",true,true);
//        $("#"+formId).submit();
    }

</script>