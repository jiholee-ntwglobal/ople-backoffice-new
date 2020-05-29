<?php

/**
 * Created by PhpStorm.
 * File name : db.php.
 * Comment :
 * Date: 2016-01-19
 * User: Minki Hong
 */
class db
{
    //20190517 곽범석 주석
    var $ntics_db;
    var $ople_db;
    var $ople_db_pdo;
    var $test_pdo;

    function __construct()
    {
        $hostname = "ntics.ntwsec.com";
        $port = 1433;
        $dbname = "NTICS";
        $username = "sa";
        $pw = "Tlstkddnr80";
        //20190517 곽범석 주석
       $this->ntics_db = new PDO ("dblib:host=$hostname:$port;dbname=$dbname","$username","$pw");
        
        $this->test_pdo = new PDO ("dblib:host=$hostname:$port;dbname=$dbname;charset=UTF-8","$username","$pw");

        $this->ople_db_pdo = new PDO("mysql:host=66.209.90.19;dbname=okflex5;charset=utf8", 'sales', 'dhvmfghkdlxld123');

        $this->ople_db = new mysqli('66.209.90.19','sales','dhvmfghkdlxld123','okflex5');

    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        //20190517 곽범석 주석
        $this->ntics_db = null;
        $this->ople_db = null;
    }
}