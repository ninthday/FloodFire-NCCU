<?php namespace Us\Crawler\Storage;

use \PDO;
use \PDOException;

class RDBStorage extends Database implements StorageInterface
{

	function __construct($db_username, $db_password = '')
	{
		parent::__construct($db_username, $db_password);
	}

	/**
	 * Insert List
	 */
	public function InsertList($array, $board_name)
	{
		$sql = 'INSERT INTO list (id, forum, title, `date`, author) VALUES (:id, :forum, :title, :date, :author)';
		$bind["id"] = $array["url"];
		$bind["forum"] = $board_name;
		$bind["title"] = $array["title"];
		$bind["date"] = $array["date"];
		$bind["author"] = $array["author"];
		$res = null;
		try {
			$query = $this->db->prepare($sql);
			$res = $query->execute($bind);
		} catch (PDOException $e) {
			if ($e->getCode() === "23000") {
				// skip on duplicate key
			} else {
				// TODO handle more exception
				throw $e;
			}
		}
		return $res;
	}

	/**
	 * Insert Article
	 */
	public function InsertArticle($article_array, $board_name)
	{
		$sql = "INSERT INTO article (id, forum, author, nick, title, content, `ts`) VALUES (:id, :forum, :author, :nick, :title, :content, :ts)";
		$bind["id"] = $article_array["id"];
		$bind["forum"] = $board_name;
		$bind["author"] = $article_array["author"];
		$bind["nick"] = $article_array["nick"];
		$bind["title"] = $article_array["title"];
		$bind["content"] = $article_array["content"];
		$bind["ts"] = date("Y-m-d H:i:s", $article_array["ts"]);

		$count = 0;
		while ($count < 3) {
			try {
				$query = $this->db->prepare($sql);
				$query->execute($bind);
				$count = 3; // FIXME weird logic here
			} catch (PDOException $e) {
				if ($e->errorInfo[1] == SERVER_SHUTDOWN_CODE) {
						$count++;
						$this->reconnectPDO();
					}
				throw $e;
			}
		}

		return $this->db->lastInsertId();
	}

	public function InsertComments($article_id, $article_time, $comment_array)
	{
		$year = date("Y", $article_time); // use article year as comment year
		foreach ($comment_array as $item) {
			$item['ts'] = date("Y-m-d H:i:s", strtotime($item['time'] . " $year")); // FIXME dangerous
			$sql = 'INSERT INTO `comment` (article_id, `type`, content, `ts`, author) VALUES (:article_id, :type, :content, :ts, :author)';
			$bind["article_id"] = $article_id;
			$bind["type"] = $item["type"];
			$bind["author"] = $item["author"];
			$bind["content"] = $item["content"];
			$bind["ts"] = $item["ts"];
			$count = 0;
			while ($count < 3) {
				try {
					$query = $this->db->prepare($sql);
					$query->execute($bind);
					break;
				} catch (PDOException $e) {
					if ($e->errorInfo[1] == SERVER_SHUTDOWN_CODE) {
						$this->reconnectPDO();
					}
					$count++;
				}
			}
		}

		return $this->db->lastInsertId();
	}

	public function GetArticleByArticleId($article_id)
	{
		$sql = "SELECT * FROM article WHERE id = :article_id";
		$bind["article_id"] = $article_id;

		try {
			$query = $this->db->prepare($sql);
			$query->execute($bind);
		} catch (PDOException $e) {
			throw $e;
		}
		$result = $query->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
}