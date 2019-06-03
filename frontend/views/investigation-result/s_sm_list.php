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
<table class="table table-bordered table-hover table-center">
                    <tbody>
                      <tr>
                        <td><?= Yii::t('common', 'real_name') ?></td>
                        <td><?= Yii::t('frontend', 'department') ?></td>
                        <td><?= Yii::t('frontend', 'position') ?></td>
                        <td><?= Yii::t('common', 'examination_submit_at') ?></td>
                        <td><?= Yii::t('frontend', 'result') ?></td>
                      </tr>
                      <? foreach ($data as $pinfo): ?>
                      <tr>
                        <td><?=$pinfo['real_name'] ?></td>
                        <td><?=$pinfo['orgnization_name'] ?></td>
                        <td><?=$pinfo['position_name'] ?></td>
                        <td><?=$pinfo['created_at'] ?></td>                        
                        <td>
                        <? if($pinfo['created_at']!=""){ ?>
                        <a href="###" onclick='view_survey_show("<?=$pinfo['kid'] ?>")' class="btn-xs"><?= Yii::t('common', 'view_button') ?></a>
                         <?} ?>
                        </td>
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
