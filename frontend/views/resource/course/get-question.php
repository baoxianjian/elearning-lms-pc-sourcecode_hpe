<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/8
 * Time: 11:52
 */
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TLinkPager;
use common\helpers\TStringHelper;

$uid = Yii::$app->user->getId();
if (!empty($result)){
?>
    <style>
        .answerUser.mini_answerUser span a {
            width: 107px;
            height: 107px;
            top: -112px;
            line-height: 9rem;
        }
        .subcomments .answerUser.mini_answerUser span a {
            width: 61px;
            height: 61px;
            top: -66px;
            line-height: 5rem;
        }
        .pageNumber,.jumpPageButton {display: none;}
    </style>
    <?=$this->render('/common/point-trans')?>
<?php
foreach ($result as $val) {
?>
<div class="answerBlock">
    <div class="answerUser popContainer">
        <a><img src="<?=TStringHelper::Thumb($val['thumb'],$val['gender']) ?>"></a>
        <span><?= Html::encode($val['real_name']) ?></span>
        <?if($uid != $val['user_id']):?>
            <ul class="popPanel">
                <li><a href="javascript:void(0);" class="btn btn-xs" data-id="<?= $val['user_id'] ?>" onclick="memberAttention('<?= $val['user_id'] ?>');"><?=$soUserAttention->getAttentionText($val['user_id'])?></a></li>
                <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$val['user_id']?>')"><?= Yii::t('frontend', 'point_gratuity') ?></a></li>
            </ul>
        <?endif;?>
    </div>

    <div class="answerDetail">
        <h4><?=Html::encode($val['title'])?></h4>
        <p><?=Html::encode($val['question_content'])?></p>
        <div class="answerInteract">
            <span><a href="javascript:;" class="answerComments" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'reanswer') ?>(<font id="answerNum_<?=$val['kid']?>"><?=$val['answer_num']?></font>)</a></span>
            <span><a href="javascript:;" class="share_btn" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'share') ?>(<font><?=$val['share_num']?></font>)</a></span>
            <span><a href="javascript:;" class="fav_btn" data-id="<?=$val['kid']?>"><?= Yii::t('common', 'collection') ?>(<font><?=$val['collect_num']?></font>)</a></span>
            <span><a href="javascript:;" class="attention_btn" data-id="<?=$val['kid']?>"><?= Yii::t('frontend', 'care_the_question') ?>(<font><?=$val['attention_num']?></font>)</a></span>
            <span role="date">&nbsp;&nbsp;<?= Yii::t('common', 'time') ?>：<i><?=date('Y-m-d H:i', $val['created_at'])?></i></span>
            <?php
            if (!empty($val['tagArr'])) {
            ?>
            <span><?= Yii::t('common', 'tag') ?>：
            <?php
            foreach ($val['tagArr'] as $vo) {
            ?>
                <a href="<?=Url::toRoute(['/question/index', 'kid' => $vo->kid,'value'=> $vo->tag_value])?>" class="tags"><?=$vo->tag_value?></a>
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
<!-- 收藏的弹出窗口 -->
<div class="ui modal" id="newFollow">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'prompt') ?></h4>
    </div>
    <div class="body"></div>
</div>
<?php
}
?>
<div class="" style="text-align: right;">
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
        var answer_content = $("#answer_"+qid).val().replace(/\s+/g,'');
        if (answer_content == ""){
            $("#answer_"+qid).focus();
            app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','answer_content')])?>');
            return false;
        }
        $.ajax({
            url: answerUrl,
            data: {questionId: qid, answer_content: answer_content},
            dataType: 'json',
            type: 'post',
            success: function(data){
                if (data.result == 'failure'){
                    app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','answer_content')])?>');
                }else{
                    $("#answer_"+qid).val('');
                    if (checkPointResult(data.pointResult)){
                        //score-Effect(data.score);
                        scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                    }else{
                        app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                    }
                    getAnswerList(answerListUrl,qid);
                    $("#answerNum_"+qid).html(parseInt($("#answerNum_"+qid).html())+1);
                }
            },
            error: function(){
                app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>.');
            }
        });
    });
    /*关注用户*/
    function memberAttention(uid){
        $.post('<?=Url::toRoute([$this->context->id.'/member-attention'])?>?',{uid: uid}, function(res){
            if (res.result == 'success'){
                $(".btn-xs[data-id='"+uid+"']").html(res.msg);
            }else{
                app.showMsg('<?= Yii::t('frontend', 'care_failed') ?>');
            }
        },'json');
    }
    var share_url = '<?=Yii::$app->urlManager->createUrl(['resource/course/share-question'])?>';
    $(".share_btn").click(function(){
        var question_id = $(this).attr('data-id');
        var share_obj = $(this).find('font');
        $.post(share_url, {question_id: question_id}, function(data) {
            if (data.result == 'success'){
                share_obj.html(parseInt(share_obj.html())+1);
                app.showMsg('<?= Yii::t('frontend', 'share_sucess') ?>');
            }else{
                app.showMsg('<?= Yii::t('frontend', 'share_failed') ?>');
            }
        });
    });
    var fav_url = '<?=Yii::$app->urlManager->createUrl(['question/collect'])?>';
    $(".fav_btn").click(function(){
        var question_id = $(this).attr('data-id');
        var fav_obj = $(this).find('font');
        $.post(fav_url, {"qid": question_id}, function(data) {
            if (data.result == 'success') {
                if (data.status == 'success') {
                    fav_obj.html(parseInt(fav_obj.html()) + 1);
                    if (checkPointResult(data.pointResult)){
                        //score-Effect(data.point);
                        scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                    }
                    else {
                        app.showMsg('<?= Yii::t('frontend', 'collection_sucess') ?>');
                    }
                } else if (data.status == 'cancel') {
                    var fav_num = parseInt(fav_obj.html()) - 1;
                    fav_num = fav_num > 0 ? fav_num : 0;
                    fav_obj.html(fav_num);
                    app.showMsg('<?= Yii::t('frontend', 'sucess_to_do_not_collect') ?>');
                }
            } else {
                app.showMsg("<?= Yii::t('frontend', 'collection_failed') ?>");
            }
        });
    });
    var attention_url = '<?=Yii::$app->urlManager->createUrl(['question/care'])?>';
    $(".attention_btn").click(function(){
        var question_id = $(this).attr('data-id');
        var attention_obj = $(this).find('font');
        $.post(attention_url, {qid: question_id}, function(data) {
            if (data.result == 'success') {
                if (data.status == 'success') {
                    attention_obj.html(parseInt(attention_obj.html()) + 1);
                    if (checkPointResult(data.pointResult)){
                        //score-Effect(data.point);
                        scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                    }
                    else {
                        app.showMsg('<?= Yii::t('frontend', 'attention_sucess') ?>');
                    }
                } else if (data.status == 'cancel') {
                    var attention_num = parseInt(attention_obj.html()) - 1;
                    attention_num = attention_num > 0 ? attention_num : 0;
                    attention_obj.html(attention_num);
                    app.showMsg('<?= Yii::t('frontend', 'sucess_to_do_not_followed') ?>');
                }
            } else {
                app.showMsg('<?= Yii::t('frontend', 'care_failed') ?>');
            }
        });
    });
    <?php
    }
    ?>
</script>
<?php
}else{
?>
    <div class="answerBlock"><?= Yii::t('frontend', 'temp_no_data') ?>!</div>
<?php
}
?>