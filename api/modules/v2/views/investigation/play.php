<?php

use common\models\learning\LnComponent;
use common\models\learning\LnModRes;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;

$this->title = $name;


$this->params['breadcrumbs'][] = ['label'=>$name];
$this->params['breadcrumbs'][] = '调查播放';
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

        var ajaxUrl = "<?=Url::toRoute(['investigation/'.$componentCode.'-player','investigation_id'=>$id,
        'investigation_type'=>$investigation_type, 'mode' => 'normal','access_token'=>$access_token,'system_key'=>$system_key])?>";
        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
//        alert(ajaxUrl);
        ajaxGet(ajaxUrl, "player-frame");
    }

   

</script>
<div id="play-control-frame"></div>
<div id="player-frame"></div>
