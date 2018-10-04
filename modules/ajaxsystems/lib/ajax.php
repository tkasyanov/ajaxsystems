<?php
namespace AjaxSystems;

class AjaxSystems
{

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->bridge_username = $password;


        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, 'https://app.ajax.systems/api/account/do_login');
        curl_setopt($ch,CURLOPT_POSTFIELDS, 'j_username='.$username.'&j_password='.$password);
        curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie_ajax.txt');
        curl_setopt ($ch, CURLOPT_COOKIEFILE , 'cookie_ajax.txt');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch,CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // bad style, I know...
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $this->curl = $ch;

    }

    public function  curl_close()
    {
        curl_close($this->curl);

    }
    public function login(){
        $response = curl_exec($this->curl);
        return $response;
    }
    public function getCsa(){
        curl_setopt($this->curl,CURLOPT_URL, 'https://app.ajax.systems/SecurConfig/api/account/getCsaConnection');
        $response = curl_exec($this->curl);
        return $response;
    }
    public function getHubData(){
        curl_setopt($this->curl,CURLOPT_URL, 'https://app.ajax.systems/SecurConfig/api/dashboard/getHubsData');
        curl_setopt($this->curl,CURLOPT_POSTFIELDS,"");
        $response = curl_exec($this->curl);
        return $response;

    }
    public function hetHubLogs($hubid){
        curl_setopt($this->curl,CURLOPT_URL, 'https://app.ajax.systems/SecurConfig/api/dashboard/getLogs');
        curl_setopt($this->curl,CURLOPT_POSTFIELDS,'hubId='.$hubid.'&count=50&offset=0');
        $response = curl_exec($this->curl);
        return $response;
    }
}


