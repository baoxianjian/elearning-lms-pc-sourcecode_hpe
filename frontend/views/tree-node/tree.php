<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 11:36 AM
 */
use components\widgets\TJsTree;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
//如果有传入ListRoute参数就用ListRoute作为获取数据的url页面，没有传入则用默认参数
//$ListRoute = $ListRoute ? $ListRoute : $ContentName.'/list';
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
    $mergeRoot = "False";

if (!isset($openAllNode))
    $openAllNode = "False";
?>


<div style="text-align: left;width: 100px;">

    <?=
    TJsTree::widget([
        'id' => 'tree',
        'treeFlag'=> isset($treeFlag) ? $treeFlag : null,
        'needRegister'=> isset($needRegister) ? $needRegister : null,
        'core' => [
            'check_callback' => true,
            'state' => [ 'key' => 'common' ],
            'data' => [
                'url' =>  Url::toRoute(['tree-node/tree-data']),
                'data' => new JsExpression(
                    "function (node) {
                        return {
                            'ID' : node.id ,
                            'TreeType': $('#jsTree_tree_type".$suffix ."').val(),
                            'ShowAll' : 'N',
                            'IncludeRoot' : '".$includeRoot ."',
                            'companyId' : '".$companyId ."',
                            'MergeRoot' : '".$mergeRoot ."',
                            'ShowContentCount' : '".$showContentCount ."',
                            'OpenAllNode' : '".$openAllNode ."',
                            'DeleteNode' : '".$DeleteNode ."',
                            'EditNode' : '".$EditNode ."',
                            'ListRouteParams' : '".$ListRouteParams."'
                        };
                    }"
                ),
            ]

        ],
        'selectNodeAction' =>  isset($ListRoute) ? (is_array($ListRoute) ? Url::toRoute($ListRoute): Url::toRoute([$ListRoute])) : null
//        'plugins' => ['types', 'dnd', 'contextmenu', 'wholerow', 'state'],
    ]);
    ?>
</div>


    <!-- 树类型-->
<?= Html::hiddenInput("jsTree_tree_type".$suffix,$TreeType,['id'=>'jsTree_tree_type'.$suffix])?>

    <!-- 树勾选项目，多选时用-->
<?= Html::hiddenInput("jsTree_tree_changed_result".$suffix,'',['id'=>'jsTree_select-tree_changed_result'.$suffix])?>

    <!-- 树加载项目-->
<?= Html::hiddenInput("jsTree_tree_loaded_result".$suffix,'',['id'=>'jsTree_select-tree_loaded_result'.$suffix])?>

    <!-- 树点选项目，单选时用-->
<?= Html::hiddenInput("jsTree_tree_selected_result".$suffix,'["-1"]',['id'=>'jsTree_tree_selected_result'.$suffix])?>


<?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
