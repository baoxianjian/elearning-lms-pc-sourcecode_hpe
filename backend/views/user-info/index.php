<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/7/15
 * Time: 11:11 PM
 */

use components\widgets\TGridView;
use components\widgets\TModal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$ContentTanslateName =  Yii::t('backend', 'user_'.$formType) ;

$this->params['breadcrumbs'][] =  $ContentTanslateName;
?>
<head>
    <?=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
    <script>
        $(document).ready(function() {
//            alert('loadList');
            loadList();
        });

        function loadList(){
            var ajaxUrl = "<?=Url::toRoute(['user-info/'.$formType])?>";
            ajaxGet(ajaxUrl, "rightList");
        }


    </script>
</head>

<div id="content-body">
    <div id="rightList"></div>
</div>
