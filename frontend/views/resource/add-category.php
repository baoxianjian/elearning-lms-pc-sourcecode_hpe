<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 15/09/07
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script>
$(function(){
    var validation_cat = app.creatFormValidation($("#form_category"));
    $("#add-catlog").on('click', function(e){
        e.preventDefault();
        app.alertSmall('#category-dialog');
    });
    $(".save-catlog").on('click', function(){
        var category_name = $("#category_name").val().replace(/(^\s*)|(\s*$)/g,'');
        if (category_name == ""){
            //app.showMsg('请输入<?=$title?>名称','center');
            validation_cat.showAlert($("#category_name"));
            $("#category_name").focus();
            return false;
        }
        if (app.stringLength(category_name) > 75){
            //app.showMsg('<?=$title?>名称不能超过25个汉字');
            validation_cat.showAlert($("#category_name"),'<?=$title?><?=Yii::t('frontend', '{value}_limit_25_word',['value'=>Yii::t('common','name')])?>');
            return false;
        }
        var error = 0;
        var xss_category_name = filterXSS(category_name);
        if (category_name != xss_category_name){
            error ++;
            validation_cat.showAlert($("#category_name"),'<?=$title?><?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common','name')])?>');
            return false;
        }
        if (error > 0) return false;
        var parent_category_id = $("#parent_category_id").val();
        $.post("<?=Url::toRoute(['/resource/add-'.$tree_type_code])?>",{id: '<?=$model->kid?>', tree_node_code: '<?=$tree_type_code?>',category_name: category_name, parent_category_id: parent_category_id}, function(data){
            if (data.result == 'success'){
                $("#category_name").val('');
                $("#parent_category_id").val('');
                app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                loadTree();
                app.hideAlert('#category-dialog');
            }else if (data.result == 'reply') {
                app.showMsg(data.errmsg);
                return false;
            }else{
                app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                return false;
            }
        },'json');
    });
    $(".close-category").on('click', function(){
        $("#category_name").val('');
        $("#parent_category_id").val('');
        app.hideAlert('#category-dialog');
    });
});
</script>
    <div class="header">
        <button type="button" class="close close-category" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?=Yii::t('frontend', 'add')?><?=$title?></h4>
    </div>
    <div class="body" style="max-width: 320px; margin: 0 auto; min-height: 200px;">
        <div class="infoBlock">
            <div class="row">
                <form id="form_category" name="form_category">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-4 control-label"><?=Yii::t('common', 'name')?></label>
                        <div class="col-sm-8">
                            <input type="text" id="category_name" onkeyup="this.value=this.value.replace(/\s+/g,'');" class="form-control" style="width: 100%;" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'input_{value}',['value'=>$title])?><?=Yii::t('common', 'name')?>" value="<?=$model->category_name?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-4 control-label"><?=Yii::t('common', 'parent_node')?></label>
                        <div class="col-sm-8">
                            <div class="btn-group timeScope pull-left"  style="width:100%;margin-top:0;">
                                <select class="ui dropdown" id="parent_category_id" style="width: 163px;">
                                    <option value=""><?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common', 'parent_node')])?></option>
                                    <?php
                                    $resourceService = new \common\services\learning\ResourceService();
                                    echo $resourceService->getCategoryTree($catlog, $model->kid, $model->parent_category_id);
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="actions" style="text-align: center;">
        <button type="button" class="btn btn-primary save-catlog"><?=Yii::t('common','submit')?></button>
        <button type="button" class="btn btn-default close-category"><?=Yii::t('common','close')?></button>
    </div>