<?php

/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2019-03-22
 * Time : 오후 3:13
 */

class Master_id
{
    protected $CI;

    public function __construct($param=array())
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();

        if(element('chk_master_id', $param, true))
            $this->chkMasterIdSession();

    }

    public function getMasterId(){
        $master_id_list = array();
        $this->CI->load->model('channel/channel_info_model','',TRUE);


        $master_result = $this->CI->channel_info_model->getNewChannelInfos(array(""));

        foreach ($master_result->result_array() as $master_infos){
            array_push($master_id_list, element('master_id', $master_infos));
        }

        $master_id_list = array_unique($master_id_list);
        return $master_id_list;
    }
    public function chkMasterIdSession()
    {
        if(!$this->masterIdCheck())
            $this->CI->session->set_userdata(array("oms_master_id"=>"fastople"));


    }

    public function masterIdCheck()
    {
        return $this->CI->session->has_userdata('oms_master_id');
    }

}