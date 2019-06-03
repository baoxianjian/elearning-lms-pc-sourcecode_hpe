<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertificationTemplate */

?>
<div class="eln-certificaiton-template-view">

    <table width="100%" border="0" class="table table-striped table-bordered">
        <tr>
            <th>
                <?= Yii::t('common','template_code');?>
            </th>
            <td colspan="3">
                <?= $model->template_code ?>
            </td>
            <th>
                <?= Yii::t('common','template_name');?>
            </th>
            <td  colspan="3">
                <?= $model->template_name ?>
            </td>
            <th>
                <?= Yii::t('common', 'share_flag');?>
            </th>
            <td>
                <?= $model->getShareFlagText() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]);?>
            </th>
            <td colspan="7">
                <?= Html::encode($model->getCompanyName()) ?>
            </td>

            <th>
                <?= Yii::t('common', 'sequence_number');?>
            </th>
            <td>
                <?= $model->sequence_number ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common','certification_img_url');?>
            </th>
            <td colspan="9">
                <a href="<?= Yii::$app->urlManager->createUrl(['certification-template/preview','id'=>$model->kid]) ?>" target="_blank">
                    <img id="imgTemplateUrl" width="280" height="195" src="<?=$model->file_path . "preview.png" ?>" alt=""/>
                </a>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'print_type');?>
            </th>
            <td>
                <?= $model->getPrintTypeName() ?>
            </td>
            <th>
                <?= Yii::t('common', 'is_auto_certify');?>
            </th>
            <td>
                <?= $model->getIsAutoCertifyName() ?>
            </td>
            <th>
                <?= Yii::t('common', 'is_print_score');?>
            </th>
            <td>
                <?= $model->getIsPrintScoreName() ?>
            </td>
            <th>
                <?= Yii::t('common', 'is_email_user');?>
            </th>
            <td>
                <?= $model->getIsEmailUserName() ?>
            </td>
            <th>
                <?= Yii::t('common', 'is_email_teacher');?>
            </th>
            <td>
                <?= $model->getIsEmailTeacherName() ?>
            </td>
        </tr>
        <tr>

            <th>
                <?= Yii::t('common', 'is_display_certify_date');?>
            </th>
            <td>
                <?= $model->getIsDisplayCertifyDateName() ?>
            </td>
            <th>
                <?= Yii::t('common','print_orientation');?>
            </th>
            <td>
                <?= $model->getPrintOrientationName() ?>
            </td>
            <th>
                <?= Yii::t('common','status');?>
            </th>
            <td>
                <?= $model->getStatusText() ?>
            </td>
            <th>
<!--                --><?//= Yii::t('common', 'seal_top');?>
            </th>
            <td>
<!--                --><?//= $model->seal_top ?>
            </td>
            <th>
<!--                --><?//= Yii::t('common', 'seal_left');?>
            </th>
            <td>
<!--                --><?//= $model->seal_left ?>
            </td>

        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'certification_display_name');?>
            </th>
            <td colspan="9">
                <?= $model->certification_display_name ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'description');?>
            </th>
            <td colspan="9">
                <?= $model->description ?>
            </td>
        </tr>
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certification_name_top');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certification_name_top ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certification_name_left');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certification_name_left ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certification_name_size');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certification_name_size ?>
<!--            </td>-->
<!---->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certification_name_color');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certification_name_color ?>
<!--            </td>-->
<!--            <th>-->
<!--                &nbsp;-->
<!--            </th>-->
<!--            <td>-->
<!--                &nbsp;-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certification_name_font');?>
<!--            </th>-->
<!--            <td colspan="9">-->
<!--                --><?//= $model->certification_name_font ?>
<!--            </td>-->
<!--        </tr>-->
<!---->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'name_top');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->name_top ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'name_left');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->name_left ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'name_size');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->name_size ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'name_color');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->name_color ?>
<!--            </td>-->
<!--            <th>-->
<!--                &nbsp;-->
<!--            </th>-->
<!--            <td>-->
<!--                &nbsp;-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'name_font');?>
<!--            </th>-->
<!--            <td colspan="9">-->
<!--                --><?//= $model->name_font ?>
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'serial_number_top');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->serial_number_top ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'serial_number_left');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->serial_number_left ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'serial_number_size');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->serial_number_size ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'serial_number_color');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->serial_number_color ?>
<!--            </td>-->
<!--            <th>-->
<!--                &nbsp;-->
<!--            </th>-->
<!--            <td>-->
<!--                &nbsp;-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'serial_number_font');?>
<!--            </th>-->
<!--            <td colspan="9">-->
<!--                --><?//= $model->serial_number_font ?>
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'score_top');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->score_top ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'score_left');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->score_left ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'score_size');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->score_size ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'score_color');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->score_color ?>
<!--            </td>-->
<!--            <th>-->
<!--                &nbsp;-->
<!--            </th>-->
<!--            <td>-->
<!--                &nbsp;-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'score_font');?>
<!--            </th>-->
<!--            <td colspan="9">-->
<!--                --><?//= $model->score_font ?>
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certify_date_top');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certify_date_top ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certify_date_left');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certify_date_left ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certify_date_size');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certify_date_size ?>
<!--            </td>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certify_date_color');?>
<!--            </th>-->
<!--            <td>-->
<!--                --><?//= $model->certify_date_color ?>
<!--            </td>-->
<!--            <th>-->
<!--                &nbsp;-->
<!--            </th>-->
<!--            <td>-->
<!--                &nbsp;-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <th>-->
<!--                --><?//= Yii::t('common', 'certify_date_font');?>
<!--            </th>-->
<!--            <td colspan="9">-->
<!--                --><?//= $model->certify_date_font ?>
<!--            </td>-->
<!--        </tr>-->
    </table>



</div>
