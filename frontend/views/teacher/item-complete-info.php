<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 9/10/2015
 * Time: 1:12 PM
 */
use common\models\framework\FwUser;
use common\services\framework\UserService;
use components\widgets\TLinkPager;
use common\services\learning\ResourceCompleteService;

?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=$itemName?>－<?= Yii::t('frontend', 'learn_statistics') ?></h4>
</div>
<div class="content">
    <div class="panel-body">
        <div class="col-md-12 col-sm-12">

            <table class="table table-bordered table-hover table_teacher">
                <tbody>
                    <tr>
                        <td><?= Yii::t('common', 'real_name') ?></td>
                        <td><?= Yii::t('frontend', 'top_mail_text') ?></td>
                        <td><?= Yii::t('frontend', 'organization_department') ?></td>
                        <td><?= Yii::t('frontend', 'position') ?></td>
                        <td><?=Yii::t('common', 'status')?></td>
                    </tr>
                    <?
                    if (!empty($datas)) {
                    ?>
                        <? /** @var FwUser $user */
                        foreach ($datas as $user) {?>
                            <tr>
                                <td><?=$user->real_name?></td>
                                <td><?=$user->email?></td>
                                <td><?=$user->getOrgnizationName()?></td>
                                <td>
                                    <? $commonUserService = new UserService();
                                    echo $commonUserService->getPositionListStringByUserId($user->kid);?>
                                </td>
                                <td>
                                    <?
                                    $isResCompleteStr = !empty($user->res_complete_kid) ? Yii::t('frontend', 'complete_status_done'): '<font color="red">'.Yii::t('frontend', 'page_lesson_hot_tab_2').'</font>';
                                    echo $isResCompleteStr;?>
                                </td>
                            </tr>
                        <? }?>
                   <? } else {?>
                    <tr>
                        <td colspan="5"><?= Yii::t('common', 'no_data') ?></td>
                    </tr>
                   <? }?>
                </tbody>
            </table>
            <table width="100%">
                <tr>
                    <td align="right">
                        <?php
                        echo TLinkPager::widget([
                            'id' => 'page-item-complete',
                            'displayPageSizeSelect'=>false,
                            'pagination' => $pages,
                        ]);
                        ?>
                    </td>
                </tr>
           </table>
        </div>
    </div>
</div>