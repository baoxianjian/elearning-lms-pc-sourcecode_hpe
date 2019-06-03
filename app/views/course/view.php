 <div class="container">
      <div class="row">
          <div>
              <div class="panel panel-default hotNews">
                  <div class="panel-body">
                      <div class="courseTitle">
                          <div class="left"><?= $model->theme ? '<img width="452px" src="'.$model->getCourseCover().'"/>' : ''?></div>
                          <div class="right">
                              <h2><?=$model->course_name?>
                                  <a href="<?=Yii::$app->urlManager->createUrl(['course/play','id'=>$model->kid])?>" class="btn btn-success btn-sm pull-right">开始学习</a>
                              </h2>
                              <table>
                                  <tr>
                                      <td><span><strong>级别:</strong><?=$model->getDictionaryText('course_level',$model->course_level)?></span></td>
                                      <td><span><strong>语言:</strong> <?=$model->getDictionaryText('course_language',$model->course_language)?></span></td>
                                  </tr>
                                  <tr>
                                      <td><span><strong>类型:</strong> <?=$model->course_type=='0'?Yii::t('frontend', 'course_online'): Yii::t('frontend', 'course_face')?></span></td>
                                      <td><span><strong>时长:</strong> <?= round($time / 60,2)?></span></td>
                                  </tr>
                                  <tr>
                                      <td><span><strong>价格:</strong> <?=$model->course_price?></span></td>
                                      <td><span><strong>有效期:</strong><?= $model->start_time ? date("Y-m-d",$model->start_time) : '~~';?>至<?= $model->end_time ? date("Y-m-d",$model->end_time) : '~~';?></span></td>
                                  </tr>
                              </table>
                          </div>
                      </div>
                      <div class="courseInfo">
                          <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                              <li role="presentation" class="active"><a href="#courseIntro" aria-controls="courseIntro" role="tab" data-toggle="tab">课程介绍</a></li>
                              <li role="presentation"><a href="#courseAward" aria-controls="courseAward" role="tab" data-toggle="tab">课程安排</a></li>
                          </ul>
                          <div class="tab-content">
                              <div role="tabpanel" class="tab-pane active" id="courseIntro">
                                  <div class="panel-default scoreList">
                                      <div class="panel-default scoreList pathBlock">
                                          <div role="tab" id="headingOne">
                                              <p>课程介绍:</p>
                                              <p><?= $model['course_desc']?></p>
                                              <hr />
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div role="tabpanel" class="tab-pane" id="courseAward">
                                  <div class=" panel-default scoreList">
                                      <div class="panel-body">
                                          <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                              <div class="panel-body">
                                                  <ul class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" id="collapseExample">
                                                      <? $i = 1;foreach($courseMods as $mod){
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
                                                                      $learned = $i == 1 ? 'learned' : '';
                                                                      ?>
                                                                      <li>
                                                                          <span class="task"><?=$resource->getCoursewareIcon()?></span>
                                                                          <span class="taskName"><i class="unLearn <?=$learned?>"></i><a target="_blank" href="<?=Yii::$app->urlManager->createUrl(['resource/courseware/view','id'=>$resource['kid']])?>" ><?=$resource['courseware_name']?></a></span>
                                                                          <span class="taskTime pull-right"><a href="<?=Yii::$app->urlManager->createUrl(['course/play','id'=>$model->kid,'modid'=>$mod['kid'],'resid'=>$resource['kid']])?>" class="btn btn-success btn-xs btn-start">进入学习</a></span>
                                                                      </li>
                                                                      <?
                                                                      $i++;
                                                                  }?>
                                                              </ul>
                                                          </li>
                                                          <?
                                                          $i++;
                                                      }?>
                                                  </ul>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>