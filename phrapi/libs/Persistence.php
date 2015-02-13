<?php defined("PHRAPI") or die("Direct access not allowed!");
/**
 * Persist a variable throw serialization and files
 *
 * @abstract
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 * @package rapi_cache
 *
 */

final class Persistence {
	/**
	 *
	 * @var String Cache path
	 */
	private $path;

	/**
	 * @var Object instance
	 */
	private static $instance;

	function __construct()
	{
		//$config = framework_Config::getInstance();
		$this->path = "cache" . DS;
	}

	/**
	 * Singleton pattern http://en.wikipedia.org/wiki/Singleton_pattern
	 * @return object Class Instance
	 */
	public static function getInstance()
	{
		if (!self::$instance instanceof self)
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Serialize and save the value
	 *
	 * @param $name
	 * @param $data
	 */
	public function set($name, $data) {
		$filename = $this->path . md5($name) . ".var";

		if(!file_exists($filename) && is_writable($this->path)) {
			touch($filename);
		}

		if (file_exists($filename) && is_writable($filename)) {
			file_put_contents($filename, serialize($data));
		}
	}

	/**
	 * Load and unserialize the value
	 * @param $name
	 * @return mixed
	 */
	public function get($name) {
		$filename = $this->path . md5($name) . ".var";

		if (file_exists($filename) && is_readable($filename)) {
			return unserialize(file_get_contents($filename));
		}

		return false;
	}

	/**
	 * Validate if a data saved is older than $timeline
	 *
	 * @uses $persist->isOlderThan('my_var', '+5 minutes');
	 *
	 * @param $name
	 * @param $timeline
	 * @return boolean
	 */
	public function isOlderThan($name, $timeline) {
		$filename = $this->path . md5($name) . ".var";

		if (!file_exists($filename)) {
			return false;
		}

		$file_date = filemtime($filename);

		if (strtotime($timeline, $file_date) < time()) {
			return true;
		}

		return false;
	}

	/**
	 * It exists?
	 * @param $name
	 * @return boolean
	 */
	public function isSaved($name) {
		$filename = $this->path . md5($name) . ".var";

		if(file_exists($filename)) {
			return true;
		}

		return false;
	}
}
