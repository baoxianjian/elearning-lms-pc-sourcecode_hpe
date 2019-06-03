<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?= Html::hiddenInput("currentScoId",$currentScoId,['id'=>'currentScoId'])?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<div id="iframe-player" data-type="doc">
    <div class="courseTitle" >
        <div class="left" style="padding:15px;">
            <img src="<?php if(!empty($result['image'])){ echo $result['image'];}else{ echo "/static/frontend/images/nobook.jpg";}?>">
            <div class="centerBtnArea" style="margin-top:20px;">
                <?php if(!empty($result['bookurl'])){?>
                    <a href="<?=$result['bookurl']?>" class="btn btn-success btn-sm" target="_Blank"><?=Yii::t('frontend', 'douban_link')?></a>
                <?php }?>
            </div>
        </div>
        <div class="right">
            <h2 class="lessWord" style="width:95%"><?=$result['book_name']?></h2>
            <table style="table-layout: fixed">
                <tbody>
                <tr style="width: 40%;">
                    <td><span class="lessWord" style="width:95%"  title="<?=$result['author_name']?>"><strong><?=Yii::t('common', 'art_author')?>:</strong> <?=$result['author_name']?></span></td>
                    <td><span><strong><?=Yii::t('common', 'page_number')?>:</strong> <?=$result['page_number']?></span></td>
                </tr>
                <tr>
                    <td><span class="lessWord" style="width:95%"  title="<?=$result['publisher_name']?>"><strong><?=Yii::t('frontend', 'prompt')?>:</strong> <?=$result['publisher_name']?></span></td>
                    <td><span><strong><?=Yii::t('common', 'price')?>:</strong><?=$result['price']?></span></td>
                </tr>
                <tr>
                    <td><span><strong><?=Yii::t('common', 'original_book_name')?>:</strong> <?=$result['original_book_name']?></span></td>
                    <td><span><strong><?=Yii::t('common', 'binding_layout')?>:</strong> <?=$result['binding_layout']?></span></td>
                </tr>
                <tr>
                    <td><span><strong><?=Yii::t('common', 'translator')?>:</strong> <?=$result['translator']?></span></td>
                    <td><span><strong><?=Yii::t('common', 'isbn_no')?>:</strong> <?=$result['isbn_no']?></span></td>
                </tr>
                <tr>
                    <td><span><strong><?=Yii::t('frontend', 'publication_time')?>:</strong> <?=$result['publisher_date']?></span></td>
                </tr>
                <tr>
                    <td colspan="2" style="line-height:1.8em;"><span><strong><?=Yii::t('frontend', 'content_introduce')?>:</strong> <?=$result['description']?></span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        LoadiFramePlayer();
    });

    function LoadiFramePlayer(){
        resizeIframe();
        miniScreen();
        diffTemp();
    }
</script>