<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-21
 * File: Master_item.php
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Master_item_tmp
{
    protected $CI;

    private $channel_id;
    private $sales_channel_config;

    private $virtual_item_code;
    private $master_item_code;

    private $master_item_price;
    private $img_url;
    private $category_data;
    private $item_full_html;
    private $item_name;
    private $item_mfg_name;

    function __construct(){

        $this->CI =& get_instance();
//		$this->CI->config->load('master_item_config');
        $this->CI->load->model('product_temp/master_item_model');

//        $this->CI->load->model('ople_item_model');
//        $this->CI->load->model('openmarket_model');
    }

    public function setChannelId($channel_code, $account_id, $channel_config){

        $channel_info		= $this->CI->master_item_model->getChannelInfo(array('channel_code'=>$channel_code, 'account_id'=>$account_id));
        $this->channel_id	= element('channel_id',$channel_info);

        // master item 관련설정 로드
        $this->sales_channel_config	= $channel_config;

        return $this->channel_id;
    }

    public function getNoRegItems($filter){
        return $this->CI->master_item_model->getNoRegItem($filter);
    }

    public function getItidByVcode($v_code){
        $master_item_info	= $this->CI->master_item_model->geItidByVcode($v_code);
        if($master_item_info) {
            $this->master_item_code = element('master_item_id', $master_item_info);
            return element('it_id', $master_item_info, false);
        }else{
            return false;
        }
    }

    public function setMasterItemInfo($virtual_item_code){

        $this->virtual_item_code	= $virtual_item_code;

        $this->master_item_price	= $this->CI->master_item_model->getMasterItemPrice($this->virtual_item_code);

        echo $this->channel_id . "//" . $this->virtual_item_code.PHP_EOL;
        $this->category_data		= $this->CI->master_item_model->getMasterItemCategory(array('virtual_item_id'=>$this->virtual_item_code, 'channel_id'=>$this->channel_id));

        $this->img_url				= $this->getImgUrl();

        $this->generateDescHtml();

        return array(
            'item_price'	=> $this->master_item_price
        ,	'img_url'		=> $this->img_url
        ,	'cate_info'		=> $this->category_data
        ,	'desc_html'		=> $this->item_full_html
        ,	'item_maker'	=> $this->item_mfg_name
        ,	'item_name'		=> $this->item_name
        );
    }

    private function getImgUrl(){
        return str_replace(
            element('virtual_item_code_name',$this->sales_channel_config)
            ,	$this->virtual_item_code
            ,	element('img_base_url',$this->sales_channel_config)
        );
    }

    private function generateSupplements(){

        $supplementoptions	= $this->CI->master_item_model->getSupplementFactOptions(array('master_item_code'=>$this->master_item_code));
        if($supplementoptions == null)  return '';
        $supplement_fact_options = $supplementoptions->row_array();

        if(count($supplement_fact_options) < 1) return '';

        $supplementfacts_data_html = $supplementfacts_data_row_html = '';

		$supplementfacts	= $this->CI->master_item_model->getSupplementFacts(array('master_item_code'=>$this->master_item_code));
        if($supplementfacts != null) {

            foreach ($supplementfacts->result_array() as $supplement_fact) {

                $supplementfacts_data_row_html .= sprintf(element('supplementfacts_data_row_html',$this->sales_channel_config),
                    str_replace(array('<','>'),'',element('attname', $supplement_fact)),
                    str_replace(array('<','>'),'',element('attvalue', $supplement_fact)),
                    str_replace(array('<','>'),'',element('attdv', $supplement_fact)));

            }

            if ($supplementfacts_data_row_html != '') {
                $supplementfacts_data_html = sprintf(element('supplementfacts_data_html',$this->sales_channel_config), $supplementfacts_data_row_html);
            }         

        }
        return sprintf(element('supplement_fact_options_html',$this->sales_channel_config),
            rtrim(element('ServingSize', $supplement_fact_options)),
            rtrim(element('ServingPerContainer', $supplement_fact_options)),
            $supplementfacts_data_html);
    }

    private function generateDescHtml(){

        $item_description	= $item_name	= '';

        $decription_info			= $this->CI->master_item_model->getMasterItemDescription($this->virtual_item_code);
        if(!$decription_info){
            $decription_info		= $this->CI->master_item_model->getMasterItemDescByMasterId($this->master_item_code);
        }
        $this->item_mfg_name	= trim(element('it_maker_eng',$decription_info));
        $item_name	= element('it_maker_eng',$decription_info)." ".element('it_name_eng',$decription_info);
        $item_name	= htmlspecialchars(str_replace(element('replace_name_tag_target',$this->sales_channel_config), element('replace_name_tag_value',$this->sales_channel_config), $item_name));
        $item_name	= preg_replace("/[^\x20-\x7e]/", '', $item_name);
        $this->item_name	= $item_name;
        $item_description	.=
            $this->CI->load->view(
                element('name_block',element('view_blocks',$this->sales_channel_config)),
                array(
                    'item_name' => str_replace(element('replace_text',$this->sales_channel_config), element('replace_value',$this->sales_channel_config), $item_name),
                    'item_img' => $this->img_url
                )
                , true);

        $block_num	= 1;
        if(element('desc_direction',$decription_info,false) !== false){
            $item_description	.=
                $this->CI->load->view(
                    element('desc_block',element('view_blocks',$this->sales_channel_config)),
                    array(
                        'block_num' => ++$block_num,
                        'block_name' => '섭취/사용방법',
                        'description' => str_replace(element('replace_text',$this->sales_channel_config), element('replace_value',$this->sales_channel_config), element('desc_direction',$decription_info)),
                        'add_description' => '',

                    )
                    , true);
        }

        // 주의사항 + 오플 문구 추가 : http://66.209.90.21/mall5/adm/shop_admin/commnet_insert.php
        $DESCRIPTION_OPLE = $this->CI->master_item_model->getAddDescription($this->virtual_item_code);
        $DESCRIPTION_OPLE = (empty($DESCRIPTION_OPLE) === true ) ? '' : "<br><br>".$DESCRIPTION_OPLE;

        if(element('desc_warning',$decription_info,false) !== false){
            $item_description	.=
                $this->CI->load->view(
                    element('desc_block',element('view_blocks',$this->sales_channel_config)),
                    array(
                        'block_num' => ++$block_num,
                        'block_name' => '주의사항',
                        'description' => str_replace(element('replace_text',$this->sales_channel_config), element('replace_value',$this->sales_channel_config), element('desc_warning',$decription_info)),
                        'add_description' => $DESCRIPTION_OPLE,

                    )
                    , true);
        }
        if(element('desc_kor',$decription_info,false) !== false){
            $decription_info['desc_kor_tmp'] = preg_replace("/<img[^>]+\>/i", "", element('desc_kor',$decription_info));

            $item_description	.=
                $this->CI->load->view(
                    element('desc_block',element('view_blocks',$this->sales_channel_config)),
                    array(
                        'block_num' => ++$block_num,
                        'block_name' => '제품설명',
                        'description' => str_replace(element('replace_text',$this->sales_channel_config), element('replace_value',$this->sales_channel_config), element('desc_kor_tmp',$decription_info)),
                        'add_description' => '',
                    )
                    , true);
        }
        if(element('desc_eng',$decription_info,false) !== false){
            $item_description	.=
                $this->CI->load->view(
                    element('desc_block',element('view_blocks',$this->sales_channel_config)),
                    array(
                        'block_num' => ++$block_num,
                        'block_name' => '영문설명',
                        'description' => str_replace(element('replace_text',$this->sales_channel_config), element('replace_value',$this->sales_channel_config), element('desc_eng',$decription_info)),
                        'add_description' => '',
                    )
                    , true);
        }

        //성분표 다음에 한글 상세설명에 있는 이미지 추가
        $ople_kor_des_img_src = '';
        $ople_kor_des_img = '';
         if(element('desc_kor', $decription_info, '') != '') {
             preg_match_all('/(<img[^>]+>)/i', element('desc_kor', $decription_info, ''), $out, PREG_PATTERN_ORDER);
             @$ople_kor_des_img = $out[0][0];
             preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $ople_kor_des_img, $ople_kor_des_img_src);
         }

        $item_supplementfact	= $this->generateSupplements();


        $this->item_full_html = str_replace(
            element('replace_tag_target',$this->sales_channel_config),
            element('replace_tag_value',$this->sales_channel_config),
            $this->CI->load->view(
                element('description',element('view_blocks',$this->sales_channel_config)),
                array(
                    'ITEM_DESCRIPTION'				=> str_replace(array('','','†','®','','™'),'',$item_description),
                    'ITEM_SUPPLEMENT_FACTS_BLOCK'	=> str_replace(array('','','†','®','','™'), '', $item_supplementfact),
                    'ITEM_SUPPLEMENT_FACTS_IMG'     => sprintf(element('supplementfacts_img_html',$this->sales_channel_config), @$ople_kor_des_img_src[1][0]),
                ),
                true));
    }


    public function clear(){
        $this->virtual_item_code	= null;
        $this->master_item_code		= null;
        $this->master_item_price	= null;
        $this->img_url				= null;
        $this->category_data		= null;
        $this->item_full_html		= null;
        $this->item_name			= null;
        $this->item_mfg_name		= null;
    }
}