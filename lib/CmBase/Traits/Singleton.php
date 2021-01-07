<?php
namespace Cm\CmBase\Traits;

trait Singleton
{
	private static $instance = null;

	private function __construct(){}

	private function __clone(){}
}