<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/26
 * Time: 13:50
 */

namespace common\traits;

use common\helpers\TMessageHelper;

trait ResponseTrait {
    public function exception($error) {
        $default = [
            'code' => 'common',
            'number' => null,
            'name' => null,
            'message' => null,
            'param' => null
        ];
        if($error !== null && is_array($error)) {
            $default = array_merge($default,$error);
        }
        $errorArray = TMessageHelper::errorBuild($default['code'], $default['number'], $default['name'], $default['message'], $default['param']);
        $result = TMessageHelper::resultBuildByErrorArray($this->systemKey, $errorArray);
        return $result;
    }

    public function response($res = null) {
        $default = [
            'code' => null,
            'name' => null,
            'message' => null,
            'data' => null,
            'status' => 200
        ];
        if($res !== null && is_array($res)) {
            $default = array_merge($default,$res);
        }

        return TMessageHelper::resultBuild($this->systemKey,$default['code'], $default['name'], $default['message'], $default['data'],$default['status']);
    }
}