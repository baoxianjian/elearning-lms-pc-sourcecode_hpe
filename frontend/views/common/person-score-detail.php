<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/12/4
 * Time: 11:57
 */

use common\models\framework\FwUser;
use common\services\framework\UserService;
use components\widgets\TLinkPager;
use common\services\learning\ResourceCompleteService;
use yii\helpers\Html;

?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?=Html::encode($itemName)?>－<?= Yii::t('frontend', 'learn_statistics') ?></h4>
        </div>
        <div class="modal-body">
            <div class="panel-body">
                <div class="col-md-12 col-sm-12">

                    <table class="table table-bordered table-hover table_teacher">
                        <tbody>
                        <tr>
                            <td><?= Yii::t('common', 'real_name') ?></td>
                            <td><?= Yii::t('frontend', 'top_mail_text') ?></td>
                            <td><?= Yii::t('frontend', 'organization_department') ?></td>
                            <td><?= Yii::t('frontend', 'position') ?></td>
                            <td><?= Yii::t('common', 'status') ?></td>
                        </tr>
                        <?
                        if (!empty($datas)) {
                            ?>
                            <? /** @var FwUser $user */
                            foreach ($datas as $user) {?>
                                <tr>
                                    <td><?=Html::encode($user->real_name)?></td>
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
    </div>
</div>