<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/22/2015
 * Time: 4:03 PM
 */

namespace common\crpty;

use Exception;

class AES
{
    //CRYPTO_CIPHER_BLOCK_SIZE 32

    private $_secret_key = 'default_secret_key';

    public function setSecretKey($encodingKey) {
        $this->_secret_key = $encodingKey;
    }

    public function encrypt($data) {
        try {
            $md5Result = md5($this->_secret_key);
            $key = mb_substr($md5Result, 0, 16);
            $iv = mb_substr($md5Result, 16, 16);
            $result = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv));
            return array(CryptErrorCode::OK, $result);
        } catch (Exception $e) {
            //print $e;
            return array(CryptErrorCode::ENCRYPT_ERROR, null);
        }
    }

    public function decrypt($data)
    {
        try {
            $data = base64_decode($data);
            $md5Result = md5($this->_secret_key);
            $key = mb_substr($md5Result, 0, 16);
            $iv = mb_substr($md5Result, 16, 16);
            $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
            $result = str_replace("\0","",$result);
            return array(CryptErrorCode::OK, $result);
        } catch (Exception $e) {
            //print $e;
            return array(CryptErrorCode::DECRYPT_ERROR, null);
        }
    }
}