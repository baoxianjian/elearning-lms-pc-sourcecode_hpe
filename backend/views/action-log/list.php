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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['tree-node-content/action-log']);?>"/>
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
        var ajaxUrl = "<?=Url::toRoute(['action-log/list','TreeNodeKid'=>$selectNodeId])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());

        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

    function exportForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['action-log/export','TreeNodeKid'=>$selectNodeId])?>";

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

<?php  echo $this->render('_search', ['model' => $searchModel,'includeSubNode'=>$includeSubNode,'actionLogFilterModel'=>$actionLogFilterModel]); ?>
<!-- /.panel-heading -->
<div class="treetype-body">
    <?
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('common','serial_number'),
        ],
        [// the owner name of the model
            'label' => Yii::t('common', 'user_name'),
            'value' => 'UserName',
        ],
        [// the owner name of the model
            'label' => Yii::t('common', 'real_name'),
            'value' => 'RealName',
        ],
        [// the owner name of the model
            'label' => Yii::t('common', 'releate_orgnization'),
            'value' => 'OrgnizationName',
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'label' => Yii::t('common', 'action_name'),
            'value'=>'FilterName',
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'label' => Yii::t('common', 'action_ip'),
            'value'=>'action_ip',
        ],
        [
            'class' => 'kartik\grid\DataColumn',
            'label' => Yii::t('common', 'action_time'),
            'format'=>'datetime',
            'value'=>'created_at',
        ],
    ];
    ?>

    <?
    $contentName = Yii::t('common', 'action_log');


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