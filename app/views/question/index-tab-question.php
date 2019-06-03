<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 13:28
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>

<html lang="<?= Yii::$app->language ?>">

<head>
  <meta charset="<?= Yii::$app->charset ?>"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?= Html::csrfMetaTags() ?>
  <title><?= Html::encode("惠普在线学习平台 提交问题") ?></title>
  <?php $this->head() ?>
  <!-- Bootstrap -->
	<?= html::cssFile('/static/app/css/bootstrap.css') ?>
	<?= html::cssFile('/static/app/css/index.css') ?>
	<?= html::cssFile('/static/app/css/mobileStyle.css') ?>
	<?= html::cssFile('/static/app/css/liveSearch.css') ?>
  
	<?= html::jsFile('/vendor/bower/jquery/dist/jquery.min.js') ?>
	<?= html::jsFile('/vendor/bower/jquery-ui/jquery-ui.min.js') ?>
	<?= html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js') ?>
	<?= html::jsFile('/static/common/js/common.js') ?>
	<?= html::jsFile('/static/app/js/liveSearch.js') ?>
</head>

<body>
  <div class="container">
    <div class="row">
      <div class=" panel-default scoreList">
        <div class="panel-body">
          <div class="infoBlock">
          <?php $form = ActiveForm::begin(['id' => 'questionForm',   'method' => 'post',]); ?>
          	<?= $form->field($data, 'user_id')->hiddenInput(['value' => Yii::$app->user->getId()])->label('') ?>
            <div class="row">
              <div class="col-md-6 col-sm-6">
                <div class="form-group form-group-sm">
                  <label class="col-sm-3 control-label">问题标题</label>
                  <div class="col-sm-9">
                  	<?= $form->field($data, 'title')->textInput(['id'=>'formGroupInputSmall','maxlength' => 50, 'placeholder' => '请输入标题','class' => 'form-control'])->label(false) ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-sm-6">
                <div class="form-group form-group-sm">
                  <label class="col-sm-3 control-label">问题内容</label>
                  <div class="col-sm-9">
                  	<?= $form->field($data, 'question_content')->textarea(['id'=>'question_content','placeholder' => '请输入内容','rows' => '8', 'style' => 'height:auto','class'=>'form-control'])->label(false) ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-sm-6">
                <div class="form-group form-group-sm">
                  <label class="col-sm-3 control-label">话题</label>
                  <div class="col-sm-9">
                    <input class="form-control" style="font-family:monospace;border-color:transparent;color:#CACACA" type="text" id="formGroupInputSmall2">
                    <a href="#" class="btn btn-sm btn-default" id="addTag">添加</a>
                  </div>
                </div>
              </div>
            </div>
             <input type="hidden" id="tags" name="tags" value=""/>
            <div class="row centerContainer">
              <?= Html::submitButton(Yii::t('frontend', '提问'),['id' => 'shareBtn', 'onclick'=>"return check()" , 'class' => 'btn btn-success btn-md centerBtn']) ?>
            </div>
            <?php ActiveForm::end(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  
  function liveSearch_query(word)
  {
	if(word!=""){
		$.post("/app/question/get-tag.html", {val: word},
				liveSearch_gotSearch
        , "json");
	}
	 
  }
  
  
  function check(){
	    var shareBtn = $("#shareBtn");
	    $("#tags").val(liveSearch_getTags());
		var title = $("#formGroupInputSmall").val();
	    var content = $("#soquestion-question_content").val();
	    var tags = $("#tags").val();
	    if (title == '') {
	        NotyWarning('请输入标题', 'center', 1000);
	        return false;
	    }
	    if (content == '') {
	        NotyWarning('请输入内容', 'center', 1000);
	        return false;
	    }

	    if (tags == '') {
	    	//$("#tag_input").focus();
	        NotyWarning('请添加话题', 'center', 1500);
	        return false;
	    }
	    
	    submitModalForm("","questionForm","",true,false,null,null);
	    shareBtn.html("<?= Yii::t('common', 'submiting')?>");
	    return false;
	  }
  
  function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
  {
	  NotyWarning('问题提交成功!', 'center', 2000);
	  var shareBtn = $("#shareBtn");
	  shareBtn.html("<?= Yii::t('frontend', '提问')?>");
  }
  
  </script>
  <?=Html::jsFile('/components/noty/packaged/jquery.noty.packaged.min.js')?>
</body>

</html>
