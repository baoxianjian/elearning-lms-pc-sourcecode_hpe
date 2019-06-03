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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['component/index']);?>"/>
<script>

    var indexUrl = document.getElementById('indexUrl');

    if(!document.getElementById("content-body"))
    {
        window.location = indexUrl.value;
    }

    function reloadForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['component/list'])?>";
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['component/export'])?>";

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

</script>

<?php  echo $this->render('_search', ['model' => $searchModel]); ?>
<div class="treetype-body">
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
            'header' => Yii::t('backend','serial_number'),
        ],
        [
            'header' => Yii::t('backend','component_type'),
            'attribute' => 'component_type',
            'value' => function ($model, $key, $index, $column) {
                return $model['component_type'] == '0' ? Yii::t('backend','resource') : Yii::t('backend','active');
            }
        ],
//        [
//            'header' => Yii::t('common','icon'),
//            'attribute' => 'icon',
//            'width' => '60px',
//            'format' => 'html',
//        ],
        [
            'header' => Yii::t('backend','title'),
            'attribute' => 'title',
        ],
        [
            'header' => Yii::t('backend','component_code'),
            'attribute' => 'component_code',
        ],
        'file_type',
        [
            'header' => Yii::t('backend','component_category'),
            'attribute' => 'component_category',
            'value' => function($model, $key, $index, $column){
                switch ($model['component_category']){
                    case '0':
                        return Yii::t('backend', 'component_course');
                    break;
                    case '1':
                        return Yii::t('backend', 'component_media');
                    break;
                    case '2':
                        return Yii::t('backend', 'component_activity');
                    break;
                }
            }
        ],
        [
            'header' => Yii::t('backend','is_display_pc'),
            'attribute' => 'is_display_pc',
            'value' => function($model, $key, $index, $column){
                switch ($model['is_display_pc']){
                    case '0':
                        return Yii::t('backend', 'no');
                        break;
                    case '1':
                        return Yii::t('backend', 'yes');
                        break;
                }
            }
        ],
        [
            'header' => Yii::t('backend','is_display_mobile'),
            'attribute' => 'is_display_mobile',
            'value' => function($model, $key, $index, $column){
                switch ($model['is_display_mobile']){
                    case '0':
                        return Yii::t('backend', 'no');
                        break;
                    case '1':
                        return Yii::t('backend', 'yes');
                        break;
                }
            }
        ],
        [
            'header' => Yii::t('backend','is_allow_download'),
            'attribute' => 'is_allow_download',
            'value' => function($model, $key, $index, $column){
                switch ($model['is_allow_download']){
                    case '0':
                        return Yii::t('backend', 'no');
                        break;
                    case '1':
                        return Yii::t('backend', 'yes');
                        break;
                }
            }
        ],
        'default_time',
        'default_credit',
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
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['component/view','id'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                        return
                            Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                                ['id' => 'EditButton', 'title' => Yii::t('backend', 'edit_button'),
                                    'onclick' => 'loadModalFormData("updateModal","' . Yii::$app->urlManager->createUrl(['component/update', 'id' => $key]) . '");'
                                ]);
                },
                'deleteButton' => function ($url, $model, $key) {
                        return
                            Html::a('<span class="glyphicon glyphicon-trash"></span>', '#',
                                ['id' => 'DeleteButton', 'title' => Yii::t('backend', 'delete_button'),
                                    'onclick' => 'deleteButton("' . $key . '","' . Yii::$app->urlManager->createUrl(['component/delete', 'id' => $key]) . '");'
                                ]);
                },
            ],
        ],
    ];
    ?>

    <?php
    $contentName = Yii::t('common', 'component');

    if ($forceShowAll == 'True') {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-small"></i> ' . Yii::t('backend', 'resize_current_button'), [
            'title' => Yii::t('backend', 'resize_current_button'), 'class' => 'btn btn-default resizeBtn',
            'onclick' => 'ResizeCurrentButton();'
        ]);
    } else {
        $pageButton = Html::button('<i class="glyphicon glyphicon-resize-full"></i> ' . Yii::t('backend', 'resize_full_button'), [
            'title' => Yii::t('backend', 'resize_full_button'), 'class' => 'btn btn-default resizeBtn',
            'onclick' => 'ResizeFullButton();'
        ]);
    }

    echo TGridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'panel' => [
            'type' => TGridView::TYPE_DEFAULT,
            'heading' => '<h3 class="panel-title" style="text-align: left;"><i class="glyphicon glyphicon-book"></i> ' .Yii::t('backend', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        'toolbar' => [
            ['content'=>
                Html::button('<i class="glyphicon glyphicon-plus"></i> '.Yii::t('backend', 'add_button'),[
                    'title'=>Yii::t('backend', 'add_button'), 'class'=>'btn btn-default greenBtn',
                    'onclick'=>'loadModalFormData("addModal","'. Yii::$app->urlManager->createUrl(['component/create']) .'");'
                ])
                .' '.
                Html::button('<i class="glyphicon glyphicon-minus"></i> '.Yii::t('backend', 'batch_delete_button'),[
                    'title'=>Yii::t('backend', 'batch_delete_button'), 'class'=>'btn btn-default redBtn',
                    'onclick'=>'batchDeleteButton("'. Yii::$app->urlManager->createUrl('component/batch-delete').'");'
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
        ],
        'pjax'=>true,
        'pjaxSettings'=>[
            'neverTimeout'=>true,
        ]
    ]);
    ?>
</div>