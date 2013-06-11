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

                // flush automatic apc cache if in sandbox
                add_action('switch_theme', array($this, 'apc_cache_flush'));
                add_action('publish_phone', array($this, 'apc_cache_flush'));
                add_action('publish_post', array($this, 'apc_cache_flush'));
                add_action('edit_post', array($this, 'apc_cache_flush'));
                add_action('save_post', array($this, 'apc_cache_flush'));
                add_action('wp_trash_post', array($this, 'apc_cache_flush'));
                add_action('delete_post', array($this, 'apc_cache_flush'));
                add_action('trackback_post', array($this, 'apc_cache_flush'));
                add_action('pingback_postt', array($this, 'apc_cache_flush'));
                add_action('comment_post', array($this, 'apc_cache_flush'));
                add_action('edit_comment', array($this, 'apc_cache_flush'));
                add_action('wp_set_comment_status', array($this, 'apc_cache_flush'));
                add_action('delete_comment', array($this, 'apc_cache_flush'));
                add_action('comment_cookie_lifetime', array($this, 'apc_cache_flush'));
                add_action('wp_update_nav_menu', array($this, 'apc_cache_flush'));
                add_action('edit_user_profile_update', array($this, 'apc_cache_flush'));
            }
        }
    }

    public function sandbox() {
        $url = 'http://'.$this->host_name.':'.$this->host_port.'/'.$this->domain;
        return $url;
    }

    public function apc_cache_flush() {
        if ( function_exists( 'apc_clear_cache' ) ) {
            apc_clear_cache();
            apc_clear_cache('user');
            apc_clear_cache('opcode');
        }
    }
}

$wpoven_sandbox = new WPOven_Sandbox();