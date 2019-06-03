<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/9/22
 * Time: 10:01
 */
use common\helpers\TStringHelper;
use yii\helpers\Html;

if (!empty($list)) {
    foreach ($list as $item) {
        ?>
        <div class="answerBlock ">
            <div class="answerUser">
                <a href="javascript:;" data-thumb="<?=$item['thumb']?>"><img src="<?= TStringHelper::Thumb($item['thumb'],$item['gender']) ?>" /></a>
                <span><?= $item['real_name'] ?></span>
            </div>
            <div class="answerDetail">
                <p><?= Html::encode($item['comment_content']) ?></p>
            </div>
        </div>
        <?php
    }
}
