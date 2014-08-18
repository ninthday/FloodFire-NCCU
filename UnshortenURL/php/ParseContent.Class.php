<?php

/**
 * Description of ParseContent
 * 用來解析資料的類別
 * 
 * @author ninthday <jeffy@ninthday.info>
 * @version 1.0
 * @copyright (c) 2014, Jeffy Shih
 */
namespace Floodfire\TwitterProcess;
class ParseContent {

    public function __construct() {
        
    }

    /**
     * 由內容中取得所有的 URLs
     * 
     * @param String $strContent 內容字串
     * @return array URLs 陣列
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function getURLContent($strContent) {
        $rtn = array();

        // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|sftp)\:\/\/[a-zA-Z0-9\-\.]+\.\w{2,3}(\/\w*)?/";
        preg_match_all($reg_exUrl, $strContent, $out);

        foreach ($out[0] as $url) {
            array_push($rtn, $url);
        }

        return $rtn;
    }

    /**
     * 利用 get_header 方式短網址還原
     * （不推薦使用保留做研究）
     * 
     * @param string $strURL 短網址
     * @return string 還原後的網址
     */
    public function expendShortURL($strURL) {
        $aryHeader = get_headers($strURL, 1);
        $intCount = count($aryHeader['Location']);
        return $aryHeader['Location'][$intCount - 1];
    }

    /**
     * 利用 get_header 方式短網址還原
     * （不推薦使用保留做研究）
     * 
     * @param string $strURL 短網址
     * @return string 還原後的網址
     */
    public function expendShortURL2($strURL) {
        $strURL = trim($strURL);
        //Get response headers
        $response = get_headers($strURL, 1);

        if (array_key_exists('location', $response)) {
            $location = $response["location"];
            if (is_array($location)) {
                if ($location[count($location) - 1] == $location[count($location) - 2]) {
                    return $location[count($location) - 1];
                }
                // t.co gives Location as an array
                return $this->expendShortURL2($location[count($location) - 1]);
            } else {
                return $this->expendShortURL2($location);
            }
        } elseif (array_key_exists('Location', $response)) {
            $location = $response["Location"];
            if (is_array($location)) {
                if ($location[count($location) - 1] == $location[count($location) - 2]) {
                    return $location[count($location) - 1];
                }
                // t.co gives Location as an array
                return $this->expendShortURL2($location[count($location) - 1]);
            } else {
                return $this->expendShortURL2($location);
            }
        }
        return $strURL;
    }

    /**
     * 利用 get_header 方式短網址還原
     * （不推薦使用保留做研究）
     * 
     * @param string $strURL 短網址
     * @return string 還原後的網址
     */
    public function expendShortURL3($strURL) {
        $url = trim($strURL);
        $headers = get_headers($url, 1);
        var_dump($headers);
        $location = $url;
        $short = false;
        foreach ($headers as $head) {
            if ($head == "HTTP/1.1 302 Found") {
                $short = true;
            }

            if ($short && $this->startwith($head, "Location: ")) {
                $location = substr($head, 10);
            }
        }
        return $location;
    }

    /**
     * 利用cURL的方式短網址還原（建議使用）
     * 
     * @param string $strURL 短網址
     * @return string 還原後的網址
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function expendShortURLBycURL($strURL) {
        $ch = curl_init("$strURL");
        curl_setopt_array($ch, array(
            CURLOPT_FOLLOWLOCATION => TRUE,     // the magic sauce
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,    // suppress certain SSL errors
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_USERAGENT => "Google Bot",
            CURLOPT_CONNECTTIMEOUT => 10,       // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely
            CURLOPT_TIMEOUT => 10               // The maximum number of seconds to allow cURL functions to execute
        ));
        curl_exec($ch);
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $url;

    }
    
    /**
     * 找出網址中的 Domain name
     * 
     * @param type $strURL
     * @return string 傳入網址的 Domain Name
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function getDomainByURL($strURL){
        return parse_url($strURL, PHP_URL_HOST);
    }

    private function startwith($Haystack, $Needle) {
        return strpos($Haystack, $Needle) === 0;
    }

    public function __destruct() {
        
    }

}
