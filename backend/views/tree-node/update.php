<?php

use yii\helpers\Url;
use yii\bootstrap\Tabs;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\treemanager\FwTreeNode */

?>
<div class="modal-div">

    <?php
//    $url = null;
    $tabItems = [
        [
            'label' => Yii::t('backend','tab_basic_info'),
            'content' => $this->render('_form', [
                'model' => $model,
                'formType' => $formType,
                'isSpecialUser' => $isSpecialUser
            ]),
            'options' => ['id' => 'base-tab-update'],
        ],
    ];

    if ($model->fwTreeType->tree_type_code == 'company' ||
        $model->fwTreeType->tree_type_code == 'permission' ||
        $model->fwTreeType->tree_type_code == 'domain' ||
        $model->fwTreeType->tree_type_code == 'orgnization' ||
        $model->fwTreeType->tree_type_code == 'course-category' ||
        $model->fwTreeType->tree_type_code == 'courseware-category')
    {
        if ($model->fwTreeType->tree_type_code == 'company')
            $url = Url::toRoute(['company/update', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'permission')
            $url = Url::toRoute(['permission/update', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'domain')
            $url = Url::toRoute(['domain/update', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'orgnization')
            $url = Url::toRoute(['orgnization/update', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'course-category')
            $url = Url::toRoute(['course-category/update','id'=>$model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'courseware-category')
            $url = Url::toRoute(['courseware-category/update','id'=>$model->kid]);

        $otherItem = [
            'label' => Yii::t('backend','tab_other_info'),
            'content' => '',
//            'url' => $url,
            'options' => ['id' => 'other-tab-update'],
        ];

        array_push($tabItems,$otherItem);
    }

    ?>

    <script>
        TabClear('other-tab-update');
//        alert($('#other-tab-update').html());
        <?php if (isset($url) && $url != null):?>
            var ajaxUrl = "<?=$url?>";
//            alert(ajaxUrl);
            TabLoad('other-tab-update', ajaxUrl);
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
