<?php


namespace common\services\learning;


use common\models\framework\FwCompany;
use common\models\framework\FwPosition;
use common\models\framework\FwUser;
use common\models\framework\FwUserPosition;
use common\models\learning\LnCertificationTemplate;
use common\models\treemanager\FwTreeNode;
use common\services\framework\RbacService;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class CertificationTemplateService extends LnCertificationTemplate{


    /**
     * @param $imgSrc 目标图片，可带相对目录地址
     * @param $markImg 水印图片，可带相对目录地址，支持PNG和GIF两种格式，如水印图片在执行文件mark目录下，可写成：mark/mark.gif
     * @param $markText 给图片添加的水印文字
     * @param $textColor 水印文字的字体颜色
     * @param $top 上起位置
     * @param $left 左起位置
     * @param $fontType 具体的字体库，可带相对目录地址
     * @param $markType 图片添加水印的方式，img代表以图片方式，text代表以文字方式添加水印
     */
    function setWater($imgSrc,$markImg,$markText, $fontSize,$fontColor,$top,$left,$fontType,$markType)
    {
        $srcim = null;
        $markim = null;
        $dst_img = null;

        if (!empty($fontColor))
            $fontColor = explode(',', $fontColor);
        else
            $fontColor = array(0,0,0);

        if (!empty($fontType))
            $fontType = Yii::$app->basePath . '/..' . $fontType;//字体
        else
            $fontType = Yii::$app->basePath . '/..' . '/static/common/fonts/simsun.ttc';//字体

        if (empty($fontSize))
        {
            $fontSize = 16;
        }

        $srcInfo = @getimagesize($imgSrc);
        $srcImg_w    = $srcInfo[0];
        $srcImg_h    = $srcInfo[1];

        switch ($srcInfo[2])
        {
            case 1:
                $srcim =imagecreatefromgif($imgSrc);
                break;
            case 2:
                $srcim =imagecreatefromjpeg($imgSrc);
                break;
            case 3:
                $srcim =imagecreatefrompng($imgSrc);
                break;
            default:
                die("不支持的图片文件类型");
                exit;
        }

        if(!strcmp($markType,"img"))
        {
            if(!file_exists($markImg) || empty($markImg))
            {
                return;
            }

            $markImgInfo = @getimagesize($markImg);
            $markImg_w    = $markImgInfo[0];
            $markImg_h    = $markImgInfo[1];

            if($srcImg_w < $markImg_w || $srcImg_h < $markImg_h)
            {
                return;
            }

            switch ($markImgInfo[2])
            {
                case 1:
                    $markim =imagecreatefromgif($markImg);
                    break;
                case 2:
                    $markim =imagecreatefromjpeg($markImg);
                    break;
                case 3:
                    $markim =imagecreatefrompng($markImg);
                    break;
                default:
                    die("不支持的水印图片文件类型");
                    exit;
            }

            $logow = $markImg_w;
            $logoh = $markImg_h;
        }

        if(!strcmp($markType,"text"))
        {
//            $fontSize = 16;
            if(!empty($markText))
            {
                if(!file_exists($fontType))
                {
                    return;
                }
            }
            else {
                return;
            }

            $box = @imagettfbbox($fontSize, 0, $fontType,$markText);
            $logow = max($box[2], $box[4]) - min($box[0], $box[6]);
            $logoh = max($box[1], $box[3]) - min($box[5], $box[7]);
        }

        if ($left <= ($srcImg_w - $logow))
            $x = $left;
        else
            $x = ($srcImg_w - $logow);

        if ($top <= ($srcImg_h - $logoh))
            $y = $top;
        else
            $y = ($srcImg_h - $logoh);


        $dst_img = @imagecreatetruecolor($srcImg_w, $srcImg_h);

        imagecopy($dst_img, $srcim, 0, 0, 0, 0, $srcImg_w, $srcImg_h);

        if(!strcmp($markType,"img"))
        {
            imagecopy($dst_img, $markim, $x, $y, 0, 0, $logow, $logoh);
            imagedestroy($markim);
        }

        if(!strcmp($markType,"text"))
        {
            $rgb = $fontColor;

            $color = imagecolorallocate($dst_img, $rgb[0], $rgb[1], $rgb[2]);
            imagettftext($dst_img, $fontSize, 0, $x, $y, $color, $fontType,$markText);
        }

        switch ($srcInfo[2])
        {
            case 1:
                imagegif($dst_img, $imgSrc);
                break;
            case 2:
                imagejpeg($dst_img, $imgSrc);
                break;
            case 3:
                imagepng($dst_img, $imgSrc);
                break;
            default:
                die("不支持的水印图片文件类型");
                exit;
        }

        imagedestroy($dst_img);
        imagedestroy($srcim);
    }
}