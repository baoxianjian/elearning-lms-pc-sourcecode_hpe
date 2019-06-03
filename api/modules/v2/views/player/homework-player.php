<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use components\widgets\TUploadifive;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
?>
<style>
    .uploadifive-button {
        background-color: #505050;
        background-image: -moz-linear-gradient(center bottom , #505050 0%, #707070 100%);
        background-position: center top;
        background-repeat: no-repeat;
        border: 2px solid #808080;
        border-radius: 30px;
        color: #fff;
        font: bold 12px Arial,Helvetica,sans-serif;
        text-align: center;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
        text-transform: uppercase;
        width: 100%;
    }
    #endline{
        float: right !important;
    }
</style>
        <div class="content">
            <div class="header">
                <h4 class="modal-title" id="myModalLabel">提交作业<i id="endline">截止日期:<?=date('Y-m-d',$result['finish_before_at'])?></i></h4>
            </div>
            <div class="modal-body">
                <div class="courseInfo">
                    <div role="tabpanel" class="tab-pane active" id="teacher_info">
                        <div class=" panel-default scoreList">
                            <div class="panel-body">
                                <div class="infoBlock">
                                    <?php if($result['homework_mode']==1||$result['homework_mode']==2){?>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-12 control-label">作业要求</label>
                                                    <div class="col-sm-12">
                                                        <lable><?=$result['requirement']?></lable>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php foreach($teacherfiles as $k=>$v){?>
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-group form-group-sm">
                                                        <label class="col-sm-3 control-label">附件<?=($k+1)?>:</label>
                                                        <a href="<?=$v->file_url?>"><div class="col-sm-9"><?=$v->file_name?></div></a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }?>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-12 control-label">作业内容</label>
                                                    <div class="col-sm-12">
                                                        <textarea <? if(!empty($homeworkresult->homework_result)){ echo "disabled";}?>  placeholder="填写答案"><?=$homeworkresult->homework_result?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }?>
                                    <?php if($result['homework_mode']==0||$result['homework_mode']==2){?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-12 control-label">上传作业</label>
                                                <div class="col-sm-12">
                                                <div id="filelist"></div>
                                                <div id="queue" style="display: none"></div>
                                                <div class="form-control  pull-left"id="queue_list"  style="width:75%"></div>
                                                    <div id="uploadifive-uploadScorm" class="uploadifive-button" style="height: 30px; line-height: 30px; overflow: hidden; position: relative; text-align: center; width: 100px;">
                                                        上传
                                                        <input id="uploadScorm" class="btn btn-default btn-sm pull-right" type="button" style="width: 20%; display: none;">
                                                        <input type="button" style="font-size: 30px; opacity: 0; position: absolute; right: -3px; top: -3px; z-index: 999;" multiple="multiple">
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                    <?php }?>
                                    <?php if($view == 0){?>
                                    <div class="centerBtnArea">
                                        <a href="###" class="btn btn-success centerBtn" style="width:20%;">提交</a>
                                    </div>
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?=html::cssFile('/static/mobile/assets/bootstrap/css/bootstrap.min.css')?>
<?= html::jsFile('/static/app/js/fastclick.js') ?>
<?= html::jsFile('/static/app/js/jquery.min.js') ?>
<?= html::jsFile('/static/app/js/main.js')?>
<?= html::jsFile('/static/common/js/common.js')?>
<link href="/static/frontend/css/elearning.1.css" rel="stylesheet">
<link href="/static/frontend/css/elearning.2.css" rel="stylesheet">
<link href="/static/frontend/css/elearning.3.css" rel="stylesheet">
<script>
    $(document).ready(function() {

        LoadiFramePlayer();
    });

    function change_size(zoom)
    {
        //此方法必须存在，以便play.php调用
        var iframeWindow = $("#iframe-player");
        if (zoom) {
            //alert(zoom);
            iframeWindow.height(750);
        }
        else
        {
            if (navigator.userAgent.indexOf('MSIE') >= 0){
                //alert('你是使用IE')
            }
            else {
                iframeWindow.height(500);
            }
        }
    }


    function LoadiFramePlayer(){
//        alert(compnentCode);
 //       var playZoom = getCookie("play_zoom");
 //       if (playZoom == "0")
 //       {
 //           change_size(true);
  //      }
    }
</script>
