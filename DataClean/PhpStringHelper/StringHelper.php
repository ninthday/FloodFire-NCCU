<?php namespace Floodfire\Helper;
class StringHelper {
	public static function replace_symbles($str_in, $token = '`') {
		// source http://blog.xuite.net/vexed/tech/60284077-PHP+%E5%8E%BB%E6%8E%89%E5%AD%97%E4%B8%B2%E5%85%A8%E5%9E%8B%E5%8D%8A%E5%9E%8B%E6%A8%99%E9%BB%9E%E7%AC%A6%E8%99%9F
		$str_in = strip_tags($str_in); // XXX
		return str_replace(
			array(
				'!', '"', '#', '$', '%', '&', '\'', '(', ')', '*',
				'+', ', ', '-', '.', '/', ':', ';', '<', '=', '>',
				'?', '@', '[', '\\', ']', '^', '_', '`', '{', '|',
				'}', '~', '；', '﹔', '︰', '﹕', '：', '，', '﹐', '、',
				'．', '﹒', '˙', '·', '。', '？', '！', '～', '‥', '‧',
				'′', '〃', '〝', '〞', '‵', '‘', '’', '『', '』', '「',
				'」', '“', '”', '…', '❞', '❝', '﹁', '﹂', '﹃', '﹄',
				"\n", "\r", '	', '【', '╱', '】', '）', '（',
			),
			$token,
			$str_in);
	}


	public static function split_article($str_in) {
		$str_out = self::replace_symbles($str_in);
		//mb_internal_encoding('utf-8');
		$str_out = preg_replace('/[`]+/', '`', $str_out);
		//mb_internal_encoding('iso-8859-1');
		$res = explode('`', $str_out);
		return $res;
	}

	public static function generate_split_article_array($input_array, $delimeter = ' ', $max_length = 140) {
		$res_array = array();
		$str = '';
		foreach($input_array as $val) {
			$new_str = $str . $delimeter . $val;
			if (mb_strlen($new_str, 'utf8') >= $max_length) {
				array_push($res_array, $str);
				$str = $val;
			} else {
				$str = $new_str;
			}
		}
		array_push($res_array, $str);
		return $res_array;
	}
}
