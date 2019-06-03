<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21
 * Time: 13:57
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TStringHelper;
use common\models\learning\LnExamination;

?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><span style="text-decoration:underline;" ><?=Html::encode($user->real_name)?>(<?=Html::encode($user->email)?>)</span> <?=Yii::t('frontend', '{value}_exam_record',['value'=>'<span style="text-decoration:underline;" >'.Html::encode($examination->title).'</span>'])?></h4>
</div>
<div class="content">
    <div role="tabpanel" class="tab-pane active">
        <div class="col-sm-12" style="margin:30px 0 0 0;">
                    <table class="table table-bordered table-hover table-striped table-center" id="kr_examination">
                        <tbody>
                        <tr>
                            <td><?=Yii::t('frontend', 'exam_choose_wanchengshijian')?></td>
                            <td><?=Yii::t('frontend', 'exam_yongshi')?></td>
                            <?php
                            if ($examination->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
                            ?>
                            <td><?=Yii::t('frontend', 'exam_score')?></td>
                            <?php
                            }else{
                            ?>
                            <td><?=Yii::t('frontend', 'exam_zhengquelv')?></td>
                            <?php
                            }
                            ?>
                            <td><?=Yii::t('frontend', 'exam_choose_caozuo')?></td>
                        </tr>
                        <?php
                        if (!empty($userResultAll)){
                            foreach ($userResultAll as $item){
                                ?>
                                <tr>
                                    <td class="nowrap"><?=date('Y-m-d H:i', $item['end_at'])?></td>
                                    <td><?=!empty($item['examination_duration']) ? TStringHelper::timeSecondToHMS($item['examination_duration']) : '--'?></td>
                                    <?php
                                    if ($examination->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
                                    ?>
                                    <td><?=$item['examination_score'] ? $item['examination_score'] : '--'?></td>
                                    <?php
                                    }else{
                                    ?>
                                    <td><?=sprintf("%.2f", $item['correct_rate']).'%'?>(<?=$item['correct_number']?>/<?=$item['all_number']?>)</td>
                                    <?php
                                    }
                                    ?>
                                    <td>
                                        <a href="<?=Url::toRoute(['/exam/play-view','id' => $item['kid'], 'examination_range' => 'True', 'backBtn' => 'False'])?>" target="_blank" class="log_info"><?=Yii::t('frontend', 'exam_xiangqing')?></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }else{
                            ?>
                            <tr>
                                <td colspan="4"><?=Yii::t('frontend', 'exam_choose_wushuju')?>!</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
    </div>
    <div class="c"></div>
</div>
