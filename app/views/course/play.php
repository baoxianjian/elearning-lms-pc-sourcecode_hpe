<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

$this->pageTitle = '课程播放';
$this->params['breadcrumbs'][] = ['label'=>$model->course_name,'url'=>['view','id'=>$model->kid]];
$this->params['breadcrumbs'][] = $this->pageTitle;


if($modid && !$resid){
    $i = 1;
    foreach($courseMods as $mod){
        if($mod->kid == $modid){
            $current = $i;
            $resource = current($mod['coursewares']);
            break 1;
        }
        $i++;
    }
}else if($modid && $resid){
    $i = 1;
    foreach($courseMods as $mod){
        if($mod->kid == $modid){
            foreach($mod['coursewares'] as $res){
                $current = $i;
                if($res['kid'] == $resid){
                    $resource = $res;
                    break 2;
                }
                $i++;
            }
        }
        $i++;
    }
}else{
    $current = 1;
    $mod = current($courseMods);
    $resource = current($mod['coursewares']);
}

$iframeurl = Yii::$app->urlManager->createUrl(['resource/courseware/view','id'=>$resource['kid']]);

?>
<style>
    .coursePlayList .pathStep .pathTask .task{
        display:inline-block !important;}
</style>
  <div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      <div class="col-md-12 ">
        <div class="panel panel-default hotNews">
          <div class="panel-heading">
            <i class="glyphicon glyphicon-dashboard"></i> 课程播放
          </div>
          <div class="col-md-8">
            <div class="panel-body">
              <h5>完成标准:</h5>
<!--                学习课程达到45分钟以上为完成-->
                <iframe width="100%" height="420px" src="<?=$iframeurl?>" frameborder="0"></iframe>
            </div>
          </div>
          <div class="col-md-4" style="border-left:1px solid #eee;">
            <div class="panel-body coursePlayList" style="padding:0;">
              <ul class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" id="collapseExample">
                  <h4>课程组成</h4>
                  <?
                  $i = 1;
                  foreach($courseMods as $mod){
                      $time = 0;
                      foreach($mod['coursewares'] as $res){
                          $time += $res['courseware_time'];
                      }
                      ?>
                      <li class="pathStep">
                          <span class="step "><?=$mod['mod_name']?></span>
                          <span class="stepTime pull-right">学时：<?= round($time / 60,2)?>小时</span>
                          <ul class="pathTask">
                              <? foreach($mod['coursewares'] as $resource){
                                  $learned = $i == $current ? 'learned' : '';
                                  ?>
                                  <li>
                                      <span class="task"><?=$resource->getCoursewareIcon()?></span>
                                      <span class="taskName"><i class="unLearn <?=$learned?>"></i>
                                          <a href="<?=Yii::$app->urlManager->createUrl(['resource/course/play','id'=>$model->kid,'modid'=>$mod['kid'],'resid'=>$resource['kid']])?>" ><?=$resource['courseware_name']?></a>
                                      </span>
                                  </li>
                              <? $i++; }?>
                          </ul>
                      </li>
                      <?
                  }?>
              </ul>
            </div>
          </div>
          <div class="col-md-12">
            <div class="panel-body" style="width: 100%">
            <h2>课程问答</h2>
            <hr/>
            </div>
          </div>
          <div style="clear:both;"></div>
        </div>
      </div>
    </div>
  </div>