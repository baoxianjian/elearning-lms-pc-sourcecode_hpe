<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/20
 * Time: 17:08
 */
use components\widgets\TModal;
use yii\helpers\Html;
use yii\helpers\Url;

$this->params['breadcrumbs'][] = Yii::t('common', 'interface_record_log') . Yii::t('backend', 'search');
?>

<head>
    <!--    --><?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
    <!--    --><?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
    <!--    --><?//=Html::cssFile('/static/backend/css/style.min.css')?>
    <!--    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>-->
    <script>
        $(document).ready(function() {
//            alert($('jsTree_tree_changed_result').html());
//            loadTree();
            loadList();

//            $("#addModal").draggable({
//                handle: ".modal-header"
//            });

        });


        function loadList(){
            var ajaxUrl = "<?=Url::toRoute(['interface-record/list'])?>";
//            alert(ajaxUrl);
              ajaxGet(ajaxUrl, "rightList");
        }

        function TreeCallback(){
//            alert('TreeCallback');
        }
    </script>
</head>
<div class="col-lg-12">
    <div class="col-lg-12">
        <div class="panel" >
            <div class="panel-body" id="content-body">
                <div id="rightList"></div>
            </div>
        </div>
    </div>
</div>
<?php
TModal::begin([
    'id'=>'viewModal',
    'header' => '<h2>'.Yii::t('common', 'view_{value}',['value'=>Yii::t('common', 'interface_record_log')]).'</h2>',
    'footer' =>
        Html::button(Yii::t('common', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal']),
]);
?>

    <div class="modal-body-view">

    </div>

<?php TModal::end(); ?>