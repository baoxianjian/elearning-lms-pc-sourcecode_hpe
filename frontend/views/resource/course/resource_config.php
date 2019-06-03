<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/14
 * Time: 16:14
 */
use common\models\learning\LnModRes;
?>
<div class="panel-default scoreList">
    <div class="panel-body">
        <table class="table table-bordered table-hover table_teacher" style="margin-top:20px;">
            <tr>
                <td><?= Yii::t('frontend', 'resources_name') ?></td>
                <td><?= Yii::t('frontend', 'score_rule') ?></td>
                <td><?= Yii::t('common', 'complete_rule') ?></td>
                <td><?= Yii::t('frontend', 'course_complete') ?></td>
                <td><?= Yii::t('frontend', 'weight_and_score') ?></td>
            </tr>
            <?php
            if (!empty($list)){
                foreach ($list as $item){
                    if (!empty($item['courseitems'])){
                        foreach ($item['courseitems'] as $val){
//                            var_dump($val['modRes']);
            ?>
            <tr>
                <td align="left"><?=$val['itemName']?></td>
                <td>
                    <div class="form-group" style="margin-bottom:0;">
                        <select class="form-control" style="width:100%;margin:0;">
                            <option value="0"><?= Yii::t('frontend', 'highst_score') ?></option>
                            <option value="3" <?=$val['modRes']->score_strategy==LnModRes::ATTEMPT_STRATEGY_FIRST?'selected':''?>><?= Yii::t('frontend', 'config_first_time') ?></option>
                            <option value="1" <?=$val['modRes']->score_strategy==LnModRes::ATTEMPT_STRATEGY_LAST?'selected':''?>><?= Yii::t('frontend', 'config_last_time') ?></option>
                            <option value="2" <?=$val['modRes']->score_strategy==LnModRes::ATTEMPT_STRATEGY_AVERAGE?'selected':''?>><?= Yii::t('frontend', 'config_every_time') ?></option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="form-group" style="margin-bottom:0;">
                        <select class="form-control" style="width:100%;margin:0;">
                            <option value="0"><?= Yii::t('frontend', 'complete_rule_browse') ?></option>
                            <option value="1"><?= Yii::t('frontend', 'complete_rule_score') ?></option>
                        </select>
                    </div>
                </td>
                <td>
                    <label>
                        <input type="checkbox" value="1" <?=$val['modRes']->direct_complete_course == LnModRes::DIRECT_COMPLETE_COURSE_YES?'checked':''?> />
                    </label>
                </td>
                <td>
                    <input type="text" placeholder="<?= Yii::t('frontend', 'percent') ?>" value="<?=$val['modRes']->score_scale?>">&nbsp; %
                </td>
            </tr>
            <?php
                       }
                    }
                }
            }
            ?>
        </table>
    </div>
</div>
