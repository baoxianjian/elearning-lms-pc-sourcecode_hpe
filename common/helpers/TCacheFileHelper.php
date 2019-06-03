<?php

namespace common\helpers;

use common\eLearningLMS;
use yii\base\InvalidParamException;

class TCacheFileHelper
{
	public static function getCachedValue($cacheItem,$cacheKey=null)
	{
		$cache = eLearningLMS::getAppParam($cacheItem);
	
		if($cache === null)
		{
			throw new InvalidParamException('cache item:' . $cacheItem . ' does not exist');
		}
	
		if($cacheKey === null)
		{
			return $cache;
		}
		
		if(array_key_exists($cacheKey,$cache))
		{
			return $cache[$cacheKey];
		}
	
		throw new InvalidParamException('cache key:' . $cacheKey .'('. $cacheItem . '} does not exist');
	}
	
	public static function getCachedConfigValue($id = null)
	{
		$cachedData = self::getCachedValue('cachedConfigs',$id);
		return $cachedData['value'];
	}
}
