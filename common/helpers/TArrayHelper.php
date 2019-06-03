<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/5/2015
 * Time: 12:24 PM
 */

namespace common\helpers;


use yii\helpers\ArrayHelper;
use yii\helpers\BaseArrayHelper;

class TArrayHelper extends BaseArrayHelper
{
    public static function array_minus($arrayA, $arrayB)
    {
        $countA = count($arrayA);
        $countB = count($arrayB);
        $No_same = 0;

        for ($i = 0; $i < $countA; $i++) {
            for ($j = 0; $j < $countB; $j++) {
                if ($arrayA[$i] == $arrayB[$j])
                    $No_same = 1;
            }

            if ($No_same == 0)
                $rest_array[] = $arrayA[$i];
            else
                $No_same = 0;
        }

        if (!isset($rest_array))
            $rest_array = [];

        return $rest_array;
    }

    /**
     * 对象数组转数组
     * @param array $array 对象数组
     * @param string $key 属性
     * @return array
     */
    public static function get_array_key($array, $key)
    {
        if (isset($array) && $array !== null && count($array) > 0) {
            $result = ArrayHelper::map($array, $key, $key);
            $result = array_keys($result);
        }

        return $result;
    }
}