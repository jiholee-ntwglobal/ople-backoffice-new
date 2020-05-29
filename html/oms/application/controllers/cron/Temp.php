<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-06-18
 * Time: 오후 8:31
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Temp extends CI_Controller
{
    private $item_additional_info;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('order/order_model');
        $this->load->model('order/order_item_model');
        $this->load->model('channel/channel_info_model');

        $this->item_additional_info = array();

    }

    public function aaa()
    {
        $order_ids = array('2108', '2107', '2106', '2105', '2104', '2103', '2102', '2101', '2099', '2098',
            '2097', '2096', '2095', '2094', '2093', '2092', '2091', '2090', '2089', '2088',
            '2087', '2086', '2085', '2084', '2083', '2082', '2081', '2080', '2079', '2076',
            '2075', '2074', '2073', '2072', '2071', '2215', '2214', '2213', '2212', '2211',
            '2210', '2209', '2208', '2207', '2205', '2204', '2202', '2201', '2199', '2198',
            '2197', '2196', '2195', '2194', '2193', '2192', '2191', '2190', '2189', '2188',
            '2187', '2186', '2185', '2184', '2183', '2182', '2181', '2180', '2178', '2177',
            '2176', '2175', '2174', '2173', '2172', '2171', '2170', '2169', '2167', '2166',
            '2165', '2164', '2163', '2162', '2161', '2160', '2159', '2158', '2157', '2156',
            '2155', '2154', '2153', '2151', '2150', '2149', '2148', '2147', '2146', '2145',
            '2143', '2142', '2141', '2140', '2139', '2138', '2137', '2136', '2135', '2134',
            '2133', '2132', '2129', '2128', '2127', '2126', '2125', '2124', '2123', '2122',
            '2121', '2120', '2119', '2118', '2116', '2115', '2114', '2112', '2111', '2110',
            '2109');

        $validate_arr = array();

        $order_item_result = $this->order_item_model->getOrderItems(array('order_id_in' => $order_ids));

        foreach ($order_item_result->result_array() as $order_item_data){

            $result_arr = $this->getItemAddInfo(element('virtual_item_id', $order_item_data));

            if(!array_key_exists(element('order_id', $order_item_data), $validate_arr))
                $validate_arr[element('order_id', $order_item_data)] = array('weight' => 0, 'health_cnt' => 0);

            $validate_arr[element('order_id', $order_item_data)]['weight'] += element('weight', $result_arr) * element('qty', $order_item_data);
            $validate_arr[element('order_id', $order_item_data)]['health_cnt'] += element('health_cnt', $result_arr) * element('qty', $order_item_data);

        }

        foreach ($validate_arr as $order_id => $validate_result){

            $validate_error = 0;

            if(element('weight', $validate_result) > 5000){
                $validate_error += 4;
            }
            if(element('health_cnt', $validate_result) > 6){
                $validate_error += 2;
            }

            echo "update `order` set validate_error='". $validate_error . "' where order_id='" . $order_id . "';<br/>";



        }
    }



    private function getItemAddInfo($virtual_item_id)
    {
        if(trim($virtual_item_id) == ''){
            return array('weight'=> 0, 'health_cnt'=> 0);
        }
        if(element($virtual_item_id, $this->item_additional_info, false) !== false){
            return element($virtual_item_id, $this->item_additional_info);
        }
        $return_arr	= array(
            'weight'		=> 0
        ,	'health_cnt'	=> 0
        );
        $item_add_info	= $this->order_item_model->getItemAddtionalInfo($virtual_item_id);
        $return_arr['weight']		+= element('weight', $item_add_info, 0);
        $return_arr['health_cnt']	+= element('health_cnt', $item_add_info, 0);
        array_merge($this->item_additional_info,array($virtual_item_id => $return_arr));
        return $return_arr;
    }

}