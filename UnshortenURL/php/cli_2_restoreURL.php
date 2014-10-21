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
// 來源資料表名稱
$strSourceDB = '';

$intProc_num = 3;
$aryChildren = array();

try {
    $pdoConn = \Floodfire\myPDOConn::getInstance('myPDOConnConfig.inc.php');
    $objUnshorten = new \Floodfire\TwitterProcess\UnshortrenURL($pdoConn);
    // 設定資料表前綴詞
    $objUnshorten->setDBPrefixName($strDBPrefix);
    // 取得要還原的總數量
    $intAmount = $objUnshorten->getUnshortenAmount();
    // 每個子程序要還原的數量
    $intSeg = ceil($intAmount / $intProc_num);
    // 建立多執行程序
    for ($i = 1; $i <= $intProc_num; $i++) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            exit(1);
        } else if ($pid) {
            //父程序執行
            $aryChildren[] = $pid; //紀錄下每個子程序的編號
            print $objUnshorten->getNowTime() . '--> Parent: fork no.' . $i . ' process, pid is ' . $pid . PHP_EOL;
        } else {
            //子程序
            break; //直接出迴圈
        }
    }

    if ($pid) {
        /* 父程序等待區 */
        $status = null;
        sleep(120);
        foreach ($aryChildren as $pid) { //要等每個孩子都離開才離開
            pcntl_waitpid($pid, $status);
            print 'Parent: The process pid - ' . $pid . ' is End. ---------------------------------' . PHP_EOL;
        }
        print 'Parent process is End.' . PHP_EOL;
    } else {
        /* 子程序執行區 */
        $objUnshorten->restoreShortURL($i, $intSeg);
        print $objUnshorten->getNowTime() . 'I am the ' . $i .' Children process, I will stop.' . PHP_EOL;
        exit(0);
    }
    
} catch (Exception $ex) {
    echo $ex->getMessage();
}



