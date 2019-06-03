<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwRole */

?>
<div class="eln-role-create">


    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('common','tab_basic_info'),
            'content' => $this->render('_form', [
                'model' => $model,
                'formType'=>'create',
            ]),
            'options' => ['id' => 'base-tab-create'],
        ],
    ];

    $permissionItem = [
        'label' => Yii::t('common','tab_permission_info'),
        'content' => '',
        'options' => ['id' => 'permission-tab-create'],
    ];

    array_push($tabItems,$permissionItem);

    $url = Url::toRoute(['role-permission/create']);

    ?>

    <script>
        TabClear('permission-tab-create');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('permission-tab-create', ajaxUrl);
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
