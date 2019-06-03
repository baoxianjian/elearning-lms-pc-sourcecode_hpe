<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 12:01 PM
 */
use components\widgets\TModal;
use yii\helpers\Html;
use yii\helpers\Url;

$ContentTanslateName =  Yii::t('backend', $ContentName) ;

$this->params['breadcrumbs'][] =  $ContentTanslateName . $FunctionName;
?>

<head>
<!--    --><?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<!--    --><?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
<!--    --><?//=Html::cssFile('/static/backend/css/style.min.css')?>
<!--    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>-->
    <script>
        $(document).ready(function() {
//            alert($('jsTree_tree_changed_result').html());
            loadTree();
            loadList();

//            $("#addModal").draggable({
//                handle: ".modal-header"
//            });

        });


        function loadTree(){
//            $('#leftTree').empty();
//            alert('1');
//            alert($('#jsTree_tree').html());
            var ajaxUrl = "<?=Url::toRoute(['tree-node/tree','TreeType'=>$TreeType,'ContentName'=>$ContentName])?>";
            ajaxGet(ajaxUrl, "jsTree");
        }


        function loadList(){
            var ajaxUrl = "<?=Url::toRoute([$ContentName.'/list', 'TreeNodeKid'=>'-1'])?>";
//            alert(ajaxUrl);
            ajaxGet(ajaxUrl, "rightList");
        }

        function reloadtree() {
//            alert('reloadtree');
            saveRefeshLeftTree('jsTree_tree');
        }

        //保存和刷新左边的树
        function saveRefeshLeftTree(tree){
//            alert('saveRefeshLeftTree-start');
            $.jstree.reference('#'+tree).save_state();
            $.jstree.reference('#'+tree).refresh();

//            alert('saveRefeshLeftTree-end');
        }

//        function test(){
//            reloadtree();
////            alert('1');
////            $("#tabs li:eq(0) a").tab("show");
//        }

        function TreeCallback(){
//            alert('TreeCallback');
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
<!--    <input type="button" id="test" onclick="test();" value="test"/>-->
<div class="col-lg-12">
    <div class="col-lg-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <div id="jsTree" class="demo"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="panel" >
            <div class="panel-body" id="content-body">
                <div id="rightList"></div>
            </div>
        </div>
    </div>
</div>

<?//=Html::jsFile('/static/backend/js/jquery.form.js')?>
<?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>



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
    'id'=>'moveModal',
    'size' => 'modal-sm',
    'header' => '<h2>'.Yii::t('backend', 'move_{value}',['value'=>$ContentTanslateName]).'</h2>',
    'footer' =>
        Html::button(Yii::t('backend', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal'])
        . '' .
        Html::button(Yii::t('backend', 'update'),
            ['id'=>'saveBtn','class'=>'btn btn-primary','onclick'=>'submitModalFormCustomized("","clientform-move","moveModal",true,false);'])
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