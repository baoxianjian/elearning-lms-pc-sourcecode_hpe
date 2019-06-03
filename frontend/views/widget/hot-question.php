<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:31
 */
use yii\helpers\Html;

?>
<div class="panel panel-default finishLearn">
          <div class="panel-heading">
            <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('common','art_hot')?><?=Yii::t('frontend','question')?>
          </div>
          <div class="panel-body">
            <a title="<?=Yii::t('frontend','question_area')?>" href="<?=Yii::$app->urlManager->createUrl('question/index')?>"><img class="introPic" src="/static/frontend/images/quicklink2.jpg" style="width:100%"></a>
            <div class="courseResume">
              <ul>
                  <?php foreach ($data as $k => $v):?>
                    <li>
                     <h5> <a href="<?=Yii::$app->urlManager->createUrl(['question/detail','id'=> "$v[kid]"])?>">[<?=Yii::t('frontend','question')?>]<?= Html::encode(mb_substr($v['title'],0,17,"utf-8"));if(mb_strlen($v['title'],'utf-8')>18) echo "..."?></a><span class="pull-right"><?=Yii::t('frontend','reanswer')?>:<?= $v['answer_num']?></span></h5>
                    </li>
                  <?php endforeach; ?>
              </ul>
            </div>
          </div>
 </div>
