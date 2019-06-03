<?php
namespace common\crpty;
/**
 * Crypt Error Code 说明.
 * <ul>
 *    <li>-40001: 签名验证错误</li>
 *    <li>-40002: 签名失败</li>
 *    <li>-40003: 加密密钥无效</li>
 *    <li>-40004: 外部系统令牌校验错误</li>
 *    <li>-40005: 加密失败</li>
 *    <li>-40006: 解密失败</li>
 *    <li>-40007: 解密后得到的buffer非法</li>
 * </ul>
 */
class CryptErrorCode
{
    const OK = 0;
    const VALIDATE_SIGNATURE_ERROR = -40001;
    const COMPUTE_SIGNATURE_ERROR = -40002;
    const ILLEGAL_ENCODING_KEY = -40003;
    const VALIDATE_SYSTEM_KEY_ERROR = -40004;
    const ENCRYPT_ERROR = -40005;
    const DECRYPT_ERROR = -40006;
    const ILLEGAL_BUFFER = -40007;

    public static function getCryptErrorMessage($code)
    {

        switch ($code) {
            case self::VALIDATE_SIGNATURE_ERROR:
                $result = "签名验证错误";
                break;
            case self::COMPUTE_SIGNATURE_ERROR:
                $result = "签名失败";
                break;
            case self::ILLEGAL_ENCODING_KEY:
                $result = "加密密钥无效";
                break;
            case self::VALIDATE_SYSTEM_KEY_ERROR:
                $result = "外部系统令牌校验错误";
                break;
            case self::ENCRYPT_ERROR:
                $result = "加密失败";
                break;
            case self::DECRYPT_ERROR:
                $result = "解密失败";
                break;
            case self::ILLEGAL_BUFFER:
                $result = "解密后得到的buffer非法";
                break;
            default:
                $result = "OK";
                break;
        }

        return $result;
    }
}

?>