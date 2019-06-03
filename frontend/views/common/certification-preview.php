<?php

use common\models\learning\LnCertification;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertification */

?>


<?

if (empty($message)) {
    $format = "A4";
    $mpdf = new mPDF('zh-CN',$format);
    $mpdf->SetDisplayMode('fullpage');
//                $mpdf = new mPDF('UTF-8');
    $mpdf->useAdobeCJK = true;

    if($printOrientation == LnCertification::PRINT_ORIENTATION_LANDSCAPE) {
        $mpdf->AddPage('L');
    }

    $mpdf->WriteHTML($html);
    $mpdf->Output();
    exit;
}
else {
    echo $message;
}

?>