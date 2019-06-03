<?php

use yii\helpers\Html;


?>
<head>
    <?=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
</head>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="panel panel-default hotNews topBordered" style="min-height: 400px; margin:40px 0">
                    <div class="panel-body">
                        <div class="row" style=" width: 500px; margin: 100px auto; ">
                            <div class="col-xs-4" style=" text-align: center; font-size: 6rem; padding-top: 30px; ">
                                <i class="glyphicon glyphicon-alert" style="color: #0197d6;"></i>
                            </div>
                            <div class="col-xs-8">
                                <h2><?=Yii::t('common','authority_error')?></h2>
                                <p><?=Yii::t('common','error_message')?>：<?=Yii::t('common','no-authority')?></p>
                                <p><?=Yii::t('common','contact-administrator')?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


