<?php 
/*
----------------------------------------------------------------------
file name	 : openmarket_model.php
comment		 : openmarket_model
date		 : 2015-09-03
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Openmarket_model extends CI_Model {

	function __construct(){

		parent::__construct();		
        
        $this->ntshipping_db	 = $this->load->database('ntics', TRUE);
		$this->openmarket_db	 = $this->load->database('default', TRUE);

	}
	
	public function getGmarketProductOfficialInfo($filter){
		if(element('group_no', $filter, false)){
			$this->openmarket_db->where('group_no', element('group_no', $filter));
		}
		
		$this->openmarket_db->select('noti_item_no, noti_value');
		$this->openmarket_db->from('openmarket.gmarket_product_official_info');
		
		return $this->openmarket_db->get();
	}
	
	public function getBrandInfo($it_maker){
		
		$this->openmarket_db->where('it_maker', $it_maker);
		$this->openmarket_db->from('openmarket.gmarket_brand_info');
		
		$query = $this->openmarket_db->get();
		
		return $query->row_array();
		
	}
	
	public function addOmsUploadProductInfo($add_data){
		
		$this->openmarket_db->insert('OMS.channel_item_info', $add_data);
		
	}
	
	public function addOmsProductUploadError($error_data){
		
		$this->openmarket_db->insert('OMS.channel_product_upload_error', $error_data);
		
	}
	
//	function get_info($param){
//
//		$val = $param[key($param)];
//
//		$where = '';
//
//		switch(key($param)){
//			case 'op_od_id' : $where = " and i.op_od_id='$val' "; break;
//			case 'od_id' : $where = " and o.od_id='$val' "; break;
//		}
//
//		$query = $this->openmarket_db->query("
//		select
//			o.channel,o.account_id, o.od_id, o.od_b_name, i.op_od_id
//		from
//			open_market_order_item i, open_market_order o
//		where
//			o.op_cart_id = i.op_cart_id and o.channel=i.channel  and o.account_id = i.account_id $where
//		");
//
//		return $query->row_array();
//
//	}
//
//	function get_s01($od_id){
//
//		$this->ntshipping_db->from('NS_S01')->where('od_id',$od_id);
//
//		$query = $this->ntshipping_db->get();
//
//		return $query->row_array();
//	}
//
//	function get_so3($on_uid){
//
//		$this->ntshipping_db->from('NS_S03')->where('on_uid',$on_uid);
//
//		$query = $this->ntshipping_db->get();
//
//		$return_arr = array();
//
//		foreach ($query->result_array() as $row){
//			array_push($return_arr,$row);
//		}
//
//		return $return_arr;
//	}
//
//	function cancel_s01($od_id){
//
//		$qry = "update NS_S01 set status='9',shippingcomment = 'CANCEL ORDER!!!'  where od_id='$od_id'";
//		$this->ntshipping_db->query($qry);
//		return true;
//	}
//
//	function cancel_s03($on_uid){
//		$on_uid = trim($on_uid);
//		$qry = "update NS_S03 set status='shipped',shippingcode='CANCEL' where on_uid='$on_uid'";
//		$this->ntshipping_db->query($qry);
//		return true;
//	}
//	function cancel_openmarket($od_id){
//		$od = $this->openmarket_db->query("select od_id,on_uid from yc4_order where od_id = ?",array(substr($od_id, 1, strlen($od_id) -1)))->row();
//		$this->openmarket_db->query("update yc4_order set od_shop_memo = concat(od_shop_memo,'\\n',?),od_status_update_dt = now() where od_id = ?",array('취소처리완료',$od->od_id));
//		$this->openmarket_db->query("update yc4_cart set ct_status = ? where on_uid = ?",array('취소',$od->on_uid));
//
//
//	}
//
//    public function insertProductStockInfo($insert_data){
//
//        $this->openmarket_db->query("insert into product_stock_info (op_it_id, stock_qty) values (" . $this->openmarket_db->escape(element('op_it_id',$insert_data)) .", " . $this->openmarket_db->escape(element('stock_qty',$insert_data)) .") ");
//    }
//
//    public function getPriceUpdateProduct($update_dt){
//
//        $query = $this->openmarket_db->query("
//            SELECT m.op_it_id, m.soldout_fg, p.uid, p.price, p.discount_price, p.discount_start_dt, p.discount_end_dt
//            FROM open_market_mapping m
//            LEFT JOIN gmarket_product_update_price p ON m.op_it_id=p.op_it_id
//            WHERE m.channel='G' AND m.account_id='neiko13' and IFNULL(m.option_name,'')='' AND p.update_flag IS NULL AND p.update_dt='{$update_dt}'");
//
//        return $query;
//
//    }
//
//    public function getPriceUpdateProduct_la($update_dt){
//
//        $query = $this->openmarket_db->query("
//            SELECT m.op_it_id, m.soldout_fg, p.uid, p.price, p.discount_price, p.discount_start_dt, p.discount_end_dt
//            FROM open_market_mapping m
//            LEFT JOIN gmarket_la_product_update_price p ON m.op_it_id=p.op_it_id
//            WHERE m.channel='G' AND m.account_id='allthatmal' and IFNULL(m.option_name,'')='' AND p.update_flag IS NULL AND p.update_dt='{$update_dt}'");
//
//        return $query;
//
//    }
//
//    public function updatePriceUpdateResult($uid, $stock_fg){
//
//        $this->openmarket_db->query("UPDATE gmarket_product_update_price SET update_flag='Y',stock_fg='{$stock_fg}' WHERE uid='{$uid}'");
//
//    }
//
//    public function updatePriceUpdateResult_la($uid, $stock_fg){
//
//        $this->openmarket_db->query("UPDATE gmarket_la_product_update_price SET update_flag='Y',stock_fg='{$stock_fg}' WHERE uid='{$uid}'");
//
//    }
//
//    public function updateMappingStockIn($op_it_id){
//
//        $this->openmarket_db->query("
//        update open_market_mapping
//          set soldout_fg='N'
//          where
//           channel = 'G' AND
//           account_id = 'neiko13' AND
//          op_it_id='$op_it_id'");
//
//    }
//
//    public function updateMappingStockIn_la($op_it_id){
//
//        $this->openmarket_db->query("
//        update open_market_mapping
//          set soldout_fg='N'
//          where
//           channel = 'G' AND
//           account_id = 'allthatmal' AND
//          op_it_id='$op_it_id'");
//
//    }
//
//    public function getProductCategoryIdOne($filter){
//
//        if(element('it_id', $filter, false)){
//            $this->openmarket_db->where('it_id', element('it_id', $filter));
//        }
//        $this->openmarket_db->select('ca_id');
//        $this->openmarket_db->from('yc4_category_item');
//        $this->openmarket_db->limit(1);
//
//        $query = $this->openmarket_db->get();
//
//        $result= $query->row_array();
//
//        return element('ca_id', $result);
//    }
//
//    public function getGmarketCategoryMapping($filter){
//
//        $where = array();
//
//        if(element('ca_id', $filter, false)){
//            $where['ca_id'] = element('ca_id', $filter);
//        }
//
//        if(count($where) > 0)
//            $this->openmarket_db->where($where);
//
//        $this->openmarket_db->from('gmarket_category_mapping');
//
//        $query = $this->openmarket_db->get();
//
//        return $query->row_array();
//
//    }
//
//    public function getGmarketNoRegItem($filter){
//
//        $where = array();
//
//        if(element('has_stock', $filter, false)){
//            $this->openmarket_db->where('i.it_stock_qty>0',null,false);
//        }
//        if(element('no_it_id_in', $filter, false) && count(element('no_it_id_in', $filter))>0){
//            $this->openmarket_db->where_not_in('i.it_id', element('no_it_id_in', $filter));
//        }
//        if(element('no_upc_in', $filter, false) && count(element('no_upc_in', $filter) > 0)){
//            $this->openmarket_db->where_not_in('m2.upc', element('no_upc_in', $filter));
//        }
//        if(element('no_it_maker_in', $filter, false)){
//            $this->openmarket_db->where_not_in('i.it_maker', element('no_it_maker_in', $filter));
//        }
//        if(element('it_id', $filter, false)){
//            $where['i.it_id'] = element('it_id', $filter);
//        }
//        if(element('it_discontinued', $filter, false) !== false){
//            $where['i.it_discontinued'] = element('it_discontinued', $filter);
//        }
//        if(element('it_use', $filter, false)){
//            $where['i.it_use'] = element('it_use', $filter);
//        }
//        if(element('bigger_it_id', $filter, false)){
//            $this->openmarket_db->where("i.it_id>='". element('bigger_it_id', $filter)."'", null, false);
//        }
//
//        if(count($where) > 0) $this->openmarket_db->where($where);
//
//        $this->openmarket_db->select('i.it_id, i.it_name, i.it_explan, i.it_amount, i.it_health_cnt, m2.upc');
//        $this->openmarket_db->from('yc4_item i');
//        $this->openmarket_db->join('ople_mapping m2', "m2.it_id = i.it_id AND m2.ople_type='m'", 'LEFT');
//        $this->openmarket_db->join("(select it_id from open_market_mapping a where ifnull(a.option_name ,'')= '' and a.channel='G' and a.account_id='neiko13') m", "m.it_id=i.it_id", 'LEFT');
//        $this->openmarket_db->where('m.it_id IS NULL');
///*        $this->openmarket_db->where("i.it_id in ('1511168715', '1511198619', '1511268347', '1511269747', '1511269847',
//'1511271548', '1511271648', '1511275548', '1511280048', '1511282348',
//'1511282448', '1511286860', '1511287260', '1511288660', '1511291260',
//'1511291660', '1511304162')");*/
//        $this->openmarket_db->group_by('i.it_id');
//        $this->openmarket_db->order_by('i.it_id', 'ASC');
//        //$this->openmarket_db->limit(1);
//
//        return $this->openmarket_db->get();
//
//    }
//
//    public function getGmarketNoRegItem_la($filter){
//
//        $where = array();
//
//        if(element('has_stock', $filter, false)){
//            $this->openmarket_db->where('i.it_stock_qty>0',null,false);
//        }
//        if(element('no_it_id_in', $filter, false) && count(element('no_it_id_in', $filter))>0){
//            $this->openmarket_db->where_not_in('i.it_id', element('no_it_id_in', $filter));
//        }
//        if(element('no_upc_in', $filter, false) && count(element('no_upc_in', $filter) > 0)){
//            $this->openmarket_db->where_not_in('m2.upc', element('no_upc_in', $filter));
//        }
//        if(element('no_it_maker_in', $filter, false)){
//            $this->openmarket_db->where_not_in('i.it_maker', element('no_it_maker_in', $filter));
//        }
//        if(element('it_id', $filter, false)){
//            $where['i.it_id'] = element('it_id', $filter);
//        }
//        if(element('it_discontinued', $filter, false) !== false){
//            $where['i.it_discontinued'] = element('it_discontinued', $filter);
//        }
//        if(element('it_use', $filter, false)){
//            $where['i.it_use'] = element('it_use', $filter);
//        }
//        if(element('bigger_it_id', $filter, false)){
//            $this->openmarket_db->where("i.it_id>='". element('bigger_it_id', $filter)."'", null, false);
//        }
//
//        if(count($where) > 0) $this->openmarket_db->where($where);
//
//        $this->openmarket_db->select('i.it_id, i.it_name, i.it_explan, i.it_amount, i.it_health_cnt, m2.upc');
//        $this->openmarket_db->from('yc4_item i');
//        $this->openmarket_db->join('ople_mapping m2', "m2.it_id = i.it_id AND m2.ople_type='m'", 'LEFT');
//        $this->openmarket_db->join("(select it_id from open_market_mapping a where ifnull(a.option_name ,'')= '' and a.channel='G' and a.account_id='allthatmal') m", "m.it_id=i.it_id", 'LEFT');
//        $this->openmarket_db->where('m.it_id IS NULL');
//                $this->openmarket_db->where("i.it_id in ('1510520213')");
//        $this->openmarket_db->group_by('i.it_id');
//        $this->openmarket_db->order_by('i.it_id', 'ASC');
//        /*$this->openmarket_db->limit(10);*/
//
//        return $this->openmarket_db->get();
//
//    }
//
//    public function getG9NoRegItem($filter){
//
//        $where = array();
//
//        if(element('has_stock', $filter, false)){
//            $this->openmarket_db->where('i.it_stock_qty>0',null,false);
//        }
//        if(element('no_it_id_in', $filter, false) && count(element('no_it_id_in', $filter))>0){
//            $this->openmarket_db->where_not_in('i.it_id', element('no_it_id_in', $filter));
//        }
//        if(element('no_upc_in', $filter, false) && count(element('no_upc_in', $filter) > 0)){
//            $this->openmarket_db->where_not_in('m2.upc', element('no_upc_in', $filter));
//        }
//        if(element('no_it_maker_in', $filter, false)){
//            $this->openmarket_db->where_not_in('i.it_maker', element('no_it_maker_in', $filter));
//        }
//        if(element('it_id', $filter, false)){
//            $where['i.it_id'] = element('it_id', $filter);
//        }
//        if(element('it_discontinued', $filter, false) !== false){
//            $where['i.it_discontinued'] = element('it_discontinued', $filter);
//        }
//        if(element('it_use', $filter, false)){
//            $where['i.it_use'] = element('it_use', $filter);
//        }
//        if(element('bigger_it_id', $filter, false)){
//            $this->openmarket_db->where("i.it_id>='". element('bigger_it_id', $filter)."'", null, false);
//        }
//
//        if(count($where) > 0) $this->openmarket_db->where($where);
//
//        $this->openmarket_db->select('g.uid,i.it_id, i.it_name, i.it_explan, i.it_amount, i.it_health_cnt, m2.upc');
//        $this->openmarket_db->from('yc4_item i');
//        $this->openmarket_db->join('ople_mapping m2', "m2.it_id = i.it_id AND m2.ople_type='m'", 'LEFT');
//        $this->openmarket_db->join('g9_upload_product g', "g.it_id=i.it_id", 'LEFT');
//        $this->openmarket_db->where('g.uploaded','0');
//        $this->openmarket_db->group_by('i.it_id');
//        $this->openmarket_db->order_by('g.uid', 'ASC');
//        //$this->openmarket_db->limit(1);
//
//        return $this->openmarket_db->get(); //echo $this->openmarket_db->last_query();exit;
//
//    }
//
//    public function updateG9UploadProduct($filter, $update_data)
//    {
//        $this->openmarket_db->where($filter);
//        $this->openmarket_db->update('g9_upload_product', $update_data);
//    }
//
//    public function getProductMakers(){
//
//        $this->openmarket_db->select('i.it_maker,i.it_maker_kor');
//        $this->openmarket_db->from('yc4_item i');
//        $this->openmarket_db->join('gmarket_brand_info b', 'i.it_maker=b.it_maker', 'LEFT OUTER');
//        $this->openmarket_db->where('i.it_use','1');
//        $this->openmarket_db->where('b.it_maker IS NULL');
//        $this->openmarket_db->group_by('i.it_maker,i.it_maker_kor');
//
//        return $this->openmarket_db->get();
//
//    }
//
//    public function addBrandInfo($add_data){
//
//        $this->openmarket_db->insert('gmarket_brand_info', $add_data);
//
//    }
//
//    public function addOpenmarketMapping($mapping_data){
//
//        $this->openmarket_db->insert('open_market_mapping', $mapping_data);
//
//    }
//
//    public function addProductUploadResult($add_data){
//
//        $this->openmarket_db->insert('gmarket_product_upload_result', $add_data);
//
//    }
//    public function addProductUploadResult_la($add_data){
//
//        $this->openmarket_db->insert('gmarket_la_product_upload_result', $add_data);
//
//    }
//    public function addProductUploadError($error_data){
//
//        $this->openmarket_db->insert('gmarket_product_upload_error', $error_data);
//
//    }
//
//    public function addProductUploadError_la($error_data){
//
//        $this->openmarket_db->insert('gmarket_la_product_upload_error', $error_data);
//
//    }
//
//	public function getGmarketItem(){
//
//		$this->oms_db	 = $this->load->database('oms', TRUE);
//
//		$this->oms_db->select('channel_item_code, virtual_item_id, upload_price');
//		$this->oms_db->from('channel_item_info');
//		$this->oms_db->where('channel_id', 1);
//
//		return $this->oms_db->get();
//	}
}