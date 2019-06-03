<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/28
 * Time: 11:27
 */

namespace common\traits;
use common\helpers\TMessageHelper;

trait ParserTrait {

    /**
     * 
     * @param $input
     * @param array $fields
     * @param bool $query_string
     * @return mixed|\stdClass
     */
    public function parseParams($input,$fields = [],$query_string = true,$as_array = false) {
        $retObj = $as_array ? [] : new \stdClass();
        if(!$query_string) {
            $rawDecryptBody = TMessageHelper::decryptMsg($this->systemKey, $input, $errorCode, $errorMessage);
            if(!empty($errorCode)) return $retObj;
            return $this->_setParseField($retObj,$fields,$rawDecryptBody,false,$as_array);
        }

        return $this->_setParseField($retObj,$fields,$input,true,$as_array);
    }

    private function _setParseField($obj,$fields,&$data,$decrypt = false,$as_array) {
        foreach($fields as $field) {
            if(!isset($data[$field])) continue;
            if($decrypt) {
                $val = TMessageHelper::decryptMsg($this->systemKey, $data[$field], $errorCode, $errorMessage);
                if(empty($errorCode)) {
                    $as_array ? $obj[$field] = $val : $obj->{$field} = $val;
                }
            } else {
                $as_array ? $obj[$field] = $data[$field] : $obj->{$field} = $data[$field];
            }
        }
        return $obj;
    }
}