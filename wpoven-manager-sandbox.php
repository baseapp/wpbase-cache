<?php
class WPOven_Sandbox {

    public $host_name;
    public $host_port;
    public $domain;

    public function __construct() {
        if(isset($_SERVER['SERVER_SOFTWARE']) && !(php_sapi_name() == 'cli' || (is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0))) {
            $this->host_name = $_SERVER["SERVER_NAME"];
            $this->host_port = $_SERVER["SERVER_PORT"];
            $this->domain = $_SERVER["REQUEST_URI"];
            $this->domain = explode("/", $this->domain);
            $this->domain = $this->domain[1];

            if(filter_var($this->host_name, FILTER_VALIDATE_IP)){
                define('WPOVEN_MANAGER_SANDBOX', true);
                add_filter('pre_option_home', array($this, 'sandbox'));
                add_filter('pre_option_siteurl', array($this, 'sandbox'));
            }
        }
    }

    public function sandbox() {
        $url = 'http://'.$this->host_name.':'.$this->host_port.'/'.$this->domain;
        return $url;
    }

}

$wpoven_sandbox = new WPOven_Sandbox();