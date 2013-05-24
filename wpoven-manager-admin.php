<?php
class WPOven_Manager_Admin {
    
    public function __construct(){
        if(is_admin()){
	    add_action('admin_menu', array($this, 'add_wpoven_manager_page'));
	    add_action('admin_init', array($this, 'wpoven_manager_page_init'));
            add_action('update_option_wpoven_manager_maintenance', array($this, 'change_maintenance'), 10, 2);
            add_action('update_option_wpoven_manager_cache', array($this, 'change_cache'), 10, 2);
            
            add_action('admin_footer', array($this, 'add_javascript'));
            add_action('wp_ajax_wpoven_manager_flush_all', array($this, 'ajax_flush_cache'));
            
            register_activation_hook(WPOVEN_MANAGER_DIR.'/wpoven-manager.php', array($this, 'activate'));
            register_deactivation_hook(WPOVEN_MANAGER_DIR.'/wpoven-manager.php', array($this, 'deactivate'));
	}
        
        $this->load_plugins();
        
        add_action('send_headers', array($this, 'send_headers'));
    }
    
    public function add_wpoven_manager_page() {
        add_options_page('WPOven Manager', 'WPOven', 'manage_options', 'wpovenmanager', array($this, 'create_wpoven_manager_page'));
    }
    
    public function create_wpoven_manager_page() {
        if( ! current_user_can('manage_options') ) {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>WPOVEN</h2>
            <form method="post" action="options.php">
                <?php
                    settings_fields('wpoven_manager_options');	
                    do_settings_sections('wpovenmanager');
                ?>
                <?php submit_button(); ?>
            </form>
            
            <a class="button" id="wpoven_manager_flush_all">Empty All Caches</a>
        </div>
        <?php
    }
    
    public function wpoven_manager_page_init() {
        register_setting('wpoven_manager_options', 'wpoven_manager_maintenance');
        register_setting('wpoven_manager_options', 'wpoven_manager_cache');
        
        add_settings_section(
            'wpoven_manager_section',
            'WPOven Manager Settings',
            array($this, 'wpoven_manager_section_desc'),
            'wpovenmanager'
        );
        
        add_settings_field(
            'wpoven_manager_maintenance',
            'Activate Maintenance Mode',
            array($this, 'wpoven_manager_maintenance_input'),
            'wpovenmanager',
            'wpoven_manager_section'
        );
        
        add_settings_field(
            'wpoven_manager_cache',
            'Activate Caching',
            array($this, 'wpoven_manager_cache_input'),
            'wpovenmanager',
            'wpoven_manager_section'
        );
        
    }
    
    public function wpoven_manager_section_desc() {
        //echo 'These settings are part of wpoven manager plugin.';
    }
    
    public function wpoven_manager_maintenance_input() {
        $options = get_option('wpoven_manager_maintenance');
        $checked = checked('1', $options, FALSE);
        echo "<input id='wpoven_manager_maintenance' name='wpoven_manager_maintenance' type='checkbox' value='1' $checked />";
    }
    
    public function wpoven_manager_cache_input() {
        $options = get_option('wpoven_manager_cache');
        $checked = checked('1', $options, FALSE);
        echo "<input id='wpoven_manager_cache' name='wpoven_manager_cache' type='checkbox' value='1' $checked />";
    }
    
    public function add_javascript() {
        $nonce = wp_create_nonce('wpoven_manager_flush_all');
        ?>
        <script type="text/javascript" >
        jQuery(document).ready(function($) {

            $('#wpoven_manager_flush_all').click(function(){
                var element = $(this);
                var data = {
                    action: 'wpoven_manager_flush_all',
                    _ajax_nonce: '<?php echo $nonce; ?>'
                };

                $.post(ajaxurl, data, function(response) {
                    if(response == 1) {
                        message = 'Sucessfully flushed all caches';
                    } else if(response == -1) {
                        message = 'Unauthorised request';
                    } else {
                        message = response;
                    }
                    element.replaceWith(message);
                });
            });
        });
        </script>
        <?php
    }
    
    public function ajax_flush_cache() {
        check_ajax_referer('wpoven_manager_flush_all');
        
        if(!current_user_can('manage_options')) {
            wp_die( __('You do not have sufficient permissions to perform this action.') );
        }
        
        $this->w3tc_cache_flush();
        
        echo 1;
        die;
    }
    
    public function load_plugins(){
        require_once(WPOVEN_MANAGER_DIR.'/nginx-compatibility.php');
        
        $cache = get_option('wpoven_manager_cache');
        if($cache === "1"){
            require_once(WPOVEN_MANAGER_DIR.'/w3-total-cache/w3-total-cache.php');
        }
        
        $maintenance = get_option('wpoven_manager_maintenance');
        if($maintenance === "1"){
            require_once(WPOVEN_MANAGER_DIR.'/wpoven-manager-maintenance.php');
        }
    }
    
    public function activate() {
        add_option('wpoven_manager_maintenance', '');
        add_option('wpoven_manager_cache', '1');
        $this->activate_w3tc();
        $this->w3tc_cache_flush();
    }
    
    public function deactivate() {
        $this->w3tc_cache_flush();
        delete_option('wpoven_manager_maintenance');
        delete_option('wpoven_manager_cache');
    }
    
    public function activate_w3tc() {
        require_once(WPOVEN_MANAGER_DIR.'/w3-total-cache/w3-total-cache.php');
        if(isset($root)){
            $root->activate(false);
        }
    }
    
    public function deactivate_w3tc() {
        require_once(WPOVEN_MANAGER_DIR.'/w3-total-cache/w3-total-cache.php');
        if(isset($root)){
            $root->deactivate();
        } else {
            $root = w3_instance('W3_Root');
            $root->deactivate();
        }
    }
    
    public function w3tc_cache_flush() {
        if ( function_exists( 'w3tc_pgcache_flush' ) ) {
            w3tc_pgcache_flush();
        }
        if ( function_exists( 'w3tc_minify_flush' ) ) {
            w3tc_minify_flush();
        }
        if ( function_exists( 'w3tc_dbcache_flush' ) ) {
            w3tc_dbcache_flush();
        }
        if ( function_exists( 'w3tc_objectcache_flush' ) ) {
            w3tc_objectcache_flush();
        }
        if ( function_exists( 'w3tc_cdncache_purge' ) ) {
            w3tc_cdncache_purge();
        }
        if ( function_exists( 'w3tc_varnish_flush' ) ) {
            w3tc_varnish_flush();
        }
    }
    
    public function change_maintenance($oldvalue, $newvalue) {
        if($oldvalue !== $newvalue){
            $this->w3tc_cache_flush();
        }
    }
    
    public function change_cache($oldvalue, $newvalue) {
        if($oldvalue !== $newvalue){
            if('1' === $newvalue) {
                $this->activate_w3tc();
                $this->w3tc_cache_flush();
            } else if('' === (string) $newvalue) {
                $this->w3tc_cache_flush();
                $this->deactivate_w3tc();
            }
        }
    }
    
    public function send_headers() {
        $cache = get_option('wpoven_manager_cache');
        if($cache === "1"){
            $state = 'On';
        } else {
            $state = 'Off';
        }
        @header("WPOven-Cache: $state");
    }
    
}

$wpoven_manager = new WPOven_Manager_Admin();