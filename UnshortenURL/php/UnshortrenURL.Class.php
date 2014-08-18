<?php

/**
 * Description of UnshortrenURL
 * 2013-08-13
 * 目前主要使用在處理 niceKeeper 收集回來的資料，如使用在其他工具收集回來的資料表，
 * 需要修改資料表的欄位名稱。
 *
 * @author ninthday <jeffy@ninthday.info>
 * @version 1.0
 * @copyright (c) 2014, Jeffy Shih
 */

namespace Floodfire\TwitterProcess;

class UnshortrenURL {

    private $pdoDB = NULL;
    private $dbh = NULL;
    private $objParse = NULL;
    private $strURLDBName = NULL;
    private $strUniqueDBName = NULL;

    /**
     * 連線設定
     * @param \Floodfire\myPDOConn $pdoConn myPDOConn object
     */
    public function __construct(\Floodfire\myPDOConn $pdoConn) {
        $this->pdoDB = $pdoConn;
        $this->dbh = $this->pdoDB->dbh;

        require _APP_PATH . 'classes/ParseContent.Class.php';
        $this->objParse = new \Floodfire\TwitterProcess\ParseContent($this->pdoDB);
    }

    /**
     * 設定資料庫前綴詞
     * 
     * @param string $strDBPrefix 整理用資料表前綴詞名稱
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function setDBPrefixName($strDBPrefix) {
        if (empty($strDBPrefix)) {
            throw new Exception('Database prefix name is empty!');
        }
        $this->strURLDBName = $strDBPrefix . '_URLinTweets';
        $this->strUniqueDBName = $strDBPrefix . '_UniqueURL';
    }

    /**
     * 初始化記錄抽取短網址的資料表
     * 
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function initURLTable() {
        $sql_init = "CREATE TABLE IF NOT EXISTS `" . $this->strURLDBName . "` (
            `URTId` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `TweetId` bigint(20) unsigned NOT NULL,
            `ShortenURL` varchar(255) NOT NULL COMMENT '截取的短網址',
            `regularURL` text COMMENT '原來的網址',
            `domainURL` text COMMENT 'only domain',
            PRIMARY KEY (`URTId`),
            KEY `TweetId` (`TweetId`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        try {
            $stmt = $this->dbh->prepare($sql_init);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new \Exception($exc->getMessage());
        }
    }

    /**
     * 初始化整理用單一短網址資料表
     * 
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function initUniqueURLTable() {
        $sql_init = "CREATE TABLE IF NOT EXISTS `" . $this->strUniqueDBName . "` (
            `URTId` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `ShortenURL` varchar(255) NOT NULL COMMENT '截取的短網址',
            `regularURL` text COMMENT '原來的網址',
            PRIMARY KEY (`URTId`),
            UNIQUE KEY `ShortenURL` (`ShortenURL`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        try {
            $stmt = $this->dbh->prepare($sql_init);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    /**
     * 由指定資料表的推文欄位中取出網址，呼叫函式儲存至資料表
     * 
     * @param String 資料表名稱
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function extraURLinTweetByDBName($strDBName) {

        $sql_get = 'SELECT `text`, `id` FROM `' . $strDBName . '`';
        try {
            $stmt = $this->dbh->prepare($sql_get);
            $stmt->bindParam(':dbName', $strDBName, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $aryURLs = $this->objParse->getURLContent($row['text']);
            $this->saveURLsSingleTB($row['id'], $aryURLs);
            echo $row['id'] . ' ----> ' . count($aryURLs) . PHP_EOL;
        }
    }

    /**
     * 將短網址資料取唯一新增至另一個資料表，以便進行短網址還原
     * 唯一化短網址主要的目的，是減少相同短網址做多次解析的時間
     * 
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function uniqueShortURL() {
        $sql = "INSERT INTO `" . $this->strUniqueDBName . "`(`ShortenURL`)
            SELECT `ShortenURL` FROM `" . $this->strURLDBName . "`
            GROUP BY `ShortenURL`";
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    /**
     * 由資料表取資料進行短網址還原
     * 
     * @param int $intBegain
     * @author ninthday
     * @since 1.0
     * @throws Exception
     * @access public
     */
    public function restoreShortURL($intBegain, $intSeg) {
//        $intSeg = 6000;
        $intLimitBegin = ($intBegain - 1) * $intSeg;
        $sql_get = 'SELECT * FROM `' . $this->strUniqueDBName . '` WHERE `regularURL` IS NULL LIMIT ' . $intLimitBegin . ', ' . $intSeg;
        echo $sql_get, PHP_EOL;
        try {
            $stmt = $this->dbh->prepare($sql_get);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }

        $intCount = $intLimitBegin;
        $arySaveRegular = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $intCount += 1;
            echo $intCount . '.(' . $row['URTId'] . ')' . PHP_EOL;
            usleep(rand(250000, 500000));
            $strRegularURL = $this->objParse->expendShortURLBycURL($row['ShortenURL']);
            if ($intCount % 10 == 0) {
                $this->saveRegularURL($arySaveRegular);
                unset($arySaveRegular);
                $arySaveRegular = array();
                array_push($arySaveRegular, array($row['URTId'], $strRegularURL));
            } else {
                array_push($arySaveRegular, array($row['URTId'], $strRegularURL));
            }
            echo 'Child-', $intBegain, ': ', $row['ShortenURL'], ' --> ', $strRegularURL, PHP_EOL;
        }

        if ($intCount % 10 != 0) {
            $this->saveRegularURL($arySaveRegular);
        }
    }

    /**
     * 取得唯一化資料表中需被短網址還原的數量
     * 
     * @return int 需要被短網址還原的數量
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function getUnshortenAmount() {
        $sql_get = 'SELECT COUNT(*) FROM `' . $this->strUniqueDBName . '` WHERE `regularURL` IS NULL';
        try {
            $stmt = $this->dbh->prepare($sql_get);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        return (int) $row[0];
    }

    /**
     * 將唯一化資料表已還原的網址更新回原來的表
     * 
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function updateUnshorted() {
        $sql = 'UPDATE `' . $this->strURLDBName . '`
            INNER JOIN `' . $this->strUniqueDBName . '` ON `' . $this->strUniqueDBName . '`.`ShortenURL` = `' . $this->strURLDBName . '`.`ShortenURL` 
            SET `' . $this->strURLDBName . '`.`regularURL`= `' . $this->strUniqueDBName . '`.`regularURL`';
        echo $sql;
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    /**
     * 由指定名稱資料庫中取出 domain name 分析
     * 
     * @param string $strDBName
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function extractDomain() {
        $sql_get = 'SELECT * FROM `' . $this->strURLDBName . '`';
        try {
            $stmt = $this->dbh->prepare($sql_get);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }
        $intCount = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $intCount += 1;
            $strDomain = parse_url($row['regularURL'], PHP_URL_HOST);
            $this->saveDomain($row['URTId'], $strDomain);
            if ($intCount % 100 == 0) {
                echo $intCount . '............. ' . PHP_EOL;
            }
        }
    }

    /**
     * 將取出的短網址存入 URLinTweets 資料表
     * 
     * @param string $strTweetID Twitter編號
     * @param array $aryURLs 包含一維的網址陣列
     * @author ninthday
     * @since 1.0
     * @access private
     */
    private function saveURLsSingleTB($strTweetID, $aryURLs) {
        $sql_insert = 'INSERT INTO `' . $this->strURLDBName . '`(`TweetId`, `ShortenURL`) '
                . 'VALUES (:TweetId, :ShortenURL)';
        $stmt = $this->dbh->prepare($sql_insert);
        $stmt->bindParam(':TweetId', $strTweetID, \PDO::PARAM_STR);
        foreach ($aryURLs as &$strURL) {
            $stmt->bindParam(':ShortenURL', $strURL, \PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    /**
     * 儲存短網址還原後的原始 URL，每次多筆資料一起儲存
     * 
     * @param array $arySavePair
     * @author ninthday
     * @since 1.0
     * @access private
     */
    private function saveRegularURL($arySavePair) {
        $sql_update = 'UPDATE `' . $this->strUniqueDBName . '` SET `regularURL`=:regularURL WHERE `URTId`=:URTId';
        $stmt = $this->dbh->prepare($sql_update);
        foreach ($arySavePair as $aryPair) {
            $stmt->bindParam(':regularURL', $aryPair[1], \PDO::PARAM_STR);
            $stmt->bindParam(':URTId', $aryPair[0], \PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /**
     * 儲存由 URL 取出的 Domain name 資料
     * 
     * @param int $intURTId 標號
     * @param string $strDomain 網域名稱
     * @author ninthday
     * @since 1.0
     * @access private
     */
    private function saveDomain($intURTId, $strDomain) {
        $sql_update = 'UPDATE `' . $this->strURLDBName . '` SET `domainURL`=:domainURL WHERE `URTId`=:URTId';
        $stmt = $this->dbh->prepare($sql_update);
        $stmt->bindParam(':domainURL', $strDomain, \PDO::PARAM_STR);
        $stmt->bindParam(':URTId', $intURTId, \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * 取得現在的時間
     * 
     * @return String Return Now DateTime
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function getNowTime() {
        return date('Y-m-d H:i:s');
    }

    public function __destruct() {
        $this->pdoDB = NULL;
        $this->objParse = NULL;
    }

}
