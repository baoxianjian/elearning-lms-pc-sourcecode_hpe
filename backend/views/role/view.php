<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwRole */

?>
<div class="eln-role-view">

    <?php
    //        $url = null;
    $tabItems = [
        [
            'label' => Yii::t('common','tab_basic_info'),
            'content' => DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'role_code',
                    'role_name',
                    [
                        'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]),
                        'value'=> $model->getCompanyName()
                    ],
                    [
                        'label' => Yii::t('common', 'share_flag'),
                        'value' => $model->getShareFlagText(),
                    ],
                    [
                        'label' => Yii::t('common', 'limitation'),
                        'value' => $model->getLimitationText(),
                    ],
                    [
                        'label' => Yii::t('common', 'status'),
                        'value' => $model->getStatusText(),
                    ],
                    'description:text',
                ]
            ]),
            'options' => ['id' => 'base-tab-create'],
        ],
    ];

    $positionItem = [
        'label' => Yii::t('common','tab_permission_info'),
        'content' => '',
        'options' => ['id' => 'permission-tab-view'],
    ];

    array_push($tabItems,$positionItem);

    $url = Url::toRoute(['role-permission/view','roleId'=>$model->kid]);

    ?>

    <script>
        TabClear('permission-tab-view');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('permission-tab-view', ajaxUrl);
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
