<?php


namespace common\helpers;

use yii\redis\Cache;


class TRedisHelper extends Cache
{
	
	public function getCache($key){
		//return parent::get($key);
		$key = parent::buildKey($key);
		$value = parent::getValue($key);
		return $value;
	}
	
}