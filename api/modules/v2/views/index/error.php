<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

?>
<div class="errorInfo">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="100px" align="right">
                <div class="logo pull-left"></div>
            </td>
            <td>
                <div><h1><?= Html::encode($name) ?></h1></div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="alert alert-danger">
                    <?=Yii::t('common','error_message')?>：<?= nl2br(Html::encode($message)) ?>
<!--                    --><?//=$exception->statusCode?>
                </div>
            </td>
        </tr>
        <?php if (YII_DEBUG == true) :?>
        <tr>
            <td colspan="2">
                <div class="alert alert-danger">
                    <?= nl2br(Html::encode($exception)) ?>
                </div>
            </td>
        </tr>
        <?php endif?>
    </table>
</div>