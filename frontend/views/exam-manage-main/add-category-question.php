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
<div class="header">
    <button type="button" class="close close-category" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?=Yii::t('frontend', 'exam_xintiku')?></h4>
</div>
<div class="content" style="margin: 0 auto; min-height: 200px;">
    <div class="infoBlock">
        <div class="row">
            <form id="form_category" name="form_category">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'tag_mingcheng')?>:</label>
                    <div class="col-sm-9">
                        <input type="text" id="category_name" onkeyup="this.value=this.value.replace(/\s+/g,'');" class="form-control" style="width: 100%;" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_type_xintikumingcheng')?>" value="<?=$model->category_name?>">
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-3 control-label"><?= Yii::t('common', 'description') ?>:</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" style="width: 100%;" id="description"><?=$model->description?></textarea>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
<div class="actions" style="text-align: center;">
    <button type="button" class="btn btn-primary save-catlog"><?=Yii::t('common','save')?></button>
</div>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script>
    $(function(){
        var validation_cat = app.creatFormValidation($("#form_category"));
        $(".save-catlog").click(function(){
            var category_name = $("#category_name").val().replace(/(^\s*)|(\s*$)/g,'');
            if (category_name == ""){
                //app.showMsg('请输入题库名称','center');
                validation_cat.showAlert($("#category_name"));
                $("#category_name").focus();
                return false;
            }
            if (app.stringLength(category_name) > 75){
                //app.showMsg('题库名称不能超过25个汉字');
                validation_cat.showAlert($("#category_name"),'<?=Yii::t('frontend', 'exam_err_tikumingcheng_more')?>');
                return false;
            }
            /*关键词查询*/
            var error = 0;
            var xss_category_name = filterXSS(category_name);
            if (category_name != xss_category_name){
                error ++;
                $("#category_name").focus();
                validation_cat.showAlert($("#category_name"),'<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common', 'name')])?>');
                return false;
            }
            if (error > 0) return false;
            var description = $("#description").val().replace(/(^\s*)|(\s*$)/g,'');
            var xss_description = filterXSS(description);
            if (description != xss_description){
                error ++;
                $("#description").focus();
                app.showMsg('<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common', 'description')])?>');
                return false;
            }
            if (error > 0) return false;
            $.post("<?=Url::toRoute(['/exam-manage-main/save-examination-question-category'])?>",{tree_node_code: 'examination-question-category',category_id: '<?=$model->kid?>', category_name: category_name, description: description, edit: '<?=$edit?>'}, function(data){
                if (data.result == 'success'){
                    $("#category_name").val('');
                    $("#description").val('');
                    app.showMsg('<?=Yii::t('frontend', 'exam_opt_succeed')?>');
                    loadTree();
                    <?php
                    if (!empty($model->kid)){
                    ?>
                    app.hideAlert('#category-dialog');
                    <?php
                    }else{
                    ?>
                    app.hideAlert('#category-dialog');
                    <?php
                    }
                    ?>
                }else if (data.result == 'repeat') {
                    app.showMsg(data.errmsg);
                    return false;
                }else{
                    app.showMsg('<?=Yii::t('frontend', 'exam_opt_failed')?>');
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