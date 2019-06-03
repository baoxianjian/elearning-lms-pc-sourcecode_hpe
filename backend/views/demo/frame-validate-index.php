<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/11/15
 * Time: 10:02 PM
 */
use yii\helpers\Html;
use yii\helpers\Url;

?>

<head>

<!--    --><?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
    <?=Html::jsFile('/static/backend/js/common.js')?>
    <script>
        $(document).ready(function() {

            loadList();
        });

        function loadList(){
            var ajaxUrl = "<?=Url::toRoute(['demo/frame-validate-list'])?>";

            ajaxGet(ajaxUrl, "rightList");
        }


    </script>
</head>
    <input type="button" id="reloadtree" onclick="loadList();" value="reloadtree"/>
<div id="rightList"></div>
