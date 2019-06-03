<?php

namespace common\crpty;

include_once "AES.php";
include_once "DES.php";
include_once "CryptErrorCode.php";
/**
 * 1.第三方回复加密消息给平台；
 * 2.第三方收到平台发送的消息后对消息进行解密。
 */
class MessageCrypt
{
    const ENCRYPT_MODE_NONE = "0";
    const ENCRYPT_MODE_AES = "1";
    const ENCRYPT_MODE_DES = "2";

	private $encodingKey;

	/**
	 * 构造函数
	 * @param $encodingKey string 平台上设置的加密密钥
	 */
	public function MessageCrypt($encodingKey)
	{
		$this->encodingKey = $encodingKey;
	}

	/**
	 * 将平台发送给用户的消息进行加密处理。
	 * <ol>
	 *    <li>对要发送的消息进行加密处理</li>
	 * </ol>
	 *
     * @param $encryptMode string 加密模式，0：无，1：AES，2：DES
	 * @param $message string 平台待回复用户的消息
	 * @param &$encryptMsg string 加密后的可以直接回复用户的密文
	 *                      当return返回0时有效
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function encryptMsg($encryptMode, $message, &$encryptMsg)
	{
        if ($encryptMode == self::ENCRYPT_MODE_NONE) {
            $encryptMsg = $message;
            return CryptErrorCode::OK;
        }
        else {
            if ($encryptMode == self::ENCRYPT_MODE_AES)
                $pc = new AES();
            else if ($encryptMode == self::ENCRYPT_MODE_DES)
                $pc = new DES();

            $pc->setSecretKey($this->encodingKey);

            //加密
            $array = $pc->encrypt($message);
            $ret = $array[0];
            if ($ret != CryptErrorCode::OK) {
                return $ret;
            }

            $encryptMsg = $array[1];
            return CryptErrorCode::OK;
        }
	}


	/**
	 * 将平台接收到的消息进行解密处理。
	 * <ol>
	 *    <li>对消息进行解密</li>
	 * </ol>
	 *
     * @param $encryptMode string 加密模式，0：无，1：AES，2：DES
	 * @param $encryptMsg string 密文，对应POST请求的数据
	 * @param &$message string 解密后的原文，当return返回0时有效
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptMsg($encryptMode,$encryptMsg, &$message)
	{
        if ($encryptMode == self::ENCRYPT_MODE_NONE) {
            $message = $encryptMsg;
            return CryptErrorCode::OK;
        }
        else {
            if ($encryptMode == self::ENCRYPT_MODE_AES)
                $pc = new AES();
            else if ($encryptMode == self::ENCRYPT_MODE_DES)
                $pc = new DES();

            $pc->setSecretKey($this->encodingKey);

            $result = $pc->decrypt($encryptMsg);
            if ($result[0] != CryptErrorCode::OK) {
                return $result[0];
            }

            $message = $result[1];

            return CryptErrorCode::OK;
        }
	}
}

