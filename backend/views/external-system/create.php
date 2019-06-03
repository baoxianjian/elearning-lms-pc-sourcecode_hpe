<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwExternalSystem */

?>
<div class="clientform-create">

    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('common','tab_basic_info'),
            'content' => $this->render('_form', [
                'model' => $model,
                'formType' => 'create'
            ]),
            'options' => ['id' => 'base-tab-create'],
        ],
    ];

    $companyItem = [
        'label' => Yii::t('common','tab_company_info'),
        'content' => '',
        'options' => ['id' => 'company-tab-create'],
    ];

    array_push($tabItems,$companyItem);

    $url = Url::toRoute(['company-external-system/create']);

    ?>

    <script>
        TabClear('company-tab-create');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('company-tab-create', ajaxUrl);
        <?php endif?>
    </script>

    <?php
    echo Tabs::widget([
        'id' => 'tabs',
        'items' => $tabItems,
//        'options' => ['tag' => 'div'],
//        'itemOptions' => ['tag' => 'div'],
//        'headerOptions' => ['class' => 'my-class'],
    ]);

    ?>

</div>