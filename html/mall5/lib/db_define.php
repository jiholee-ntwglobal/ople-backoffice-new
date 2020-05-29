<?php
/**
 * Created by PhpStorm.
 * File name : db_define.php.
 * Date: 2017-12-27
 * User: Developer_kki
 */

$CONN['data_col'] = array(
    'host'=>'ntics2.ntwsec.com',
    'user'=>'sa',
    'password'=>'Admin$123$567',
    'database' => 'NTICS'
);
$CONN['data_col']['dsn'] = 'dblib:host='.$CONN['data_col']['host'].':1433;dbname='.$CONN['data_col']['database'];


$CONN['ntics'] = array(
    'host'=>'ntics.ntwsec.com',
    'user'=>'sa',
    'password'=>'Tlstkddnr80',
    'database' => 'NTICS'
);
$CONN['ntics']['dsn'] = 'dblib:host='.$CONN['ntics']['host'].':1433;dbname='.$CONN['ntics']['database'];

$CONN['ople'] = array(
    'host'=>'66.209.90.19',
    'user'=>'sales',
    'password'=>'dhvmfghkdlxld123',
    'database' => 'okflex5'
);
$CONN['ople']['dsn'] = 'mysql:host='.$CONN['ople']['host'].';dbname='.$CONN['ople']['database'];

$CONN['open'] = array(
//    'host'=>'209.216.56.102',
    'host'=>'127.0.0.1',
    'user'=>'sales',
    'password'=>'dhvmfghkdlxld123',
    'database' => 'openmarket'
);
$CONN['open']['dsn'] = 'mysql:host='.$CONN['open']['host'].';dbname='.$CONN['open']['database'];

$CONN['atm'] = array(
    'host' => '115.68.110.184',
    'user' => 'sales',
    'password' => 'dhfeotahfghkdlxld123',
    'database' => 'atmall'
);
$CONN['atm']['dsn'] = 'mysql:host='.$CONN['atm']['host'].';dbname='.$CONN['atm']['database'];

$CONN['opk'] = array(
    'host' => '115.68.114.153',
    'user' => 'neiko',
    'password' => 'rsmaker@ntwglobal',
    'database' => 'opk'
);
$CONN['opk']['dsn'] = 'mysql:host='.$CONN['opk']['host'].';dbname='.$CONN['opk']['database'];

$CONN['st11'] = array(
	'host' => '115.68.114.153',
	'user' => 'neiko',
	'password' => 'rsmaker@ntwglobal',
	'database' => 'MANAGE11ST'
);
$CONN['st11']['dsn'] = 'mysql:host='.$CONN['st11']['host'].';dbname='.$CONN['st11']['database'];

$CONN['new_opk'] = array(
	'host' => '115.68.114.153',
	'user' => 'neiko',
	'password' => 'rsmaker@ntwglobal',
	'database' => 'oplekorea'
);
$CONN['new_opk']['dsn'] = 'mysql:host='.$CONN['new_opk']['host'].';dbname='.$CONN['new_opk']['database'];
