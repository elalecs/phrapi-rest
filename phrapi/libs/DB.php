<?php defined("PHRAPI") or die("Direct access not allowed!");

/**
 * Database drive class
 *
 * @author Alejandro Galindo, twitter.com/elalecs
 * @copyright Tecnologías Web de México S.A. de C.V.
 */
class DB
{
	/**
	 * @var Object instance
	 */
	private static $instance = array();
	/**
	 * @var PDO
	 */
	private $link = null;
	public $last_query = "";
	public $last_error = "";
	public $debugged = false;
	public $config = null;

	public function __construct($server = '')
	{
		if (empty($server)) {
			$server = 'default';
		}

		$config = $GLOBALS['config'];

		if (!isset($config['db'][$server])) {
			return false;
		}

		$this->config = $config['db'][$server];

		try {
			$cnx_config = array(
				PDO::ATTR_PERSISTENT => true,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			);

			if (defined('PDO::MYSQL_ATTR_COMPRESS')) {
				$cnx_config[PDO::MYSQL_ATTR_COMPRESS] = true;
			}

			$dsn = "mysql:host={$this->config['host']};dbname={$this->config['name']}";
			$this->link = new PDO($dsn, $this->config['user'], $this->config['pass']);
		} catch(PDOException $e) {
			D($e->getMessage());
		}

		$this->link->query("SET CHARACTER SET utf8");
		$this->link->query("SET NAMES utf8");

		if (isset($config->app['gmt']) && !empty($config->app['gmt']))
			$this->link->query("SET time_zone = '{$config->app['gmt']}'");

		if (isset($config->app['locale']) && !empty($config->app['locale']))
			$this->link->query("SET lc_time_names = '{$config->app['locale']}'");
	}

	/**
	 * Singleton pattern http://en.wikipedia.org/wiki/Singleton_pattern
	 * @param int $config_index
	 * @return object Class Instance
	 */
	public static function getInstance($config_index = null)
	{
		if ($config_index === null OR is_numeric($config_index) OR empty($config_index) OR !$config_index) {
			$config_index = 'default';
		}

		if (!isset(self::$instance[$config_index]) || !self::$instance[$config_index] instanceof self)
			self::$instance[$config_index] = new self($config_index);

		return self::$instance[$config_index];
	}

	/**
	 * Show a DB error
	 * @param array $error_info
	 */
	private function showError($error_info)
	{
		$error = isset($error_info[2]) ? $error_info[2] : '';

		$this->last_error = $error . " SQL: " . $this->last_query;
		$msg = array(
			'error' => $error,
			'sql' => $this->last_query,
			'backtrace' => array()
		);

		$backtrace_array = debug_backtrace();
		array_shift($backtrace_array);
		$backtrace = array();
		foreach ($backtrace_array as $backtrace_resource) {
			$backtrace[] = "{$backtrace_resource['file']}:{$backtrace_resource['line']}";
		}
		krsort($backtrace);
		$msg['backtrace'] = $backtrace;

		D($msg);
		die;
	}

	/**
	 * Execute a query, return nothig, useful for UPDATE and INSERT querys
	 * @return boolean
	 * @param string $sql
	 * @param array $params
	 */
	public function query($sql, $params = null)
	{
		if (empty($sql))
			return false;

		$this->last_query = &$sql;

		try {
			if (is_array($params)) {
				$params = $this->normalizeParams($sql, $params);
				$stmt = $this->link->prepare($sql);
				$result = $stmt->execute($params);

				if (!$result) {
					$this->showError($stmt->errorInfo());
				}

				return $stmt;
			} else {
				$result = $this->link->query($sql);

				if (!$result) {
					$this->showError($this->link->errorInfo());
				}

				return $result;
			}
		} catch(PDOException $e) {
			E($e);
			die;
		}

		return false;
	}

	private function normalizeParams($sql, $params) {
		$matches = array();
		preg_match_all('/(=|\ |\()(:[_a-zA-Z0-9]+)(\)|\ |,|$|\n)/', $sql, $matches);
		if (isset($matches[2]) && sizeof($matches[2]) > 0) {
			$expected = $matches[2];

			$params_ok = array();
			foreach($expected as $expect) {
				$params_ok[$expect] = isset($params[$expect]) ? $params[$expect] : "";
			}
			$params = $params_ok;
		}

		return $params;
	}

	/**
	 * Execute a query and return his values into an array objects
	 * @return array
	 * @param string $sql
	 * @param array $params
	 */
	public function queryAll($sql, $params = null)
	{
		if (empty($sql))
			return false;

		$rows = array();

		$stmt = $this->query($sql, $params);

		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$rows[] = $row;
		}

		$stmt = null;

		return $rows;
	}

	/**
	 * Execute a query and return his values into an array objects
	 * @return array
	 * @param string $sql
	 * @param array $params
	 */
	public function queryAllOne($sql, $params = null)
	{
		if (empty($sql))
			return false;

		$rows = array();

		$stmt = $this->query($sql, $params);

		while($row = $stmt->fetch(PDO::FETCH_NUM))
		{
			$rows[] = $row[0];
		}

		$stmt = null;

		return $rows;
	}

	/**
	 * Execute a query and return his values into an array for a Html::Select
	 * the sql need to be like SELECT id, label FROM ....
	 * @return array
	 * @param string $sql
	 * @param array $params
	 */
	public function queryAllSpecial($sql, $params = null)
	{
		if (empty($sql))
			return false;

		$stmt = $this->query($sql, $params);

		$rows = array();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$rows[$row['id']] = $row['label'];
		}

		$stmt = null;

		return $rows;
	}

	/**
	 * Execute a query and return just the first row
	 * @return object
	 * @param string $sql
	 * @param array $params
	 */
	public function queryRow($sql, $params = null)
	{
		if (empty($sql))
			return false;

		$stmt = $this->query($sql, $params);

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$row) {
			$row = array();
		}

		$stmt = null;

		return $row;
	}

	/**
	 * Execute a query and return just the first cell
	 * @return mixed
	 * @param string $sql
	 * @param array $params
	 */
	public function queryOne($sql, $params = null)
	{
		if (empty($sql))
			return false;

		$stmt = $this->query($sql, $params);

		if (!$stmt) {
			return false;
		}

		$value = $stmt->fetchColumn();
		$value = stripslashes($value);

		$stmt = null;

		return $value;
	}

	/**
	 * Get the last generated ID
	 * @return string Last id
	 */
	public function getLastID()
	{
		return $this->link->lastInsertId();
	}

	/**
	 * Return the enum options
	 * @param string $table
	 * @param string $field
	 * @param bool $key_label
	 * @return array
	 */
	public function getEnumOptions($table = '', $field = '', $key_label = false) {
		if (empty($table) OR empty($field)) {
			return array();
		}

		$stmt = $this->query("SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'");

		$options = $stmt->fetch(PDO::FETCH_OBJ);

		preg_match_all("/'([^']+)'/i", $options->Type, $result);
		$options = array();
		foreach($result[1] as $key => $val) {
			if ($key_label) {
				$options[$val] = $val;
			} else {
				$options[$key + 1] = $val;
			}
		}

		$stmt = null;

		return $options;
	}

	/**
	 * Return a Navigation object to be used in Html::adminNavigation,
	 * this is just used on admin, but can be used on frontend
	 *
	 * @uses $navigation = $this->getNavigation(array(
	 *  	per_page => 2,
	 *  	offset => $this->getParam("offset", 0),
	 *  	sql_total => "SELECT COUNT(*) FROM tabla"
	 *  ));
	 *
	 * @param array Need 3 params:
	 *                'per_page' number of elements by page
	 *                'offset' actual offset
	 *                'sql_total' sql query to know how many elements are there
	 * @return object
	 */
	public function getNavigation($config)
	{
		$config = $config + array(
			"per_page" => 10,
		//	"offset" => framework_Params::getInstance()->getParam("offset", 0),
			"offset" => 0,
			"total" => 0,
			"sql_total" => ""
		);
		$navigation = new stdClass;
		$navigation->per_page = $config['per_page'];
		$navigation->total = !empty($config['sql_total']) ? $this->queryOne($config['sql_total']) : $config['total'];

		$navigation->link = "";

		$navigation->links = new stdClass;
		$navigation->links->previous = false;
		$navigation->links->next = false;

		$navigation->offsets = new stdClass;
		$navigation->offsets->actual = $config['offset'];
		$navigation->offsets->previous = $config['offset'] - $config['per_page'];
		$navigation->offsets->next = $config['offset'] + $config['per_page'];
		if ($navigation->offsets->next > $navigation->total) {
			$navigation->offsets->next = $navigation->total;
		}

		$navigation->items = new stdClass;
		$navigation->items->starting = $navigation->offsets->actual + 1;
		$navigation->items->ending = $config['offset'] + $config['per_page'];
		if ($navigation->items->ending > $navigation->total) {
			$navigation->items->ending = $navigation->total;
		}

		$navigation->pages = new stdClass;
		$navigation->pages->total = ceil($navigation->total / $navigation->per_page);
		$navigation->pages->actual = ceil($navigation->offsets->actual / $navigation->per_page) + 1;

		$fin = 0;
		$ini = 0;
		$bloque = 5;

		$ini = $navigation->pages->actual - floor($bloque / 2);
		$fin = $navigation->pages->actual + floor($bloque / 2);
		if ($ini <= 0) {
			$fin += abs($ini)+1;
			$ini = 1;
		}
		if ($fin > $navigation->pages->total) {
			$ini -= $fin - $navigation->pages->total;
			if ($ini <= 0) {
				$ini = 1;
			}
			$fin = $navigation->pages->total;
		}

		$navigation->offsets->pages = array();
		for($p = $ini; $p <= $fin; $p++) {
			$navigation->offsets->pages[$p] = ($navigation->per_page * $p - $navigation->per_page);
		}

		if ($navigation->offsets->previous >= 0)
			$navigation->links->previous = true;

		if ($navigation->offsets->next < $navigation->total)
			$navigation->links->next = true;

		$navigation->limit = " LIMIT {$navigation->offsets->actual}, {$navigation->per_page}";

		return $navigation;
	}
}

/**
 * Just a little function which mimics the original mysql_real_escape_string but which doesn't need an active mysql connection.
 * http://php.net/mysql_real_escape_string#101248
 *
 * @param mixed $inp
 * @return mixed
 */
function mysql_escape_mimic($inp) {
	if(is_array($inp))
		return array_map(__METHOD__, $inp);

	if(!empty($inp) && is_string($inp)) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
	}

	return $inp;
}