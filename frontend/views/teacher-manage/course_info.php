<?php


?>


                <div class=" panel-default scoreList">
                  <div class="panel-body">
                    <div class="row">
                    
                    </div>
                    <div class="infoBlock">
                   
                     
                      <table class="table table-bordered table-hover table-striped table-center">
                        <tbody>
                          <tr>
                            <td width="60%"><?= Yii::t('common', 'course_name') ?></td>
                            <td width="30%"><?=Yii::t('frontend', 'start_course_time')?></td>
                            <td width="10%"><?=Yii::t('common', 'status')?></td>
                          </tr>
                        <? foreach ($courses as $cour): ?>
                          <tr>
                            <td style="text-align: left;">[<?if($cour['course_type']=='1'){echo Yii::t('common', 'face_to_face');} else {echo Yii::t('common', 'online') ;}; ?>]<?=$cour['course_name'] ?></td>
                            <td><?=$cour['show_start_time'] ?>-<?=$cour['show_end_time'] ?></td>
                            <td><?=$cour['return_val'] ?></td>
                          </tr>
                        <? endforeach; ?>
                        </tbody>
                      </table>
                     
                   
                    </div>
                  </div>
                </div>
        


