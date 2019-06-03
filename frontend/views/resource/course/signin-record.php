<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/31
 * Time: 13:17
 */
use common\helpers\TTimeHelper;
use common\models\learning\LnCourseSignIn;
?>

<form class="form-inline pull-left">
    <div class="form-group">
        <div class="form-group field-courseservice-course_type ">
            <select id="course_sign" class="form-control">
                <option value="" selected><?=Yii::t('frontend', 'signin_all_date')?></option>
                <?php
                if (!empty($signDates)) {
                    foreach ($signDates as $key => $val) {
                ?>
                <option value="<?= $val->sign_date ?>" <?=$val->sign_date == $selectDate ? 'selected' : ''?>><?=TTimeHelper::FormatTime($val->sign_date)?></option>
                <?php
                    }
                }
                ?>
            </select>
        </div>
    </div>
</form>
<table class="table table-bordered table-hover table-teacher table-center">
    <tbody>
    <tr>
        <td><?=Yii::t('frontend', 'date_text')?></td>
        <td><?=Yii::t('frontend', 'sign_in_time')?></td>
    </tr>
    <?php
    if (!empty($result)){
        foreach ($result as $key => $val){
            $row = count($val['sign_settings']);
    ?>
    <tr class="sign_tr">
        <td style="padding: 0;">
            <table class="table setting_table" style="margin: 0; padding: 0;">
                <tr style="background-color: transparent!important;">
                    <td rowspan="<?=$row?>" style="vertical-align: middle; border-right: 1px solid #eee;"><?=TTimeHelper::FormatTime($key)?></td>
                    <td><?=$val['sign_settings'][0]['title']?>(<?=TTimeHelper::FormatTime($val['sign_settings'][0]['star_at'],4)?>~<?=TTimeHelper::FormatTime($val['sign_settings'][0]['end_at'],4)?>)</td>
                </tr>
                <?php
                foreach ($val['sign_settings'] as $k => $item) {
                    if ($k == 0 ){
                        continue;
                    }
                ?>
                <tr class="notopborder">
                    <td><?=$item['title']?>(<?=TTimeHelper::FormatTime($item['start_at'],4)?>~<?=TTimeHelper::FormatTime($item['end_at'],4)?>)</td>
                </tr>
                <?php
                }
                ?>
            </table>
        </td>
        <td style="padding: 0;">
            <table class="table" style="margin: 0; padding: 0;">
                <?php
                foreach ($val['students'][0]['sign_data'] as $v) {
                ?>
                <tr style="background-color: transparent!important;">
                    <td>
                        <?php
                        if (!empty($v)){
                            if($v->sign_flag == LnCourseSignIn::SIGN_FLAG_LEAVE)
                            {
                                echo Yii::t('frontend', 'sign_in_left');
                            }
                            else if($v->sign_flag == LnCourseSignIn::SIGN_FLAG_SIGN_IN)
                            {
                                echo Yii::t('frontend', 'signined');
                            }
                            else
                            {
                                echo 'unkown';
                            }
                        }else{
                            echo Yii::t('frontend', 'signined_not');
                        }
                        ?>
                    </td>
                </tr>
                <?php
                }
                ?>
            </table>
        </td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="3"><?=Yii::t('frontend', 'temp_no_data')?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>
