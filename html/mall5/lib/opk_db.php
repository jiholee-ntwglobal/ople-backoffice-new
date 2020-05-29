<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-06-18
 * Time: 오전 11:50
 */

class opk_db {
    var $opk_db;
    function __construct(){
        $opk_host = '115.68.114.153';
        $opk_id = 'neiko';
        $opk_pw = 'rsmaker@ntwglobal';
        $opk_db = 'opk';
        //$this->opk_db = new PDO("mysql:host=$opk_host;dbname=$opk_db;charset=utf8", $opk_id, $opk_pw);
        $this->opk_db = new mysqli($opk_host,$opk_id,$opk_pw,$opk_db);
        $this->opk_db->query("set names utf8");
    }

    function query($sql){
        $this->opk_db->query($sql);
    }
}