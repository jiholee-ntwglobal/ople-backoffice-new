<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-24
 * File: Master_item_model.php
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Master_item_model extends CI_Model
{
	function __construct(){
		
		parent::__construct();
		
		$this->item_master_db	= $this->load->database('ntics', TRUE);
		$this->openmarket_db	= $this->load->database('default', TRUE);
		$this->ople_db			= $this->load->database('ople', TRUE);
		
	}
	
	public function getChannelInfo($filter=array()){
		
		if(element('channel_code',$filter, false) !== false){
			$this->openmarket_db->where('channel_code', element('channel_code',$filter, false));
		}
		if(element('account_id',$filter, false) !== false){
			$this->openmarket_db->where('account_id', element('account_id',$filter, false));
		}
		if(element('channel_id',$filter, false) !== false){
			$this->openmarket_db->where('channel_id', element('channel_id',$filter, false));
		}
		$query	= $this->openmarket_db->get('OMS.channel_info');
		
		return $query->row_array();
	}
	
	public function getMasterItemPrice($virtual_item_id){
		$this->openmarket_db->select('it_amount');
		$this->openmarket_db->where('it_id',$virtual_item_id);
		$query	= $this->openmarket_db->get('openmarket.yc4_item');
		$rs	= $query->row_array();
		
		return element('it_amount',$rs);
	}
	
	public function getMasterItemCategory($filter){
		
		if(element('channel_id',$filter, false) !== false) {
			switch(element('channel_id',$filter)){
				case '1':
				case '3':
				case '5':
					$this->openmarket_db->select('sc.gmarket_group_id as group_id, sc.gmarket_ca_id as sales_channel_cate_id');
					$this->openmarket_db->select('sc.gmarket_attr_id as attr_id, sc.gmarket_attr_id2 as attr_id2');
					$this->openmarket_db->join('openmarket.gmarket_category_mapping sc', 'c.ca_id=sc.ca_id', 'LEFT');
					$this->openmarket_db->where('sc.ca_id IS NOT NULL');
					break;
				case '2':
				case '4':
				case '8':
					$this->openmarket_db->select('sc.auction_notice_type as group_id, cs.auction_cate_code as sales_channel_cate_id');
					$this->openmarket_db->join('openmarket.auction_category sc', 'c.ca_id=sc.cate_code', 'LEFT');
					$this->openmarket_db->where('sc.cate_code IS NOT NULL');
					break;
				default :
					return false;
			}
		}else {
			return false;
		}
		$this->openmarket_db->from('openmarket.yc4_category_item c');
		$this->openmarket_db->where('c.it_id', element('virtual_item_id',$filter));
		$this->openmarket_db->limit('1');
		$query	= $this->openmarket_db->get();
		
		return $query->row_array();
	}
	
	public function getMasterItemDescription($virtual_item_id){
		$this->ople_db->select('it_maker_eng, it_name_eng, desc_eng, desc_direction, desc_warning, desc_kor');
		$this->ople_db->where('it_id', $virtual_item_id);
		$query	= $this->ople_db->get('channel_item.ople_item_data');
		
		return $query->row_array();
	}
	
	public function getMasterItemDescByMasterId($master_item_id){
		$this->item_master_db->select("CONCAT(RTRIM(i.ITEM_NAME),' ',RTRIM(i.COUNT),' ',RTRIM(i.TYPE)) AS it_name_eng");
		$this->item_master_db->select("RTRIM(m.mfgname) AS it_maker_eng");
		$this->item_master_db->select("i.ITEM_DESC AS desc_eng");
		$this->item_master_db->select("i.ITEM_USAGE AS desc_direction");
		$this->item_master_db->select("i.ITEM_WARNING AS desc_warning");
		$this->item_master_db->from('NTICS.dbo.N_MASTER_ITEM i');
		$this->item_master_db->join('NTICS.dbo.N_MFG m', 'm.mfgcd = i.MfgCD', 'LEFT');
		$this->item_master_db->where('i.master_item_id', $master_item_id);
		$query	= $this->item_master_db->get();
		
		return $query->row_array();
	}
	
	public function getSupplementFacts($filter){
		
		if(element('master_item_code', $filter, false)){
			$this->item_master_db->where('d.master_item_id', element('master_item_code', $filter));
		}
		if(element('master_item_codes', $filter, false)){
			$this->item_master_db->where_in('d.master_item_id', element('master_item_codes', $filter));
		}
		
		$this->item_master_db->select('s.upc, s.attname, s.attdv, s.attvalue');
		$this->item_master_db->from('NTICS.dbo.N_MASTER_ITEM_SUPPLEMENTFACTS s');
		$this->item_master_db->join('NTICS.dbo.N_MASTER_ITEM i', 'i.upc=s.upc', 'LEFT');
		$this->item_master_db->join('NTICS.dbo.virtual_item_detail d', 'd.master_item_id=i.master_item_id', 'LEFT');
		
		return $this->item_master_db->get();
		
	}
	
	public function getSupplementFactOptions($filter){
		
		if(element('master_item_code', $filter, false)){
			$this->item_master_db->where('d.master_item_id', element('master_item_code', $filter));
		}
		if(element('master_item_codes', $filter, false)){
			$this->item_master_db->where_in('d.master_item_id', element('master_item_codes', $filter));
		}
		
		$this->item_master_db->select('s.upc, s.ServingSize, s.ServingPerContainer, s.options, s.formtype, s.html, s.confirm');
		$this->item_master_db->from('NTICS.dbo.N_MASTER_ITEM_SUPPLEMENTFACTS_OPTIONS s');
		$this->item_master_db->join('NTICS.dbo.N_MASTER_ITEM i', 'i.upc=s.upc', 'LEFT');
		$this->item_master_db->join('NTICS.dbo.virtual_item_detail d', 'd.master_item_id=i.master_item_id', 'LEFT');
		
		return $this->item_master_db->get();
		
	}

	public function getNoRegItem($filter){

		$this->openmarket_db->select("CONCAT('V', LPAD(i.virtual_item_id, 8, '0')) AS virtual_item_code", false);
		$this->openmarket_db->from('OMS.virtual_item i');
		$this->openmarket_db->join('OMS.virtual_item_detail d', 'd.virtual_item_id = i.virtual_item_id', 'LEFT');
		if(element('channel_id',$filter,false) !== false){
			$this->openmarket_db->join('OMS.channel_item_info c', "CONCAT('V', LPAD(i.virtual_item_id, 8, '0'))=c.virtual_item_id AND c.channel_id=".element('channel_id',$filter), 'LEFT', false);
		}
		if(element('no_v_code_arr',$filter,false) !== false){
			$this->openmarket_db->where_not_in("CONCAT('V', LPAD(i.virtual_item_id, 8, '0'))", element('no_v_code_arr',$filter), false);
		}
		if(element('vcode_in',$filter,false) !== false){
			$this->openmarket_db->where_in("CONCAT('V', LPAD(i.virtual_item_id, 8, '0'))", element('vcode_in',$filter), false);
		}
		$this->openmarket_db->where('c.item_info_id IS NULL');
		$this->openmarket_db->group_by("virtual_item_code");
		$this->openmarket_db->having('SUM(d.quantity) = 1');
		if(element('test',$filter,false) !== false) {
			$this->openmarket_db->limit('2');
		}
		return $this->openmarket_db->get();
	}
	
	public function geItidByVcode($v_code){
		$this->item_master_db->select('i.master_item_id, m.it_id');
		$this->item_master_db->from('NTICS.dbo.ople_mapping m');
		$this->item_master_db->join('NTICS.dbo.N_MASTER_ITEM i', "i.upc=m.upc AND m.Ople_Type='m'", 'LEFT');
		$this->item_master_db->join('NTICS.dbo.virtual_item_detail d', "d.master_item_id = i.master_item_id", 'LEFT');
		$this->item_master_db->where("CONCAT('V', REPLICATE('0', 8 - LEN(d.virtual_item_id)), RTRIM(d.virtual_item_id))=", $v_code);
		$this->item_master_db->where_not_in("m.it_id", array(
			'1510579722','1415145688','1505176088','1510331115','1412110123','1510292515','1505215541','1510428515','1510428415','1506221725'
		,	'1510505713','1411190013','1510295115','1506223725','1510330415','1511392263','1510295215','1510295015','1510295515','1510295315'
		,	'1510293215','1505100133','1510293815','1510295615','1378159396','1510437515','1413885651','1510292915','1510451315','1510451515'
		,	'1510330515','1510430615','1510292715','1511314562','1510295415','1510331015','1510294715','1504114923','1511420363'));
		$query	= $this->item_master_db->get();
		return $query->row_array();
	}
	
//	public function getMasterItemBasicInfo($virtual_item_id){
//		$this->openmarket_db->select('i.it_id, m.upc as master_item_id, c.ca_id as master_cate_id, i.it_amount as item_price');
//		$this->openmarket_db->from('yc4_item i');
//		$this->openmarket_db->join('ople_mapping m', "m.it_id=i.it_id AND m.ople_type='m'", 'LEFT');
//		$this->openmarket_db->join('yc4_category_item c', 'c.it_id=i.it_id', 'LEFT');
//		// 분기 gmarket
//		$this->openmarket_db->select('sc.gmarket_group_id as group_id, cs.gmarket_ca_id as sales_channel_cate_id');
//		$this->openmarket_db->join('gmarket_category_mapping sc', 'c.ca_id=sc.ca_id', 'LEFT');
//		$this->openmarket_db->where('sc.ca_id IS NOT NULL');
//		// 분기 auction
//		$this->openmarket_db->select('sc.auction_notice_type as group_id, cs.auction_cate_code as sales_channel_cate_id');
//		$this->openmarket_db->join('auction_category sc', 'c.ca_id=sc.cate_code', 'LEFT');
//		$this->openmarket_db->where('sc.ca_id IS NOT NULL');
//		$this->openmarket_db->where('i.it_id', $virtual_item_id);
//		$this->openmarket_db->limit('1');
//		return $this->openmarket_db->get();
//	}
//	public function getSalesChannelCategoryCode($master_cate_id){
//	}

}