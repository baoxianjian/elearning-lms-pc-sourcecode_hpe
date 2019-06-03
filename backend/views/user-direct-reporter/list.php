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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['tree-node-content/user-direct-reporter']);?>"/>
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
        var ajaxUrl = "<?=Url::toRoute(['user-direct-reporter/list','TreeNodeKid'=>$selectNodeId])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
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
            modalClear("addModal");
            modalClear("updateModal");
            modalClear("moveModal");
            modalClear("viewModal");

            openModalForm(modalId, url);

//        }
    }


    function submitModalFormCustomized(frameId,formId,modalId,isClose,isErrorSubmit)
    {
        submitModalForm(frameId,formId,modalId,isClose,isErrorSubmit);
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
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['user-direct-reporter/create','TreeNodeKid'=>$selectNodeId])?>');
        }

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
            'header' => Yii::t('common','serial_number'),
        ],
        'user_name',
        'real_name',
        [// the owner name of the model
            'label' => Yii::t('common', 'releate_orgnization'),
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
            'template' => '{viewpop}{updatepop}',
            'width' => '120px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                            ['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['user-direct-reporter/view','userId'=>$key]).'");'
                            ]);
                },
                'updatepop' => function ($url, $model, $key) {
                    return
                        Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                            ['id'=>'EditButton', 'title'=>Yii::t('common', 'edit_button'),
//                                'data-toggle'=>'modal',
//                                'data-target'=>'#viewModal',
                                'onclick'=>'loadModalFormData("updateModal","'. Yii::$app->urlManager->createUrl(['user-direct-reporter/update','userId'=>$key]).'");'
                            ]);
                },

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
    $contentName = Yii::t('backend', 'user_direct_reporter');


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
            ['content'=>
                $pageButton
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