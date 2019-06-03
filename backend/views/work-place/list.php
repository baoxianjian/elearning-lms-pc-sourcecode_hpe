<?php
/**
 * Created by PhpStorm.
 * FwUser: Alex Liu
 * Date: 5/5/2016
 * Time: 13:49 PM
 */
use components\widgets\TGridView;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['tree-node-content/work-place']);?>"/>
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
        var ajaxUrl = "<?=Url::toRoute(['work-place/list','TreeNodeKid'=>$selectNodeId])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['work-place/export','TreeNodeKid'=>$selectNodeId])?>";

        exportWithForm('searchForm', ajaxUrl);
    }

    function loadModalFormData(modalId,url)
    {
//        var selectNodeId = $('#selectNodeId').val();
//        if (modalId == 'addModal' && selectNodeId == '')
//        {
//            alert('<?//=Yii::t('common','cannot_add_in_root');?>//');
//        }
//        else {
            modalClear("addModal");
            modalClear("updateModal");
            modalClear("moveModal");
            modalClear("viewModal");

            openModalForm(modalId, url);
//        }
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
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['work-place/create','TreeNodeKid'=>$selectNodeId])?>');
        }

    }

    function submitModalFormCustomized(frameId,formId,modalId,isClose,isErrorSubmit)
    {
        submitModalForm(frameId,formId,modalId,isClose,isErrorSubmit);
    }


</script>

<?php  echo $this->render('_search', ['model' => $searchModel,'includeSubNode'=>$includeSubNode]); ?>
<!-- /.panel-heading -->
<div class="certificaiton-template-body">
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
        [// the owner name of the model
            'label' => Yii::t('backend', '{value}_tree_node_code',['value'=>Yii::t('backend','work_place')]),
            'value' => 'dictionary_code',
        ],
        [// the owner name of the model
            'label' => Yii::t('backend', '{value}_tree_node_name',['value'=>Yii::t('backend','work_place')]),
            'value' => 'dictionary_name',
        ],
        [// the owner name of the model
            'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','domain')]),
            'value' => function ($model, $key, $index, $cloumn) {
                $temp = $model->fwDomainWorkplace;
                $result = "";
                foreach ($temp as $t) {
                    $result .= ', ' . $t->fwDomain->domain_name;
                }
                return $result ? substr($result, 2) : null;
            },
        ],
//        'wechat_template_id',
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
            'template' => '{viewpop}{updatepop}{moveButton}{statusButton}{deleteButton}',
            'width' => '140px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['work-place/view','id'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                            ['id' => 'EditButton', 'title' => Yii::t('common', 'edit_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#updateModal',
                                'onclick' => 'loadModalFormData("updateModal","' . Yii::$app->urlManager->createUrl(['work-place/update', 'id' => $key]) . '");'
                            ]);
                },
            ],
//                'headerOptions' => ['width' => '80'],
        ],
    ];
    ?>

    <?
    $contentName = Yii::t('backend', 'work-place');

//    if (!$includeSubNode) {
//        $buttonDropdownItems[] = [
//            'label' => Yii::t('common', 'BatchMoveButton'),
//            'url' => '#',
//            'linkOptions' =>
//                [
//                    'class' => 'glyphicon glyphicon-move',
//                    'title' => Yii::t('common', 'BatchMoveButton'),
//                    'onclick' => 'batchOperateButton("moveModal","' . Yii::$app->urlManager->createUrl('work-place/move') . '");'
//                ]
//        ];
//    }

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

    if ($selectNodeId != null) {
        $addButton = Html::button('<i class="glyphicon glyphicon-plus"></i> '.Yii::t('common', 'add_button'),[
            'title'=>Yii::t('common', 'add_button'), 'class'=>'btn btn-default greenBtn',
//                    'data-toggle'=>'modal',
//                    'data-target'=>'#addModal',
            'onclick'=>'loadModalFormData("addModal","'. Yii::$app->urlManager->createUrl(['wechat-template/create','TreeNodeKid'=>$selectNodeId]) .'");'
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
            'heading' => '<h3 class="panel-title" style="text-align: left;"><a href="#" id="jsTreeClose" class="glyphicon glyphicon-menu-left " onclick="TreeHide();"></a> <i class="glyphicon glyphicon-book"></i> '.Yii::t('common', '{value}_record', ['value'=>$contentName]).'</h3>',
        ],
        'toolbar' => [
            ['content'=>
                $pageButton
            ],
//            '{export}',
//            '{toggleData}'
        ],
//        '_isShowAll'=>true,
        'pjax'=>true,
        'pjaxSettings'=>[
            'neverTimeout'=>true,
        ]
    ]);
    ?>
</div>