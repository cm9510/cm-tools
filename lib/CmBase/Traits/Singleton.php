<?php
namespace Cm\CmBase\Traits;

trait Singleton
{
	protected static $instance = null;

	private function __construct(){}

	private function __clone(){}
}