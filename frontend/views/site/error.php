<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

?>
<div class="headBanner4"></div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="panel panel-default hotNews topBordered" style="min-height: 400px; margin:40px 0">
                    <div class="panel-body">
                        <div class="row" style=" width: 1000px; margin: 50px auto; ">
                            <div class="col-xs-2" style="text-align: center; font-size: 6rem; padding-top: 30px; ">
                                <i class="glyphicon glyphicon-alert" style="color: #0197d6;"></i>
                            </div>
                            <div class="col-xs-10" style="padding-left: 30px; ">
                                <h2><?= Html::encode($name) ?></h2>
                                <p><?=Yii::t('common','error_message')?>：<?= nl2br(Html::encode($message)) ?></p>
                                <p><?= nl2br(Html::encode($exception)) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>