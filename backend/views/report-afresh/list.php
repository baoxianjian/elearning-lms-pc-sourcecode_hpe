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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['report-afresh/index']);?>"/>
<script>

        var indexUrl = document.getElementById('indexUrl');

        //    alert(document.getElementById("content-body"));
        if(!document.getElementById("content-body"))
        {
            window.location = indexUrl.value;
        }

    function reloadForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['report-afresh/list'])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        ajaxUrl = urlreplace(ajaxUrl,'PageSize',$('#PageSize_grid').val());
        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }

   

    function loadModalFormData(modalId,url)
    {

        modalClear("viewModal");

        openModalForm(modalId, url);

//        }
    }


</script>

<!-- /.panel-heading -->
<div class="treetype-body">
    <?
    $gridColumns = [
        [
            'class' => 'kartik\grid\SerialColumn',
            'header' => Yii::t('common','serial_number'),
        ],  
    	[
    		'header' => Yii::t('common', 'report_service_name'),
    		'value' => function($model){
    			return $model->service_name;
    		
    		}
    	],
    	[
    		'header' => Yii::t('common', 'report_allow_restart'),
    		'value' => function($model){
    			return $model->getAllowRestart();
    		
    		}
    	],
    	
        [
            'class' => 'kartik\grid\ActionColumn',
            'header' => Yii::t('common','operation_button'),
            'template' => '{viewpop}{updatepop}{moveButton}{statusButton}{deleteButton}',
            'width' => '80px',
            'buttons' => [
                'viewpop' => function ($url, $model, $key) {
                
                if($model->is_allow_restart == '1'){
                	return
                	Html::a('<span class="btn btn-default btn-xs">'.Yii::t('backend', 'report_regenerate').'</span>', '#',
                			['id'=>'ViewButton', 'title'=>Yii::t('common', 'view_button'),
                			//                                'class'=>'modal',
                			//                                'data-target'=>'#viewModal',
                					'onclick'=>'loadModalFormData("viewModal","'. Yii::$app->urlManager->createUrl(['report-afresh/restart','id'=>$key]).'");'
                			]);
                }else{
                	return '';
                }
                   
                },
            ]
        ]
    ];
    ?>

    <?
    $contentName = Yii::t('common', 'service_type_report');

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
                   Html::button('<i class="glyphicon glyphicon-export"></i> '.Yii::t('backend', 'report_refresh'),[
                       'title'=>Yii::t('backend', 'report_refresh'), 'class'=>'btn btn-default blueBtn',  'id'=>'exportCvs',
                       'onclick'=>'reloadForm()'
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