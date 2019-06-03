<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/6
 * Time: 11:04
 */
use mobile\widgets\WechatConfigWidget;
echo $message;

var_dump($login);
?>
<?php echo WechatConfigWidget::widget(['shareData' => ['title' => 'test','desc' => '1111','success' => 'ok']])?>