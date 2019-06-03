<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/31
 * Time: 17:19
 */
use common\models\framework\FwUser;
use common\services\framework\UserService;
use components\widgets\TLinkPager;
?>

<div class="actionBar">
    <form class="form-inline pull-right">
        <div class="form-group">
            <input type="text" class="form-control" id="searchPerson" placeholder="<?=Yii::t('frontend', 'input_name_email')?>（<?=Yii::t('frontend', 'choose_student')?>）" value="<?=$params['keyword']?>">
            <button type="reset" class="btn btn-default pull-right" id="resetPersonButton"><?=Yii::t('common', 'reset')?></button>
            <button type="button" class="btn btn-primary pull-right" id="searchPersonButton" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
        </div>
    </form>
</div>
<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td width="15%"><?=Yii::t('common', 'real_name')?></td>
        <td width="20%"><?=Yii::t('common', 'department')?></td>
        <td width="15%"><?=Yii::t('common', 'position')?></td>
        <td width="25%"><?=Yii::t('common', 'user_email')?></td>
        <td width="25%"><?=Yii::t('common', 'mobile_no')?></td>
    </tr>
    <?php
    if (!empty($result['data'])){
        foreach ($result['data'] as $val){
    ?>
    <tr>
        <td style="text-left">
            <label style="display: inherit;">
                <input type="checkbox" class="checkbox_user" id="check_<?=$val->user_id?>" value="<?=$val->user_id?>" />
                <?=$val->real_name?>
            </label>
        </td>
        <td><?=$val->orgnization_name?></td>
        <td><?=$val->position_name?></td>
        <td><?=$val->email?></td>
        <td><?=$val->mobile_no?></td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="5"><?=Yii::t('common', 'no_data')?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<?php
if (!empty($result['data'])) {
?>
<div class="nav clearfix">
    <label class="pull-left" style="margin: 20px 0;">
        <input type="checkbox" class="checkboxAll" id="checkboxAll" /> <?=Yii::t('common', 'check_all')?>
    </label>
    <nav class="pull-right text-right" id="userList-page">
        <?php
            echo TLinkPager::widget([
                'displayPageSizeSelect' => false,
                'pagination' => $result['page'],
            ]);
        ?>
    </nav>
</div>
<?php
}
?>
<script>
    $(function(){
        $("#rightList .pagination").on('click', 'a', function(e){
            e.preventDefault();
           $.get($(this).attr('href'), function(r){
               if (r){
                   $("#rightList").html(r);
               }else{
                   app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
               }
           });
       });
        $("#rightList").on('click', '#checkboxAll', function(e){
            if ($(this).is(":checked") == true) {
                $(".checkbox_user").each(function() {
                    if ($(this).is(":checked") == true){
                        return false;
                    } else {
                        this.checked = true;
                        actionCheckUserList('add', this.value);
                    }
                });
            }else{
                $(".checkbox_user").each(function() {
                    if ($(this).is(":checked") == false){
                        return false;
                    } else {
                        this.checked = false;
                        actionCheckUserList('min', this.value);
                    }
                });
            }
        });
        $("#rightList").on('click', '.checkbox_user', function(e){
            if ($(this).is(":checked") == true) {
                actionCheckUserList('add', this.value);
            }else{
                actionCheckUserList('min', this.value);
            }
        });
        $("#rightList .checkbox_user").each (function(){
            if (user_list.indexOf(this.value) > -1){
                this.checked = true;
            }
        });
    });
</script>