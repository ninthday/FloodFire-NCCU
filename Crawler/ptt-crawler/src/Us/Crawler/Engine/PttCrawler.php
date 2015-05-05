<?php namespace Us\Crawler\Engine;

use \Sunra\PhpSimple\HtmlDomParser;
use \Us\Crawler\Storage\StorageInterface;

class PttCrawler
{
	private $storage = null; // Storage物件
	private $board_name = null; // 版名
	private $config = array(); // 設定參數陣列

	const STATE_DUPLICATED = 0x01;
	const STATE_DATE_REACHED = 0x02;
	const STATE_DATE_AHEAD = 0x04;

	public function __construct(StorageInterface $storage, $board_name)
	{
		date_default_timezone_set("Asia/Taipei");

		$this->storage = $storage;
		$this->board_name = $board_name;
		$this->set_config(null);
	}

	public function set_config($config)
	{
		// 每頁清單抓完間隔時間
		$this->config["list_sleep"] = (!isset($config["list_sleep"])) ? 2 : $config["list_sleep"];
		// 每篇文章抓完間隔時間
		$this->config["article_sleep"] = (!isset($config["article_sleep"])) ? 2 : $config["article_sleep"];
		// 連線失敗間隔時間
		$this->config["error_sleep"] = (!isset($config["error_sleep"])) ? 2 : $config["error_sleep"];
		// 連線送出timeout
		$this->config["timeout"] = (!isset($config["timeout"])) ? 10 : $config["timeout"];
		$this->config["start-page"] = (!isset($config["start-page"])) ? null : $config["start-page"];
		// 抓取文章的最後日期
		$this->config["stop-date"] = (!isset($config["stop-date"])) ? date("Y-m-d") : $config["stop-date"];
		$this->config["start-date"] = (!isset($config["start-date"])) ? date("Y-m-d") : $config["start-date"];
		// 設定是否只抓到上次的最後一篇
		$this->config["stop-on-duplicate"] = $config["stop-on-duplicate"];
	}

	// 供外部程式呼叫執行
	public function run()
	{
		if ($this->main()) {
			return 0;
		} else {
			return 1;
		}

	}

	// 主程式邏輯
	private function main()
	{
		$state = 0;
		$is_stop = false;
		if (isset($this->config['start-page'])) {
			$last_page = $this->config['start-page'];
		} else {
			// 取得總頁數
			$last_page = $this->page_count();
			if ($last_page === null) {
				$this->error_output("Failed to get total page \n");
				return false;
			}
		}

		for ($i = $last_page; $i >= 1; $i--) {
			sleep($this->config["list_sleep"]);
			// 檢查爬蟲是否該繼續爬資料
			if ($is_stop) break;
			// 取得每頁文章基本資料
			$current_page = $this->fetch_page($i);

			// 過濾失敗文章
			if ($current_page == null) {
				$this->error_output("notice! list: " . $i . " was skipped \n");
				continue;
			}

			$save_article_arr = array();
			foreach ($current_page as $item) {
				// 略過已抓過的文章
				$article_id = $item['url'];
				if ($this->storage->GetArticleByArticleId($article_id)) {
					$this->error_output("notice! article: " . $article_id . " ({$item['date']}) has been in database \n");
					// 檢查是否抓到上次最後一篇
					if ($this->config["stop-on-duplicate"] && $i < ($last_page - 1)) {
						// 頭兩頁不因為重複而停止
						$is_stop = true;
						$state |= self::STATE_DUPLICATED;
					}
					continue;
				}

				// skip articles when date ahead
				if ($this->is_date_ahead($item["date"])) {
					$this->error_output("notice! article: " . $item["url"] . " ({$item['date']}) is ahead " . $this->config["start-date"] . ", skipped \n");
					$state |= self::STATE_DATE_AHEAD;
					continue;
				}
				// 略過已到設定日期文章
				if ($this->is_date_over($item["date"]) && $i < ($last_page - 1)) {
					// 頭兩頁不因為過期而停止
					$this->error_output("notice! article: " . $item["url"] . " ({$item['date']} is earlier than " . $this->config["stop-date"] . " \n");
					$is_stop = true;
					$state |= self::STATE_DATE_REACHED;
					continue;
				}
				// 存入要抓取詳細資料的article陣列
				array_push($save_article_arr, $item);
				// 存入每頁文章基本資料
				try {
					$this->storage->InsertList($item, $this->board_name);
				} catch (PDOException $e) {
					if ($e->errorInfo[1] == SERVER_SHUTDOWN_CODE) {
						exit("mysql server connection error!");
						// todo
					} // FIXME URGENT what else ?
				}
			}

			foreach ($save_article_arr as $item) {
				$this->error_output("fetching article id: " . $item["url"] . "({$item['date']}) \n");
				// 取得每筆文章詳細資料
				$article_array = $this->fetch_article($item["url"]);
				// 過濾詭異文章
				if ($article_array == null) {
					$this->error_output("notice! article: " . $item["url"] . " was skipped \n");
					continue;
				}
				// 存入每筆文章詳細資料(returned id)
				$article_array['ts'] = 0;
				try {
					// convert to unix timestamp
					$article_array['ts'] = strtotime($article_array["time"]);
					$article_array['title'] = $item['title'];
					$this->storage->InsertArticle($article_array, $this->board_name);
				} catch (PDOException $e) {
					if ($e->errorInfo[1] == SERVER_SHUTDOWN_CODE) { // FIXME just $e->getCode()
						exit("mysql server connection error!");
						// todo
					}
				}

				try {
					$this->storage->InsertComments($article_array['id'], $article_array['ts'], $article_array['comments']);
				} catch (PDOException $e) {
					if ($e->errorInfo[1] == SERVER_SHUTDOWN_CODE) { // FIXME just $e->getCode()
						exit("mysql server connection error!");
						// todo
					}
				}
				sleep($this->config["article_sleep"]);
			}
		}
		// 檢測文章是否到期
		if ($state & self::STATE_DATE_REACHED) {
			$this->error_output("Stop fetching cause the article date are older than " . $this->config["stop-date"] . "\n");
		}

		// 檢查是否已重複
		if ($state & self::STATE_DUPLICATED) {
			$this->error_output("Stop fetching cause the articles are duplicated. \n");
		}

		$this->error_output("Fetch finished! \n");
		return true;
	}

	// 取得該版總頁數
	private function page_count()
	{
		$result = array();
		$dom = HtmlDomParser::str_get_html($this->fetch_page_html(null), $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=false);
		if (!$dom) {
			return null;
		}
		foreach ($dom->find('a[class=btn wide]') as $element) {
			array_push($result, $element->href);
		}
		$last_page = str_replace(array("/bbs/" . $this->board_name . "/index", ".html"), "", $result[1]) + 1;
		$this->error_output("total page: " . $last_page . "\n");
		return $last_page;
	}

	// 取得當頁的文章基本資料
	private function fetch_page($index)
	{
		$this->error_output("fetching page: " . $index . "\n");
		$dom = HtmlDomParser::str_get_html($this->fetch_page_html($index), $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=false);
		// 如果取得資料失敗, 回傳NULL
		if ($dom == null) {
			return null;
		}
		$result = array();
		$post_temp = array();
		$count = 0;
		foreach ($dom->find('div[class=title] a, div[class=date], div[class=author]') as $element) {
			$count++;
			$post = array();
			if ($count % 3 == 1) { // FIXME kind of ugly
				$post_temp["url"] = str_replace(array("/bbs/" . $this->board_name . "/", ".html"), "", $element->href); // FIXME this should be called article_id instead of url
				$post_temp["title"] = $element->plaintext;
				// 過濾被刪除文章
				if (empty($post_temp["url"])) {
					$post_temp = array();
					$count = 0;
				}
			} else if ($count % 3 == 2) {
				$post_temp["date"] = $element->plaintext;
			} else {
				$post_temp["author"] = $element->plaintext;
				array_push($result, $post_temp);
				$post_temp = array();
			}
		}
		return $result;
	}

	// 取得當頁的html
	private function fetch_page_html($index = null)
	{
		$result = null;
		$url = "https://www.ptt.cc/bbs/{$this->board_name}/index{$index}.html";
		$context = $this->init_opts();

		// 連線逾時超過三次, 回傳NULL
		$error_count = 0;
		while ($error_count < 3 && ($result = @file_get_contents($url, false, $context)) == false) {
			$this->error_output("connection error, retry... \n"); // FIXME what kind of error? URGENT
			sleep($this->config["error_sleep"]);
			$error_count++;
		}
		return $result;
	}

	// 取得當篇文章的html
	private function fetch_article_html($id)
	{
		$result = null;
		$url = "https://www.ptt.cc/bbs/{$this->board_name}/{$id}.html";
		$context = $this->init_opts();

		// 連線逾時超過三次, 回傳NULL
		$error_count = 0;
		while ($error_count < 3 && ($result = @file_get_contents($url, false, $context)) == false) { // FIXME do not use @
			$response = substr(@$http_response_header[0], 9, 3); // FIXME use explode, do not use substr, do not use @
			if ($response == "404") {
				$this->error_output("response 404..., this article will be skipped \n");
				break;
			} else {
				$this->error_output("connection error, retry... \n");
				sleep($this->config["error_sleep"]);
				$error_count++;
			}
		}
		return $result;
	}

	// 取得當篇文章的詳細資料
	private function fetch_article($id)
	{
		$dom = HtmlDomParser::str_get_html($this->fetch_article_html($id), $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=false);

		// 如果取得資料失敗, 回傳NULL
		if ($dom == null) {
			return null;
		}
		$result = array();


		$count = 0;
		foreach ($dom->find('span[class=article-meta-value]') as $element) {
			$count++;
			if ($count % 4 == 0) {
				$result["id"] = $id;
				$result["time"] = trim($element->plaintext);
			} else if ($count % 4 == 1) {
				$author_string = trim($element->plaintext);
				$matches = array();
				preg_match('/^(?P<author>.*) \((?P<nick>.*)\)/', $author_string, $matches);
				$result['author'] = $matches['author'];
				$result['nick'] = $matches['nick'];
			}
		}


		// 取得文章內容
		// FIXME don't use foreach. there will be only one main-container in a page.
		foreach ($dom->find('div[id=main-container]') as $element) {
			$content = strip_tags(trim($element));

			// 整篇文章
			$article_array = explode(PHP_EOL, $content);
			// 內文
			$content_array = array();
			// 推文
			$valid_comments = array(
				'+' => '推',
				'-' => '噓',
				'=' => '→'
			);
			$comment_array = array();

			// trim article headers
			if (count($article_array) > 2) {
				if (trim($article_array[0]) === "") {
					array_shift($article_array);
				}
				$meta_line = trim($article_array[0]);
				if (strpos($meta_line, "作者") === 0) {
					array_shift($article_array);
				}
			}

			// trim article footers
			while (count($article_array) && trim($article_array[count($article_array) - 1]) === "") {
				array_pop($article_array);
			}

			// comments

			$is_comments_start = false;
			foreach ($article_array as $line) {
				$line = htmlspecialchars_decode($line);
				if (strpos($line, "※ 發信站:") === 0) {
					// the following lines are comments
					$is_comments_start = true;
				}

				// 還沒到 comment, 就當作是內文
				if (!$is_comments_start) {
					array_push($content_array, $line);
				} else {
					$comment_type_str = mb_substr($line, 0, 1, "utf-8");
					if (in_array($comment_type_str, $valid_comments)) {
						$matches = array();
						preg_match('/^(?P<type_str>[推噓→]) (?P<author>[a-zA-Z0-9]+)\s*:\s*(?P<content>.*)[ ]*(?P<time>\d\d\/\d\d \d\d:\d\d)$/u', $line, $matches);
						$actual_keys = array_keys($matches);
						$expected_keys = array('type_str', 'author', 'content', 'time');
						if (count(array_intersect($expected_keys, $actual_keys)) !== 4) {
							$this->error_output("Incorrect comment line: " . $line . "\n");
						} else {
							$comment_data = array(
								'type' => array_search($matches['type_str'], $valid_comments),
								'author' => $matches['author'],
								'content' => $matches['content'],
								'time' => $matches['time'],
							);

							array_push($comment_array, $comment_data);
						}
					}
				}
			}

			$result['comments'] = $comment_array;
			$result['content'] = implode("\n", $content_array);
		}

		// 過濾詭異文章
		if (!isset($result["id"])) {
			return null;
		}
		return $result;
	}

	private function is_date_over($article_date)
	{
		return (strtotime(date($article_date)) <= strtotime('-1 day', strtotime($this->config["stop-date"]))) ? true : false;
	}

	private function is_date_ahead($article_date)
	{
		return (strtotime(date($article_date)) > strtotime($this->config["start-date"])) ? true : false;
	}

	private function init_opts()
	{
		$opts = array(
			'http' => array(
				'method' => "GET",
				'timeout' => $this->config["timeout"],
				'header' => "Accept-language: zh-TW\r\n" . "Cookie: over18=1\r\n",
				'User-Agent' => "Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko"
			)
		);
		return stream_context_create($opts);
	}

	private function error_output($message)
	{
		$fh = fopen('php://stderr', 'w');
		fwrite($fh, $message);
		fclose($fh);
	}
}
