<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/8
 * Time: 11:52
 */
use common\helpers\TStringHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TLinkPager;

$uid = Yii::$app->user->getId();
foreach ($result as $val) {
    ?>
    <div class="answerBlock">
        <div class="answerUser">
            <a href="javascript:;"><img src="<?= TStringHelper::Thumb($val['thumb'],$val['gender']) ?>"></a>
        <span style="text-align:left;"><?=$val['real_name']?>
            <?php
            if ($uid != $val['user_id']) {
                ?><a  class="pull-right member_attention" data-id="<?= $val['user_id'] ?>"><?=$soUserAttention->getAttentionText($val['user_id'])?></a>
                <?php
            }
            ?>
        </span>
        </div>
        <div class="answerDetail">
            <h4><?=$val['title']?></h4>
            <p><?=$val['question_content']?></p>
            <div class="answerInteract">
                <span><a href="#" class="answerComments" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'reanswer') ?>(<code id="answerNum_<?=$val['kid']?>"><?=$val['answer_num']?></code>)</a></span>
                <span class="share_btn" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'share') ?>(<?=$val['share_num']?>)</span>
                <span class="fav_btn" data-id="<?=$val['kid']?>"><?= Yii::t('common', 'collection') ?>(<?=$val['fav_num']?>)</span>
                <span class="attention_btn" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'care_the_question') ?>(<?=$val['attention_num']?>)</span>
                <span role="date">&nbsp;&nbsp;<?= Yii::t('common', 'time') ?>:<i><?=date('Y-m-d H:i', $val['created_at'])?></i></span>
                <?php
                if (!empty($val['tagArr'])) {
                    ?>
                    <span><?= Yii::t('common', 'tag') ?>:
                        <?php
                        foreach ($val['tagArr'] as $vo) {
                            ?>
                            <a class="tags"><?=$vo->tag_value?></a>
                            <?php
                        }
                        ?>
            </span>
                    <?php
                }
                ?>
                <div class="commentInput subcomments hide">
                    <div id="answer-<?=$val['kid']?>"></div>
                 </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<div class="">
    <?php
    echo TLinkPager::widget([
        'id' => 'page',
        'pagination' => $pages,
    ]);
    ?>
</div>
<script>
    $(".pagination").on('click', 'a', function(e){
        e.preventDefault();
        ajaxGet($(this).attr('href'), "question-list");
    });

    function reloadForm(){
        var ajaxUrl = '<?=Url::toRoute(['resource/course/get-scan-question','courseId'=> $courseId])?>';
        var pageSize = $('#pageSizeSelect_grid').val();
        if(typeof pageSize != 'undefined'){
            ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
        }
        ajaxGet(ajaxUrl, "question-list");
    }
    var answerListUrl = '<?=Url::toRoute(['resource/course/get-scan-question-answer'])?>';
    function getAnswerList(answerlistUrl, questionId){
        $.ajax({
            url: answerListUrl,
            type: 'GET',
            data: {questionId: questionId},
            async: false,
            success: function (data) {
                $("#answer-"+questionId).html(data);
            }
        });
    }
    var expandBtn = $(".answerComments");
    expandBtn.bind("click", function() {
        var questionId = $(this).attr('data-id');
        getAnswerList(answerListUrl,questionId);
        var b = $(this).parent();
        var c = $(b).nextAll(".commentInput");
        if (c.hasClass("hide")) {
            c.removeClass("hide")
        } else {
            c.addClass("hide")
        }
    });

</script>
