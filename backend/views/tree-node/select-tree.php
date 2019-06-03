<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 11:36 AM
 */
use components\widgets\TJsTree;
use yii\helpers\Url;
use yii\web\JsExpression;

?>
<?php

$suffix = '';
if (isset($treeFlag) && $treeFlag != null && $treeFlag != '')
    $suffix = '_' . $treeFlag;

if (!isset($includeRoot))
    $includeRoot = "True";

if (!isset($mergeRoot))
    $mergeRoot = "False";

if (!isset($showContentCount))
    $showContentCount = "False";

if (!isset($openAllNode))
    $openAllNode = "False";
?>
<div style="text-align: left;width: 100px;">

    <?=
    TJsTree::widget([
        'id' => 'select-tree',
        'treeFlag'=> isset($treeFlag) ? $treeFlag : null,
        'needRegister'=> isset($needRegister) ? $needRegister : null,
        'core' => [
            'check_callback' => true,
            'state' => [ 'key' => 'common' ],
            'data' => [
                'url' =>  (isset($treeDataUrl) && $treeDataUrl != null && $treeDataUrl != '') ? $treeDataUrl : Url::toRoute(['tree-node/tree-data']),
                'data' => new JsExpression(
                    "function (node) {
                        return {
                            'ID' : node.id ,
                            'TreeType': $('#jsTree_select-tree_type".$suffix ."').val(),
                            'ShowAll' : 'N',
                            'IncludeRoot' : '".$includeRoot ."',
                            'MergeRoot' : '".$mergeRoot ."',
                            'ShowContentCount' : '".$showContentCount ."',
                            'OpenAllNode' : '".$openAllNode ."'
                        };
                    }"
                ),
            ]
        ],
    ]);
    ?>
</div>
<?//= Html::hiddenInput("jsTree_tree_type",$TreeType,['id'=>'jsTree_tree_type'])?>
<!--<input type="text" id="jsTree_select-tree_changed_result"/>-->
<!--<input type="text" id="jsTree_select-tree_loaded_result"/>-->
<!--<input type="text" id="jsTree_select-tree_selected_result" value='["-1"]'/>-->

<?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>