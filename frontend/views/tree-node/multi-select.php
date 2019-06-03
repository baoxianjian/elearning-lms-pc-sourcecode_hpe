<?php

use components\widgets\TJsTree;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;


/* @var $model common\models\treemanager\FwTreeNode */

?>
<?php

$suffix = '';
if (isset($treeFlag) && $treeFlag != null && $treeFlag != '')
    $suffix = '_' . $treeFlag;


$checkRoute = isset($checkRoute) && $checkRoute ? $checkRoute : '/tree-node/multi-select-tree';
?>

<div class="modal-div">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-' . $formType,
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

    <div id='<?="multiSelectTree".$suffix?>'></div>



<!-- 树类型-->
    <?= Html::hiddenInput("jsTree_multi-select-tree_type".$suffix, $TreeType,['id'=>'jsTree_multi-select-tree_type'.$suffix])?>

<!-- 临时变量，传值用-->
    <?= Html::hiddenInput("tempTransData".$suffix,isset($tempTransData) ? $tempTransData : '',['id'=>'tempTransData'.$suffix])?>

<!-- 树勾选项目，多选时用-->
    <?= Html::hiddenInput("jsTree_multi-select-tree_changed_result".$suffix,'',['id'=>'jsTree_multi-select-tree_changed_result'.$suffix])?>

<!-- 树最近加载项目-->
    <?= Html::hiddenInput("jsTree_multi-select-tree_loaded_result".$suffix,'',['id'=>'jsTree_multi-select-tree_loaded_result'.$suffix])?>

<!-- 树点选项目，单选时用-->
    <?= Html::hiddenInput("jsTree_multi-select-tree_selected_result".$suffix,'["-1"]',['id'=>'jsTree_multi-select-tree_selected_result'.$suffix])?>

<!-- 树当前显示的所有项目-->
    <?= Html::hiddenInput("jsTree_multi-select-tree_displayed_result".$suffix,'',['id'=>'jsTree_multi-select-tree_displayed_result'.$suffix])?>

    <?php ActiveForm::end(); ?>
</div>


<script>

    $(document).ready(function() {
//            alert('1');
        loadTree();

//        TreeCallback();
    });

    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute([$checkRoute,
            'treeDataUrl'=> isset($treeDataUrl) ? $treeDataUrl : null,
            'needRegister'=> isset($needRegister) ? $needRegister : null,
            'treeFlag'=> isset($treeFlag) ? $treeFlag : null,
            'treeState'=> isset($treeState) ? $treeState : "True"])?>";
//        alert(ajaxUrl);

        var divName = "<?="multiSelectTree".$suffix?>";
        //alert(divName);
        ajaxGet(ajaxUrl, divName);
    }
</script>