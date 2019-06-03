<?php
namespace common\crpty;
/**
 * error code 说明.
 * <ul>
 *    <li>-40001: 签名验证错误</li>
 *    <li>-40002: xml解析失败</li>
 *    <li>-40003: sha加密生成签名失败</li>
 *    <li>-40004: encodingAesKey 非法</li>
 *    <li>-40005: appid 校验错误</li>
 *    <li>-40006: aes 加密失败</li>
 *    <li>-40007: aes 解密失败</li>
 *    <li>-40008: 解密后得到的buffer非法</li>
 *    <li>-40009: base64加密失败</li>
 *    <li>-40010: base64解密失败</li>
 *    <li>-40011: 生成xml失败</li>
 * </ul>
 */
class WechatErrorCode
{
	const OK = 0;
	const VALIDATE_SIGNATURE_ERROR = -40001;
	const PARSE_XML_ERROR = -40002;
	const COMPUTE_SIGNATURE_ERROR = -40003;
	const ILLEGAL_AES_KEY = -40004;
	const VALIDATE_APPID_ERROR = -40005;
	const ENCRYPT_AES_ERROR = -40006;
	const DECRYPT_AES_ERROR = -40007;
	const ILLEGAL_BUFFER = -40008;
	const ENCODE_BASE64_ERROR = -40009;
	const DECODE_BASE64_ERROR = -40010;
	const GEN_RETURN_XML_ERROR = -40011;

	public static function GetCryptErrorMessage($code) {

		switch ($code) {
			case self::VALIDATE_SIGNATURE_ERROR: $result = "签名验证错误";break;
			case self::PARSE_XML_ERROR: $result = "xml解析失败";break;
			case self::COMPUTE_SIGNATURE_ERROR: $result = "sha加密生成签名失败";break;
			case self::ILLEGAL_AES_KEY: $result = "encodingAesKey 非法";break;
			case self::VALIDATE_APPID_ERROR: $result = "appid 校验错误";break;
			case self::ENCRYPT_AES_ERROR: $result = "aes 加密失败";break;
			case self::DECRYPT_AES_ERROR: $result = "aes 解密失败";break;
			case self::ILLEGAL_BUFFER: $result = "解密后得到的buffer非法";break;
			case self::ENCODE_BASE64_ERROR: $result = "base64加密失败";break;
			case self::DECODE_BASE64_ERROR: $result = "base64解密失败";break;
			case self::GEN_RETURN_XML_ERROR: $result = "生成xml失败";break;
			default: $result = "OK";break;
		}

		return $result;
	}
}

?>