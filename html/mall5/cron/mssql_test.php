<?php 
/*
----------------------------------------------------------------------
file name	 : mssql_test.php
comment		 : 
date		 : 2014-08-26
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE );

if(!function_exists('mssql_connect')) echo 'not exists!!!';
/*
try {
$_CONNECT_INFO = array(				
				'UID' => 'sa',
				'PWD' => 'Tlstkddnr80',
				'Database' => 'NTICS','CharacterSet'=>'UTF-8');

$MS_CONN = sqlsrv_connect('ntics.ntwsec.com',$_CONNECT_INFO);

} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}*/
echo '1';
?>