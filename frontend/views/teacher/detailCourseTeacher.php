  <?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;

?>
<div class=" panel-default scoreList">
    <div class="panel-body">
    <?php  if (!empty($teacher)) { ?>
        <div class="row col-md-12">
            <?php
            $teacherCount = count($teacher);
            $i = 0;
            foreach ($teacher as $i => $vo) {
            ?>
            <div class="row <?=$teacherCount>1?'col-md-6':'col-md-12'?>">
                <div class="col-md-2">
                    <img src="<?= TStringHelper::Thumb($vo['teacher_thumb_url'],$vo['gender']) ?>" onerror="this.src='/static/common/images/man.jpeg';" style="margin-top:20px; width:100%; text-align:center;">
                </div>
                <div class="col-md-10">
                    <h3 style="text-align:left;"><?= $vo['teacher_name'] ?></h3>
                    <p>
                        <?php
                        if (!empty($vo['description'])){
                            echo $vo['description'];
                        }else{
                            ?>
                            <?=Yii::t('frontend', 'warning_for_teacher_detail')?>
                            <?php
                        }
                        ?>
                    </p>
                </div>
            </div>
            <?php
            if ($i%2==1){
            ?>
            </div>
            <div class="row col-md-12">
            <?php
            }
            }
            ?>
        </div>
       <?php
    }
    ?>
    </div>
</div>
   
                               