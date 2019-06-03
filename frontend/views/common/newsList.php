<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use components\widgets\TLinkPager;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */

?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h5 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'list_of_{value}',['value'=>Yii::t('frontend','top_news_text')]) ?></h5>
        </div>
        <div class="modal-body msgCentent">
            <?php if (empty($data) || count($data) == 0): ?>
                <!-- 没有数据时,显示的提醒页面 -->
                <div class="noMessage" style="zoom:0.7; min-height:500px;">
                    <div class="noMessageCard">
                        <div class="logo pull-left"></div>
                        <div class="tips pull-right">
                            <h3><?= Yii::t('frontend', 'warning_for_nomsg') ?></h3>
                            <h4>Sorry, there is no message here now.</h4>
                        </div>
                    </div>
                </div>
            <?php else : ?>
            <ul>
                <? foreach ($data as $row): ?>
                    <li>
                        <span><img src="/static/frontend/images/userNameCard1.jpeg" alt="" height="48" width="48">&gt;</span>
                        <div class="msRight">
                            <h5><?= $row['title'] ?><i class="glyphicon glyphicon-time"><?= $row['timestamp'] ?></i></h5>
                            <p><?= $row['data'] ?> </p>
                            <strong><?= Yii::t('frontend', 'page_info_author') ?>:<?= $row['name'] ?></strong>
                        </div>
                    </li>
                <? endforeach; ?>
            </ul>
            <? endif;?>
        </div>
        <div class="modal-footer msgfoot">
            <nav>
                <?php
                echo TLinkPager::widget([
                    'id' => 'page',
                    'pagination' => $pages,
                ]);
                ?>
            </nav>
        </div>
    </div>
</div>