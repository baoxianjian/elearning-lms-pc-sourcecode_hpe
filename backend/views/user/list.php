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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['tree-node-content/user']);?>"/>
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
        var ajaxUrl = "<?=Url::toRoute(['user/list','TreeNodeKid'=>$selectNodeId])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['user/export','TreeNodeKid'=>$selectNodeId])?>";

        exportWithForm('searchForm', ajaxUrl);
    }

    function loadModalFormData(modalId,url)
    {
//        var selectNodeId = $('#selectNodeId').val();
////        alert(selectNodeId);
//        if (modalId == 'addModal' && selectNodeId == '')
//        {
//            NotyWarning('<?//=Yii::t('common','cannot_add_in_root');?>//');
//        }
//        else {
            modalClear("addModal");
            modalClear("updateModal");
            modalClear("moveModal");
            modalClear("viewModal");

            openModalForm(modalId, url);

//        }
    }

    function ContinueSubmit(frameId,modalId,isClose,isErrorSubmit,kid)
    {
//        alert('ContinueSubmit');
        submitModalForm(frameId,"clientform-position",modalId,isClose,isErrorSubmit,kid,'1');
    }

    function submitModalFormCustomized(frameId,formId,modalId,isClose,isErrorSubmit)
    {
        if ($("#clientform-position").length > 0)
        {
            submitModalFormWithContinue(frameId,formId,modalId,isClose,isErrorSubmit,'0');
        }
        else
        {
            submitModalForm(frameId,formId,modalId,isClose,isErrorSubmit);
        }
    }

    function ReloadPageAfterDelete()
    {
        //alert('1');
        reloadForm();
    }

    function ReloadPageAfterChangeStatus()
    {
//            alert('ReloadPageAfterChangeStatus');
        reloadForm();
//        reloadtree();
    }

    function ReloadPageAfterResetPass()
    {
        reloadForm();
    }

    function ReloadPageAfterPost()
    {
//        reloadForm();
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
//        alert("frameId:"+frameId);
//        alert("formId:"+formId);
//        alert("modalId:"+modalId);
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
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['user/create','TreeNodeKid'=>$selectNodeId])?>');
        }

    }

    function resetPassButton(url)
    {

        var msg = "<?=Yii::t('backend','reset_password');?>";
        var data = "";

        NotyConfirm(msg,  function(data){
            ajaxPostDataWithReload(data, url);
        });

//        if (confirm(msg)) {
//            ajaxPostDataWithReload(data, url);
//        }
    }




</script>

<?php  echo $this->render('_search', ['model' => $searchModel,'includeSubNode'=>$includeSubNode]); ?>
<!-- /.panel-heading -->
<div class="treetype-body">
    <?
    $gridColumns = [
        [
            'name' => 'selectedIds',
            'class' => 'kartik\grid\CheckboxColumn',
            'checkboxOptions' => function($model, $key, $index, $column) {
                return ['value' => $model->kid];
            }
        ],
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('backend','serial_number'),
        ],
        'user_name',
        'real_name',
        [// the owner name of the model
            'label' => Yii::t('backend', 'releate_orgnization'),
            'value' => 'OrgnizationName',
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
                    return Yii::t('backend', 'status_temp');
                else if ($model->status=='1')
                    return Yii::t('backend', 'status_normal');
                else if ($model->status=='2')
                    return Yii::t('backend', 'status_stop');
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
            'template' => '{viewpop}{updatepop}{moveButton}{statusButton}{resetPassButton}{deleteButton}',
            'width' => '120px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('backend', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['user/view','id'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                            ['id'=>'EditButton', 'title'=>Yii::t('backend', 'edit_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#updateModal',
                                'onclick'=>'loadModalFormData("updateModal","'. Yii::$app->urlManager->createUrl(['user/update','id'=>$key]).'");'
                            ]);
                },
                'moveButton' => function ($url, $model, $key) {
                    if ($model->status == '1') {
                        return
                            Html::a('<span class="glyphicon glyphicon-move"></span>', '#',
                                ['id' => 'MoveButton', 'title' => Yii::t('backend', 'move_button'),
                //                                        'data-toggle'=>'modal',
                //                                        'data-target'=>'#moveModal',
                                    'onclick' => 'loadModalFormData("moveModal","' . Yii::$app->urlManager->createUrl(['user/move', 'id' => $key]) . '");'
                                ]);
                    }
                },
                'statusButton' => function ($url, $model, $key) {

                    if ($model->status == '1') {
                        $status = "2";
                        $title = Yii::t('backend', 'change_status_stop');
                        $class = 'glyphicon glyphicon-pause';
                    }
                    else
                    {
                        $status = "1";
                        $title = Yii::t('backend', 'change_status_start');
                        $class = 'glyphicon glyphicon-play';
                    }

                    return

                        Html::a('<span class="'.$class.'"></span>', '#',
                            ['id'=>'StatusButton', 'title'=> $title,
                                'onclick' => 'statusButton("' . $status . '","' . Yii::$app->urlManager->createUrl(['user/status', 'id' => $key, 'status' => $status]) . '");'
                            ]);
                },
                'resetPassButton' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-refresh"></span>', '#',
                            ['id'=>'ResetPassButton', 'title'=> Yii::t('backend', 'reset_pass_button'),
                                'onclick'=>'resetPassButton("'. Yii::$app->urlManager->createUrl(['user/reset-pass','id'=>$key]).'");'
                            ]);
                },
                'deleteButton' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-trash"></span>', '#',
                            ['id'=>'DeleteButton', 'title'=> Yii::t('backend', 'delete_button'),
                                'onclick'=>'deleteButton("'.$key.'","'. Yii::$app->urlManager->createUrl(['user/delete','id'=>$key]).'");'
                            ]);
                },
            ],
//                'headerOptions' => ['width' => '80'],
        ],
    ];
    ?>

    <?
    $contentName = Yii::t('backend', 'user');

    $buttonDropdownItems[] = [
        'label' => Yii::t('backend', 'batch_delete_button'),
        'url' => '#',
        'linkOptions'=>
            [
                'class'=>'glyphicon glyphicon-minus',
                'title'=>Yii::t('backend', 'batch_delete_button'),
                'onclick'=>'batchDeleteButton("'. Yii::$app->urlManager->createUrl('user/batch-delete').'");'
            ]
    ];


    $buttonDropdownItems[] =  [
        'label' => Yii::t('backend', 'batch_stop_button'),
        'url' => '#',
        'linkOptions'=>
            [
                'class'=>'glyphicon glyphicon-pause',
                'title'=>Yii::t('backend', 'batch_stop_button'),
                'onclick'=>'batchOperateButton("stop","'. Yii::$app->urlManager->createUrl(['user/status','status' => '2']).'");'
            ]
    ];

    $buttonDropdownItems[] =  [
        'label' => Yii::t('backend', 'batch_start_button'),
        'url' => '#',
        'linkOptions'=>
            [
                'class'=>'glyphicon glyphicon-play',
                'title'=>Yii::t('backend', 'batch_start_button'),
                'onclick'=>'batchOperateButton("start","'. Yii::$app->urlManager->createUrl(['user/status','status' => '1']).'");'
            ]
    ];

//    if (!$includeSubNode) {
        $buttonDropdownItems[] = [
            'label' => Yii::t('backend', 'batch_move_button'),
            'url' => '#',
            'linkOptions' =>
                [
                    'class' => 'glyphicon glyphicon-move',
                    'title' => Yii::t('backend', 'batch_move_button'),
                    'onclick' => 'batchOperateButton("moveModal","' . Yii::$app->urlManager->createUrl('user/move') . '");'
                ]
        ];
//    }

    $buttonDropdownItems[] = [
        'label' => Yii::t('backend', 'batch_reset_pass_button'),
        'url' => '#',
        'linkOptions' =>
            [
                'class' => 'glyphicon glyphicon-refresh',
                'title' => Yii::t('common', 'batch_reset_pass_button'),
                'onclick' => 'batchOperateButton("resetPass","' . Yii::$app->urlManager->createUrl('user/reset-pass') . '");'
            ]
    ];

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

    if ($selectNodeId != null) {
        $addButton = Html::button('<i class="glyphicon glyphicon-plus"></i> ' . Yii::t('backend', 'add_button'), [
            'title'=>Yii::t('backend', 'add_button'), 'class'=>'btn btn-default greenBtn',
//                    'data-toggle'=>'modal',
//                    'data-target'=>'#addModal',
            'onclick' => 'loadModalFormData("addModal","' . Yii::$app->urlManager->createUrl(['user/create', 'TreeNodeKid' => $selectNodeId]) . '");'
        ]);
    }
    else
    {
        $addButton = "";
    }


    echo TGridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        //  'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'panel' => [
            'type' => TGridView::TYPE_DEFAULT,
            'heading' => '<h3 class="panel-title" style="text-align: left;"><a href="#" id="jsTreeClose" class="glyphicon glyphicon-menu-left " onclick="TreeHide();"></a> <i class="glyphicon glyphicon-book"></i> '.Yii::t('backend', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        'toolbar' => [
            ['content'=>
                $addButton
                .' '.
                ButtonDropdown::widget([
                    'encodeLabel' => false,
                    'label' => '<i class="glyphicon glyphicon-ok"></i> ' . Yii::t('backend', 'batch_operate_button'),
                    'dropdown' => [
                        'items' => $buttonDropdownItems,
                    ],
                    'options'=>[
                        'class'=>'btn btn-default redBtn dropdown-toggle'
                    ]
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