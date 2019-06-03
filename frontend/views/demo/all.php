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
<div class="panel panel-default scoreList">
    <div class="panel-body" id="dhome">
        <?php foreach ($data as $row): ?>
            <h3><?= $row['name'] ?> <a class="follow pull-right" href="#" role="button"><?=Yii::t('common', 'attention')?></a> <a
                    class="pull-right more" href="#" role="button"><?= Yii::t('frontend', 'detail') ?> &raquo;</a></h3>
            <span><?= Yii::t('frontend', 'date_text') ?>:2015-01-22</span>
            <span><?= Yii::t('frontend', 'from_text') ?>:学习路径</span>
            <span><?=Yii::t('frontend', 'editor_text')?>:明强</span>

            <p><?= $row['general_description'] ?></p>
            <hr/>
        <?php endforeach; ?>

        <nav>
            <?php
            echo TLinkPager::widget([
                'id' => 'page1',
                'pagination' => $pages,
            ]);
            ?>
        </nav>
    </div>
</div>