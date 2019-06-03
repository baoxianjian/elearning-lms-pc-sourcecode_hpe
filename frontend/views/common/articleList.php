<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use components\widgets\TLinkPager;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<div class="panel-body">
    <?php if (empty($data) || count($data) == 0): ?>
        <!-- 没有数据时,显示的提醒页面 -->
        <div class="noMessage">
            <div class="noMessageCard">
                <div class="logo pull-left"></div>
                <div class="tips pull-right">
                    <h3><?= Yii::t('frontend', 'unmeet_result') ?></h3>
                    <h4>Sorry, there is no result here now.</h4>
                </div>
            </div>
        </div>
    <?php else : ?>
        <? foreach ($data as $row): ?>
            <h3>
                <a href="<?= Url::toRoute(['information/detail', 'id' => $row['kid']]) ?>"><?= $row['art_title'] ?></a>
<!--                <a class="follow pull-right" href="#" role="button">关注</a> --><a
                    class="pull-right more"
                    href="<?= Url::toRoute(['information/detail', 'id' => $row['kid']]) ?>"
                    role="button"><?= Yii::t('frontend', 'detail_info_text') ?> &raquo;</a>
            </h3>
            <span><?= Yii::t('frontend', 'date_text') ?>:<?= date('Y-m-d', $row['created_at']) ?></span>
            <span><?= Yii::t('frontend', 'from_text') ?>:<?= $row['art_from'] ?></span>
            <span><?= Yii::t('frontend', 'editor_text') ?>:<?= $row['art_author'] ?></span>

            <p><?= $row['art_sub_title'] ?></p>
            <hr/>
        <? endforeach; ?>
        <nav>
            <?php
            echo TLinkPager::widget([
                'id' => 'page',
                'pagination' => $pages,
            ]);
            ?>
        </nav>
    <?php endif; ?>
</div>