<?php

class MyCurl
{
    public $curl, $url, $header = [], $is_json = true;
    public $options = [
        'return' => true,
    ];

    static function connect($url)
    {
        return new self($url);

    }
    function speedRun(){

    }
    function verifySSL($status = true)
    {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, $status);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $status);
        return $this;
    }

    function noReturn($v=true){
       $this->options['return'] = !$v;
       return $this;
    }
    function noBody($v=true){
        $this->options['body'] = $v;
        return $this;
    }
    function noHead($v=true){
        $this->options['head'] = !$v;
        return $this;
    }
    function timeout($v=1){
        $this->options['timeout'] = $v;
        return $this;
    }
    function json()
    {
        $this->header[] = "Content-Type: application/json";
        $this->header[] = "Accept: application/json";
        return $this;

    }

    function __construct($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        $this->url = &$url;
        $this->curl = &$curl;
        return $this;
    }

    function post($data = null)
    {
        curl_setopt($this->curl, CURLOPT_POST, true);

        if ($data) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        }
        return $this;
    }

    function header($data, $merge = true)
    {
        if ($merge) {
            $data = [...$this->header, ...$data];
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $data);
        $this->header = $data;
        return $this;
    }

    function token($value)
    {
        $this->header[] = 'Authorization: ' . $value;
        return $this;
    }

    function run($close = true)
    {
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, $this->options['return'] );

        if($this->is_json){
            $this->json();
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->header);

        if(isset($this->options['body']))
            curl_setopt($this->curl, CURLOPT_NOBODY, $this->options['body']);

        if(isset($this->options['head']))
            curl_setopt($this->curl, CURLOPT_HEADER, $this->options['head']);

        if(isset($this->options['timeout']))
            curl_setopt($this->curl, CURLOPT_TIMEOUT_MS, $this->options['timeout']);

        $res = curl_exec($this->curl);
        if ($close) {
            $this->close();
        }
        if ($this->is_json) {
            return json_decode($res, true);
        }

        return $res;
    }

    function close()
    {
        curl_close($this->curl);
        return $this;
    }

    function __destruct()
    {
        $this->close();
    }
}
