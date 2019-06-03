<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/23/15
 * Time: 1:16 AM
 */
use components\widgets\TGridView;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\framework\FwPointRule;

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['point/index']);?>"/>
<script>

    var indexUrl = document.getElementById('indexUrl');

    if(!document.getElementById("content-body"))
    {
        window.location = indexUrl.value;
    }

    function reloadForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['point/list','TreeNodeKid'=>$treeNodeKid,'TreeType'=>'company'])?>";
        //var ajaxUrl = "<?=Url::toRoute(['point/list'])?>";
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['point/export','TreeNodeKid'=>$treeNodeKid,'TreeType'=>'company'])?>";

        exportWithForm('searchForm', ajaxUrl);
    }
    
    function loadModalFormData(modalId,url)
    {
        modalClear("addModal");
        modalClear("updateModal");
        modalClear("viewModal");

        openModalForm(modalId, url);
    }

    function ReloadPageAfterDelete()
    {
        reloadForm();
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
        reloadForm();
        if (isClose) {

            modalClear(modalId);
            modalHidden(modalId);
        }
        else
        {
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['component/create'])?>');
        }
    }
    function ReloadPageAfterChangeStatus()
    {
//            alert('ReloadPageAfterChangeStatus');
        reloadForm();
//        reloadtree();
    }

</script>

<!--搜索-->
<?php  echo $this->render('_search', ['model' => $searchModel]); ?>


<div class="treetype-body">
    <?

//action

    $gridColumns = [
        'point_code',
        'point_name',
        [
            'header' => Yii::t('common','cycle_range'),
            'attribute' => 'cycle_range',
            'value' => function($model, $key, $index, $column){
                return $model->getCycleRanges($model->cycle_range);
            }
        ],
        /*
        [
            'header' => Yii::t('common','point_op'),
            'attribute' => 'point_op',
            'width' => '20px',
        ],
        */
        [
            'header' => Yii::t('common','standard_value'),
            'attribute' => 'standard_value',
            'value' => function($model, $key, $index, $column){
                return $model->point_op. $model->standard_value;
            }
        ],
        /*
        [
            'header' => Yii::t('common','is_in_using'),
            'attribute' => 'status',
            'value' => function($model, $key, $index, $column){
                return $model->getStatuses($model->status);
            }
        ],
        */
        [
            'header' => Yii::t('common','is_in_using'),
            'class' => 'kartik\grid\DataColumn',
            'attribute'=>'status',
            'format'=>'text',
            'value'=> function ($model, $key, $index, $cloumn){
                if ($model->status=='0')
                    return Yii::t('common', 'status_temp');
                else if ($model->status=='1')
                    return Yii::t('common', 'status_normal');
                else if ($model->status=='2')
                    return Yii::t('common', 'status_stop');
            },
            'contentOptions' => function ($model, $key, $index, $cloumn){
                if ($model->status=='2')
                    return ['style' => 'color:red'];
                else
                    return [];
            },
        ],

        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => Yii::t('common', 'operation_button'),
            'template' => '{statusButton}{updatepop}',
            'width' => '120px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['point/view','id'=>$key]).'");'
                            ]);
                },
                'statusButton' => function ($url, $model, $key) {

                    if ($model->status == '1') {
                        $status = "2";
                        $title = Yii::t('common', 'change_status_stop');
                        $class = 'glyphicon glyphicon-pause';
                    }
                    else
                    {
                        $status = "1";
                        $title = Yii::t('common', 'change_status_start');
                        $class = 'glyphicon glyphicon-play';
                    }

                    return
                        Html::a('<span class="'.$class.'"></span>', '#',
                            ['id'=>'StatusButton', 'title'=> $title,
                                'onclick' => 'statusButton("' . $status . '","' . Yii::$app->urlManager->createUrl(['point/status', 'id' => $key, 'status' => $status]) . '");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                            ['id' => 'EditButton', 'title' => Yii::t('common', 'edit_button'),
                                'onclick' => 'loadModalFormData("updateModal","' . Yii::$app->urlManager->createUrl(['point/update', 'id' => $key]) . '");'
                            ]);
                },

            ],
        ],
    ];
    ?>

    <?php

    $contentName = Yii::t('common', 'point');

    if ($forceShowAll == 'True') {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-small"></i> ' . Yii::t('common', 'resize_current_button'), [
            'title' => Yii::t('common', 'resize_current_button'), 'class' => 'btn btn-default resizeBtn',
            'onclick' => 'ResizeCurrentButton();'
        ]);
    } else {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-full"></i> ' . Yii::t('common', 'resize_full_button'), [
            'title' => Yii::t('common', 'resize_full_button'), 'class' => 'btn btn-default resizeBtn',
            'onclick' => 'ResizeFullButton();'
        ]);
    }

    echo TGridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'panel' => [
            'type' => TGridView::TYPE_DEFAULT,
            'heading' => '<h3 class="panel-title" style="text-align: left;"><a href="#" id="jsTreeClose" class="glyphicon glyphicon-menu-left " onclick="TreeHide();"></a> <i class="glyphicon glyphicon-book"></i> '.Yii::t('common', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        /* */
        'toolbar' =>
        [
            ['content'=>
                $pageButton
                .' '.
                 Html::button('<i class="glyphicon glyphicon-export"></i> '.Yii::t('backend', 'export_button'),[
                     'title'=>Yii::t('backend', 'export_button'), 'class'=>'btn btn-default blueBtn',
                     'onclick'=>'exportForm();'
                 ])
            ],
//            '{export}',
        ],
        
        'pjax'=>true,
        'pjaxSettings'=>[
            'neverTimeout'=>true,
        ]
    ]);
    ?>
</div>