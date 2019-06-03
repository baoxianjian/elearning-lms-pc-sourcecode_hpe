<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/6
 * Time: 20:30
 */
use yii\helpers\Html;

?>
<div class="col-md-8 col-md-offset-2">
    <h3><?=Html::encode($results['title']) ?></h3>
    <p><?= Yii::t('frontend', 'join_member_{value}',['value'=>'<strong class="strong">'.$results['sub_result_arr_num'].'</strong>']) ?>
        <? if($is_course!="yes"){ ?>    <?= Yii::t('common', 'time_validity') ?>：<?=$results['start_at'] ?> - <?=$results['end_at'] ?>
        <?} ?>   </p>
    <hr>
    <? $num=0;  ?>
    <? foreach ($results['question'] as $ques): ?>
        <? if ($ques['question_type']=='0'||$ques['question_type']=='1'){ ?>
            <? $num=$num+1; ?>
            <div class="question_results">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <h4><?=$num ?>.<?=Html::encode($ques['question_title']) ?></h4>
                    </div>
                </div>
                <? foreach ($ques['options'] as $opt): ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-12 control-label"><?=Html::encode($opt['option_title']) ?></label>
                                <div class="col-sm-12">
                                    <div class="col-sm-8 voteBack"><span class="voteValue" style="width:<?=$opt['submit_num_rate'] ?>%;"></span></div>
                                    <div class="col-sm-4 voteNum"><?=$opt['submit_num'] ?>(<?=$opt['submit_num_rate'] ?>%)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        <?  }else if($ques['question_type']=='2'){ ?>
            <? $num=$num+1; ?>
            <div class="question_results">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <h4><?=$num ?>.<?=Html::encode($ques['question_title']) ?></h4>
                    </div>
                </div>
                <? foreach ($ques['options'] as $opt): ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <p><?=Html::encode($opt['option_result']) ?></p>
                            <h6><?=Html::encode($opt['user_id1']) ?> <?=Yii::t('frontend', 'publication_at')?><?=$opt['created_at'] ?></h6>
                        </div>
                    </div>
                <? endforeach; ?>
                <div class="moreDetails hide">
                    <? foreach ($ques['options2'] as $opt): ?>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <p><?=Html::encode($opt['option_result']) ?></p>
                                <h6><?=Html::encode($opt['user_id1']) ?> <?=Yii::t('frontend', 'publication_at')?><?=$opt['created_at'] ?></h6>
                            </div>
                        </div>
                    <? endforeach; ?>
                </div>
                <? if(sizeof($ques['options2'])>0){?>
                    <a href="###" class="moreDetailsBtn pull-right"><?=Yii::t('common', 'menu_collapse')?></a>
                <?} ?>
            </div>
        <? } ?>
    <? endforeach; ?>
</div>
