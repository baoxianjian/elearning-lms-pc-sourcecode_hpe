<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/10
 * Time: 0:54
 */
use common\helpers\TURLHelper;
use yii\helpers\Html;

?>
<div class="panel panel-default examState">
    <div class="panel-body">
        <div class="nameCard">
            <div class="boxHead">
                <div class="col-xs-5">
                    <a href="<?= Yii::$app->urlManager->createUrl('student/person-info') ?>" class="userIcon">
                        <img src="<?= $thumb ?>"/>
                    </a>
                    <h4 style="width: 100%" class="lessWord"><?php //echo Html::encode(Yii::$app->user->identity->real_name) ?></h4>
                </div>
                <div class="col-xs-7 scoreBanner">
                    <p class="scores"><a href="<?= Yii::$app->urlManager->createUrl('student/index')?>"><?=Html::encode(Yii::$app->user->identity->real_name);?></a></p>
                    <!--<p class="level">小学一年级</p>
                    <p class="levelStatu">差129分升级到小学二年级</p>
                    <div class="voteBack"><span class="voteValue" style="width:19.5%;"></span></div>-->
                </div>
            </div>
            <div class="boxBody">
                <div class="info">
                    <span><strong><a href="<?= Yii::$app->urlManager->createUrl(['student/my-course','tab'=>'allLesson']) ?>"><?= $reg_count ?></a></strong><?=Yii::t('frontend','signup_yes')?></span>
                    <span><strong><a href="<?= Yii::$app->urlManager->createUrl(['student/my-course','tab'=>'finished']) ?>"><?= $done_count ?></a></strong><?=Yii::t('frontend','complete_status_done')?></span>
                    <span><strong><a href="<?= Yii::$app->urlManager->createUrl(['student/my-course','tab'=>'unfinished']) ?>"><?= $doing_count ?></a></strong><?=Yii::t('frontend','tab_btn_todo')?></span>
                </div>
                <a href="<?= Yii::$app->urlManager->createUrl('student/person-info') ?>" class="btn btn-default btn-sm pull-right"><?=Yii::t('frontend','person_info')?></a>
                <div class="btn-group dropup pull-left">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle " data-toggle="dropdown" aria-expanded="false">
                        <?=Yii::t('frontend','my_tools')?> <span class="caret"></span>
                    </button>
                    <? if (!empty($menu) && count($menu) > 0) { ?>
                        <ul class="dropdown-menu" role="menu">
                            <?foreach ($menu as $single) {
                                echo "<li><a href=" . Yii::$app->urlManager->createUrl([$single->action_url]) . ">" .Yii::t('data', $single->i18n_flag) . "</a></li>";
                            }?>
                        </ul>
                    <? }?>
                </div>
            </div>
        </div>
    </div>
</div>