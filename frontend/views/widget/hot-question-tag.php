<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:31
 */
use yii\helpers\Html;

?>
<div class="panel panel-default examState">
          <div class="panel-body">
            <div class="nameCard">
              <div class="boxBody">
                <h3><?=Yii::t('common','art_hot')?><?=Yii::t('common','tag')?></h3>
                <div class="tagBall">
                  <div id="myCanvasContainer">
                    <canvas width="300" height="300" id="myCanvas">
                    <p><?=Yii::t('frontend','warning_for_canvas')?></p>
                      <ul>
                        <?php foreach($data as $k => $v):?>
                        <li><a href="#" onclick="tagpage('<?= $v->kid ?>','<?= Html::encode($v->tag_value) ?>')"><?= Html::encode($v->tag_value)?></a></li>
                        <?php endforeach;?>
                      </ul>
                    </canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>
</div>

