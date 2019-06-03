<?php

use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use common\models\learning\LnExamination;
use common\helpers\TStringHelper;
use common\models\learning\LnExaminationResultUser;

$this->params['breadcrumbs'][] = ['label'=>$model->title,'url'=>['/exam/view', 'id' => $model->kid]];
//$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','exam_management'),'url'=>['/exam-manage-main/index']];
$this->params['breadcrumbs'][] = Yii::t('common','examination_view');
$this->params['breadcrumbs'][] = '';

?>
<style>
  @media only screen and (min-width:1024px){
    #kr_examination .nowrap
    {
      white-space: nowrap;
    }
  }
</style>
<div class="container">
    <div class="row">
      <?= TBreadcrumbs::widget([
          'tag' => 'ol',
          'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
      ]) ?>
      <div class="col-md-12 bd">
        <div class="modal-header exam_header">
          <h4 class="modal-title"><?=$model->title?>
            <?php
            if ($paperUser->examination_question_number) {
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;<strong><?=Yii::t('frontend', 'exam_gong')?><?=$paperUser->examination_question_number?><?=Yii::t('frontend', 'exam_ti')?></strong>
            <?php
            }
            ?>
            <?php
            if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
            ?>
            <span class="pull-right"><?=Yii::t('frontend', 'exam_ruchangshijian')?>: <?=date('Y-m-d H:i', $model->start_at)?> - <?=date('Y-m-d H:i', $model->end_at)?></span>
            <?php
            }
            ?>
          </h4>
        </div>
        <div class="modal-body">
          <div class="">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock" style="text-align:center">
                    <h3 class="examDes"><?=$model->pre_description?></h3>
                    <ul class="exam_info">
                      <?php
                      if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
                        if (!empty($model->limit_time)) {
                          ?>
                          <li><?=Yii::t('frontend', 'exam_duration')?>:
                            <?=$model->limit_time?><?=Yii::t('frontend', 'exam_time_m')?>
                          </li>
                          <?php
                        }
                        ?>
                        <li><?=Yii::t('frontend', 'exam_t_times')?>:
                          <?=$model->limit_attempt_number == 0 ? Yii::t('frontend', 'exam_na') : $model->limit_attempt_number . Yii::t('frontend', 'exam_times') ?>
                        </li>
                        <li><?=Yii::t('frontend', 'exam_score_way')?>:
                          <?php
                          if ($model->attempt_strategy== LnExamination::ATTEMPT_STRATEGY_FIRST) {
                            echo Yii::t('frontend', 'exam_first');
                          } else if ($model->attempt_strategy == LnExamination::ATTEMPT_STRATEGY_AVG) {
                            echo Yii::t('frontend', 'exam_ap');
                          } else if ($model->attempt_strategy == LnExamination::ATTEMPT_STRATEGY_LAST) {
                            echo Yii::t('frontend', 'exam_last');
                          } else if ($model->attempt_strategy == LnExamination::ATTEMPT_STRATEGY_TOP) {
                            echo Yii::t('frontend', 'exam_hs');
                          }
                          ?>
                        </li>
                        <?php
                        if (!empty($model->pass_grade)) {
                          ?>
                          <li><?=Yii::t('frontend', 'exam_jige')?>: <?=$model->pass_grade?>%</li>
                          <?php
                        }
                      }else{
                        ?>
                        <p><?=Yii::t('frontend', 'exam_duration')?>: <?=Yii::t('frontend', 'exam_na')?></p>
                        <?php
                      }
                      ?>
                    </ul>
                    <div class="centerBtnArea">
                        <?php
                        if ($validity == 'starting' && $generate){
                          if (!empty($examinationLast)) {
                            ?>
                            <a href="<?= Url::toRoute(['/exam/player', 'id' => $examinationLast->kid]) ?>" class="btn btn-sm btn-success centerBtn" style="width:auto;" id="start_study"><?=Yii::t('frontend', 'exam_start')?></a>
                            <?php
                          }else {
                            ?>
                            <a href="javascript:;" class="btn btn-sm btn-success centerBtn disabled" style="width:auto;"><?=Yii::t('frontend', 'exam_exam_done')?>!</a>
                            <?php
                          }
                        }else if ( $validity == 'no_start' && $generate) {
                        ?>
                          <a href="javascript:;" class="btn btn-sm btn-success centerBtn disabled" style="width: auto;"><?=Yii::t('frontend', 'exam_exam_ba')?>!</a>
                        <?php
                        }else if ( $validity == 'end' && $generate) {
                        ?>
                          <a href="javascript:;" class="btn btn-sm btn-success centerBtn disabled" style="width:auto;"><?=Yii::t('frontend', 'exam_exam_na')?>!</a>
                        <?php
                        }else if (!$generate){
                          ?>
                      <p><?=$errMessage?></p>
                      <?php
                        }
                      ?>
                    </div>
                  </div>
                  <div class="col-sm-12" style="border-top:1px solid #ccc; margin:30px 0 0 0; float:left">
                    <p><?=Yii::t('frontend', 'exam_his_rec')?>
                      <?php
                      if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST && $model->limit_attempt_number > 0) {
                        ?>(<?=!empty($resultUser)?count($resultUser):0?>/<?=$model->limit_attempt_number?>)
                        <?php
                      }
                      ?>
                    </p>
                    <table class="table table-bordered table-hover table-striped table-center" id="kr_examination">
                      <tbody>
                      <tr>
                        <td class="nowrap"><?=Yii::t('frontend', 'exam_comp_time')?></td>
                        <td><?=Yii::t('frontend', 'exam_yongshi')?></td>
                        <?php
                        if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
                          ?>
                          <td><?=Yii::t('frontend', 'exam_score')?></td>
                          <td><?=Yii::t('frontend', 'tag_opt')?></td>
                          <?php
                        }else{
                        ?>
                          <td><?=Yii::t('frontend', 'exam_zhengquelv')?></td>
                        <?php
                        }
                        ?>
                      </tr>
                      <?php
                      if (!empty($resultUser)){
                        foreach ($resultUser as $item){
                          ?>
                          <tr>
                            <td><?=!empty($item['end_at']) ? date('Y-m-d- H:i', $item['end_at']) : "--"?></td>
                            <td><?=!empty($item['examination_duration']) ? TStringHelper::timeSecondToHMS($item['examination_duration']) : '--'?></td>
                            <?php
                            if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
                              ?>
                              <td><?=$item['examination_score'] ? $item['examination_score'] : '--'?></td>
                              <td>
                                <?php
                                if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                                  ?>
                                  <a href="<?=Url::toRoute(['/exam/play-view', 'id' => $item['kid']])?>" class="btn-xs icon iconfont play_view" title="<?=Yii::t('frontend', 'vender_view')?>">&#x1007;</a>
                                  <?php
                                }else{
                                  echo '--';
                                }
                                ?>
                              </td>
                              <?php
                            }else{
                            ?>
                            <td><?=$item['correct_rate'].'%'?>(<?=$item['correct_number']?>/<?=$item['all_number']?>)</td>
                            <?php
                            }
                            ?>
                          </tr>
                          <?php
                        }
                      }else{
                        ?>
                        <tr>
                          <td colspan="<?=$model->examination_mode == LnExamination::EXAMINATION_MODE_TEST?4:3?>"><?=Yii::t('frontend', 'exam_no_data')?></td>
                        </tr>
                        <?php
                      }
                      ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
<script type="text/javascript">
  app.extend('alert');
</script>