<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/30/15
 * Time: 9:56 PM
 */
use components\widgets\TGridView;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['tree-node-content/user-online']);?>"/>
<input type="hidden" id="selectNodeId" value="<?=$selectNodeId?>"/>
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
        var ajaxUrl = "<?=Url::toRoute(['user-online/list','TreeNodeKid'=>$selectNodeId])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['user-online/export','TreeNodeKid'=>$selectNodeId])?>";

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

<?php  echo $this->render('_search', ['model' => $searchModel,'includeSubNode'=>$includeSubNode]); ?>
<!-- /.panel-heading -->
<div class="treetype-body">
    <?
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('common','serial_number'),
        ],
        'user_name',
        'real_name',
        [// the owner name of the model
            'label' => Yii::t('common', 'releate_orgnization'),
            'value' => 'OrgnizationName',
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'attribute'=>'last_action_at',
            'format'=>'datetime',
            'value'=>'last_action_at',
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'label'=>Yii::t('common', 'idle_minute'),
            'value'=> function ($model, $key, $index, $cloumn){
                return round((time() - $model->last_action_at) / 60);
            },
        ],
//        [
//            'class' => 'kartik\grid\DataColumn',
//            'attribute'=>'limitation',
//            'format'=>'text',
//            'value'=> function ($model, $key, $index, $cloumn){
//
//                if ($model->limitation=='N')
//                    return Yii::t('common', 'limitation_none');
//                else if ($model->limitation=='R')
//                    return Yii::t('common', 'limitation_readonly');
//                else if ($model->limitation=='U')
//                    return Yii::t('common', 'limitation_onlyname');
//                else
//                    return Yii::t('common', 'limitation_hidden');
//            },
//        ],
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
            'header' => Yii::t('common','operation_button'),
            'template' => '{viewpop}',
            'width' => '120px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['user/view','id'=>$key]).'");'
                            ]);
                }

//                'resetPassButton' => function ($url, $model, $key) {
//                    return
//                        Html::a('<span class="glyphicon glyphicon-refresh"></span>', '#',
//                            ['id'=>'ResetPassButton', 'title'=> Yii::t('common', 'reset_pass_button'),
//                                'onclick'=>'resetPassButton("'. Yii::$app->urlManager->createUrl(['user/reset-pass','id'=>$key]).'");'
//                            ]);
//                },
            ],
//                'headerOptions' => ['width' => '80'],
        ],
    ];
    ?>

    <?
    $contentName = Yii::t('common', 'user_online');


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