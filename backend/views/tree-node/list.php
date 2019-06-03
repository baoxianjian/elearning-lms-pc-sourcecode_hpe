<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 12:15 PM
 */
use components\widgets\TGridView;
use components\widgets\TModal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

?>

<input type="hidden" id="tree_changed_result"/>
<input type="hidden" id="tree_loaded_result"/>
<input type="hidden" id="tree_selected_result"/>

<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['tree-node/'.$treeType]);?>"/>
<?//=Yii::$app->urlManager->hostInfo?>
<?= Html::hiddenInput("treeTypeId",$treeTypeId,['id'=>'treeTypeId'])?>
<?= Html::hiddenInput("parentNodeId",$parentNodeId,['id'=>'parentNodeId'])?>
<?= Html::hiddenInput("limitation",$limitation,['id'=>'limitation'])?>
<script>
    var indexUrl = document.getElementById('indexUrl');
//    alert(indexUrl.value);
    if(!document.getElementById("jsTree"))
    {
        window.location = indexUrl.value;
    }



    function loadModalFormData(modalId,url)
    {
        TabClear('tabs');

        modalClear("addModal");
        modalClear("updateModal");
        modalClear("viewModal");
        modalClear("moveModal");

        openModalForm(modalId, url);
    }


    function reloadForm()
    {
//            alert('reloadForm');
//            $.pjax.reload({container:"#gridframe"});
        var ajaxUrl = "<?=Url::toRoute(['tree-node/list','TreeNodeKid'=>$parentNodeId,'TreeType'=>$treeType])?>";
//        ajaxGet(ajaxUrl, "rightList");
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl, 'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['tree-node/export','TreeNodeKid'=>$parentNodeId,'TreeType'=>$treeType])?>"; 

        exportWithForm('searchForm', ajaxUrl);
    }

    function ReloadPageAfterDelete()
    {
//            alert('ReloadPageAfterDelete');
        reloadForm();
        reloadtree();

    }

    function ReloadPageAfterChangeStatus()
    {
//            alert('ReloadPageAfterChangeStatus');
        reloadForm();
        reloadtree();
    }



    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
//        alert('ReloadPageAfterUpdate');
//        alert(isClose);
        reloadForm();
        reloadtree();

//            $.pjax.reload({container:"#grid"});
        if (isClose) {
//            $('#'+modalId).modal('hide');
//            modalClear(modalId);
            modalClear(modalId);
            modalHidden(modalId);
//            alert('closed');
        }
        else
        {
//            alert(modalId);
//            modalClear(modalId);
            modalLoad(modalId,'<?=Yii::$app->urlManager->createUrl(['tree-node/create',
        'treeTypeId'=>$treeTypeId,'parentNodeId'=>$parentNodeId])?>');
        }
    }

    function ContinueSubmit(frameId,modalId,isClose,isErrorSubmit,kid)
    {
//        alert('ContinueSubmit');
//        alert('kid:'+kid);
        submitModalForm(frameId,"clientform-other",modalId,isClose,isErrorSubmit,kid,'1');
    }



    function submitModalFormWithOther(frameId,formId,modalId,isClose,isErrorSubmit)
    {
        //alert($("#clientform-other").length);
        if ($("#clientform-other").length > 0)
        {
            if (validateOtherClientForm()) {
                submitModalFormWithContinue(frameId, formId, modalId, isClose, isErrorSubmit, '0');
            }
        }
        else
        {
            submitModalForm(frameId,formId,modalId,isClose,isErrorSubmit);
        }
    }

//    function ReloadPage()
//    {
//            $.pjax.reload({container:"#grid"});
////        var ajaxUrl = "<?////=Url::toRoute(['tree-node/list','treeNodeKid'=>$parentNodeId,'treeType'=>$treeType])?>////";
////        ajaxGet(ajaxUrl, "rightList");
//    }
    </script>
    <?php  echo $this->render('_search', ['model' => $searchModel, 'treeTypeId'=>$treeTypeId,
        'parentNodeId'=>$parentNodeId,'TreeTypeName'=>Yii::t('backend',$TreeTypeCode),'TreeTypeCode'=>$TreeTypeCode,'treeType'=>$treeType, 'includeSubNode'=>$includeSubNode]); ?>
<!--        <input type="button" id="reloadForm" onclick="ReloadPage();" value="reloadForm"/>-->
    <!-- /.panel-heading -->
    <div class="tree-body">

        <?php

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

        if ($limitation == 'N')
        {
            $template = '{viewpop}{updatepop}{moveButton}{statusButton}{deleteButton}';

            $content =
            Html::button('<i class="glyphicon glyphicon-plus"></i> '.Yii::t('backend', 'add_button'),[
                'title'=>Yii::t('backend', 'add_button'), 'class'=>'btn btn-default greenBtn',
                'onclick'=>'loadModalFormData("addModal","'. Yii::$app->urlManager->createUrl(['tree-node/create',
                        'treeTypeId'=>$treeTypeId,'parentNodeId'=>$parentNodeId]) .'");'
            ])
            .' '.
            Html::button('<i class="glyphicon glyphicon-minus"></i> '.Yii::t('backend', 'batch_delete_button'),[
                'title'=>Yii::t('common', 'batch_delete_button'), 'class'=>'btn btn-default redBtn',
                'onclick'=>'batchDeleteButton("'. Yii::$app->urlManager->createUrl('tree-node/batch-delete').'");'
            ])
            .' '.
            $pageButton;
        }
        elseif ($limitation == 'U') {
            $template = '{viewpop}{updatepop}';

            $content = $pageButton;
        }
        elseif ($limitation == 'R') {
            $template = '{viewpop}';

            $content = $pageButton;
        }


        $content .= ' '.
        Html::button('<i class="glyphicon glyphicon-export"></i> '.Yii::t('backend', 'export_button'),[
            'title'=>Yii::t('backend', 'export_button'), 'class'=>'btn btn-default blueBtn',
            'onclick'=>'exportForm();'
        ]);
        ?>

        <?
            $tree_node_code_header = Yii::t('backend', '{value}_tree_node_code',['value'=>Yii::t('backend',$TreeTypeCode)]);
            $tree_node_name_header = Yii::t('backend', '{value}_tree_node_name',['value'=>Yii::t('backend',$TreeTypeCode)]);

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
                [
                    'class' => 'kartik\grid\DataColumn',
                    'attribute'=>'tree_node_code',
                    'format'=>'text',
                    'header'=>$tree_node_code_header,
                    'value' => 'tree_node_code'
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'attribute'=>'tree_node_name',
                    'format'=>'text',
                    'header'=>$tree_node_name_header,
                    'value' => 'tree_node_name'
                ],
                [
                    'class' => 'kartik\grid\DataColumn',
                    'attribute'=> 'parentNodeText',
                    'format'=>'text',
                    'header'=> Yii::t('common','parent_node_name'),
                ],
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
                    'header' => Yii::t('backend','action'),
                    'template' => $template,
                    'width' => '150px',
//                    'headerOptions' => ['width' => '550px'],
                    'buttons' => [
                        'viewpop' => function ($url, $model, $key) {
                            return
                                Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '#',
                                    ['id'=>'ViewButton', 'title'=>Yii::t('backend', 'view_button'),
//                                        'data-toggle'=>'modal',
//                                        'data-target'=>'#viewModal',
                                        'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['tree-node/view','id'=>$key]).'");'
                                    ]);
                        },
                        'updatepop' => function ($url, $model, $key) {
                            return
                                Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#',
                                    ['id'=>'EditButton', 'title'=>Yii::t('backend', 'edit_button'),
//                                        'data-toggle'=>'modal',
//                                        'data-target'=>'#updateModal',
                                        'onclick'=>'loadModalFormData("updateModal","'. Yii::$app->urlManager->createUrl(['tree-node/update','id'=>$key]).'");'
                                    ]);
                        },
                        'moveButton' => function ($url, $model, $key) {
                            if ($model->status == '1') {
                                return
                                    Html::a('<span class="glyphicon glyphicon-move"></span>', '#',
                                        ['id' => 'MoveButton', 'title' => Yii::t('backend', 'move_button'),
//                                        'data-toggle'=>'modal',
//                                        'data-target'=>'#moveModal',
                                            'onclick' => 'loadModalFormData("moveModal","' . Yii::$app->urlManager->createUrl(['tree-node/move', 'id' => $key]) . '");'
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
                                        'onclick' => 'statusButton("' . $status . '","' . Yii::$app->urlManager->createUrl(['tree-node/status', 'id' => $key, 'status' => $status]) . '");'
                                    ]);
                        },
                        'deleteButton' => function ($url, $model, $key) {
                            return
                                Html::a('<span class="glyphicon glyphicon-trash"></span>', '#',
                                    ['id'=>'DeleteButton', 'title'=> Yii::t('backend', 'delete_button'),
                                        'onclick'=>'deleteButton("'.$key.'","'. Yii::$app->urlManager->createUrl(['tree-node/delete','id'=>$key]).'");'
                                    ]);
                        },
                    ],

                ],
            ];
        ?>


<!--        --><?php //Pjax::begin(['id' => 'gridframe']) ?>
        <?php



            echo TGridView::widget([
                'id'=>'grid',
                'dataProvider' => $dataProvider,
                //  'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'panel' => [
                    'type' => TGridView::TYPE_DEFAULT,
                    'heading' => '<h3 class="panel-title" style="text-align: left;"><a href="#" id="jsTreeClose" class="glyphicon glyphicon-menu-left " onclick="TreeHide();"></a> <i class="glyphicon glyphicon-book"></i>'. ' ' .Yii::t('backend', '{value}_record',['value'=>Yii::t('backend',$TreeTypeCode)]).'</h3>',
                ],
                'toolbar' => [
                    ['content'=> $content],
//                    '{export}',
//                    '{toggleData}'
                ],
//                'displayPageSizeSelect'=>false,
                'pjax'=>true,
                'pjaxSettings'=>[
                    'neverTimeout'=>true,
                ]
            ]);
        ?>

<!--        --><?//= Html::hiddenInput("PageShowAll",$forceShowAll,['id'=>'PageShowAll'])?>
<!--        --><?//= Html::hiddenInput("PageSize",$pageSize,['id'=>'PageSize'])?>
<!--        --><?php //Pjax::end() ?>
    </div>


<?//=Html::jsFile('/static/backend/js/jquery.form.js')?>
<?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>

