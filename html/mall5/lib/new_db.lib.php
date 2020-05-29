<?php

/**
 * Created by PhpStorm.
 * File name : new_db.lib.php.
 * Comment :
 * Date: 2017-11-27
 * User: Developer_kki
 */
class new_db
{

    protected $ntisc_db;
    protected $data_col_db;
    protected $ople_db;
    protected $open_db;
    protected $atm_db;
	protected $app_db;
	protected $opk_db;
	protected $st11_db;
    protected $new_opk_db;

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $ntisc_db	= null;
        $data_col_db= null;
        $ople_db	= null;
        $open_db	= null;
        $atm_db		= null;
		$app_db		= null;
		$opk_db		= null;
		$st11_db	= null;
		$new_opk_db	= null;
	}

    /**
     * @param $db_name string 데이터베이스 이름
     * @return bool|PDO
     */
    public function init_db($db_name)
    {
        include (__DIR__ . '/db_define.php');

        $db_name = strtolower($db_name);

        if(property_exists($this, $db_name.'_db')) {
			try {
				$this->{$db_name . '_db'} = new PDO($CONN[$db_name]['dsn'], $CONN[$db_name]['user'], $CONN[$db_name]['password']);
				return $this->{$db_name . '_db'};
			} catch (PDOException $e) {
				echo 'LINE : ' . __LINE__;
				echo 'Database Connection Error : ' . $db_name . PHP_EOL;
				echo $e;
				return false;
			}
		}
        return false;
    }
}