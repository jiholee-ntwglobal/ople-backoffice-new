<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['dsn']      The full DSN string describe a connection to the database.
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database driver. e.g.: mysqli.
|			Currently supported:
|				 cubrid, ibase, mssql, mysql, mysqli, oci8,
|				 odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Query Builder class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['encrypt']  Whether or not to use an encrypted connection.
|
|			'mysql' (deprecated), 'sqlsrv' and 'pdo/sqlsrv' drivers accept TRUE/FALSE
|			'mysqli' and 'pdo/mysql' drivers accept an array with the following options:
|
|				'ssl_key'    - Path to the private key file
|				'ssl_cert'   - Path to the public key certificate file
|				'ssl_ca'     - Path to the certificate authority file
|				'ssl_capath' - Path to a directory containing trusted CA certificates in PEM format
|				'ssl_cipher' - List of *allowed* ciphers to be used for the encryption, separated by colons (':')
|				'ssl_verify' - TRUE/FALSE; Whether verify the server certificate or not ('mysqli' only)
|
|	['compress'] Whether or not to use client compression (MySQL only)
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|	['ssl_options']	Used to set various SSL options that can be used when making SSL connections.
|	['failover'] array - A array with 0 or more data for connections if the main should fail.
|	['save_queries'] TRUE/FALSE - Whether to "save" all executed queries.
| 				NOTE: Disabling this will also effectively disable both
| 				$this->db->last_query() and profiling of DB queries.
| 				When you run a query, with this setting set to TRUE (default),
| 				CodeIgniter will store the SQL statement for debugging purposes.
| 				However, this may cause high memory usage, especially if you run
| 				a lot of SQL queries ... disable this to avoid that problem.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $query_builder variables lets you determine whether or not to load
| the query builder class.
*/
$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'	=> '',
    'hostname' => '127.0.0.1',
    'username' => 'qten',
    'password' => 'thwlsWkd',
    'database' => 'qten',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array()
//    'save_queries' => TRUE
);

//	Ntics Shipping Database
$db['ntics']['hostname'] = 'ntics.ntwsec.com';
$db['ntics']['username'] = 'sa';
$db['ntics']['password'] = 'Tlstkddnr80';
$db['ntics']['database'] = 'NTICS';
$db['ntics']['dbdriver'] = 'pdo';
$db['ntics']['dsn'] = "dblib:host={$db['ntics']['hostname']};port=1433;dbname={$db['ntics']['database']}";
$db['ntics']['dbprefix'] = '';
$db['ntics']['pconnect'] = FALSE;
$db['ntics']['db_debug'] = TRUE;
$db['ntics']['cache_on'] = FALSE;
$db['ntics']['cachedir'] = '';
$db['ntics']['char_set'] = 'utf8';
$db['ntics']['dbcollat'] = 'utf8_general_ci';
$db['ntics']['swap_pre'] = '';
$db['ntics']['autoinit'] = TRUE;
$db['ntics']['stricton'] = FALSE;

//	Ntics Shipping Database
$db['ntics2']['hostname'] = 'ntics2.ntwsec.com';
$db['ntics2']['username'] = 'sa';
$db['ntics2']['password'] = 'Admin$123$567';
$db['ntics2']['database'] = 'NTICS';
$db['ntics2']['dbdriver'] = 'pdo';
$db['ntics2']['dsn'] = "dblib:host={$db['ntics2']['hostname']};port=1433;dbname={$db['ntics2']['database']}";
$db['ntics2']['dbprefix'] = '';
$db['ntics2']['pconnect'] = FALSE;
$db['ntics2']['db_debug'] = TRUE;
$db['ntics2']['cache_on'] = FALSE;
$db['ntics2']['cachedir'] = '';
$db['ntics2']['char_set'] = 'utf8';
$db['ntics2']['dbcollat'] = 'utf8_general_ci';
$db['ntics2']['swap_pre'] = '';
$db['ntics2']['autoinit'] = TRUE;
$db['ntics2']['stricton'] = FALSE;

$db['ople']['hostname'] = '66.209.90.19';
$db['ople']['username'] = 'sales';
$db['ople']['password'] = 'dhvmfghkdlxld123';
$db['ople']['database'] = 'okflex5';
$db['ople']['dbdriver'] = 'mysqli';
$db['ople']['dbprefix'] = '';
$db['ople']['pconnect'] = false;
$db['ople']['db_debug'] = TRUE;
$db['ople']['cache_on'] = FALSE;
$db['ople']['cachedir'] = '';
$db['ople']['char_set'] = 'utf8';
$db['ople']['dbcollat'] = 'utf8_general_ci';
$db['ople']['swap_pre'] = '';
$db['ople']['autoinit'] = TRUE;
$db['ople']['stricton'] = FALSE;


// Ntics Test and debug
$db['ntics_debug'] = $db['ntics'];
$db['ntics_debug']['database'] = 'NTICS_TEST';

// data col debug
$db['data_col_debug_db'] = $db['ntics2'];
$db['data_col_debug_db']['database'] = 'NTICS_TEST';