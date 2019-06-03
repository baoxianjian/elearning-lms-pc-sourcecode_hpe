<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDictionary */

?>
<?=Yii::t('backend', '{value}_tree_node_name',['value'=>Yii::t('backend','work_place')])?>：<?=Html::encode($model->dictionary_name)?>
<?=Yii::t('backend', '{value}_tree_node_code',['value'=>Yii::t('backend','work_place')])?>：<?=Html::encode($model->dictionary_code)?>
<div class="eln-work-place-update">

    <?php
    $tabItems = [
        [
            'label' => Yii::t('common', 'domain'),
            'content' => '',
            'options' => ['id' => 'domain-tab-create'],
        ]
    ];

    $domainUrl = Url::toRoute(['work-place/domain','id'=>$model->kid]);
    ?>

    <?php
    echo Tabs::widget([
        'id' => 'tabs',
        'items' => $tabItems,
//        'options' => ['tag' => 'div'],
//        'itemOptions' => ['tag' => 'div'],
//        'headerOptions' => ['class' => 'my-class'],
    ]);

    ?>

    <script>
        TabClear('domain-tab-create');
        //            alert($('#other-tab-create').html());
        <?php if (isset($domainUrl) && $domainUrl != null):?>
        var domainAjaxUrl = "<?=$domainUrl?>";
        //        alert(ajaxUrl);
        TabLoad('domain-tab-create', domainAjaxUrl);
        <?php endif?>

        var operation = '';

        function FormSubmit()
        {
            submitModalForm("","clientform-work-place-domain","updateModal",true,true);
        }
    </script>
</div>