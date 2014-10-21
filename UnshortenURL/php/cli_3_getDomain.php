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
$strDBPrefix = 'TP';

try {
    $pdoConn = \Floodfire\myPDOConn::getInstance('myPDOConnConfig.inc.php');
    $objUnshorten = new \Floodfire\TwitterProcess\UnshortrenURL($pdoConn);
    // 設定資料表前綴詞
    $objUnshorten->setDBPrefixName($strDBPrefix);
    
    $objUnshorten->updateUnshorted();
    $objUnshorten->extractDomain();
    
} catch (Exception $ex) {
    echo $ex->getMessage();
}
