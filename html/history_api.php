<?php
namespace scheduler;

class History_api
{
    private $return_url;
    private $params;

    public function __construct()
    {
        $this->return_url = "http://47.176.39.145/scheduler/apihistory";
    }

    public function getHistoryID($scheduler_id){
        $this->params = array(
            'scheduler_info_id' => $scheduler_id
        );
    }
    public function sendHistoryID(){

        $encoded='';

        foreach ($this->params as $name => $value) {
            $encoded .= urlencode($name) . '=' . urlencode($value) . '&';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->return_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $output;


    }


}
