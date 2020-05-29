<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date : 2018-07-27
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Channel_option_item_description_model extends CI_Model{

	private $oms_db;

	function __construct()
	{
		parent::__construct();

		$this->oms_db = $this->load->database('default', true);
	}

	function add($insert_data){

		$this->oms_db->insert('option_item_description', $insert_data);
	}

	function update($update_data, $filter){

		foreach ($filter as $filter_key=>$filter_val){
			switch ($filter_key) {
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}

		$this->oms_db->update('option_item_description', $update_data);
	}

	function getOptionDescription($filter=array(), $select=null, $order_by=array()){

		$filter = ($filter) ? : array();
		$select = ($select) ? : "*";


		foreach ($filter as $filter_key=>$filter_val){
			switch ($filter_key){

				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;

			}
		}

		$this->oms_db->select($select);
		$this->oms_db->from("option_item_description");

		foreach ($order_by as $orderby=>$direct){
			$this->oms_db->order_by($orderby, $direct);
		}

		return $this->oms_db->get()->row_array();

	}
}