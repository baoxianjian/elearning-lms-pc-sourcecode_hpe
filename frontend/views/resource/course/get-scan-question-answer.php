<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/7/9
 * Time: 13:22
 */
use common\helpers\TStringHelper;
use yii\helpers\Html;
use yii\helpers\Url;
if (!empty($result)){
foreach ($result as $val) {
?>
<div class="answerBlock ">
    <div class="answerUser">
        <a href="javascript:;"><img src="<?= TStringHelper::Thumb($val['thumb'],$val['gender']) ?>"></a>
        <span><?= $val['real_name'] ?></span>
    </div>
    <div class="answerDetail">
        <p><?= $val['answer_content'] ?></p>
    </div>
    <div style="clear:both"><i><?= date('Y-m-d H:i:s', $val['created_at']) ?></i></div>
</div>
<?php
}
}
?>