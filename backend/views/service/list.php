<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/20
 * Time: 17:08
 */
use common\helpers\TStringHelper;
use components\widgets\TGridView;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['service/log']);?>"/>
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
        var ajaxUrl = "<?=Url::toRoute(['service/list'])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());
        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['service/export'])?>";

        exportWithForm('searchForm', ajaxUrl);
    }

    function loadModalFormData(modalId,url)
    {
//        var selectNodeId = $('#selectNodeId').val();
////        alert(selectNodeId);
//        if (modalId == 'addModal' && selectNodeId == '')
//        {
//            alert('<?//=Yii::t('common','cannot_add_in_root');?>//');
//        }
//        else {
        modalClear("viewModal");

        openModalForm(modalId, url);

//        }
    }

</script>
<?php  echo $this->render('_search', ['model' => $searchModel,'serviceList'=>$serviceList]); ?>
<!-- /.panel-heading -->
<div class="treetype-body">
    <?
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('common','serial_number'),
        ],
        'fwService.service_name',
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute' => 'action_status',
            'value' => function ($model, $key, $index, $cloumn) {
                return $model->getActionStatusText();
            },
            'contentOptions' => function ($model, $key, $index, $cloumn){
                if ($model->action_status=='1')
                    return ['style' => 'color:red'];
                else
                    return [];
            },
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute'=>'service_log',
            'format'=>'text',
            'value'=> function ($model, $key, $index, $cloumn){
                return TStringHelper::subStr(htmlspecialchars_decode($model->service_log) ,60,'UTF-8',0,'...');
            },
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'label' => Yii::t('common', 'action_time'),
            'format'=>'datetime',
            'value'=>'created_at',
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => Yii::t('common','operation_button'),
            'template' => '{viewpop}{updatepop}{moveButton}{statusButton}{deleteButton}',
            'width' => '80px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['service/view','id'=>$key]).'");'
                            ]);
                },
            ]
        ]
    ];
    ?>

    <?
    $contentName = Yii::t('common', 'service_log');

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
            'heading' => '<h3 class="panel-title" style="text-align: left;"><i class="glyphicon glyphicon-book"></i> '.Yii::t('common', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        'toolbar' => [
            [
                'content'=>
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