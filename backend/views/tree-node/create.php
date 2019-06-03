<?php

use yii\helpers\Html;
use yii\bootstrap\Tabs;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model common\models\treemanager\FwTreeNode */

?>
<div class="modal-div">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->

    <?php
//        $url = null;
        $tabItems = [
            [
                'label' => Yii::t('backend','tab_basic_info'),
                'content' => $this->render('_form', [
                    'model' => $model,
//                    'limitation' => $limitation,
//                    'codeGenWay' => $codeGenWay,
                    'formType' => $formType,
                    'isSpecialUser' => $isSpecialUser
                ]),
                'options' => ['id' => 'base-tab-create'],
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
                $url = Url::toRoute(['company/create','parentNodeId'=>$parentNodeId]);
            else if ($model->fwTreeType->tree_type_code == 'permission')
                $url = Url::toRoute(['permission/create','parentNodeId'=>$parentNodeId]);
            else if ($model->fwTreeType->tree_type_code == 'domain')
                $url = Url::toRoute(['domain/create','parentNodeId'=>$parentNodeId]);
            else if ($model->fwTreeType->tree_type_code == 'orgnization')
                $url = Url::toRoute(['orgnization/create','parentNodeId'=>$parentNodeId]);
            else if ($model->fwTreeType->tree_type_code == 'course-category')
                $url = Url::toRoute(['course-category/create','parentNodeId'=>$parentNodeId]);
            else if ($model->fwTreeType->tree_type_code == 'courseware-category')
                $url = Url::toRoute(['courseware-category/create','parentNodeId'=>$parentNodeId]);

            $otherItem = [
                'label' => Yii::t('backend','tab_other_info'),
                'content' => '',
//                'url' => $url,
                'options' => ['id' => 'other-tab-create'],
            ];

            array_push($tabItems,$otherItem);
        }

    ?>
        <script>
            TabClear('other-tab-create');
//            alert($('#other-tab-create').html());
            <?php if (isset($url) && $url != null):?>
                var ajaxUrl = "<?=$url?>";
//                alert(ajaxUrl);
                TabLoad('other-tab-create', ajaxUrl);
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


