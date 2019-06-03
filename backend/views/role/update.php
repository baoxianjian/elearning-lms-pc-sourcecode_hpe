<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwRole */

?>
<div class="eln-role-update">

    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('common','tab_basic_info'),
            'content' => $this->render('_form', [
                'model' => $model,
                'formType'=>'update',
            ]),
            'options' => ['id' => 'base-tab-create'],
        ],
    ];

    $positionItem = [
        'label' => Yii::t('common','tab_permission_info'),
        'content' => '',
        'options' => ['id' => 'permission-tab-update'],
    ];

    array_push($tabItems,$positionItem);

    $url = Url::toRoute(['role-permission/update','roleId'=>$model->kid]);

    ?>

    <script>
        TabClear('permission-tab-update');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('permission-tab-update', ajaxUrl);
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
