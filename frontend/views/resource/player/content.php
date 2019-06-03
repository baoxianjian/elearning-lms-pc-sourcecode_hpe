<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 11/19/2015
 * Time: 9:29 PM
 */

use common\models\learning\LnScormScoes;
use common\models\learning\LnFiles;

//中转页面提高访问安全性

//$iframeUrl = "http://www.baidu.com";
header("Location: ".$iframeUrl);
exit();
