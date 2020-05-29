<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오후 1:44
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_info_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function getChannelInfo($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('channel_info');

        //허용된 account(fastople)만 되도록
        $this->oms_db->where("use_flag",1);

        $query = $this->oms_db->get();

        return $query->row_array();

    }

    //use_flag = 0 인 애들도 불러올 수 있도록 account통합 작업 끝나면 getChannelInfo이랑 정리 - KSJ
    public function getNewChannelInfo($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('channel_info');

        $query = $this->oms_db->get();

        return $query->row_array();

    }


    public function getChannelInfos($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('channel_info');

        //허용된 account(fastople)만 되도록
        $this->oms_db->where("use_flag",1);


        return $this->oms_db->get();
    }

    public function getChannelInfostest($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('channel_info');
        $this->oms_db->where("channel_id",5);
        //허용된 account(fastople)만 되도록
        $this->oms_db->where("use_flag",1);


        return $this->oms_db->get();
    }
    //use_flag = 0 인 애들도 불러올 수 있도록 account통합 작업 끝나면 getChannelInfos이랑 정리 - KSJ
    public function getNewChannelInfos($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('channel_info');

        return $this->oms_db->get();
    }

    public function getApikey($filter){
        foreach ($filter as $filter_key=> $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select("api_key");
        $this->oms_db->from('channel_info');

        $result = $this->oms_db->get();
        $row = $result->row_array();

        return element('api_key',$row);
    }

}