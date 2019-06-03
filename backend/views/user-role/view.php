<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */

?>
<?=Yii::t('backend','user')?>：<?=Html::encode($model->real_name)?>
<div class="eln-user-role-view">

    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('common','role'),
            'content' => $this->render('_viewform', [
                'model' => $model,
                'selected_keys' => $selected_keys,
            ]),
            'options' => ['id' => 'role-tab-view'],
        ],
    ];

    //company
    $companyItem = [
        'label' => Yii::t('common','company'),
        'content' => '',
        'options' => ['id' => 'company-tab-view'],
    ];

    array_push($tabItems,$companyItem);

    $companyUrl = Url::toRoute(['user-role/company-view','userId'=>$userId]);

    //domain
    $domianItem = [
        'label' => Yii::t('common','domain'),
        'content' => '',
        'options' => ['id' => 'domain-tab-view'],
    ];

    array_push($tabItems,$domianItem);

    $domainUrl = Url::toRoute(['user-role/domain-view','userId'=>$userId]);


    //orgnization
    $orgnizationItem = [
        'label' => Yii::t('common','orgnization'),
        'content' => '',
        'options' => ['id' => 'orgnization-tab-view'],
    ];

    array_push($tabItems,$orgnizationItem);

    $orgnizationUrl = Url::toRoute(['user-role/orgnization-view','userId'=>$userId]);

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
        TabClear('company-tab-view');
        //            alert($('#other-tab-create').html());
        <?php if (isset($companyUrl) && $companyUrl != null):?>
        var companyAjaxUrl = "<?=$companyUrl?>";
        //        alert(ajaxUrl);
        TabLoad('company-tab-view', companyAjaxUrl);
        <?php endif?>

        TabClear('domain-tab-view');
        //            alert($('#other-tab-create').html());
        <?php if (isset($domainUrl) && $domainUrl != null):?>
        var domainAjaxUrl = "<?=$domainUrl?>";
        //        alert(ajaxUrl);
        TabLoad('domain-tab-view', domainAjaxUrl);
        <?php endif?>


        TabClear('orgnization-tab-view');
        //            alert($('#other-tab-create').html());
        <?php if (isset($orgnizationUrl) && $orgnizationUrl != null):?>
        var orgnizationAjaxUrl = "<?=$orgnizationUrl?>";
        //        alert(ajaxUrl);
        TabLoad('orgnization-tab-view', orgnizationAjaxUrl);
        <?php endif?>
    </script>
</div>
