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
    var $ntics_db;
    var $ople_db;
    var $ople_db_pdo;

    function __construct()
    {
        $hostname = "ntics.ntwsec.com";
        $port = 1433;
        $dbname = "NTICS";
        $username = "sa";
        $pw = "Tlstkddnr80";
        $this->ntics_db = new PDO ("dblib:host=$hostname:$port;dbname=$dbname","$username","$pw");

        $this->ople_db_pdo = new PDO("mysql:host=66.209.90.25;dbname=okflex5;charset=utf8", 'sales', 'dhvmfghkdlxld123');

        $this->ople_db = new mysqli('66.209.90.25','sales','dhvmfghkdlxld123','okflex5');
        $this->ople_backup = new mysqli('209.216.56.104','ople','qwe123qwe!@#','oplebackup');

    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->ntics_db = null;
        $this->ople_db = null;
        $this->ople_backup =null;
    }
}