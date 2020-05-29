<?php

/**
* Created by PhpStorm.
* User: ������
* Date : 2019-03-22
* Time : ���� 3:13
*/

class Account
{
    protected $CI;

    public function __construct($param=array())
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();

        if(element('chk_account', $param, true))
            $this->chkAccountSession();

     }

     public function getAccount(){
        $account_list = array();
        $this->CI->load->model('channel/channel_info_model','',TRUE);


        $account_result = $this->CI->channel_info_model->getChannelInfos(array(""));

        foreach ($account_result->result_array() as $account_infos){
            array_push($account_list, element('account_id', $account_infos));
        }

        $account_list = array_unique($account_list);
        return $account_list;
     }
    public function chkAccountSession()
    {
        if(!$this->acountCheck())
            $this->CI->session->set_userdata(array("qten_account_id"=>"gecl604"));


    }

    public function acountCheck()
    {
        return $this->CI->session->has_userdata('qten_account_id');
    }

}