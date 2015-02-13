<?php defined("PHRAPI") or die("Direct access not allowed!");
/**
 * Interactua con posts/posts_contents/getPostContent
 *
 * @author Enrique Velarde, twitter.com/EnriqueVelardeG
 * @copyright Tecnologías Web de México S.A. de C.V.
 *
 */
class Posts
{
	public $db;
	public $lang;
	/**
	 * @var Object instance
	 */
	private static $instance;

	public function __construct($config_index) {

		$this->db = db::getInstance($config_index);
		$this->lang = getValueFrom($_GET,'lang','es_MX',FILTER_SANITIZE_STRING);
		if(empty($this->lang)){
			$this->lang = 'es_MX';
		}
	}

	/**
	 * Singleton pattern http://en.wikipedia.org/wiki/Singleton_pattern
	 * @return object Class Instance
	 */
	public static function getInstance($config_index = null)
	{
		if ($config_index === null || $config_index == 'default' || empty($config_index)) {
			$config_index = 0;
		}

		if (!isset(self::$instance[$config_index]) || !self::$instance[$config_index] instanceof self)
			self::$instance[$config_index] = new self($config_index);

		return self::$instance[$config_index];
	}

	public function getSQLContent($id_post = '') {
		$sql = " getPostContent({$id_post},'es_MX') ";
		return $sql;
	}

	public function getPostContent($id_post = 0, $field = 'content'){
		$id_post = (int) $id_post;
		$result = $this->db->queryOne("
			SELECT
				{$field}
			FROM
				posts_contents
			WHERE
				lang = 'es_MX'
			AND
				id_post = '{$id_post}'
			ORDER BY
				modified_at DESC, id_content DESC
			LIMIT 1
		");
		return $result;
	}

	public function setPostContent($id_post = 0, $lang = 'es_MX', $new_content = ''){
		$id_post = (int) $id_post;
		$last_content =  $this->db->queryOne("SELECT getPostContent({$id_post},'{$lang}') as resp");
		$new_content = trim($new_content);

		$content_parent = (int) $this->db->queryOne(
			"SELECT
				id_content
			FROM
				posts_contents
			WHERE
				lang = '{$lang}'
			AND
				id_post = '{$id_post}'
			ORDER BY
				modified_at DESC, id_content DESC
			LIMIT 1
		");

		$content_parent = $content_parent == 0? 'NULL': $content_parent;

		if ($last_content !== $new_content) {

			$new_content  = Sanitize::mysql($new_content);
			$this->db->query("INSERT INTO
					posts_contents
				SET
					lang = '{$lang}',
					modified_at = NOW(),
					content_parent = {$content_parent},
					content = '{$new_content}',
					id_post = '{$id_post}'
			");
			$id_post_content = (int) $this->db->getLastID();
		}else{
			$id_post_content = $content_parent;
		}
		return true;
	}


	public function getPostID($description = ''){
		$this->db->query("INSERT INTO posts (label,created_at,status) VALUES ('{$description}',NOW(),'Activo')");
		$id_post = $this->db->getLastID();
		return $id_post;
	}

	public function getListCategories($parent = 'NULL', $prefix = '', $array = array()) {
		$config = $GLOBALS['config'];

		$sql_parent = "";
		if ($parent == 'NULL') {
			$sql_parent = "c.category_parent IS NULL";
		} else {
			$sql_parent = "c.category_parent = '{$parent}'";
		}
		$categories = $this->db->queryAllSpecial("
			SELECT
				getPostContent(c.p_title,'es_MX') as label,
				c.id_category as id
			FROM
				categories c
			WHERE
				1
				AND
				{$sql_parent}
			ORDER BY
				position
		");
		foreach($categories as $id => $label) {
			$array[$id] = $prefix . $label;
			$array = $this->getListCategories($id, $prefix . $label . "/", $array);
		}
		return $array;
	}

}
