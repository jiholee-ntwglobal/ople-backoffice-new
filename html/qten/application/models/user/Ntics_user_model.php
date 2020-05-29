<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:44
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Ntics_user_model extends CI_Model
{
    private $ntics_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics_db = $this->load->database('ntics', true);
    }

    public function getUser($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics_db->where($filter_key, $filter_val);
                    break;
            }
        }
	
		// 기본 유효유저
		$this->ntics_db->where('active', 'Y');
		$this->ntics_db->select($select);
        $this->ntics_db->from('NTICS.dbo.N_USER');

        $query = $this->ntics_db->get();

        return $query->row_array();

    }

    public function getUsers($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val)
        {
        	if($filter_key != "active")
			{
				switch ($filter_key)
				{
					case 'worker_id_in':
						$this->ntics_db->where_in('worker_id', $filter_val);
						break;
					default:
						$this->ntics_db->where($filter_key, $filter_val);
						break;
				}
			}
        }

        if(isset($filter['active']) && $filter['active'] === false)
		{
		}
		else
		{
			// 기본 유효유저
			$this->ntics_db->where('active', 'Y');
		}

		$this->ntics_db->select($select);

        $this->ntics_db->from('NTICS.dbo.N_USER');

        return $this->ntics_db->get();

    }


    public function getAllUsers($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val)
        {
            if($filter_key != "active")
            {
                switch ($filter_key)
                {
                    case 'worker_id_in':
                        $this->ntics_db->where_in('worker_id', $filter_val);
                        break;
                    default:
                        $this->ntics_db->where($filter_key, $filter_val);
                        break;
                }
            }
        }

/*
 *      퇴사자도 조회 필요 KSJ (가격 조정 히스토리 /item/single_item/productUpdatePriceHistoryList)
 *         if(isset($filter['active']) && $filter['active'] === false)
        {
        }
        else
        {
            // 기본 유효유저
            $this->ntics_db->where('active', 'Y');
        }*/

        $this->ntics_db->select($select);

        $this->ntics_db->from('NTICS.dbo.N_USER');

        return $this->ntics_db->get();

    }

}