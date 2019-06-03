<?php
use common\helpers\TStringHelper;
use components\widgets\TGridView;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['service/index']);?>"/>
<script>

    var indexUrl = document.getElementById('indexUrl');

    //    alert(document.getElementById("content-body"));
    if(!document.getElementById("content-body"))
    {
        window.location = indexUrl.value;
    }

    function reloadForm()
    {
//            alert("reloadForm");
//        $.pjax.reload({container:"#grid"});
//            $.pjax.reload({container:"#gridframe"});
        var ajaxUrl = "<?=Url::toRoute(['service/index-list'])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());
        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['service/index-export'])?>";

        exportWithForm('searchForm', ajaxUrl);
    }

    function ReloadPageAfterChangeStatus()
    {
//            alert('ReloadPageAfterChangeStatus');
        reloadForm();
//        reloadtree();
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
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['service/create'])?>');
        }

    }

</script>
<?php  echo $this->render('_index-search', ['model' => $searchModel,'serviceList'=>$result]); ?>
<!-- /.panel-heading -->
<div class="treetype-body">
    <?
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('common','serial_number'),
        ],
        'service_code',
        'service_name',
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute' => 'is_log',
            'value' => function ($model, $key, $index, $cloumn) {
                return $model->getIsLogText();
            },
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute' => 'service_status',
            'value' => function ($model, $key, $index, $cloumn) {
                return $model->getServiceStatusText();
            },
            'contentOptions' => function ($model, $key, $index, $cloumn){
                if ($model->service_status =='0')
                    return ['style' => 'color:red'];
                else
                    return [];
            },
        ],
        'run_at',
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => Yii::t('common','operation_button'),
            'template' => '{viewpop}{updatepop}{moveButton}{statusButton}{deleteButton}',
            'width' => '120px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['service/index-view','id'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                            ['id'=>'EditButton', 'title'=>Yii::t('common', 'edit_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#updateModal',
                                'onclick'=>'loadModalFormData("updateModal","'. Yii::$app->urlManager->createUrl(['service/update','id'=>$key]).'");'
                            ]);
                },
                'deleteButton' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-trash"></span>', '#',
                            ['id'=>'DeleteButton', 'title'=> Yii::t('common', 'delete_button'),
                                'onclick'=>'deleteButton("'.$key.'","'. Yii::$app->urlManager->createUrl(['service/delete','id'=>$key]).'");'
                            ]);
                },
            ]
        ]
    ];
    ?>

    <?
    $contentName = Yii::t('common', 'service');


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
            'heading' => '<h3 class="panel-title" style="text-align: left;"><a href="#" id="jsTreeClose" class="glyphicon glyphicon-menu-left " onclick="TreeHide();"></a> <i class="glyphicon glyphicon-book"></i> '.Yii::t('common', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        'toolbar' => [
            [
                'content'=>
                    Html::button('<i class="glyphicon glyphicon-plus"></i> '.Yii::t('common', 'add_button'),[
                        'title'=>Yii::t('common', 'add_button'), 'class'=>'btn btn-default greenBtn',
                        'onclick'=>'loadModalFormData("addModal","'. Yii::$app->urlManager->createUrl(['service/create']) .'");'
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
