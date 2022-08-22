<?php
namespace Cm\Base\Traits;

trait Singleton
{
	protected static $instance = null;

	private function __construct(){}

	private function __clone(){}
}