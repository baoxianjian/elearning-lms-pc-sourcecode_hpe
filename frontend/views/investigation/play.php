<?php

use common\models\learning\LnComponent;
use common\models\learning\LnModRes;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;

$this->title = $name;


$this->params['breadcrumbs'][] = ['label'=>$name];
$this->params['breadcrumbs'][] = Yii::t('common','investigation_play');
$this->params['breadcrumbs'][] = '';

/* @var $model common\models\learning\LnCourseware */
?>
<title>test</title>
<?= Html::hiddenInput("duration",$duration,['id'=>'duration'])?>
<script>
    $(document).ready(function() {

        var duration = $("#duration").val() * 1000;
//        alert(duration);
      //  setInterval("playerInterval()", duration);//1000为1秒钟

//        alert('loadPlayer');
       
        loadPlayer();
        //loadControl();
      
    });

   
   

    

    function loadPlayer(){
        $("#player-frame").empty();
        var scoId = "<?=$scoId?>";
//        alert(compnentCode);
        var ajaxUrl = "<?=Url::toRoute(['investigation/'.$componentCode.'-player','investigation_id'=>$id,
        'investigation_type'=>$investigation_type, 'mode' => 'normal'])?>";
        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
//        alert(ajaxUrl);
        ajaxGet(ajaxUrl, "player-frame");
    }

   

</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

        <div class="col-md-12" >

            <div class="panel panel-default hotNews pull-left" id="outWindow" style="width: 100%">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('common','investigation_play')?>
                </div>
                <div class="col-md-8" id="playWindow" style="width: 100%">
                    <div class="panel-body" >
                        <div id="play-control-frame"></div>
                        <hr id="player_line"/>
                        <div id="player-frame"></div>
                    </div>
                </div>

                
               
            </div>
        </div>
    </div>
</div>
