<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/11/15
 * Time: 5:16 PM
 */
use yii\helpers\Html;

?>
<head>
<!--    --><?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<!--    --><?//=Html::jsFile('/static/backend/js/common.js')?>
<!--    --><?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
    <script>

        function modalLoadFrameTest1()
        {
            parent.test();

        }

        function modalLoadFrameTest2(target, url)
        {
//            $('#test', parent.document).click();
//            parent.test();
            if(url){
                $('#'+target, parent.document).find(".modal-body-view").load(url);
            }
//
//           alert($('#'+target, parent.document).find(".modal-body-view").html());
            $('#'+target, parent.document).modal('show');

        }
    </script>
</head>

<?php

echo Html::a('frame-validate-load-parent-function', '#',
    [ 'class'=>'btn btn-default',
        'onclick'=>'modalLoadFrameTest1();'
    ]);


echo Html::a('frame-validate-load-self-function', '#',
    [ 'class'=>'btn btn-default',
        'onclick'=>'modalLoadFrameTest2("editModal","'. Yii::$app->urlManager->createUrl(['demo/jquery-validate']). '");'
    ]);
?>