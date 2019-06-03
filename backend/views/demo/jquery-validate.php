<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/11/15
 * Time: 4:10 PM
 */
use yii\helpers\Html;

?>

<?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>

<?=Html::jsFile('/static/common/js/jquery.validate.min.js')?>
<?//=Html::jsFile('/static/common/js/jquery.validate-extend.min.js')?>


<script>
    $(document).ready(function() {
        $("#commentForm").validate({
            messages: {
                email: {
                    required: 'Enter this!'
                }
            }
        });
    });
</script>

<style>
    form {
        width: 500px;
    }
    form label {
        width: 250px;
    }
    form label.error, form input.submit {
        margin-left: 253px;
    }
</style>

<form class="cmxform" id="commentForm" method="post" >
    <fieldset>
        <legend>Please enter your email address Test</legend>
        <p>
            <label for="cemail">E-Mail *</label>
            <input id="cemail" name="email" data-rule-required="true" data-rule-email="true" data-msg-required="Please enter your email address" data-msg-email="Please enter a valid email address">
        </p>
        <p>
            <input class="submit" type="submit" value="Submit">
        </p>
    </fieldset>
</form>

