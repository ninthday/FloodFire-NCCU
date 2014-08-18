<?php

/**
 * Description of NetworkAnalysis
 * 2013-08-15
 * 由發文內容整理出網絡關係
 * 指定資料表指定欄位抽取 Mention 與 Retweet 關係，使用時需先給定資料表前綴詞
 * 與初始化儲存關係資料表。
 *
 * @author ninthday <jeffy@ninthday.info>
 * @version 1.0
 * @copyright (c) 2014, Jeffy Shih
 */

namespace Floodfire\TwitterProcess;

class NetworkAnalysis {

    private $pdoDB = NULL;
    private $dbh = NULL;
    private $strRLDBName = NULL;

    /**
     * 連線設定
     * 
     * @param \Floodfire\myPDOConn $pdoConn myPDOConn object
     */
    public function __construct(\Floodfire\myPDOConn $pdoConn) {
        $this->pdoDB = $pdoConn;
        $this->dbh = $this->pdoDB->dbh;
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
        $this->strRLDBName = $strDBPrefix . '_Relation';
    }

    /**
     * 初始化關係資料表
     * 
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function initRelationTable() {
        $sql_init = "CREATE TABLE IF NOT EXISTS `" . $this->strRLDBName . "` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tweet_id` bigint(20) NOT NULL,
            `from_user_name` varchar(255) DEFAULT NULL,
            `to_user` varchar(255) DEFAULT NULL,
            `markup` char(1) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `tweet_id` (`tweet_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        try {
            $stmt = $this->dbh->prepare($sql_init);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    /**
     * 由 tweet 中找出包含「@」符號的內容，判斷是那一種形態關係並儲存至資料庫
     * 
     * @param string $strDBName 資料庫名稱
     * @author ninthday
     * @since 1.0
     * @access public
     */
    public function buildUserRelation($strDBName) {
        $sql_get = 'SELECT `data_id`, `data_text`, `data_from_user` FROM `' . $strDBName . '` WHERE `data_text` LIKE CONCAT(\'%\', :keyword, \'%\')';
        $strKeyword = "@";
        try {
            $stmt = $this->dbh->prepare($sql_get);
            $stmt->bindParam(':keyword', $strKeyword, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $exc) {
            throw new Exception($exc->getMessage());
        }

        $aryMTFlowee = array();
        $aryRTFlowee = array();
        $i = 1;
        while ($row_get = $stmt->fetch(\PDO::FETCH_NUM)) {
            echo $i, '. ' . $row_get[0], ': ', $row_get[2], ' ---> ';
            preg_match_all("([RT ]*@[0-9a-zA-Z_]+)", $row_get[1], $out);
            foreach ($out[0] as $value) {
                if (preg_match("(RT[ ]*@[0-9a-zA-Z_]+)", $value)) {
                    //取出
                    $aryRTFlowee[] = trim(str_replace('@', '', str_replace('RT', '', $value)));
                    echo 'retweet:', $value;
                } else {
                    $aryMTFlowee[] = trim(str_replace('@', '', $value));
                    echo "mention:", $value;
                }
                echo PHP_EOL;
            }

            if (count($aryMTFlowee) > 0) {
                $this->saveRelation((int)$row_get[0], $row_get[2], $aryMTFlowee, 'M');
                unset($aryMTFlowee);
                $aryMTFlowee = array();
            }

            if (count($aryRTFlowee) > 0) {
                $this->saveRelation((int)$row_get[0], $row_get[2], $aryRTFlowee, 'R');
                unset($aryRTFlowee);
                $aryRTFlowee = array();
            }
            $i++;
        }
    }

    /**
     * 儲存使用者關係，需先初始化儲存用的關係資料表
     * 
     * @param string $strTWId Tweet ID
     * @param string $strFlower Source User
     * @param array $aryFlowee Target user
     * @param string $strType M: Mention, R: Retweet
     * @throws Exception
     * @author ninthday
     * @since 1.0
     * @access private
     */
    private function saveRelation($strTWId, $strFlower, $aryFlowee, $strType) {
        if (count($aryFlowee) != 0) {
            $sql_insert = 'INSERT INTO `' . $this->strRLDBName . '` (`tweet_id`, `from_user_name`, `to_user`, `markup`) '
                    . 'VALUES (:tweet_id, :from_user_name, :to_user, :markup)';
            $stmt = $this->dbh->prepare($sql_insert);
            $stmt->bindParam(':tweet_id', $strTWId, \PDO::PARAM_INT);
            $stmt->bindParam(':from_user_name', $strFlower, \PDO::PARAM_STR);
            $stmt->bindParam(':markup', $strType, \PDO::PARAM_STR);
            foreach ($aryFlowee as $strFolwee) {
                $stmt->bindParam(':to_user', $strFolwee, \PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }

    public function __destruct() {
        $this->pdoDB = NULL;
    }

}
