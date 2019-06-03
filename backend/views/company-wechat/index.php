<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/7/15
 * Time: 11:11 PM
 */

use components\widgets\TGridView;
use components\widgets\TModal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$ContentTanslateName =  Yii::t('common', 'company_webchat_setting') ;

$this->params['breadcrumbs'][] =  $ContentTanslateName;
?>
<head>
    <?=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>

</head>

<div id="content-body">
    <div>
        <label class="control-label"><?=Yii::t('backend','company_id')?></label>&nbsp;&nbsp;<?= Html::DropdownList(
            'companyWechatSelect',
            null,
            ArrayHelper::map($companyList,'kid', 'company_name'),
            array('id' => 'companyWechatSelect',
                'class' => 'BackendSelect',
                'width' => '300px',
                'onchange' => 'loadList(this.value);'));?>
    </div>
    <div id="rightList"></div>
</div>


<script>
    $(document).ready(function() {
//            alert('loadList');
        var companyId = $("#companyWechatSelect").val();
//        alert(companyId);
        if (companyId != "") {
//            alert(companyId);
            loadList(companyId);
        }
    });

    function loadList(companyId){
        var ajaxUrl = "<?=Url::toRoute(['company-wechat/setting'])?>";
        ajaxUrl = urlreplace(ajaxUrl,"companyId",companyId);
        ajaxGet(ajaxUrl, "rightList");
    }


</script>
