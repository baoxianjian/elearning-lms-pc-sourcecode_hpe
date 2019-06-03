<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 5/25/2015
 * Time: 7:56 PM
 */

namespace common\helpers;

use common\services\framework\ExternalSystemService;
use common\models\framework\FwExternalSystem;
use common\crpty\CryptErrorCode;
use common\crpty\MessageCrypt;
use common\crpty\SHA1;
use Yii;
use yii\web\Response;

class TMessageHelper
{

    const ERROR_TYPE_COMMON = "common";
    const NONCE = "api";

    /**
     * 构造API结果返回信息
     * @param $systemKey
     * @param $code
     * @param $name
     * @param $message
     * @param null $jsonResult
     * @param string $status
     * @return array
     */
    public static function resultBuild($systemKey, $code, $name, $message, $jsonResult = null, $status = "200")
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($code == "")
            $code = null;

        if ($name == "")
            $name = null;

        if ($message == "")
            $message = null;

        if ($status == "")
            $status = null;

        if (!empty($jsonResult)) {
            $jsonResultString = json_encode($jsonResult);
//            $temp = json_decode($jsonResultString);
            $encrptResult = self::encryptMsg($systemKey, $jsonResultString);
        } else {
            $encrptResult = null;
        }

        $timeStamp = time();

        //生成安全签名
        $sha1 = new SHA1();
        $array = $sha1->getSHA1($systemKey, $timeStamp, self::NONCE, $encrptResult);
        $ret = $array[0];
        if ($ret != 0) {
            $signature = null;
//            return $ret;
        }
        else {
            $signature = $array[1];
        }

        $result = [
            'code' => $code,
            'name' => $name,
            'message' => $message,
            'result' => $encrptResult,
            'status' => $status,
            'signature' => $signature,
            'timestamp' => $timeStamp
        ];

        return $result;
    }

    /**
     * 根据错误信息构造API结果返回值
     * @param $errorArray
     * @param string $status
     * @return array
     */
    public static function resultBuildByErrorArray($systemKey, $errorArray, $status = "200")
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($status == "")
            $status = null;

        $timeStamp = time();

        //生成安全签名
        $sha1 = new SHA1();
        $array = $sha1->getSHA1($systemKey, $timeStamp, self::NONCE, $errorArray['message']);
        $ret = $array[0];
        if ($ret != 0) {
            $signature = null;
//            return $ret;
        }
        else {
            $signature = $array[1];
        }

        $result = [
            'code' => $errorArray['code'],
            'name' => $errorArray['name'],
            'message' => $errorArray['message'],
            'result' => null,
            'status' => $status,
            'signature' => $signature,
            'timestamp' => $timeStamp
        ];

        return $result;
    }

    /**
     * 构造错误消息
     * @param $type
     * @param $number
     * @param $name
     * @param $message
     * @param null $param
     * @return array
     */
    public static function errorBuild($type, $number, $name, $message, $param = null)
    {
        if ($type == self::ERROR_TYPE_COMMON) {
            switch ($number) {
                case "001" :
                    $name = Yii::t('common', 'param_{value}_error', ['value' => $param]);
                    break;
                case "002" :
                    $name = Yii::t('common', 'system_key_not_exist');
                    break;
                case "003" :
                    $name = Yii::t('common', 'invalid_request');
                    break;
                case "004" :
                    $name = Yii::t('common', 'encrypt_{value}_error', ['value' => $param]);
                    break;
                case "005" :
                    $name = Yii::t('common', 'web_api_service_stop');
                    break;
                case "006" :
                    $name = Yii::t('common', 'data_not_exist');
                    break;
                case "007" :
                    $name = Yii::t('common', 'operation_confirm_warning_failure');
                    break;
                default:
                    break;
            }
        }

        $code = $type . "-" . $number;

        $result = [
            'code' => $code,
            'name' => $name,
            'message' => $message,
        ];

        return $result;
    }

    public static function decryptMsg($systemKey, $encryptMsg, &$errorCode = null, &$errorMessage = null)
    {
        $result = null;
        if (empty($encryptMsg))
            return $result;

        else if (!empty($systemKey)) {
            $externalSystemService = new ExternalSystemService();
            $model = $externalSystemService->findBySystemKey($systemKey);

            if (!empty($model)) {
                $encodingKey = $model->encoding_key;
                $securityMode = $model->security_mode;
                $encryptMode = $model->encrypt_mode;
                if ($securityMode == FwExternalSystem::SECURITY_MODE_ENCRYPT && !empty($encodingKey)) {
                    $pc = new MessageCrypt();
                    $pc->MessageCrypt($encodingKey);

                    $tempResult = null;
                    $encrptCode = $pc->decryptMsg($encryptMode, $encryptMsg, $tempResult);
                    if ($encrptCode == CryptErrorCode::OK) {
                        $result = $tempResult;
                    } else {
                        $errorCode = $encrptCode;
                        $errorMessage = CryptErrorCode::getCryptErrorMessage($encrptCode);
//                        $result = $encrptCode;
                    }
                } else {
                    $result = $encryptMsg;
                }
            }
        }

        return $result;
    }

    public static function encryptMsg($systemKey, $message, &$errorCode = null, &$errorMessage = null)
    {
        $result = null;
        if (empty($message))
            return $result;

        if (!empty($systemKey)) {
            $externalSystemService = new ExternalSystemService();
            $model = $externalSystemService->findBySystemKey($systemKey);

            if (!empty($model)) {
                $encodingKey = $model->encoding_key;
                $securityMode = $model->security_mode;
                $encryptMode = $model->encrypt_mode;
                if ($securityMode == FwExternalSystem::SECURITY_MODE_ENCRYPT && !empty($encodingKey)) {
                    $pc = new MessageCrypt();
                    $pc->MessageCrypt($encodingKey);

                    $tempResult = null;
                    $encrptCode = $pc->encryptMsg($encryptMode, $message, $tempResult);
                    if ($encrptCode == CryptErrorCode::OK) {
                        $result = $tempResult;
                    } else {
                        $errorCode = $encrptCode;
                        $errorMessage = CryptErrorCode::getCryptErrorMessage($encrptCode);
//                        $result = $encrptCode;
                    }
                } else {
                    $result = $message;
                }
            }
        }

        return $result;
    }
}