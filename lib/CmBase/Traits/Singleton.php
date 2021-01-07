<?php
namespace Cm\CmBase\Traits;

trait Singleton
{
	private static $instance = null;

	private function __construct(){}

	private function __clone(){}

	/**
	 * 单例出口
	 * @return Singleton|null
	 */
	private static function instance():self
	{
		if(self::$instance && self::$instance instanceof self){
			return self::$instance;
		}
		self::$instance = new self;
		return  self::$instance;
	}
}