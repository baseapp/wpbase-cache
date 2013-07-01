<?php
class WPOven_Manager_Admin {

    public function __construct(){
        if(is_admin()){
            add_action('admin_menu', array($this, 'add_wpoven_manager_page'));
            add_action('admin_init', array($this, 'wpoven_manager_page_init'));
            add_action('update_option_wpoven_manager_maintenance', array($this, 'change_maintenance'), 10, 2);
            add_action('update_option_wpoven_manager_page_cache', array($this, 'change_page_cache'), 10, 2);

            add_action('admin_footer', array($this, 'add_javascript'));
            add_action('wp_ajax_wpoven_manager_flush_all', array($this, 'ajax_flush_cache'));
        }

        $this->load_plugins();

        register_activation_hook(WPOVEN_MANAGER_DIR.'/wpoven-manager.php', array($this, 'activate'));
        register_deactivation_hook(WPOVEN_MANAGER_DIR.'/wpoven-manager.php', array($this, 'deactivate'));

        $this->add_flush_actions();
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

            <?php if(!(defined('WPOVEN_MANAGER_SANDBOX') && WPOVEN_MANAGER_SANDBOX)) { ?>
                <a class="button" id="wpoven_manager_flush_all">Empty All Caches</a>
            <?php } ?>
        </div>
        <?php
    }

    public function wpoven_manager_page_init() {
        register_setting('wpoven_manager_options', 'wpoven_manager_maintenance');
        register_setting('wpoven_manager_options', 'wpoven_manager_page_cache');
        register_setting('wpoven_manager_options', 'wpoven_manager_varnish_cache');

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
            'wpoven_manager_page_cache',
            'Activate Page Cache',
            array($this, 'wpoven_manager_page_cache_input'),
            'wpovenmanager',
            'wpoven_manager_section'
        );

        add_settings_field(
            'wpoven_manager_varnish_cache',
            'Activate Varnish Caching',
            array($this, 'wpoven_manager_varnish_cache_input'),
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

    public function wpoven_manager_page_cache_input() {
        $options = get_option('wpoven_manager_page_cache');
        $checked = checked('1', $options, FALSE);

        if(!(defined('WPOVEN_MANAGER_SANDBOX') && WPOVEN_MANAGER_SANDBOX)) {
            echo "<input id='wpoven_manager_page_cache' name='wpoven_manager_page_cache' type='checkbox' value='1' $checked />";
        } else {
            echo "<input id='wpoven_manager_page_cache' disabled='disabled' name='wpoven_manager_page_cache' type='checkbox' value='1' $checked />";
        }
    }

    public function wpoven_manager_varnish_cache_input() {
        $options = get_option('wpoven_manager_varnish_cache');
        $checked = checked('1', $options, FALSE);

        if(!(defined('WPOVEN_MANAGER_SANDBOX') && WPOVEN_MANAGER_SANDBOX)) {
            echo "<input id='wpoven_manager_varnish_cache' name='wpoven_manager_varnish_cache' type='checkbox' value='1' $checked />";
        } else {
            echo "<input id='wpoven_manager_varnish_cache' disabled='disabled' name='wpoven_manager_varnish_cache' type='checkbox' value='1' $checked />";
        }
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

        $this->flush_all_cache();

        echo 1;
        die;
    }

    public function load_plugins(){
        require_once(WPOVEN_MANAGER_DIR.'/nginx-compatibility.php');

        require_once(WPOVEN_MANAGER_DIR.'/wpoven-manager-sandbox.php');

        if(!(defined('WPOVEN_MANAGER_SANDBOX') && WPOVEN_MANAGER_SANDBOX)){
            $cache = get_option('wpoven_manager_page_cache');
            if($cache === "1"){
                require_once(WPOVEN_MANAGER_DIR.'/hyper-cache/plugin.php');
            }
        }

        $maintenance = get_option('wpoven_manager_maintenance');
        if($maintenance === "1"){
            require_once(WPOVEN_MANAGER_DIR.'/wpoven-manager-maintenance.php');
        }

        $cache = get_option('wpoven_manager_varnish_cache');
        if(!($cache === "1")){
            $this->deactivate_varnish_cache();
        }
    }

    public function activate() {
        add_option('wpoven_manager_maintenance', '');
        add_option('wpoven_manager_page_cache', '1');
        add_option('wpoven_manager_varnish_cache', '1');
        $this->activate_page_cache();
    }

    public function deactivate() {
        delete_option('wpoven_manager_maintenance');
        delete_option('wpoven_manager_page_cache');
        delete_option('wpoven_manager_varnish_cache');
        $this->deactivate_page_cache();
    }

    public function activate_page_cache() {
        require_once(WPOVEN_MANAGER_DIR.'/hyper-cache/plugin.php');
        if ( function_exists( 'hyper_activate' ) ) {
            hyper_activate();
        }
    }

    public function deactivate_page_cache() {
        if ( function_exists( 'hyper_deactivate' ) ) {
            hyper_deactivate();
        }
    }

    public function deactivate_varnish_cache() {
        add_action('init', array($this, 'set_cookie'));
    }

    public function change_maintenance($oldvalue, $newvalue) {
        if($oldvalue !== $newvalue){
            $this->flush_all_cache();
        }
    }

    public function change_page_cache($oldvalue, $newvalue) {
        if($oldvalue !== $newvalue){
            if('1' === $newvalue) {
                $this->activate_page_cache();
                $this->flush_page_cache();
            } else if('' === (string) $newvalue) {
                $this->flush_page_cache();
                $this->deactivate_page_cache();
            }
        }
    }

    public function add_flush_actions(){
        // flush automatic apc cache if in sandbox
        add_action('switch_theme', array($this, 'flush_all_cache'));
        add_action('publish_phone', array($this, 'flush_all_cache'));
        add_action('publish_post', array($this, 'flush_all_cache'));
        add_action('edit_post', array($this, 'flush_all_cache'));
        add_action('save_post', array($this, 'flush_all_cache'));
        add_action('wp_trash_post', array($this, 'flush_all_cache'));
        add_action('delete_post', array($this, 'flush_all_cache'));
        add_action('trackback_post', array($this, 'flush_all_cache'));
        add_action('pingback_postt', array($this, 'flush_all_cache'));
        add_action('comment_post', array($this, 'flush_all_cache'));
        add_action('edit_comment', array($this, 'flush_all_cache'));
        add_action('wp_set_comment_status', array($this, 'flush_all_cache'));
        add_action('delete_comment', array($this, 'flush_all_cache'));
        add_action('comment_cookie_lifetime', array($this, 'flush_all_cache'));
        add_action('wp_update_nav_menu', array($this, 'flush_all_cache'));
        add_action('edit_user_profile_update', array($this, 'flush_all_cache'));
    }

    public function flush_all_cache() {
        $url = get_site_url();
        $url = $url . '/';
        $this->flush_varnish_cache($url);
        $this->flush_apc_cache();
        $this->flush_page_cache();
    }

    public function flush_varnish_cache($url) {
        if(!(defined('WPOVEN_MANAGER_SANDBOX') && WPOVEN_MANAGER_SANDBOX)) {
            wp_remote_request($url, array('method' => 'PURGE'));
        }
    }

    public function flush_apc_cache() {
        if ( function_exists( 'apc_clear_cache' ) ) {
            apc_clear_cache();
            apc_clear_cache('user');
            apc_clear_cache('opcode');
        }
    }

    public function flush_page_cache() {
        if ( function_exists( 'hyper_delete_path' ) ) {
            hyper_delete_path(WP_CONTENT_DIR . '/cache/hyper-cache');
        }
    }

    public function set_cookie() {
        if (!isset($_COOKIE['wpoven-no-cache'])) {
            setcookie('wpoven-no-cache', 1, time() + 120);
        }
    }

}

$wpoven_manager = new WPOven_Manager_Admin();