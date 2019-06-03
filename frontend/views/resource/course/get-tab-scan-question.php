<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/8
 * Time: 11:52
 */
use yii\helpers\Url;
?>
<div id="question-list"></div>
<script>
    var questionUrl = '<?=Url::toRoute(['resource/course/get-scan-question','courseId'=>$model->kid])?>';
    function getQuestionList(url) {
        $.ajax({
            url: url,
            type: 'GET',
            async: false,
            success: function (data) {
                $("#question-list").html(data);
            }
        });
    }
    getQuestionList(questionUrl);

</script>