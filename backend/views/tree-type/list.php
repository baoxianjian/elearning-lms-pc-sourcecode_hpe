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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['tree-type/index']);?>"/>
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
        var ajaxUrl = "<?=Url::toRoute(['tree-type/list'])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['tree-type/export'])?>";

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
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['tree-type/create'])?>');
        }

    }

</script>

<?php  echo $this->render('_search', ['model' => $searchModel]); ?>
<!-- /.panel-heading -->
<div class="treetype-body">
    <?
    $gridColumns = [
        [
            'name' => 'selectedIds',
            'class' => 'kartik\grid\CheckboxColumn',
            'checkboxOptions' => function($model, $key, $index, $column) {
                return [
                    'value' => $model->kid,
                    'disabled' => ($model->limitation == 'R' || $model->limitation == 'U')
                ];
            }
        ],
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('backend','serial_number'),
        ],
        'tree_type_code',
        'tree_type_name',
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute'=>'code_gen_way',
            'format'=>'text',
            'value'=> function ($model, $key, $index, $cloumn){
                return $model->code_gen_way=='0' ? Yii::t('backend', 'code_gen_way_system') :
                    Yii::t('backend', 'code_gen_way_manual');
            },
        ],
        'code_prefix',
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute'=>'limitation',
            'format'=>'text',
            'value'=> function ($model, $key, $index, $cloumn){

                if ($model->limitation=='N')
                    return Yii::t('backend', 'limitation_none');
                else if ($model->limitation=='R')
                    return Yii::t('backend', 'limitation_readonly');
                else if ($model->limitation=='U')
                    return Yii::t('backend', 'limitation_onlyname');
                else
                    return Yii::t('backend', 'limitation_hidden');
            },
        ],
        'max_level',
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => Yii::t('common', 'operation_button'),
            'template' => '{viewpop}{updatepop}{deleteButton}',
            'width' => '100px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('backend', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['tree-type/view','id'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    if ($model->limitation != 'R' ) {
                        return
                            Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                                ['id' => 'EditButton', 'title' => Yii::t('backend', 'edit_button'),
                //                                'data-toggle'=>'modal',
                //                                'data-target'=>'#updateModal',
                                    'onclick' => 'loadModalFormData("updateModal","' . Yii::$app->urlManager->createUrl(['tree-type/update', 'id' => $key]) . '");'
                                ]);
                    }
                },
                'deleteButton' => function ($url, $model, $key) {
                    if ($model->limitation != 'R' && $model->limitation != 'U') {
                        return
                            Html::a('<span class="glyphicon glyphicon-trash"></span>', '#',
                                ['id' => 'DeleteButton', 'title' => Yii::t('backend', 'delete_button'),
                                    'onclick' => 'deleteButton("' . $key . '","' . Yii::$app->urlManager->createUrl(['tree-type/delete', 'id' => $key]) . '");'
                                ]);
                    }
                },
            ],
//                'headerOptions' => ['width' => '80'],
        ],
    ];
    ?>

    <?php
    $contentName = Yii::t('common', 'tree_type');

    if ($forceShowAll == 'True') {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-small"></i> ' . Yii::t('backend', 'resize_current_button'), [
            'title' => Yii::t('backend', 'resize_current_button'), 'class' => 'btn btn-default resizeBtn',
            'onclick' => 'ResizeCurrentButton();'
        ]);
    }
    else
    {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-full"></i> ' . Yii::t('backend', 'resize_full_button'), [
            'title' => Yii::t('backend', 'resize_full_button'), 'class' => 'btn btn-default resizeBtn',
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
            'heading' => '<h3 class="panel-title" style="text-align: left;"><i class="glyphicon glyphicon-book"></i> ' .Yii::t('backend', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        'toolbar' => [
            ['content'=>
                Html::button('<i class="glyphicon glyphicon-plus"></i> '.Yii::t('backend', 'add_button'),[
                    'title'=>Yii::t('backend', 'add_button'), 'class'=>'btn btn-default greenBtn',
//                    'data-toggle'=>'modal',
//                    'data-target'=>'#addModal',
                    'onclick'=>'loadModalFormData("addModal","'. Yii::$app->urlManager->createUrl(['tree-type/create']) .'");'
//                    'onclick'=>'updateFormData("'. Yii::$app->urlManager->createUrl(['tree-type/update','id'=>43]).'");'
                ])
                .' '.
                Html::button('<i class="glyphicon glyphicon-minus"></i> '.Yii::t('backend', 'batch_delete_button'),[
                    'title'=>Yii::t('backend', 'batch_delete_button'), 'class'=>'btn btn-default redBtn',
                    'onclick'=>'batchDeleteButton("'. Yii::$app->urlManager->createUrl('tree-type/batch-delete').'");'
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