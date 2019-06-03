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

?>




<div class="modal-div">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-' . $formType,
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

<!--    <div id="selectTree"></div>-->
    <div id='<?="selectTree".$suffix?>'></div>

<!-- 原始节点，迁移时用-->
    <?= Html::hiddenInput("sourceTreeNodeId".$suffix,isset($sourceTreeNodeId) ? $sourceTreeNodeId : '',['id'=>'sourceTreeNodeId'.$suffix])?>

<!-- 临时变量，传值用-->
    <?= Html::hiddenInput("tempTransData".$suffix,isset($tempTransData) ? $tempTransData : '',['id'=>'tempTransData'.$suffix])?>

<!-- 树类型-->
    <?= Html::hiddenInput("jsTree_select-tree_type".$suffix,$TreeType,['id'=>'jsTree_select-tree_type'.$suffix])?>

<!-- 树勾选项目，多选时用-->
    <?= Html::hiddenInput("jsTree_select-tree_changed_result".$suffix,'',['id'=>'jsTree_select-tree_changed_result'.$suffix])?>

<!-- 树加载项目-->
    <?= Html::hiddenInput("jsTree_select-tree_loaded_result".$suffix,'',['id'=>'jsTree_select-tree_loaded_result'.$suffix])?>

<!-- 树点选项目，单选时用-->
    <?= Html::hiddenInput("jsTree_select-tree_selected_result".$suffix,'["-1"]',['id'=>'jsTree_select-tree_selected_result'.$suffix])?>



    <?php ActiveForm::end(); ?>
</div>


<script>

    $(document).ready(function() {
//            alert('1');
        loadTree();

//        TreeCallback();
    });

    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/select-tree',
            'treeFlag'=> isset($treeFlag) ? $treeFlag : null,
             'needRegister'=> isset($needRegister) ? $needRegister : null,
            'treeDataUrl'=> isset($treeDataUrl) ? $treeDataUrl : null])?>";
//        alert(ajaxUrl);

        var divName = "<?="selectTree".$suffix?>";
        //alert(divName);
        ajaxGet(ajaxUrl, divName);
    }
</script>