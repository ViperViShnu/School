<?php (!defined('BASEPATH')) and exit('No direct script access allowed');

class Custom_sms
{

    private $apiURL;

    public function __construct()
    {
        $ci = &get_instance();
        if (is_superadmin_loggedin()) {
            $branchID = $ci->input->post('branch_id');
        } else {
            $branchID = get_loggedin_branch_id();
        }
        $smscountry = $ci->db->get_where('sms_credential', array('sms_api_id' => 8, 'branch_id' => $branchID))->row_array();
        $this->apiURL = isset($smscountry['field_one']) ? $smscountry['field_one'] : '';
    }

    public function sendOld($numbers, $message, $dlt_template_id = '') // 2025-03-13
    {
        $message = rawurlencode($message);
        $url = $this->apiURL;
        $url = str_replace('[app_number]', $numbers, $url);
        $url = str_replace('[app_message]', $message, $url);
        $url = str_replace('[dlt_template_id]', $dlt_template_id, $url);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        curl_close($curl);
        return true;
    }

    public function send($numbers, $message, $dlt_template_id = '')
    {
        $url = $this->apiURL;
        if(strpos($url, 'http://site.ping4sms.com/api/smsapi?') === 0) {
            $this->customSmsSendNew($numbers, $message, $dlt_template_id); // 2025-03-15
        } else {
            $this->customSmsSend($numbers, $message, $dlt_template_id);
        }
    }

    public function customSmsSendNew($numbers, $message, $dlt_template_id) {
        try {
            $message = rawurlencode($message);

            // $parameters="key=fe5b7102d99a0d83ed705d3c77cd8612&route=2&sender=PINGSM&number=9087591338&sms=Dear Customers,Welcome to PING4SMS.&templateid=1107164612508406352";
            $parameters = "key=fe5b7102d99a0d83ed705d3c77cd8612&route=2&sender=PINGSM&number=Number(s)&sms=Message&templateid=DLT_Templateid";
            $parameters = str_replace('Number(s)', $numbers, $parameters);
            $parameters = str_replace('Message', $message, $parameters);
            $parameters = str_replace('DLT_Templateid', $dlt_template_id, $parameters);

            $url="http://site.ping4sms.com/api/smsapi?";
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST,1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$parameters);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($curl,CURLOPT_HEADER,0); // DO NOT RETURN HTTP HEADERS
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,1); // RETURN THE CONTENTS OF THE CALL
            
            $response = curl_exec($curl);
            
            if (curl_errno($curl)) {
                throw new Exception('cURL Error: ' . curl_error($curl)); die;
            }
            
            curl_close($curl);
            return true;
        } catch(Exception $e) {
            log_message('error',$e->getMessage()); die;
        }
    }

    public function customSmsSend($numbers, $message, $dlt_template_id) {
        $message = rawurlencode($message);
        $url = $this->apiURL;
        $url = str_replace('[app_number]', $numbers, $url);
        $url = str_replace('[app_message]', $message, $url);
        $url = str_replace('[dlt_template_id]', $dlt_template_id, $url);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        curl_close($curl);
        return true;
    }
}