<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\treemanager\FwTreeNode */

?>
<div class="modal-div">

    <?php

    if (!$isSpecialUser) {
        $displayColumns =
            [
                'tree_node_code',
                'tree_node_name',
//                    'node_id_path',
//                    'node_code_path',
            'node_name_path',
            [
                'label' => Yii::t('backend', 'parent_node_name'),
                'value' => $model->getParentNodeText(),
            ],
            [
                'label' => Yii::t('backend', 'root_node_name'),
                'value' => $model->getRootNodeText(),
            ],
            'tree_level',
            [
                'label' => Yii::t('backend', 'status'),
                'value' => $model->getStatusText(),
            ],
            'display_number',
//            'sequence_number',
        ];
    }
    else
    {
        $displayColumns =
            [
                'tree_node_code',
                'tree_node_name',
//                    'node_id_path',
//                    'node_code_path',
            'node_name_path',
            [
                'label' => Yii::t('backend', 'parent_node_name'),
                'value' => $model->getParentNodeText(),
            ],
            [
                'label' => Yii::t('backend', 'root_node_name'),
                'value' => $model->getRootNodeText(),
            ],
            'tree_level',
            [
                'label' => Yii::t('backend', 'status'),
                'value' => $model->getStatusText(),
            ],
            'display_number',
            'sequence_number',
        ];
    }
    $tabItems = [
        [
            'label' => Yii::t('backend','tab_basic_info'),
            'content' => DetailView::widget([
                'model' => $model,
                'attributes' => $displayColumns,
            ]),
            'options' => ['id' => 'base-tab-view'],
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
            $url = Url::toRoute(['company/view', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'permission')
            $url = Url::toRoute(['permission/view', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'domain')
            $url = Url::toRoute(['domain/view', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'orgnization')
            $url = Url::toRoute(['orgnization/view', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'course-category')
            $url = Url::toRoute(['course-category/view', 'id' => $model->kid]);
        else if ($model->fwTreeType->tree_type_code == 'courseware-category')
            $url = Url::toRoute(['courseware-category/view', 'id' => $model->kid]);

        $otherItem = [
            'label' => Yii::t('backend','tab_other_info'),
            'content' => '',
//            'url' => $url,
            'options' => ['id' => 'other-tab-view'],
        ];

        array_push($tabItems,$otherItem);
    }

    ?>

    <script>
        TabClear('other-tab-view');
//        alert($('#other-tab-view').html());
        <?php if (isset($url) && $url != null):?>
            var ajaxUrl = "<?=$url?>";
//            alert(ajaxUrl);
            TabLoad('other-tab-view', ajaxUrl);
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
