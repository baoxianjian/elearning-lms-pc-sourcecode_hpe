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
?>
    <div class=" panel-default scoreList">
                    <div class="panel-body">
                      <h2></h2>
                      <hr/>
<?php 
if (!empty($result)){
foreach ($result as $val) {
?>
<div class="answerBlock">
    <div class="answerUser">
        <a href="javascript:;"><img src="<?= TStringHelper::Thumb($val['thumb'],$val['gender']) ?>"></a>
        <span style="text-align:left;"><?=Html::encode($val['real_name'])?>
            <?php
            if ($uid != $val['user_id']) {
            ?><a href="javascript:;" class="pull-right member_attention" data-id="<?= $val['user_id'] ?>"><?=$soUserAttention->getAttentionText($val['user_id'])?></a>
            <?php
            }
            ?>
        </span>
    </div>
    <div class="answerDetail">
        <h4><?=$val['title']?></h4>
        <p><?=$val['question_content']?></p>
        <div class="answerInteract">
            <span><a href="javascript:;" class="answerComments" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'reanswer') ?>(<code id="answerNum_<?=$val['kid']?>"><?=$val['answer_num']?></code>)</a></span>
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
                    <a href="<?=Url::toRoute(['/question/index', 'kid' => $vo->kid,'value'=> $vo->tag_value])?>" class="tags"><?=Html::encode($vo->tag_value)?></a>
                    <?php
                    }
                    ?>
            </span>
            <?php
            }
            ?>
            <div class="commentInput subcomments hide">
                <div id="answer-<?=$val['kid']?>"></div>
                <textarea id="answer_<?=$val['kid']?>"></textarea>
                <span><a href="javascript:;" class="btn btn-success pull-right question-answer" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'reanswer') ?></a></span>
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
</div>
</div>
    <script>
    $(".pagination").on('click', 'a', function(e){
        e.preventDefault();
        ajaxGet($(this).attr('href'), "courseAnswer");
    });

    function reloadForm(){
        var ajaxUrl = '<?=Url::toRoute(['resource/course/get-question','courseId'=> $courseId,'preview'=>$preview])?>';
        var pageSize = $('#pageSizeSelect_grid').val();
        if(typeof pageSize != 'undefined'){
            ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
        }
        ajaxGet(ajaxUrl, "question-list");
    }
    var answerListUrl = '<?=Url::toRoute(['resource/course/get-question-answer','preview'=>$preview])?>';
    function getAnswerList(url, questionId){
        $.ajax({
            url: url,
            type: 'GET',
            data: {questionId: questionId},
            async: false,
            success: function (data) {
                $("#answer-"+questionId).html(data);
            }
        });
    }

    <?php
    if (!$preview){
    ?>
    /*问题回答*/
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
    /*课程回答*/
    var answerUrl = '<?=Url::toRoute(['resource/course/reply-question'])?>';
    $(".question-answer").on('click', function(){
        var qid = $(this).attr('data-id');
        var answer_content = $("#answer_"+qid).val();
        if (answer_content == ""){
            $("#answer_"+qid).focus();
            app.showMsg('<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','answer_content')]) ?>');
            return false;
        }
        $.ajax({
            url: answerUrl,
            data: {questionId: qid, answer_content: answer_content},
            dataType: 'json',
            type: 'post',
            success: function(data){
                if (data.result == 'failure'){
                    app.showMsg('<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','answer_content')]) ?>');
                }else{
                    $("#answer_"+qid).val('');
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    getAnswerList(answerListUrl,qid);
                    $("#answerNum_"+qid).html(parseInt($("#answerNum_"+qid).html())+1);
                }
            },
            error: function(){
                app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>.');
            }
        });
    });
    /*关注用户*/
    $(".member_attention").on('click', function(){
       var uid = $(this).attr('data-id');
        var index = $(this).index(".member_attention");
        $.get('<?=Url::toRoute([$this->context->id.'/member-attention'])?>?id='+uid,function(res){
            if (res.result = 'success'){
                $(".member_attention").eq(index).html(res.msg);
            }else{
                app.showMsg('<?= Yii::t('frontend', 'care_failed') ?>');
            }
        },'json');
    });
    <?php
    }
    ?>
</script>
<?php
}else{
?>
    <div class="answerBlock"><?= Yii::t('common', 'no_data') ?>!</div>
<?php
}
?>