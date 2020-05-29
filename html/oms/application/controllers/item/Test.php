<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-03-25
* Time : 오전 11:46
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Test extends CI_Controller{
    public function __construct()
    {
        parent::__construct();
        //account 체크하고 리스트 불러오기
        $this->load->library('account', array('chk_account' => true));
        $this->account_list = $this->account->getAccount();
    }

    public function test(){

        $left_data = array(
            'account_arr' =>$this->account_list,
            'current_account_id' =>$this->session->userdata('oms_account_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true),
            'add_stylesheet' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.css'),
            'add_script' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.js'),
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        $this->load->view('common/header', $header_data);
        $this->load->view('common/footer', $footer_data);
    }

    public function account_test(){
        $data['channel_info'] = $this->channel_info_model->getChannelInfo(array('channel_id' => 1));

        echo "<pre>";
        var_dump($data['channel_info']);
        echo "</pre>";
    }

    public function test_account($channel, $account){
            $this->channel	= $channel;
            $this->account	= $account;

            $this->config->load($channel.'_'.$account.'_api_config', true);
            $this->channel_config	= $this->config->item($channel.'_'.$account.'_api_config');

            $this->ticket = element('ticket',$this->channel_config);
            echo $this->ticket ;
    }
}

?>