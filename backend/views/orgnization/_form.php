<?php

use common\helpers\TArrayHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwOrgnization */
/* @var $form yii\widgets\ActiveForm */
?>

<script>

    function validateOtherClientForm()
    {
        if ($('#company_id').val() == '')
        {
            NotyWarning('<?=Yii::t('backend','please_choose_company')?>');
            TabShow('1');
            return false;
        }
        else {
            if ($('#domain_id').val() == '')
            {
                NotyWarning('<?=Yii::t('backend','please_choose_domain')?>');
                TabShow('1');
                return false;
            }
            else {
                return true;
            }
        }
    }

    function GetDomainList(companyId)
    {
        var url = "<?=Url::toRoute(['orgnization/domain']); ?>";
        url = urlreplace(url,'companyId',companyId);
        var method = 'POST';
        var dataType = 'json';
        var keys = '';
        var domainSelect = $("#domain_id");
        ajaxData(url, method, keys, dataType, function(data){

            if (data.result === 'failure') {
                NotyWarning('<?=Yii::t('backend','operation_confirm_warning_failure')?>.');
            }
            else
            {
                domainSelect.html("");
                var temp = "<?=Yii::t('backend','select_more');?>";

//                var optionStr = "";
//                optionStr += "<option value=''>" + temp+ "</option>";
                var option = $("<option/>").attr("value", "").text(temp);
                domainSelect.append(option);
//                domainSelect.appendChild( child );
//                $("<option value=''>" + temp+ "</option>").appendTo(domainSelect);
//                alert('2');
//                domainSelect.add(new Option(temp,""));
                $.each(data.domainList,function(index,domain)
                {
//                    alert(domain.kid);
//                    alert(JSON.stringify(domain));
//                    alert(domain.domain_name);
//                    alert('3');
//                    domainSelect.add(new Option(domain.domain_name,domain.kid));
//                    optionStr += "<option value='"+domain.kid+"'>" + domain.domain_name + "</option>";
//                    $("<option value='"+domain.kid+"'>" + encodeURIComponent(domain.domain_name) + "</option>").appendTo(domainSelect);
                    var subOption = $("<option/>").attr("value", domain.kid).text(domain.domain_name);
                    domainSelect.append(subOption);
                });
//                decodeURIComponent(optionStr);
//                domainSelect.innerHTML = optionStr;
//                alert(data.domainList);
                //$('#sequence_number').val(data.domainList);
            }

            return false;
        });
    }
</script>

<div class="orgnization-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-other',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

    <?= $form->field($model, 'company_id')
        ->label(Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','company')]))
        ->dropDownList(ArrayHelper::map($companyModel,'kid', 'fwTreeNode.tree_node_name'),
        ['prompt'=>Yii::t('backend','select_more'),'id'=>'company_id', 'onchange'=>'GetDomainList($(this).val());']) ?>

    <?= $form->field($model, 'domain_id')
        ->label(Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','domain')]))
        ->dropDownList(ArrayHelper::map($domainModel,'kid', 'fwTreeNode.tree_node_name'),
        ['prompt'=>Yii::t('backend','select_more'),'id'=>'domain_id']) ?>

    <?= $form->field($model, 'is_default_orgnization')->radioList([
        '0'=>Yii::t('backend', 'no'),
        '1'=>Yii::t('backend', 'yes')],
        ['separator'=>'&nbsp;&nbsp;']) ?>

    <?= $form->field($model, 'is_make_org')->radioList([
        '0'=>Yii::t('backend', 'no'),
        '1'=>Yii::t('backend', 'yes')],
        ['separator'=>'&nbsp;&nbsp;']) ?>

    <?= $form->field($model, 'is_service_site')->radioList([
        '0'=>Yii::t('backend', 'no'),
        '1'=>Yii::t('backend', 'yes')],
        ['separator'=>'&nbsp;&nbsp;']) ?>

    <?= $form->field($model, 'orgnization_level')->dropDownList(ArrayHelper::map($orgnizationLevelModel,'dictionary_value', 'dictionary_name'),
        ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>


    <?php ActiveForm::end(); ?>

</div>
