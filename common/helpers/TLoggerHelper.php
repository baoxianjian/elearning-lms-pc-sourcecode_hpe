<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/15/15
 * Time: 2:35 AM
 */

namespace common\helpers;


#use Logger;
use Yii;
use yii\helpers\VarDumper;
use yii\log\Logger;

class TLoggerHelper {

    public static function SysInfo($message)
    {
        $logger = Yii::getLogger();
        $category = 'syslog';
        $logger->log($message,Logger::LEVEL_WARNING,$category);
    }

    public static function Error($message)
    {
        $logger = Yii::getLogger();
        $category = 'errlog';
        $logger->log($message,Logger::LEVEL_ERROR,$category);
    }

    public static function Message($message)
    {
        $logger = Yii::getLogger();
        $category = 'msglog';
        $logger->log($message,Logger::LEVEL_WARNING,$category);
    }

    public static function Access($message)
    {
        $logger = Yii::getLogger();
        $category = 'acclog';
        $logger->log($message,Logger::LEVEL_TRACE,$category);
    }

    public static function VarDumper($var,$message)
    {
        $dump = VarDumper::dumpAsString($var);

//        $logger = Logger::getLogger("errlog");

//        $logger->debug($message + ":" + $dump);
    }


}