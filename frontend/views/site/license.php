<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = Yii::t('system','frontend_name');
?>
<?=Html::cssFile('/static/frontend/css/elearning.css')?>
<?=Html::cssFile('/static/frontend/css/loginPageBig_zh.css')?>
<style>
   *{box-sizing: border-box !important;}
   html{
       background-image:none !important;
       background-color: white !important;
   }
   .footer .foot_info ul {
       display: block;
       margin: 0 auto;
       width: 325px;
   }
   .content .barCode span{
       height: 220px !important;
   }
   .content .loginPanel{
       height: 360px !important;
   }
   .editPassword {
       width: 520px !important;
   }
   .editPassword strong {color: #000;}
    #reg_code {width: 100%;height: auto;min-height: 80px;border-radius: 4px;border: 1px solid #ccc;margin-bottom: 10px;}
</style>
<body>
<div class="container">
    <div class="row header">
    <?if($company && $company->logo_url):?>
        <span class="logo" title="<?=$company->company_name?>">
            <img src="<?=$company->logo_url?>" style="margin: 20px 0px 20px 20px" height="50" width="250">
        </span>
    <?endif;?>
    </div>
    <div class="row content">
        <div class='loginPanel mainContent editPassword'>
            <?php
            if (!empty($errMsg)){
            ?>
                <p style="margin-top: 30px;"><?=$errMsg?>，<a href="<?=\yii\helpers\Url::toRoute(['site/license'])?>"><?=Yii::t('frontend','click_for_license')?></a></p>
            <?php
            }else{
            ?>
            <h2><?=Yii::t('common', 'product_will_license')?></h2>
            <hr/>
            <form id="license-form" action="" method="post" role="form">
                <input type="hidden" name="_csrf" value="<?=Yii::$app->request->csrfToken?>">
                <input type="hidden" name="machine_code" value="<?=$machineCode?>"/>
                <p><strong><?=Yii::t('common', 'license_machine_code')?></strong><?=$machineCode?></p>
                <p><strong><?=Yii::t('common', 'license_reg_code')?></strong></p>
                <textarea name="reg_code" id="reg_code" placeholder="<?=Yii::t('common', 'license_reg_input')?>"></textarea>
                <button type="submit" class="btn btn-info" id="license-submit"><?=Yii::t('common', 'license_just_btn')?></button>
            </form>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="row footer">
        <div class="foot_info">
            <ul>
                <li><a href="###"><?=Yii::t('frontend','nav_home_text')?></a></li>
                <li><a href="###"><?=Yii::t('frontend','email_regist')?></a></li>
                <li><a href="###"><?=Yii::t('frontend','web_map')?></a></li>
                <li><a href="###"><?=Yii::t('frontend','private')?></a></li>
                <li><a href="###"><?=Yii::t('frontend','ad_strategy')?></a></li>
                <li><a href="###"><?=Yii::t('frontend','terms_of_use')?></a></li>
                <li><a href="###"><?=Yii::t('frontend','help')?></a></li>
            </ul>
            <p><?=Yii::t('system','version_info');?></p>
        </div>
    </div>
</div>
</body>
<script type="text/javascript">
    app.extend("alert");
    $(document).ready(function () {
        $("#license-submit").bind('click', function(){
           var reg_code = $("#reg_code").val().replace(/(^\s*)|(\s*$)/g,'');
            if (reg_code == ""){
                app.showMsg('<?=Yii::t('frontend','license_not_null')?>');
                return false;
            }
        });
    });
</script>

