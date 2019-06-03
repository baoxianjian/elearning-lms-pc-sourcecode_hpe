<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/20
 * Time: 11:30
 */
use components\widgets\TLinkPager;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use common\models\message\MsTask;
?>

 <style>
<!--
table#table_body td{
    overflow: hidden;
    max-width: 100px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

-->
</style>      
 <table class="table table-bordered table-hover table-center">
                    <tbody>
                      <tr>
                        <td><?= Yii::t('common', 'real_name') ?></td>
                        <td><?= Yii::t('frontend', 'position') ?></td>
                        <td><?= Yii::t('frontend', 'organization_department') ?></td>
                        <td><?= Yii::t('frontend', 'certifi_issued_at') ?></td>
                          <td><?= Yii::t('frontend', 'vender_status') ?></td>
                        <td><?= Yii::t('frontend', 'certifi_from') ?></td>
                        <td><?= Yii::t('common', 'action') ?></td>
                      </tr>
                      <? foreach ($data as $pinfo): ?>
                      <tr>
                        <td><?=$pinfo['real_name'] ?></td>
                        <td><span class="lessWord" style="width: 90%"><?=$pinfo['position_name'] ?></span></td>
                        <td><?=$pinfo['orgnization_name'] ?></td>
                        <td><?=$pinfo['issued_at'] ?></td>
                         <td><?=$pinfo['valid_status'] ?></td>
                        <td><span class="lessWord" style="width: 90%"><?=$pinfo['certification_from'] ?></span></td>
                      
                        <td><a target="_Blank" href="<?=Yii::$app->urlManager->createUrl(['student/certification-preview'])?>?id=<?=$pinfo['kid'] ?>"><?= Yii::t('frontend', 'detail') ?></a>
                        <a  onclick="canelCertificationUser('<?=$pinfo['kid'] ?>','<?=Yii::$app->urlManager->createUrl(['certification/canel-certification-user'])?>?cuid=<?=$pinfo['kid'] ?>')"><?= Yii::t('common', 'canel_c') ?></a></td>
                      </tr>
                     <? endforeach; ?>
                    </tbody>
                  </table>
<nav>
    <?php
    echo TLinkPager::widget([
        'id' => 'page',
        'pagination' => $pages,
        'options'=>['class'=>'pagination pull-right']
    ]);
    ?>
</nav>
