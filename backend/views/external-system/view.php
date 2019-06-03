<?php

use yii\bootstrap\Tabs;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwExternalSystem */

?>
<div class="clientform-div">

    <?php

    $tabItems = [
        [
            'label' => Yii::t('common','tab_basic_info'),
            'content' =>  DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'system_code',
                    'system_name',
                    'system_key',
                    [
                        'label' => Yii::t('common', 'system_key_is_single'),
                        'value' => $model->getSystemKeyIsSingleText(),
                    ],
                    'encoding_key',
                    [                      // the owner name of the model
                        'label' => Yii::t('common', 'security_mode'),
                        'value' => $model->getSecurityModeText(),
                    ],
                    [
                        'label' => Yii::t('common', 'encrypt_mode'),
                        'value' => $model->getEncryptModeText(),
                    ],
                    'api_address',
                    [
                        'label' => Yii::t('common', 'service_mode'),
                        'value' => $model->getSecurityModeText(),
                    ],
                    [
                        'label' => Yii::t('common', 'status'),
                        'value' => $model->getStatusText(),
                    ],
                    'user_name',
                    'password',
                    'token_expire',
                    'duration',
                    'limit_count',
                    'memo1',
                    'memo2',
                    'memo3',
                ],
            ]),
            'options' => ['id' => 'base-tab-view'],
        ],
        [
            'label' => Yii::t('common','tab_company_info'),
//            'content' =>  '',
            'options' => ['id' => 'company-tab-view'],
        ],
    ];

    $url = Url::toRoute(['company-external-system/view','systemId'=>$model->kid]);

    ?>

    <script>
        TabClear('company-tab-view');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('company-tab-view', ajaxUrl);
        <?php endif?>
    </script>
    <?=
    Tabs::widget([
        'id' => 'tabs',
        'items' => $tabItems,
//        'options' => ['tag' => 'div'],
//        'itemOptions' => ['tag' => 'div'],
//        'headerOptions' => ['class' => 'my-class'],
    ]);
    ?>

</div>
