<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/22/2015
 * Time: 4:20 PM
 */

namespace common\crpty;

use Exception;

class DES
{
    var $key;
    var $iv; //偏移量

    public function setSecretKey($encodingKey , $iv=0) {
        $this->key = substr(md5($encodingKey), 0, 8);
        if( $iv == 0 ) {
            $this->iv = $this->key;
        } else {
            $this->iv = $iv; //mcrypt_create_iv ( mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM );
        }
    }



    public function encrypt($str) {
        try {
            //加密，返回大写十六进制字符串
            $size = mcrypt_get_block_size(MCRYPT_DES);
            $str = $this->pkcs5Pad($str, $size);
            $result = strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_DES, $this->key, $str, MCRYPT_MODE_CBC, $this->iv ) ) );
            return array(CryptErrorCode::OK, $result);
        } catch (Exception $e) {
            //print $e;
            return array(CryptErrorCode::ENCRYPT_ERROR, null);
        }
    }

    function decrypt($str) {
        try {
            //解密
            $strBin = $this->hex2bin( strtolower( $str ) );
            $str = mcrypt_decrypt(MCRYPT_DES, $this->key, $strBin, MCRYPT_MODE_CBC, $this->iv );
            $result = $this->pkcs5Unpad( $str );
            return array(CryptErrorCode::OK, $result);
        } catch (Exception $e) {
            //print $e;
            return array(CryptErrorCode::DECRYPT_ERROR, null);
        }
    }

    private function hex2bin($hexData) {
        $binData = "";
        for($i = 0; $i < strlen ( $hexData ); $i += 2) {
            $binData .= chr ( hexdec ( substr ( $hexData, $i, 2 ) ) );
        }
        return $binData;
    }

    private function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

    private function pkcs5Unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }
}