<?php

use common\helpers\TArrayHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<style>
    /*label{display: none;}*/
</style>

<script>
    $("#search").on('click', function(){
//        alert('reset');
        reloadForm();
    });

    $("#reset").on('click', function(){
        //        alert('reset');
        clearForm("searchForm");
    });
</script>
<div class="list-search-form">

    <?php $form = ActiveForm::begin([
        'id' => 'searchForm',
        'action' => ['list'],
        'method' => 'get',
    ]); ?>
    <table>
        <tr>
            <td>
                <?= $form->field($model, 'tag_value')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'tag_category_id')
                    ->dropDownList(ArrayHelper::map($tagCategoryModel, 'kid', 'cate_name'),
                    ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            <td>
                <?= $form->field($model, 'company_id')
                    ->dropDownList(ArrayHelper::map($companyModel, 'kid', 'company_name'),
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            <td>
                <?= Html::button(Yii::t('common', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
                <?= Html::button(Yii::t('common', 'reset'), ['class' => 'btn btn-default', 'id'=>'reset']) ?>
            </td>
        </tr>
    </table>


    <?php ActiveForm::end(); ?>

</div>