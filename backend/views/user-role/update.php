<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */

?>
<?=Yii::t('backend','user')?>：<?=Html::encode($model->real_name)?>
<div class="eln-user-role-update">

    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('common','role'),
            'content' => $this->render('_form', [
                'model' => $model,
                'selected_keys' => $selected_keys,
                'availableList' => $availableList,
            ]),
            'options' => ['id' => 'role-tab-create'],
        ],
    ];

    //company
    $companyItem = [
        'label' => Yii::t('common','company'),
        'content' => '',
        'options' => ['id' => 'company-tab-create'],
    ];

    array_push($tabItems,$companyItem);

    $companyUrl = Url::toRoute(['user-role/company','userId'=>$userId]);

    //domain
    $domianItem = [
        'label' => Yii::t('common','domain'),
        'content' => '',
        'options' => ['id' => 'domain-tab-create'],
    ];

    array_push($tabItems,$domianItem);

    $domainUrl = Url::toRoute(['user-role/domain','userId'=>$userId]);


    //orgnization
    $orgnizationItem = [
        'label' => Yii::t('common','orgnization'),
        'content' => '',
        'options' => ['id' => 'orgnization-tab-create'],
    ];

    array_push($tabItems,$orgnizationItem);

    $orgnizationUrl = Url::toRoute(['user-role/orgnization','userId'=>$userId]);

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
        TabClear('company-tab-create');
        //            alert($('#other-tab-create').html());
        <?php if (isset($companyUrl) && $companyUrl != null):?>
        var companyAjaxUrl = "<?=$companyUrl?>";
        //        alert(ajaxUrl);
        TabLoad('company-tab-create', companyAjaxUrl);
        <?php endif?>

        TabClear('domain-tab-create');
        //            alert($('#other-tab-create').html());
        <?php if (isset($domainUrl) && $domainUrl != null):?>
        var domainAjaxUrl = "<?=$domainUrl?>";
        //        alert(ajaxUrl);
        TabLoad('domain-tab-create', domainAjaxUrl);
        <?php endif?>


        TabClear('orgnization-tab-create');
        //            alert($('#other-tab-create').html());
        <?php if (isset($orgnizationUrl) && $orgnizationUrl != null):?>
        var orgnizationAjaxUrl = "<?=$orgnizationUrl?>";
        //        alert(ajaxUrl);
        TabLoad('orgnization-tab-create', orgnizationAjaxUrl);
        <?php endif?>
    </script>
</div>
