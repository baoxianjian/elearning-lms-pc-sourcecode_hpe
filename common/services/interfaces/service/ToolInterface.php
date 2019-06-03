<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 3/22/2016
 * Time: 10:30 AM
 */

namespace common\services\interfaces\service;

use QRencode;

class ToolInterface
{
    /**
     * 生成二维码
     * @param string $text 内容
     * @param int $level 识别率级别；可选参数为：QR_ECLEVEL_L、QR_ECLEVEL_M、QR_ECLEVEL_Q、QR_ECLEVEL_H
     * @param int $size 尺寸
     * @param int $margin 边距
     */
    public static function genQRCode($text, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $outfile = false)
    {
        $enc = QRencode::factory($level, $size, $margin);
        $enc->encodePNG($text, $outfile);
    }
}