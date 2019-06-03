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

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['action-log-filter/index']);?>"/>
<script>

    var indexUrl = document.getElementById('indexUrl');

    if(!document.getElementById("content-body"))
    {
        window.location = indexUrl.value;
    }

    function reloadForm()
    {
//            alert("reloadForm");
//        $.pjax.reload({container:"#grid"});
//            $.pjax.reload({container:"#gridframe"});
        var ajaxUrl = "<?=Url::toRoute(['action-log-filter/list'])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['action-log-filter/export'])?>";

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
        //alert('1');
        reloadForm();
    }

    function ReloadPageAfterChangeStatus()
    {
        reloadForm();
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
//            alert("frameId:"+frameId);
//            alert("formId:"+formId);
//            alert("modalId:"+modalId);
//        alert("isClose:"+isClose);
        reloadForm();
        if (isClose) {

            modalClear(modalId);
            modalHidden(modalId);
//                $('#'+modalId).modal('hide');
        }
        else
        {
//                modalClear(modalId);
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['action-log-filter/create'])?>');
        }

    }

</script>

<?php  echo $this->render('_search', ['model' => $searchModel]); ?>
<!-- /.panel-heading -->
<div class="action-log-filter-body">
    <?
    $gridColumns = [
        [
            'name' => 'selectedIds',
            'class' => 'kartik\grid\CheckboxColumn',
            'checkboxOptions' => function($model, $key, $index, $column) {
                return [
                    'value' => $model->kid,
                ];
            }
        ],
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('common','serial_number'),
        ],
        'filter_code',
        'filter_name',
        'controller_id',
        'action_id',
        'system_flag',
        [
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
            'template' => '{viewpop}{updatepop}{statusButton}{deleteButton}',
            'width' => '120px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['action-log-filter/view','id'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                            ['id' => 'EditButton', 'title' => Yii::t('common', 'edit_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#updateModal',
                                'onclick' => 'loadModalFormData("updateModal","' . Yii::$app->urlManager->createUrl(['action-log-filter/update', 'id' => $key]) . '");'
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
                                'onclick' => 'statusButton("' . $status . '","' . Yii::$app->urlManager->createUrl(['action-log-filter/status', 'id' => $key, 'status' => $status]) . '");'
                            ]);
                },
                'deleteButton' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-trash"></span>', '#',
                            ['id' => 'DeleteButton', 'title' => Yii::t('common', 'delete_button'),
                                'onclick' => 'deleteButton("' . $key . '","' . Yii::$app->urlManager->createUrl(['action-log-filter/delete', 'id' => $key]) . '");'
                            ]);
                },
            ],
//                'headerOptions' => ['width' => '80'],
        ],
    ];
    ?>

    <?php
    $contentName = Yii::t('common', 'action_log_filter');

    if ($forceShowAll == 'True') {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-small"></i> ' . Yii::t('common', 'resize_current_button'), [
            'title' => Yii::t('common', 'resize_current_button'), 'class' => 'btn btn-default resizeBtn',
            'onclick' => 'ResizeCurrentButton();'
        ]);
    }
    else
    {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-full"></i> ' . Yii::t('common', 'resize_full_button'), [
            'title' => Yii::t('common', 'resize_full_button'), 'class' => 'btn btn-default resizeBtn',
            'onclick' => 'ResizeFullButton();'
        ]);
    }

    echo TGridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        //  'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'panel' => [
            'type' => TGridView::TYPE_DEFAULT,
            'heading' => '<h3 class="panel-title" style="text-align: left;"><i class="glyphicon glyphicon-book"></i> ' .Yii::t('common', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        'toolbar' => [
            ['content'=>
                Html::button('<i class="glyphicon glyphicon-plus"></i> '.Yii::t('common', 'add_button'),[
                    'title'=>Yii::t('common', 'add_button'), 'class'=>'btn btn-default greenBtn',
//                    'data-toggle'=>'modal',
//                    'data-target'=>'#addModal',
                    'onclick'=>'loadModalFormData("addModal","'. Yii::$app->urlManager->createUrl(['action-log-filter/create']) .'");'
//                    'onclick'=>'updateFormData("'. Yii::$app->urlManager->createUrl(['action-log-filter/update','id'=>43]).'");'
                ])
                .' '.
                Html::button('<i class="glyphicon glyphicon-minus"></i> '.Yii::t('common', 'batch_delete_button'),[
                    'title'=>Yii::t('common', 'batch_delete_button'), 'class'=>'btn btn-default redBtn',
                    'onclick'=>'batchDeleteButton("'. Yii::$app->urlManager->createUrl('action-log-filter/batch-delete').'");'
                ])
                .' '.
                $pageButton
                .' '.
                Html::button('<i class="glyphicon glyphicon-export"></i> '.Yii::t('backend', 'export_button'),[
                    'title'=>Yii::t('backend', 'export_button'), 'class'=>'btn btn-default blueBtn',
                    'onclick'=>'exportForm();'
                ])
            ],
//            '{export}',
//            '{toggleData}'
        ],
        'pjax'=>true,
        'pjaxSettings'=>[
            'neverTimeout'=>true,
        ]
    ]);
    ?>
</div>