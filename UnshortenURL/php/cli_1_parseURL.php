<?php
/**
 * 短網址還原，由 command line 操作
 * 
 * @author ninthday <jeffy@ninthday.info>
 * @version 1.0
 * @copyright (c) 2014, Jeffy Shih
 */
require './inc/setup.inc.php';
require './classes/myPDOConn.Class.php';
require './classes/UnshortrenURL.Class.php';
// 由內容抽取網址後的儲存資料表前綴詞
$strDBPrefix = '';
// 來源資料表名稱
$strSourceDB = '';

try {
    $pdoConn = \Floodfire\myPDOConn::getInstance('myPDOConnConfig.inc.php');
    $objUnshorten = new \Floodfire\TwitterProcess\UnshortrenURL($pdoConn);
    // 設定資料表前綴詞
    $objUnshorten->setDBPrefixName($strDBPrefix);
    // 初始化資料表
    $objUnshorten->initURLTable();
    $objUnshorten->extraURLinTweetByDBName($strSourceDB);
    
    //初始化唯一短網址資料表，準備進行短網址還原
    $objUnshorten->initUniqueURLTable();
    // 將短網址資料取唯一新增至剛剛初始化的資料表
    $objUnshorten->uniqueShortURL();
    
    
} catch (Exception $ex) {
    echo $ex->getMessage();
}
