<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model common\models\learning\LnComponent */
/* @var $form yii\widgets\ActiveForm */



$range_allow=array(
    'Login'=>                   array(0,1,1,1,1,1),
    'Search'=>                  array(0,1,0,0,0,0),
    'Register-Online-Course'=>  array(0,1,0,0,0,0),
    'Register-Face-Course'=>    array(0,1,0,0,0,0),
    'Open-Shared-Page'=>        array(0,1,0,0,0,0),
    'Open-Shared-Event'=>       array(0,1,0,0,0,0),
    'Open-Shared-Book'=>        array(0,1,0,0,0,0),
    'Download-Page'=>           array(0,1,0,0,0,0),
    'Download-Event'=>          array(0,1,0,0,0,0),
    'Download-Book'=>           array(0,1,0,0,0,0),
    'Download-Experience'=>     array(0,1,0,0,0,0),
    'Complete-Online-Course'=>  array(0,1,0,0,0,0),
    'Complete-F2F-Course'=>     array(0,1,0,0,0,0),
    'Pass-Exam'=>                array(0,1,0,0,0,0),
    'Complete-Investigation'=>  array(0,1,0,0,0,0),
    'Complete-Questionare'=>    array(0,1,0,0,0,0),
    'Get-Certification'=>       array(0,1,0,0,0,0),
    'Revoke-Certification'=>       array(0,1,0,0,0,0),
    'Complete-Self-Info'=>      array(0,1,0,0,0,0),
    'Attention-Question'=>      array(0,1,0,0,0,0),
    'Attention-People'=>        array(0,1,0,0,0,0),
    'Collect-Course'=>          array(0,1,0,0,0,0),
    'Collect-Question'=>        array(0,1,0,0,0,0),
    'Mark-Course'=>             array(0,1,0,0,0,0),
    'Comment-Course-Question'=> array(1,1,1,1,1,1),
    'Comment-Common-Question'=> array(1,1,1,1,1,1),
    'Reply-Course-Question'=>   array(1,1,1,1,1,1),
    'Reply-Common-Question'=>   array(1,1,1,1,1,1),
    'Publish-Page'=>            array(1,1,1,1,1,1),
    'Publish-Event'=>           array(1,1,1,1,1,1),
    'Publish-Book'=>            array(1,1,1,1,1,1),
    'Publish-Sharing'=>         array(1,1,1,1,1,1),
)
?>


<script type="text/javascript">
    $(document).ready(function(){
//        $("#clientform").submit(function() {
//            var url = $("#clientform").attr("action");
//            var method = $("#clientform").attr("method");
//            //alert(url);
//            $.ajax({
//                url: url,
//                cache: true,
//                type: method,
//                dataType: 'json',
//                data: null,
//                async: false,
//                success: function(data)
//                {
//                    alert(data.result); // show response from the php script.
//                }
//            });
//            return false;
//        });
    });


    var operation = '';

    var formId = "clientform-"+"<?=$formType?>";

    /**/
    $("#"+formId).on("submit", function(event) {
        return false;
        /*
        event.preventDefault();
        var validateResult = $("#"+formId).data('yiiActiveForm').validated;
        if (validateResult == true) {
           // alert("validateResult:" + validateResult);

            if (operation == 'savecontinue')
            {
                submitModalForm("",formId,"addModal",false,true);
            }
            else if (operation == 'saveclose')
            {
                submitModalForm("",formId,"addModal",true,true);
            }
            else if (operation == 'update')
            {
                submitModalForm("",formId,"updateModal",true,true);
            }
        }
        */

    });


    function FormSubmit()
    {
        var score_val=$("#standard_value").val();
        var range_val=$("#sel_range_val").val();

        var url = "<?=Url::toRoute(['point/update','id'=>$model->kid])?>";
        $.post(url, {cr:range_val,sv:score_val},function (data) {
            var result = data.result;
            if(data.result!='success')
            {
                showNotyMsg(data.msg);
            }
            else {
                modalClose('updateModal');
                reloadForm();
            }
        }, "json");
    }


    function replaceToFloat(o,accuracy)
    {     
        var regu = "^[\\\+\\\-]?[0-9]{0,}(\\.)?[0-9]{0,"+accuracy+"}$";
        if(accuracy==0)
        {
            regu= "^[\\\+\\\-]?[0-9]{0,}$";
        }
        
        var re = new RegExp(regu);
        while(!re.test(o.value) && o.value!='')
        {
            o.value=o.value.substring(0,o.value.length-1);
        }
    }



</script>

<div class="clientform-div">

    <?php $form = ActiveForm::begin([
        'id'=>'clientform-'. $formType,
    ]); ?>
    <input type="hidden" id="h_kid" value="<?=$model['kid']?>" />
    <table width="100%" border="0">
        <tr>
            <td>
                <div class="form-group field-fwtreenode-tree_node_name required">
                    <label class="control-label"><?=Yii::t('common','point_code')?></label>
                    ：<?=$model['point_code']?>
                </div>
            </td>
            <td>
                <div class="form-group field-fwtreenode-tree_node_name required">
                    <label class="control-label"><?=Yii::t('common','point_name')?></label>
                    ：<?=$model['point_name']?>
                </div>
            </td>
        <tr>
        <tr>
            <td colspan="2">
                <div class="form-group field-fwtreenode-tree_node_name required">
                    <label class="control-label" for="fwtreenode-tree_node_name"><?=Yii::t('common','standard_value')?></label>
                    <input type="text" id="standard_value" class="form-control" onkeyup="replaceToFloat(this,0);" name="standard_value" value="<?=$model['point_op'].$model['standard_value']?>" maxlength="10">
                    <div class="help-block"></div>
                </div>
            </td>
        <tr>
        </tr>
            <td colspan="2">
                <div class="form-group field-fwtreenode-tree_node_code required">
                    <label class="control-label" ><?=Yii::t('common','cycle_range')?></label>
                        <select class="form-control" id="sel_range_val">
                            <?php foreach ($cycleRanges as $k2=>$v2) {if($range_allow[$model['point_code']][$k2]){
                                ?><option <?=$cycleRangeSel[$k2]?> value="<?=$k2?>"><?=$v2?></option><?php }}?>
                        </select>
                </div>



            </td>
        </tr>
    </table>
    <?php ActiveForm::end(); ?>
</div>
