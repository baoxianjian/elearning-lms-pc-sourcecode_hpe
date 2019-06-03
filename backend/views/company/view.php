<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompany */

?>

<div class="company-view">
    <table class="table table-striped table-bordered">
        <tr>
            <th width="25%">
                <?= Yii::t('backend','org_certificate_code');?>
            </th>
            <td width="75%">
                <?= $model->org_certificate_code ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','representative');?>
            </th>
            <td>
                <?= $model->representative ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','site_url');?>
            </th>
            <td>
                <?= $model->site_url ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','resource_url');?>
            </th>
            <td>
                <?= $model->resource_url ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','reporting_model');?>
            </th>
            <td>
                <?= $model->getReportingModelName() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','second_level_domain');?>
            </th>
            <td>
                <?= $model->second_level_domain ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','default_portal');?>
            </th>
            <td>
                <?= $model->getDefaultPortalName() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','default_theme');?>
            </th>
            <td>
                <?= $model->getThemeName() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','default_language');?>
            </th>
            <td>
                <?= $model->getLanguageName() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','logo_url');?>
            </th>
            <td>
                <? if (!empty($model->logo_url)) {
                  echo Html::img($model->logo_url,['width'=>'50','height'=>'50']);
                }?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','limited_user_number');?>
            </th>
            <td>
                <?= $model->limited_user_number ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','limited_domain_number');?>
            </th>
            <td>
                <?= $model->limited_domain_number ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','is_self_register');?>
            </th>
            <td>
                <?= $model->getIsSelfRegisterText() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','is_default_company');?>
            </th>
            <td>
                <?= $model->getIsDefaultCompanyText() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('backend','description');?>
            </th>
            <td>
                <?= $model->description ?>
            </td>
        </tr>
    </table>
</div>
