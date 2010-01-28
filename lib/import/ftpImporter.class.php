<?php
/**
 * Read the contents of a file from an ftp area
 *
 * @package projectm
 * @subpackage import.lib
 *
 * @author timmy Bowler <timbowler@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class ftpImporter {

    private $_url;

    private $_username;

    private $_password;

    private $_file;

    public function  __construct()
    {
        
    }


    public function pullXml($url, $username, $password, $file)
    {
        $this->_url = $url;
        $this->_username = $username;
        $this->_password = $password;
        $this->_file = $file;

        $data = $this->ftpRequest();

        $simpleXml = simplexml_load_string($data);
        print_r($simpleXml);
        
    }


    private function ftpRequest()
    {
        
      $fullUrl = 'ftp://'.$this->_username.':'.$this->_password.'@'. $this->_url.'/'. $this->_file;
        echo $fullUrl;
       $ch=curl_init();
       curl_setopt($ch, CURLOPT_URL, $fullUrl);
       curl_setopt($ch, CURLOPT_TIMEOUT, 200);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       $data=curl_exec($ch);
       curl_close($ch);

       return $data;

    }


}
?>
