<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 9:50
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH."/third_party/PHPExcel.php";

class Excel extends PHPExcel {
    public function __construct() {
        parent::__construct();
    }
}