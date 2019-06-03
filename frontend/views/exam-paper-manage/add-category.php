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
<script>
$(function(){
	var validation_cat = app.creatFormValidation($("#form_category"));
	
    $("#add-catlog").unbind("click").click(function(e){
        
    	app.alertSmall('#category-dialog');
    	validation_cat.hideAlert($("#category_name"));
        
    });
    $(".save-catlog").click(function(){
        var category_name = $("#category_name").val();
        if (category_name == ""){
            validation_cat.showAlert($("#category_name"),"<?=Yii::t('frontend', 'exam_type_paper_cate_name')?>");
            $("#category_name").focus();
            return false;
        }
        if (app.stringLength(category_name) > 75){
            //app.showMsg('分类名称不能超过25个汉字');
            //app.showMsg('分类名称不能超过25个汉字');
             validation_cat.showAlert($("#category_name"),'<?=Yii::t('frontend', 'exam_paper_cate_name_less25')?>');
            return false;
        }
        var parent_category_id = $("#parent_category_id").val();
       
        $.post("<?=Url::toRoute(['/exam-paper-manage/add-'.$tree_type_code])?>",{id: '<?=$model->kid?>', tree_node_code: '<?=$tree_type_code?>',category_name: category_name, parent_category_id: parent_category_id}, function(data){
            if (data.result == 'success'){
                $("#category_name").val('');
                $("#parent_category_id").val('');
                app.showMsg('<?=Yii::t('frontend', 'exam_done_succeed')?>');
                loadTree();
                app.hideAlert('#category-dialog');
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
<div class="header">
    <button type="button" class="close close-category" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?=Yii::t('frontend', 'exam_add_cate')?></h4>
</div>
<div class="body" style="max-width: 320px; margin: 0 auto; min-height: 200px;">
    <div class="infoBlock">
        <div class="row">
          <form id="form_category" name="form_category">
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-4 control-label"><?=Yii::t('frontend', 'tag_mingcheng')?></label>
                    <div class="col-sm-8">
                        <input type="text" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_type_paper_cate_name')?>" id="category_name" onkeyup="this.value=this.value.replace(/\s+/g,'');" class="form-control" style="width: 100%;" value="<?=$model->category_name?>">
                    </div>
                </div>
            </div>
             </form>
            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm">
                    <label class="col-sm-4 control-label"><?=Yii::t('frontend', 'exam_fujiedian')?></label>
                    <div class="col-sm-8">
                        <div class="btn-group timeScope pull-left"  style="width:100%;">
                            <select class="ui dropdown" id="parent_category_id" style="width: 163px;">
                                <option value=""><?=Yii::t('frontend', 'exam_choose_fujiedian')?></option>
                                <?php
                                if (!empty($catlog['parent'])){
                                    foreach ($catlog['parent'] as $val){
                                        ?>
                                        <option value="<?=$val['kid']?>" data-id="<?=$val['kid']?>" label="<?=$val['category_name']?>" <?=$model->parent_category_id == $val['kid'] ? 'selected' : ''?>></option>
                                        <?php
                                        if (!empty($catlog['sub'][$val['kid']])){
                                            foreach ($catlog['sub'][$val['kid']] as $vo){
                                                ?>
                                                <option value="<?=$vo['kid']?>" data-id="<?=$vo['kid']?>" label="<?=$vo['category_name']?>"  <?=$model->parent_category_id == $vo['kid'] ? 'selected' : ''?>></option>
                                            <?php
                                            }
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="actions" style="text-align: center;">
    <button type="button" class="btn btn-primary save-catlog"><?=Yii::t('common','submit')?></button>
    <button type="button" class="btn btn-default close-category"><?=Yii::t('common','close')?></button>
</div>