<?php namespace Us\Crawler\Storage;

interface StorageInterface {

    /**
     * Insert List
     */
    public function InsertList($array, $board_name);

    /**
     * Insert Article
     */
    public function InsertArticle($array, $board_name);

    public function InsertComments($article_id, $article_time, $comment_array);

    public function GetArticleByArticleId($article_id);
}

