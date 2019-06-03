<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/11/15
 * Time: 4:45 PM
 */
use components\widgets\TModal;
use yii\helpers\Html;

?>

<link rel="stylesheet" href="/components/kindeditor/plugins/code/prettify.css" />
<link rel="stylesheet" href="/components/kindeditor/themes/default/default.css" />
<script src="/components/kindeditor/plugins/code/prettify.js"></script>
<script src="/components/kindeditor/kindeditor-all-min.js"></script>
<script>
   KindEditor.ready(function() {
        prettyPrint();
    });

    function submitForm()
    {
        $('#clientform').submit();

    }
</script>


<?php

echo Html::a('jquery-validate', '#',
    [ 'class'=>'btn btn-default',
//        'data-toggle'=>'modal',
//        'data-target'=>'#editModal',
        'onclick'=>'openModalForm("editModal","'. Yii::$app->urlManager->createUrl(['demo/jquery-validate']).'");'
    ]);



echo Html::a('modal-edit', '#',
    [ 'class'=>'btn btn-default',
//'data-toggle'=>'modal',
//'data-target'=>'#editModal',
        'onclick'=>'openModalForm("editModal","'. Yii::$app->urlManager->createUrl(['demo/modal-editor']).'");'
    ]);


echo Html::a('tree-type', '#',
[ 'class'=>'btn btn-default',
//'data-toggle'=>'modal',
//'data-target'=>'#editModal',
'onclick'=>'openModalForm("editModal","'. Yii::$app->urlManager->createUrl(['tree-type/create']).'");'
]);


echo Html::a('kindModal', '#',
    [ 'class'=>'btn btn-default',
'data-toggle'=>'modal',
'data-target'=>'#kindModal',
//        'onclick'=>'openModalForm("editModal","'. Yii::$app->urlManager->createUrl(['demo/modal-editor']).'");'
    ]);

?>

<?php
TModal::begin([
    'id'=>'editModal',
    'header' => '<h2>'.Yii::t('backend', 'edit_{value}',['value'=>Yii::t('backend','page')]).'</h2>',
    'footer' =>
        Html::button(Yii::t('common', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal'])
        . '' .
        Html::button(Yii::t('common', 'save_close_button'),
            ['id'=>'saveBtn','class'=>'btn btn-primary','onclick'=>'submitForm();']),
]);
?>


    <div class="modal-body-view">

    </div>


<?php TModal::end(); ?>




<?php
TModal::begin([
    'id'=>'kindModal',
    'header' => '<h2>'.Yii::t('backend', 'edit_{value}',['value'=>Yii::t('backend','page')]).'</h2>',
    'footer' =>
        Html::button(Yii::t('common', 'close_button'),
            ['id'=>'closeBtn','class'=>'btn btn-default','data-dismiss'=>'modal']),
]);
?>


<div class="modal-body-view">
    <form>
        <textarea name="kindcontent" style="width:500px;height:200px;visibility:hidden;"></textarea>
    </form>
</div>


<?php TModal::end(); ?>




<script>
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('textarea[name="kindcontent"]', {
            allowFileManager : true
        });

    });
</script>
