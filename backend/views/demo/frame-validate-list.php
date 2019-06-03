<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/11/15
 * Time: 4:51 PM
 */
use components\widgets\TModal;
use yii\helpers\Html;

?>

    <script>

        function test()
        {
//            alert('test');
//            $('#test').click();

            modalLoadTest('editModal','<?=Yii::$app->urlManager->createUrl(['tree-type/create'])?>');
        }

        function modalLoadTest(target, url)
        {

            if(url){
                $('#'+target).find(".modal-body-view").load(url);
                $('#'+target).modal('show');
            }


        }

        function submitForm()
        {
//        alert($('#clientform').html());
            $('#clientform').submit();

        }
    </script>

<iframe src="<?= Yii::$app->urlManager->createUrl(['demo/frame-validate-detail'])?>"
        name="listFrame" id="listFrame" width="100%"
        frameborder="1" scrolling="auto"></iframe>


<?php

echo Html::a('jquery-validate', '#',
    [ 'class'=>'btn btn-default',
//        'data-toggle'=>'modal',
//        'data-target'=>'#editModal',
        'onclick'=>'modalLoadTest("editModal","'. Yii::$app->urlManager->createUrl(['demo/jquery-validate']).'");'
    ]);


echo Html::a('tree-type', '#',
    [ 'id'=>'test', 'class'=>'btn btn-default',
//        'data-toggle'=>'modal',
//        'data-target'=>'#editModal',
        'onclick'=>'modalLoadTest("editModal","'. Yii::$app->urlManager->createUrl(['tree-type/create']).'");'
    ]);
?>

<?php
TModal::begin([
    'id'=>'editModal',
    'header' => '<h2>'.Yii::t('backend', 'add_{value}',['value'=>Yii::t('backend','tree_node')]).'</h2>',
    'footer' =>
        Html::button(Yii::t('common', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal'])
        . '' .
        Html::button(Yii::t('common', 'SaveChangeButton'),
            ['id'=>'saveBtn','class'=>'btn btn-primary','onclick'=>'submitForm();']),
]);
?>

<div class="modal-body-view">
</div>

<?php TModal::end(); ?>