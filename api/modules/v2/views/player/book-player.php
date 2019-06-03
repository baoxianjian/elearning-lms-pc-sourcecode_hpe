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

<?= html::jsFile('/static/app/js/amazeui.min.js')?>
<div data-am-widget="tabs" class="am-tabs am-tabs-d2 am-no-layout">
<ul class="am-tabs-nav am-cf">
    <li class="am-active"><a href="[data-tab-panel-0]">封面</a></li>
    <li class=""><a href="[data-tab-panel-1]">详情</a></li>
</ul>
<div class="am-tabs-bd" style="touch-action: pan-y; -webkit-user-select: none; -webkit-user-drag: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);">
    <div data-tab-panel-0="" class="am-tab-panel p0 am-active am-in">
        <img src="<?php if(!empty($result['image'])){ echo $result['image'];}else{ echo "/static/frontend/images/nobook.jpg";}?>" style="width:100%">
    </div>
    <div data-tab-panel-1="" class="am-tab-panel">
        <p><strong>内容介绍:</strong></p>
        <div class="am-list-item-text"><?=$result['description']?>
        </div>
        <br>
        <table class="am-table">
            <tbody>
            <tr>
                <td class="table-fc">书名</td>
                <td><?=$result['book_name']?></td>
            </tr>
            <tr>
                <td class="table-fc">作者</td>
                <td><?=$result['author_name']?></td>
            </tr>
            <tr>
                <td class="table-fc">原作名</td>
                <td><?=$result['original_book_name']?></td>
            </tr>
            <tr>
                <td class="table-fc">译者</td>
                <td><?=$result['translator']?></td>
            </tr>
            <tr>
                <td class="table-fc">出版社</td>
                <td> <?=$result['publisher_name']?></td>
            </tr>
            <tr>
                <td class="table-fc">页数</td>
                <td> <?=$result['page_number']?></td>
            </tr>
            <tr>
                <td class="table-fc">定价</td>
                <td><?=$result['price']?></td>
            </tr>
            <tr>
                <td class="table-fc">装帧</td>
                <td><?=$result['binding_layout']?></td>
            </tr>

            <tr>
                <td class="table-fc">ISBN</td>
                <td><?=$result['isbn_no']?></td>
            </tr>
            <tr>
                <td class="table-fc">出版时间</td>
                <td><?=$result['publisher_date']?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="lesson-btn am-cf m10" style="margin-top:20px;">
    <?php if(!empty($result['bookurl'])){?>
        <a href="<?=$result['bookurl']?>" class="am-btn am-btn-primary am-btn-xs fr" target="_Blank">豆瓣链接</a>
    <?php }?>

</div>

</div>

<!--<script>-->
<!--    $(document).ready(function() {-->
<!---->
<!--//        LoadiFramePlayer();-->
<!--    });-->
<!---->
<!--    function change_size(zoom)-->
<!--    {-->
<!--        //此方法必须存在，以便play.php调用-->
<!--        var iframeWindow = $("#iframe-player");-->
<!--        if (zoom) {-->
<!--            //alert(zoom);-->
<!--            iframeWindow.height(750);-->
<!--        }-->
<!--        else-->
<!--        {-->
<!--            if (navigator.userAgent.indexOf('MSIE') >= 0){-->
<!--                //alert('你是使用IE')-->
<!--            }-->
<!--            else {-->
<!--                iframeWindow.height(500);-->
<!--            }-->
<!--        }-->
<!--    }-->
<!---->
<!---->
<!--    function LoadiFramePlayer(){-->
<!--//        alert(compnentCode);-->
<!--        var playZoom = getCookie("play_zoom");-->
<!--        if (playZoom == "0")-->
<!--        {-->
<!--            change_size(true);-->
<!--        }-->
<!--    }-->
<!---->
<!--</script>-->
