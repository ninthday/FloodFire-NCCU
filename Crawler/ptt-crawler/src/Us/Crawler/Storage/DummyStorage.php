<?php namespace Us\Crawler\Storage;

class DummyStorage implements StorageInterface
{

	function __construct()
	{
		// todo
	}

	/**
	 * Insert List
	 */
	public function InsertList($array, $board_name)
	{
		// do nothing
	}

	/**
	 * Insert Articlep
	 */
	public function InsertArticle($array, $board_name)
	{
		echo "url: https://www.ptt.cc/bbs/{$board_name}/{$array["id"]}.html\n";
		echo "author: {$array["author"]}\n";
		echo "nick: {$array["nick"]}\n";
		echo "time: {$array["time"]}\n";
		echo "content:\n{$array["content"]}\n";
	}

	public function InsertComments($article_id, $article_time, $comment_array)
	{
		foreach ($comment_array as $item) {
			$author = $item['author'];
			$time = $item['time'];
			$type = $item['type'];
			$content = $item['content'];
			echo "comments: [$type][$author][$time] $content\n";
		}

	}

	public function GetArticleByArticleId($article_id)
	{
		return false;
	}
}