<?php
namespace Cm\CmTool;

use Cm\CmBase\Traits\Singleton;

class Redis
{
	use Singleton;

	private $redis;

	private function __construct(string $password)
	{
		try {
			if(!extension_loaded('redis')){
				throw new \Exception('没安装redis扩展~~');
			}
			$this->redis = new \Redis();
			$this->redis->connect('127.0.0.1',6379,30);
			if($password != ''){
				$this->redis->auth($password);
			}
		}catch (\Exception $e){
			exit('redis连接错误：'.$e->getMessage());
		}
	}

	/**
	 * @param string $password 密码
	 * @return static
	 */
	public static function instance(string $password = ''):self
	{
		if(self::$instance && self::$instance instanceof self){
			return self::$instance;
		}
		self::$instance = new self($password);
		return self::$instance;
	}

	/**
	 * 获取redis客户端
	 * @param int $index
	 * @return \Redis
	 */
	public function getRedis(int $index = 0): \Redis
	{
		if($index > 0){
			$this->redis->select($index);
		}
		return $this->redis;
	}

	public function setKey(string $key, string $value):bool
	{
		return $this->redis->set(trim($key), $value);
	}

	public function getKey(string $key):string
	{
		return $this->redis->get(trim($key));
	}

	public function delKey(string $key):int
	{
		return $this->redis->del(trim($key));
	}

	public function keyTtl(string $key)
	{
		return $this->redis->ttl($key);
	}

	public function close()
	{
		$this->redis->close();
	}
}