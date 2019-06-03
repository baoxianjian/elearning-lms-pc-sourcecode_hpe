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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['dictionary-category/index']);?>"/>
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

        var ajaxUrl = "<?=Url::toRoute(['dictionary-category/list'])?>";

        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

//        alert('ajaxUrl:'+ajaxUrl);
        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['dictionary-category/export'])?>";

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
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['dictionary-category/create'])?>');
        }

    }

</script>

<?php  echo $this->render('_search', ['model' => $searchModel]); ?>
<!-- /.panel-heading -->
<div class="dictionary-category-body">
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
            'header' => Yii::t('common','serial_number'),
        ],
        'cate_code',
        'cate_name',
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute'=>'cate_type',
            'format'=>'text',
            'value'=> function ($model, $key, $index, $cloumn){

                if ($model->cate_type=='0')
                    return Yii::t('common', 'dictionary_cate_type_system');
                else if ($model->cate_type=='1')
                    return Yii::t('common', 'dictionary_cate_type_company');
            },
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute'=>'limitation',
            'format'=>'text',
            'value'=> function ($model, $key, $index, $cloumn){

                if ($model->limitation=='N')
                    return Yii::t('common', 'limitation_none');
                else if ($model->limitation=='R')
                    return Yii::t('common', 'limitation_readonly');
                else if ($model->limitation=='U')
                    return Yii::t('common', 'limitation_onlyname');
                else
                    return Yii::t('common', 'limitation_hidden');
            },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => Yii::t('common', 'operation_button'),
            'template' => '{viewpop}{updatepop}{deleteButton}',
            'width' => '100px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['dictionary-category/view','id'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    if ($model->limitation != 'R' ) {
                        return
                            Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                                ['id' => 'EditButton', 'title' => Yii::t('common', 'edit_button'),
                //                                'data-toggle'=>'modal',
                //                                'data-target'=>'#updateModal',
                                    'onclick' => 'loadModalFormData("updateModal","' . Yii::$app->urlManager->createUrl(['dictionary-category/update', 'id' => $key]) . '");'
                                ]);
                    }
                },
                'deleteButton' => function ($url, $model, $key) {
                    if ($model->limitation != 'R' && $model->limitation != 'U') {
                        return
                            Html::a('<span class="glyphicon glyphicon-trash"></span>', '#',
                                ['id' => 'DeleteButton', 'title' => Yii::t('common', 'delete_button'),
                                    'onclick' => 'deleteButton("' . $key . '","' . Yii::$app->urlManager->createUrl(['dictionary-category/delete', 'id' => $key]) . '");'
                                ]);
                    }
                },
            ],
//                'headerOptions' => ['width' => '80'],
        ],
    ];
    ?>

    <?php
    $contentName = Yii::t('common', 'dictionary_category');

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
                    'onclick'=>'loadModalFormData("addModal","'. Yii::$app->urlManager->createUrl(['dictionary-category/create']) .'");'
//                    'onclick'=>'updateFormData("'. Yii::$app->urlManager->createUrl(['dictionary-category/update','id'=>43]).'");'
                ])
                .' '.
                Html::button('<i class="glyphicon glyphicon-minus"></i> '.Yii::t('common', 'batch_delete_button'),[
                    'title'=>Yii::t('common', 'batch_delete_button'), 'class'=>'btn btn-default redBtn',
                    'onclick'=>'batchDeleteButton("'. Yii::$app->urlManager->createUrl('dictionary-category/batch-delete').'");'
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

<!--    --><?//= Html::hiddenInput("PageShowAll",$forceShowAll,['id'=>'PageShowAll'])?>
<!--    --><?//= Html::hiddenInput("PageSize",$pageSize,['id'=>'PageSize'])?>
</div>