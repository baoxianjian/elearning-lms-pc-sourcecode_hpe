<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/3
 * Time: 11:01
 */
use yii\helpers\Html;

?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=Yii::t('common', 'from')?><?=$company->company_name?> <?=Yii::t('common', 'copy_course')?>&lt;&lt;<?=$course->course_name?>&gt;&gt;</h4>
</div>
<div class="content">
    <div class="infoBlock">
        <form id="copyForm" method="post" name="copyForm">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('common', 'category_id')?></label>
                        <div class="col-sm-9">
                            <div class="form-group">
                                <select class="form-control" name="category_id" id="copy_category_id">
                                    <option value=""><?=Yii::t('common', 'select_{value}', ['value' => Yii::t('common', 'category_id')])?></option>
                                    <?php
                                    if (!empty($category)){
                                        foreach ($category as $key => $items){
                                    ?>
                                    <option value="<?=$key?>"><?=$items?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('common', 'domain_id')?></label>
                        <div class="col-sm-9">
                            <div class="btn-group" data-toggle="buttons" style="width: 100%;">
                                <?php
                                foreach ($domain as $key=>$val){
                                ?>
                                <label style="width: 49%;"><input type="checkbox" name="domain_id[]" value="<?=$val->kid?>" style="height: auto; width: auto;"> <?= Html::encode($val->domain_name)?></label>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="course_id" value="<?=$course->kid?>" />
            <input type="hidden" name="company_id" value="<?=$company->kid?>" />
        </form>
    </div>
    <div class="c"></div>
    <div class="action centerBtnArea groupAddMember">
        <a href="###" class="btn centerBtn" id="saveCopyCourse"><?=Yii::t('common', 'save')?></a>
        <a href="###" class="btn centerBtn" id="closeCopyOption"><?=Yii::t('common', 'close')?></a>
    </div>
</div>