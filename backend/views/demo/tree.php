<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/5/15
 * Time: 10:32 PM
 */
use components\widgets\TJsTree;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

?>
<?=
TJsTree::widget([
    'name' => 'js_tree',
    'core' => [
        'check_callback' => true,
        'data' => [
            'url' =>  new JsExpression(
                "function (node) { return '" . Yii::$app->urlManager->createAbsoluteUrl(['demo/data']) . "';}"
                ),
            'data' => new JsExpression(
                "function (node) {
                return { 'id' : node.id };}"
            ),
        ],
    'plugins' => ['types', 'dnd', 'contextmenu', 'wholerow', 'state'],
    ]
]);
?>