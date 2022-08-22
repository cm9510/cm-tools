<?php
namespace Cm\Base\Traits;

trait CallStatic
{
	/**
	 * @param $method
	 * @param $arguments
	 * @return false|mixed
	 */
	public static function __callStatic($method, $arguments)
	{
		if(method_exists(self::class, $method)){
			return call_user_func_array([self::class, $method], $arguments);
		}
	}

}