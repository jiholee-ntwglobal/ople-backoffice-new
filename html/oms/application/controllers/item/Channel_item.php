<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오후 1:42
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_item extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
    }

    public function openChannelUrl($channel_id, $channel_product_no)
    {
        $channel_info = $this->channel_info_model->getNewChannelInfo(array('channel_id' => $channel_id));

        $channel_product_url = $this->config->item('channel_product_url');

        redirect(element(element('channel_code', $channel_info), $channel_product_url) . $channel_product_no);

    }

}