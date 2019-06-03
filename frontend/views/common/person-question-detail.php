<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/12/4
 * Time: 13:37
 */
use common\models\learning\LnInvestigation;
use common\models\learning\LnInvestigationQuestion;
use yii\helpers\Html;

?>
<?php $temp = 0;?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    <h4 class="modal-title" id="myModalLabel">[
        <?php
        if ($data['answer_type'] === LnInvestigation::ANSWER_TYPE_REALNAME) {
            echo Yii::t('frontend', 'name_real');
        } else {
            echo Yii::t('frontend', 'name_privacy');
        }
        if ($data['investigation_type'] === LnInvestigation::INVESTIGATION_TYPE_SURVEY) {
            echo Yii::t('frontend', 'questionnaire');
        } else {
            echo Yii::t('frontend', 'vote');
        } ?>]<?= $data['title'] ?></h4>
</div>
<div class="content">
    <div class="courseInfo">
        <div role="tabpanel" class="tab-pane active" id="teacher_info">
            <div class=" panel-default scoreList">
                <div class="panel-body">
                    <div class="infoBlock">
                        <div class="row questionGroup_quest">
                            <p></p>
                            <h4><?=Html::encode($data['title'])?></h4>
                            <? if ($data['investigation_type'] === LnInvestigation::INVESTIGATION_TYPE_SURVEY): ?>
                            <p style="text-align:left"><?= Yii::t('frontend', 'brief') ?>: <?=Html::encode($data['description'])?></p>
                            <? endif; ?>
                        </div>
                        <div class="row questionGroup_quest">
                            <?php foreach($data['question'] as $k=>$v):?>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group form-group-sm">
                                        <?php if($v['question_type'] !=3){?>
                                            <label class="col-sm-9 control-label">
                                                [<?php if ($v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_SINGLE) {
                                                    echo Yii::t('common', 'question_radio');
                                                } elseif ($v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE) {
                                                    echo Yii::t('common', 'question_checkbox');
                                                } elseif ($v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_QA) {
                                                    echo Yii::t('frontend', 'question_answer');
                                                } ?>]<?= Yii::t('frontend', 'question') ?><?=$k+1-$temp?>:<?=Html::encode($v['question_title'])?>
                                            </label>
                                        <?php }else{$temp++;}?>
                                        <div class="col-sm-3">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <?php  if ($v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_SINGLE || $v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE){?>
                                        <?php foreach($v['option']as $key=>$value): $arr = 1000000;?>
                                            <div class="pull-left" style="padding-left: 15px;">
                                                <label style="margin-right:40px;">
                                                    <?php foreach($resultdata[$v[kid]]['sequence_number'] as $ke=>$val){ ?>
                                                        <?php if($val==$value['sequence_number']){?>
                                                            <?php $arr=$key;?>
                                                            <?php if($v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE){ ?>
                                                                <input type="checkbox" checked="checked"  value="1" disabled>
                                                            <?php }else{?>
                                                                <input type="radio" checked="checked"  value="1" disabled>
                                                            <?php }?>
                                                        <?php  }?>
                                                    <?php }?>
                                                    <?php if($key!=$arr){?>
                                                        <?php if($v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_MULTIPLE){ ?>
                                                            <input type="checkbox"  value="1" disabled>
                                                        <?php }else{?>
                                                            <input type="radio"  value="1" disabled>
                                                        <?php }?>
                                                    <?php }?>
                                                    <?=Html::encode($value['option_title'])?>
                                                </label>
                                            </div>
                                        <?php endforeach;?>
                                    <?php }elseif($v['question_type'] === LnInvestigationQuestion::QUESTION_TYPE_QA){?>
                                        <textarea readonly="readonly" placeholder=""><?=$resultdata[$v[kid]]['option_result']?></textarea>
                                    <?php }?>
                                </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12 centerBtnArea">
                    <a href="#" class="btn btn-default btn-sm centerBtn btnaddNewChoice" id="detailrenturn" onclick="app.hideAlert('#view_mod_detail');return false;" style="width:30%"><?= Yii::t('common', 'back_button') ?></a>
                </div>
            </div>
        </div>
    </div>
    <div class="c"></div>
</div>