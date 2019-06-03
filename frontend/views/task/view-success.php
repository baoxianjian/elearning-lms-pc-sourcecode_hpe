<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/23
 * Time: 20:05
 */
use common\helpers\TTimeHelper;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>
<style>
    #pageNumber_page_success{display: none}
    #jumpPageButton_page_success{display: none}
    #pageSizeSelect_page_success{display: none}
</style>
<table class="table table-bordered table-hover table-teacher table-center">
    <tbody>
    <tr>
        <td><?= Yii::t('common', 'real_name') ?></td>
        <td><?= Yii::t('frontend', 'department') ?></td>
        <td><?=Yii::t('common', 'complete_end_at')?></td>
    </tr>
    <? foreach($data as $user):?>
        <tr>
            <td><?= Html::encode($user->object_name) ?></td>
            <td><?= Html::encode($user->org_name) ?></td>
            <td><?= TTimeHelper::toDateTime($user->created_at) ?></td>
        </tr>
    <? endforeach;?>
    </tbody>
</table>
<div>
<nav>
    <?php
    echo TLinkPager::widget([
        'id' => 'page_success',
        'pagination' => $page,
        'options'=>['style'=>'width:100%','class'=>'pagination']
    ]);
    ?>
</nav></div>
