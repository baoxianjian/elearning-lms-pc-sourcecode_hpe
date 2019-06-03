<?php
//use components\BackendFrameMenu;
use yii\helpers\Html;
use backend\assets\AppAsset;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;


?>
<!-- /.row -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="glyphicon glyphicon-book fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge">
                            <?=$course_count?>
                        </div>
                        <div>
                            <?=Yii::t('backend','lesson_no')?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="glyphicon glyphicon-user fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge">
                            <?=$user_count?>
                        </div>
                        <div>
                            <?=Yii::t('backend','user_no')?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="glyphicon glyphicon-list fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge">
                            <?=$investigation_count?>
                        </div>
                        <div>
                            <?=Yii::t('backend','investigation_no')?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-red">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="glyphicon glyphicon-bullhorn fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge">
                            <?=$question_count?>
                        </div>
                        <div>
                            <?=Yii::t('backend','question_no')?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<? if ($isSpecial) {?>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> <?=Yii::t('backend','user_trend_chart')?>
                <!--         <div class="pull-right">
           <div class="btn-group">
            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown"> Actions <span class="caret"></span> </button>
            <ul class="dropdown-menu pull-right" role="menu">
             <li><a href="#">Action</a> </li>
             <li><a href="#">Another action</a> </li>
             <li><a href="#">Something else here</a> </li>
             <li class="divider"></li>
             <li><a href="#">Separated link</a> </li>
            </ul>
           </div>
          </div>  -->
            </div>
            <div class="panel-body">
                <div id="morris-area-chart"></div>
            </div>
        </div>
        <!--           <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-bar-chart-o fa-fw"></i> 柱状图示例
                    <div class="pull-right">
       <div class="btn-group">
        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown"> Actions <span class="caret"></span> </button>
        <ul class="dropdown-menu pull-right" role="menu">
         <li><a href="#">Action</a> </li>
         <li><a href="#">Another action</a> </li>
         <li><a href="#">Something else here</a> </li>
         <li class="divider"></li>
         <li><a href="#">Separated link</a> </li>
        </ul>
       </div>
      </div>
          </div>

          <div class="panel-body">
            <div class="row">

              <div class="col-lg-12">
                <div id="morris-bar-chart"></div>
              </div>

            </div>

          </div>

        </div> -->
    </div>
    <!--      <div class="col-lg-4">
  <div class="panel panel-default">
   <div class="panel-heading">
    <i class="fa fa-bell fa-fw"></i> 事件提醒组件
   </div>

   <div class="panel-body">
    <div class="list-group">
     <a href="#" class="list-group-item"> <i class="fa fa-comment fa-fw"></i> New Comment <span class="pull-right text-muted small"><em>4 minutes ago</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-twitter fa-fw"></i> 3 New Followers <span class="pull-right text-muted small"><em>12 minutes ago</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-envelope fa-fw"></i> Message Sent <span class="pull-right text-muted small"><em>27 minutes ago</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-tasks fa-fw"></i> New Task <span class="pull-right text-muted small"><em>43 minutes ago</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-upload fa-fw"></i> Server Rebooted <span class="pull-right text-muted small"><em>11:32 AM</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-bolt fa-fw"></i> Server Crashed! <span class="pull-right text-muted small"><em>11:13 AM</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-warning fa-fw"></i> Server Not Responding <span class="pull-right text-muted small"><em>10:57 AM</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-shopping-cart fa-fw"></i> New Order Placed <span class="pull-right text-muted small"><em>9:49 AM</em> </span> </a>
     <a href="#" class="list-group-item"> <i class="fa fa-money fa-fw"></i> Payment Received <span class="pull-right text-muted small"><em>Yesterday</em> </span> </a>
    </div>

    <a href="#" class="btn btn-default btn-block">View All Alerts</a>
   </div>

  </div>

  <div class="panel panel-default">
   <div class="panel-heading">
    <i class="fa fa-bar-chart-o fa-fw"></i> 饼状图组件
   </div>
   <div class="panel-body">
    <div id="morris-donut-chart"></div>
    <a href="#" class="btn btn-default btn-block">展开</a>
   </div>

  </div>

 </div>  -->
</div>

<!-- Morris Charts JavaScript -->
<!--<script src="../bower_components/raphael/raphael-min.js"></script>-->
<?=Html::jsFile('/vendor/bower/raphael/raphael-min.js')?>

<!--<script src="../bower_components/morrisjs/morris.min.js"></script>-->
<?=Html::jsFile('/vendor/bower/morrisjs/morris.min.js')?>

<!--<script src="../js/morris-data.js"></script>-->
<script>

    $(function() {
        var ajaxUrl = "<?=Url::toRoute(['index/user-count-chart'])?>";
        ajaxGetJSON(ajaxUrl,function(result){
            Morris.Area({
                element: 'morris-area-chart',
                data: result,
                xkey: '<?=Yii::t('backend','time')?>',
                ykeys: ['<?=Yii::t('backend','user_number')?>'],
                labels: ['<?=Yii::t('backend','user_number')?>'],
                pointSize: 1,
                hideHover: 'auto',
                resize: true
            });
        });
    });

</script>
<? } ?>

<?//=Html::jsFile('/static/backend/js/morris-data.js')?>
<?=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<?=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
