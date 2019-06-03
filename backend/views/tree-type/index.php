<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/7/15
 * Time: 11:11 PM
 */

use components\widgets\TGridView;
use components\widgets\TModal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$ContentTanslateName =  Yii::t('common', 'tree_type') ;

$this->params['breadcrumbs'][] =  $ContentTanslateName . Yii::t('common', 'management');
?>
<head>
<!--    --><?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<!--    --><?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
    <script>
        $(document).ready(function() {
//            alert('loadList');
            loadList();
        });

        function loadList(){
            var ajaxUrl = "<?=Url::toRoute(['tree-type/list'])?>";
            ajaxGet(ajaxUrl, "rightList");
        }

        function SaveContinueForm()
        {
            operation = 'savecontinue';
            FormSubmit();
        }

        function SaveCloseForm()
        {
            operation= 'saveclose';
            FormSubmit();
        }


        function UpdateForm()
        {
            operation= 'update';
            FormSubmit();
        }


    </script>
</head>

<div id="content-body">
    <div id="rightList"></div>
</div>

<?php
TModal::begin([
    'id'=>'addModal',
    'header' => '<h2>'.Yii::t('backend', 'add_{value}',['value'=>$ContentTanslateName]).'</h2>',
    'footer' =>
        Html::button(Yii::t('backend', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal'])
//        . '' .
//        Html::button(Yii::t('common', 'test'),
//            ['id'=>'test','class'=>'btn btn-default','onclick'=>'test();'])
        . '' .
        Html::button(Yii::t('backend', 'save_continue_button'),
            ['id'=>'saveContinueBtn','class'=>'btn btn-default','onclick'=>'SaveContinueForm();'])
        . '' .
        Html::button(Yii::t('backend', 'save_close_button'),
            ['id'=>'saveBtn','class'=>'btn btn-primary','onclick'=>'SaveCloseForm();'])
]);
?>


<div class="modal-body-view">

</div>


<?php TModal::end(); ?>


<?php
TModal::begin([
    'id'=>'updateModal',
    'header' => '<h2>'.Yii::t('backend', 'edit_{value}',['value'=>$ContentTanslateName]).'</h2>',
    'footer' =>
        Html::button(Yii::t('backend', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal'])
        . '' .
        Html::button(Yii::t('backend', 'update'),
            ['id'=>'updateBtn','class'=>'btn btn-primary','onclick'=>'UpdateForm();'])
]);
?>


<div class="modal-body-view">

</div>


<?php TModal::end(); ?>


<?php
TModal::begin([
    'id'=>'viewModal',
    'header' => '<h2>'.Yii::t('backend', 'view_{value}',['value'=>$ContentTanslateName]).'</h2>',
    'footer' =>
        Html::button(Yii::t('backend', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal']),
]);
?>


<div class="modal-body-view">

</div>

<?php TModal::end(); ?>
