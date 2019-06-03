<?php


/* @var $this yii\web\View */
use yii\bootstrap\Tabs;
use yii\helpers\Url;

/* @var $model common\models\framework\FwExternalSystem */

?>
<div class="clientform-div">

    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('common','tab_basic_info'),
            'content' => $this->render('_form', [
                'model' => $model,
                'formType' => 'update'
            ]),
            'options' => ['id' => 'base-tab-update'],
        ],
    ];

    $companyItem = [
        'label' => Yii::t('common','tab_company_info'),
        'content' => '',
        'options' => ['id' => 'company-tab-update'],
    ];

    array_push($tabItems,$companyItem);

    $url = Url::toRoute(['company-external-system/update','systemId'=>$model->kid]);

    ?>

    <script>
        TabClear('company-tab-update');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('company-tab-update', ajaxUrl);
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
