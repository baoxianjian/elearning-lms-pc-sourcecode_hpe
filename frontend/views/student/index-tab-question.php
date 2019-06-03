<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 13:28
 */
use yii\helpers\Html;

?>
<style>
    /* 我要提问表单css */
    .field-soquestion-title .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-soquestion-title .form-control:focus{
        border: 1px solid #CCC;
        border-top: 1px solid #66afe9;
        box-shadow:none;
    }
    .field-soquestion-title .has-success .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-soquestion-title .has-success .form-control:focus{
        border: 1px solid #CCC;
        border-top: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-soquestion-title .has-error .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-soquestion-title .has-error .form-control:focus{
        border: 1px solid #CCC;
        border-top: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-soquestion-question_content .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-soquestion-question_content .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        box-shadow:none;
    }
    .field-soquestion-question_content .has-success .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-soquestion-question_content .has-success .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }
    .field-soquestion-question_content .has-error .form-control{
        border: 1px solid #CCC;
        box-shadow:none;
    }
    .field-soquestion-question_content .has-error .form-control:focus{
        border: 1px solid #CCC;
        border-bottom: 1px solid #66afe9;
        -webkit-box-shadow:none;
        box-shadow:none;
    }

    .tiwen {}
    .tiwen .huati{width:35px;}
    .tiwen .tdwrapper,.tiwen .tdwrapper #search_tag{width:160px;}
    .tiwen .mouren{width:50px;}
    .tiwen .tdwrapper,.tiwen .tdwrapper #search_people{width:160px}
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','questions')?></h4>
        </div>
        <div class="modal-body">
            <div role="tabpanel" class="tab-pane active  panel-body shareInput" id="question">
                <form id="questionForm" action="/student/index-tab-question.html" method="post">
                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <h5><?=Yii::t('frontend','question_for_audience')?></h5>
                    <div class="form-group field-soquestion-title required has-error">
                        <input type="text" id="soquestion-title" class="form-control" name="SoQuestion[title]" maxlength="100" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>" style="width:100%;">
                    </div>
                    <div class="form-group field-soquestion-question_content required">
                        <textarea id="soquestion-question_content" class="form-control" name="SoQuestion[question_content]" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','record_content')])?>" style="height:70px;width:100%;"></textarea>
                    </div>
                    <span class="addWidgets">
                        <input type="hidden" id="select_value" name="select_value" />
                        <input type="hidden" id="tags" name="tags" />
                        <?=
                        Html::submitButton(Yii::t('common', 'art_publish'),
                            ['id' => 'shareBtn', 'class' => 'btn btn-success pull-right'])
                        ?>
                    </span>
                </form>
                <table class="tiwen">
                    <tr>
                        <td class="huati" style="text-align:left"><?=Yii::t('frontend','topic')?></td>
                        <td class="tdwrapper" style="text-align:left">
                            <input id="search_tag" style="height:30px" type="text" data-url="<?=Yii::$app->urlManager->createUrl('student/get-tag')?>?format=new" data-mult="1" data-option="1" />
                        </td>
                        <td class="mouren" style="text-align:center">@<?=Yii::t('frontend','some_one')?></td>
                        <td class="tdwrapper" style="width:160px">
                            <input id="search_people" style="height:30px" type="text" data-url="<?=Yii::$app->urlManager->createUrl('common/search-people')?>?format=new" data-mult="1" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    var tag_show = false;
    var tagQueryList = app.queryList("#search_tag");
    var peopleQueryList = app.queryList("#search_people");

    function showTag() {
        if (tag_show) {
            $("#tag_panel").hide();
        }
        else {
            $("#tag_panel").show();
        }
        tag_show = !tag_show;
    }

    $("#questionForm").on("submit", function (event) {
        $("#questionForm #shareBtn").attr({"disabled": "disabled"});

        event.preventDefault();

        var title = $("#soquestion-title").val().trim();
        var content = $("#soquestion-question_content").val().trim();

        if (title == '') {
            $("#questionForm #shareBtn").removeAttr("disabled");
            $("#soquestion-title").focus();
            app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>', 1500);
            return false;
        }
        if (content == '') {
            $("#questionForm #shareBtn").removeAttr("disabled");
            $("#soquestion-question_content").focus();
            app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','record_content')])?>', 1500);
            return false;
        }

        var tags = tagQueryList.get();
        var temp = '';
        for (i = 0; i < tags.length; i++) {
            if (i === 0) {
                temp += tags[i].title;
            }
            else {
                temp += ',' + tags[i].title;
            }
        }

        $("#tags").val(temp);

        var tags = $("#tags").val();

        if (tags == '') {
            $("#questionForm #shareBtn").removeAttr("disabled");
            app.showMsg('<?=Yii::t('frontend','add_{value}',['value'=>Yii::t('frontend','topic')])?>', 1500);
            return false;
        }

        var users = peopleQueryList.get();
        var temp = '';
        for (i = 0; i < users.length; i++) {
            if (i === 0) {
                temp += users[i].kid;
            }
            else {
                temp += '|' + users[i].kid;
            }
        }

        $("#select_value").val(temp);

        submitModalForm("", "questionForm", "", true, false, null, null);
    });
</script>