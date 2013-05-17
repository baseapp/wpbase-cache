<?php
class WPOven_Manager_Admin {
    
    public function __construct(){
        if(is_admin()){
	    add_action('admin_menu', array($this, 'add_wpoven_manager_page'));
	    add_action('admin_init', array($this, 'wpoven_manager_page_init'));
            add_action('update_option_wpoven_manager_maintenance', array($this, 'change_maintenance'), 10, 2);
            
            add_action('admin_footer', array($this, 'add_javascript'));
            add_action('wp_ajax_wpoven_manager_flush_all', array($this, 'ajax_flush_cache'));
            
            register_deactivation_hook(WPOVEN_MANAGER_DIR.'/wpoven-manager.php', array($this, 'deactivate_plugin'));
	}
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
        
    }
    
    public function wpoven_manager_section_desc() {
        //echo 'These settings are part of wpoven manager plugin.';
    }
    
    public function wpoven_manager_maintenance_input() {
        $options = get_option('wpoven_manager_maintenance');
        $checked = checked('1', $options, FALSE);
        echo "<input id='wpoven_manager_maintenance' name='wpoven_manager_maintenance' type='checkbox' value='1' $checked />";
    }
    
    public function change_maintenance($oldvalue, $newvalue) {
        if($oldvalue !== $newvalue){
            if ( function_exists( 'w3tc_pgcache_flush' ) ) {
                w3tc_pgcache_flush();
            }
            if ( function_exists( 'w3tc_varnish_flush' ) ) {
                w3tc_varnish_flush();
            }
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
        
        if( ! current_user_can('manage_options') ) {
            wp_die( __('You do not have sufficient permissions to perform this action.') );
        }
        
        if ( function_exists( 'w3tc_pgcache_flush' ) ) {
            w3tc_pgcache_flush();
        }
        
        if ( function_exists( 'w3tc_varnish_flush' ) ) {
            w3tc_varnish_flush();
        }
        
        echo 1;
        die;
    }
    
    public function deactivate_plugin() {
        delete_option('wpoven_manager_maintenance');
        
        if ( function_exists( 'w3tc_pgcache_flush' ) ) {
            w3tc_pgcache_flush();
        }
        
        if ( function_exists( 'w3tc_varnish_flush' ) ) {
            w3tc_varnish_flush();
        }
    }
}

$wpoven_manager = new WPOven_Manager_Admin();