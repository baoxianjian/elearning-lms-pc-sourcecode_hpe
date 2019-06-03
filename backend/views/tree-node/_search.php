<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

?>
<!--<style>-->
<!--    label{display: none;}-->
<!--</style>-->
<script>

    $("#search").on('click', function(){
//        alert('reset');
        reloadForm();
    });
//    function loadList(){
//        var ajaxUrl = "<?//=Url::toRoute(['tree-node/list','treeNodeKid'=>'-1','treeType'=>$TreeType])?>//";
//        ajaxGet(ajaxUrl, "rightList");
//    }

    $("#reset").on('click', function(){
//        alert('reset');
        clearForm("searchForm");
    });

</script>
<!--<input type="hidden" id="searchUrl" value="--><?//=Url::toRoute(['tree-node/list','treeNodeKid'=>'-1','treeType'=>$treeType])?><!--"/>-->
<div class="list-search-form">
    <?php $form = ActiveForm::begin([
        'id' => 'searchForm',
        'action' => ['list'],
        'method' => 'get',
    ]); ?>
    <?= Html::hiddenInput("treeTypeId",$treeTypeId)?>
    <?= Html::hiddenInput("parentNodeId",$parentNodeId)?>
    <?= Html::hiddenInput("treeType",$treeType)?>
    <?= Html::hiddenInput("treeNodeKid",$parentNodeId)?>
<table>
    <tr>

        <td>
            <?= $form->field($model, 'tree_node_code')
                ->label(Yii::t('backend', '{value}_tree_node_code',['value'=>Yii::t('backend',$TreeTypeCode)]))
                ->textInput() ?>
        </td>
        <td>
            <?= $form->field($model, 'tree_node_name')
                ->label(Yii::t('backend', '{value}_tree_node_name',['value'=>Yii::t('backend',$TreeTypeCode)]))
                ->textInput() ?>
        </td>
        <td>
            <?= $form->field($model, 'status')->dropDownList([
                '1'=>Yii::t('backend','status_normal'),
                '2'=>Yii::t('backend','status_stop')],
                ['prompt'=>Yii::t('backend','all_data')]) ?>
        </td>
        <td>
            <?php if ($includeSubNode == '1') :?>
                <?= Html::checkbox('includeSubNode',true) ?><?= Yii::t('backend','include_sub_node')?>
            <?php else: ?>
                <?= Html::checkbox('includeSubNode',false) ?><?= Yii::t('backend','include_sub_node')?>
            <?php endif ?>
        </td>
        <td>
            <?= Html::button(Yii::t('backend', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
            <?= Html::button(Yii::t('backend', 'reset'), ['class' => 'btn btn-default', 'id'=>'reset']) ?>
        </td>
    </tr>
</table>


    <?php ActiveForm::end(); ?>
</div>

<?//=Html::jsFile('/static/backend/js/jquery.form.js')?>
<?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>