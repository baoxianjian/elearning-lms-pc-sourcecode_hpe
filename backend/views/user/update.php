<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */

?>
<div class="eln-user-update">

    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('backend','tab_basic_info'),
            'content' => $this->render('_form', [
                'model' => $model,
                'domainModel' => $domainModel,
                'genderModel' => $genderModel,
                'locationModel' => $locationModel,
                'themeModel'=>$themeModel,
                'languageModel'=>$languageModel,
                'timezoneModel'=>$timezoneModel,
                'employeeStatusModel'=>$employeeStatusModel,
                'workPlaceModel'=>$workPlaceModel,
                'positionMgrLevelModel'=>$positionMgrLevelModel,
                'formType'=>'update',
            ]),
            'options' => ['id' => 'base-tab-update'],
        ],
    ];

    $positionItem = [
        'label' => Yii::t('backend','tab_position_info'),
        'content' => '',
        'options' => ['id' => 'position-tab-update'],
    ];

    array_push($tabItems,$positionItem);

    $url = Url::toRoute(['user-position/update','userId'=>$model->kid]);

    ?>

    <script>
        TabClear('position-tab-update');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('position-tab-update', ajaxUrl);
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
