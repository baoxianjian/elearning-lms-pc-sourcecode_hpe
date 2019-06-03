<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/10/15
 * Time: 9:33 PM
 */
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<head>
    <script>

        $("document").ready(function(){
            $("#new_country").on("pjax:end", function() {
                $.pjax.defaults.timeout = false;//IMPORTANT
                $.pjax.reload({container:"#countries"});  //Reload GridView
            });
        });

    </script>
</head>
<div class="panel-body">

<?php Pjax::begin(['id' => 'new_country']) ?>
    <?php $form = ActiveForm::begin(['action' => ['demo/test'],
        'options' => ['data-pjax' => true ]]); ?>

        <?= Html::submitButton("load") ?>

    <?php ActiveForm::end(); ?>
<?php Pjax::end() ?>

<?php Pjax::begin(['id' => 'countries']) ?>
    <?
    $gridColumns = [
        'tree_type_code',
        'tree_type_name',
        'max_level',
    ];

    if ($dataProvider != null) :
        echo GridView::widget([
            'id'=>'grid',
            'dataProvider' => $dataProvider,
            //  'filterModel' => $searchModel,
            'columns' => $gridColumns,

        ]);
    endif
    ?>
<?php Pjax::end(); ?>
</div>