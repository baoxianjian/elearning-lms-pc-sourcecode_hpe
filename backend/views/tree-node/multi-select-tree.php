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
<!--    --><?//=$treeState?>
    <?=
    TJsTree::widget([
        'id' => 'multi-select-tree',
        'checkbox' => [
            // 禁用级联选中
            'three_state' => (isset($treeState) && $treeState === 'False') ? false : true,
            'cascade' => ['undetermined','up'] //有三个选项，up, down, undetermined; 使用前需要先禁用three_state
        ],
        'needRegister'=> isset($needRegister) ? $needRegister : null,
        'treeFlag'=> isset($treeFlag) ? $treeFlag : null,
        'core' => [
            'check_callback' => true,
            'state' => [ 'key' => 'common' ],
            'data' => [
                'url' =>  (isset($treeDataUrl) && $treeDataUrl != null && $treeDataUrl != '') ? $treeDataUrl : Url::toRoute(['tree-node/tree-data']),
                'data' => new JsExpression(
                    "function (node) {
                        return {
                            'ID' : node.id ,
                            'TreeType': $('#jsTree_multi-select-tree_type".$suffix ."').val(),
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
        'plugins' => ['types', 'state', 'checkbox']
    ]);
    ?>
</div>