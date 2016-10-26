<?php
/*
  Plugin Name: Vereinsflieger Login
  Plugin URI:
  Description:  Authenticate WordPress against Vereinsflieger.de.
  Version: 0.2
  Author:
  Author URI:
  License: GPL2
 */

if (!class_exists('WP_VfL')) {

    require_once(sprintf("%s/settings.php", dirname(__FILE__)));

    class WP_VfL {
        /* static $instance = false;
          var $prefix = 'vfl_';
          var $settings = array();
          var $vereinsfliegerRest;
          var $network_version = null;
          var $version = "01";
          var $fix_user_meta = array(); */

        static $options;
        static $basename;
        static $pages;

        public function __construct() {

            /* $this->settings = $this->get_settings_obj($this->prefix);

              require_once( plugin_dir_path(__FILE__) . "/includes/VereinsfliegerRestInterface.php" );
              $this->vereinsfliegerRest = new VereinsfliegerRestInterface();

              $this->fix_user_meta[] = array('uid', $this->prefix . 'uid');

              add_action('admin_init', array($this, 'save_settings'));
              add_action('show_user_profile', array($this, 'extra_profile_fields'));
              add_action('edit_user_profile', array($this, 'extra_profile_fields'));

              if ($this->is_network_version()) {
              add_action('network_admin_menu', array($this, 'menu'));
              } else {
              add_action('admin_menu', array($this, 'menu'));
              }

              //register my own site by parameter index.php?<PARAM>
              add_action('init', array($this, 'vfl_init_internal'));
              add_filter('query_vars', array($this, 'vfl_query_vars'));
              add_action('parse_request', array($this, 'vfl_parse_request'));

              if (str_true($this->get_setting('enabled'))) {
              $i = 10;
              if ('first' === $this->get_setting('order')) {
              $i = 1;
              } else if ('last' === $this->get_setting('order')) {
              $i = 100;
              }
              add_filter('authenticate', array($this, 'authenticate'), $i, 3);
              }

              register_activation_hook(__FILE__, array($this, 'activate'));

              // If version is false, and old version detected, run activation
              if ($this->get_setting('version') === false || $this->get_setting('version') != $version) {
              $this->upgrade_settings();
              } */

            // Initialize Settings
            //$WP_VfL_Settings = new WP_VfL_Settings();

            self::$options = WP_VfL_Options::get();
            self::$basename = plugin_basename(__FILE__);

            add_action('init', array(&$this, 'init'));
            if ($this->is_network_version()) {
                add_action('network_admin_menu', array(&$this, 'add_menu'));
            } else {
                add_action('admin_menu', array(&$this, 'add_menu'));
            }

            // Scripts and stylesheets
            add_action('wp_enqueue_scripts', array(&$this, 'wp_enqueue_scripts'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));

            $plugin = plugin_basename(__FILE__);
            add_filter("plugin_action_links_$plugin", array(&$this, 'plugin_settings_link'));

            add_action('show_user_profile', array(&$this, 'extra_profile_fields'));

            //register my own site by parameter index.php?<PARAM>            
            add_filter('query_vars', array(&$this, 'vfl_query_vars'));
            add_action('parse_request', array(&$this, 'vfl_parse_request'));
        }

        // END public function __construct

        /**
         * Activate the plugin
         */
        public static function activate() {
            
        }

        // END public static function activate

        /**
         * Deactivate the plugin
         */
        public static function deactivate() {
            
        }

        public static function init() {
            add_rewrite_rule('VfL-API.php$', 'index.php?vfl_api', 'top');

            // Load text domain
            //load_plugin_textdomain('wp_vfl', false, dirname(self::$basename) . '/languages');
            // Register hooks and create database tables
            self::register();


            // Check if upgrade is needed
            //$current_version = get_option('vfl_version');

            /* if ($current_version < '2.38.2') {
              self::$options->metaKeys = array(self::$options->metaKey);
              self::$options->save();
              } */

            //update_option('mappress_version', self::VERSION);
        }

        static function register() {
            global $wpdb;

            // Ajax
            add_action('wp_ajax_vfl-quick-search', array(__CLASS__, 'ajax_search'));
        }

        /**
         * Scripts & styles for frontend
         * CSS is loaded from: child theme, theme, or plugin directory
         */
        function wp_enqueue_scripts() {
            
        }

        // Scripts & styles for admin
        // CSS is always loaded from the plugin directory
        function admin_enqueue_scripts($hook) {

            // Some plugins call this without setting $hook
            if (empty($hook))
                return;

            // Settings page
            if ($hook == self::$pages[0]) {
                //wp_enqueue_script('postbox');
            }
        }

        function add_menu() {
            // Settings
            $settings = new WP_VfL_Settings();
            // Add a page to manage this plugin's settings
            if ($this->is_network_version()) {
                //add_submenu_page("settings.php", "Vereinsflieger Login", "Vereinsflieger Login", 'manage_network_plugins', "vereinsflieger-login", array($this, 'admin_page'));
            } else {
                //add_options_page("Vereinsflieger Login", "Vereinsflieger Login", 'manage_options', "vereinsflieger-login", array($this, 'admin_page'));
                self::$pages[] = add_options_page(
                        'Vereinsflieger Login', 'Vereinsflieger Login', 'manage_options', 'wp_vfl', array(&$settings, 'plugin_settings_page')
                );
            }
        }

        /*
         * Add the settings link to the plugins page
         */

        public function plugin_settings_link($links) {
            $settings_link = '<a href="options-general.php?page=wp_vfl">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        //END public function plugin_settings_link

        public static function vfl_query_vars($query_vars) {
            $query_vars[] = 'vfl_api';
            return $query_vars;
        }

        public function vfl_parse_request(&$wp) {
            if (isset($wp->query_vars['vfl_api']) ||
                    array_key_exists('vfl_api', $wp->query_vars)) {
                include dirname(__FILE__) . '/VfL-API.php';
                exit();
            }
            return;
        }

        function ajax_search($request = array()) {
            check_ajax_referer('my-special-string', 'security');
            //require_once(ABSPATH . 'wp-admin/includes/nav-menu.php');
            query_posts(array(
                'posts_per_page' => 10,
                'post_type' => 'page',
                's' => isset($request['q']) ? $request['q'] : '',
            ));
            if (!have_posts()) {
                wp_die();
            }
            while (have_posts()) {
                the_post();
                /* $var_by_ref = get_the_ID();                
                  echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', array(get_post($var_by_ref))), 0, array()); */
                echo wp_json_encode(
                        array(
                            'ID' => get_the_ID(),
                            'post_title' => get_the_title(),
                            'post_url' => esc_url(get_permalink()),
                            'post_type' => get_post_type(),
                        )
                );
                echo "\n";
            }
            wp_die();
        }

        /**
         * Returns whether this plugin is currently network activated
         */
        function is_network_version() {
            if ($this->network_version !== null) {
                return $this->network_version;
            }

            if (!function_exists('is_plugin_active_for_network')) {
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            }

            if (is_plugin_active_for_network(plugin_basename(__FILE__))) {
                $this->network_version = true;
            } else {
                $this->network_version = false;
            }
            return $this->network_version;
        }

        function extra_profile_fields($user) {
            ?>
            <h3>Meta Data from <code>Vereinsflieger.de</code></h3>
            <table class="form-table">
                <tr>
                    <th><label for="vfl_uid">UID</label></th>
                    <td>
                        <input type="text" name="vfl_uid" id="vfl_uid" value="<?php echo esc_attr(get_user_meta($user->ID, 'vfl_uid', true)); ?>" class="regular-text" /><br />
                        <span class="description"><code>Vereinsflieger.de</code> User ID</span>
                    </td>
                </tr>
            </table>
            <?php
        }

        /*
          function get_settings_obj() {
          if ($this->is_network_version()) {
          return get_site_option("{$this->prefix}settings", false);
          } else {
          return get_option("{$this->prefix}settings", false);
          }
          }

          function set_settings_obj($newobj) {
          if ($this->is_network_version()) {
          return update_site_option("{$this->prefix}settings", $newobj);
          } else {
          return update_option("{$this->prefix}settings", $newobj);
          }
          }

          function set_setting($option = false, $newvalue) {
          if ($option === false)
          return false;

          $this->settings = $this->get_settings_obj($this->prefix);
          $this->settings[$option] = $newvalue;
          return $this->set_settings_obj($this->settings);
          }

          function get_setting($option = false) {
          if ($option === false || !isset($this->settings[$option])) {
          return false;
          }

          if ('user_meta_data' === $option) {
          $result = array_merge($this->settings[$option], $this->fix_user_meta);
          return apply_filters($this->prefix . 'get_setting', $result, $option);
          }

          return apply_filters($this->prefix . 'get_setting', $this->settings[$option], $option);
          }

          function add_setting($option = false, $newvalue) {
          if ($option === false)
          return false;

          if (!isset($this->settings[$option])) {
          return $this->set_setting($option, $newvalue);
          } else
          return false;
          }

          function get_field_name($setting, $type = 'string') {
          return "{$this->prefix}setting[$setting][$type]";
          }

          function save_settings() {
          if (isset($_REQUEST["{$this->prefix}setting"]) && check_admin_referer('save_sll_settings', 'save_the_sll')) {
          $new_settings = $_REQUEST["{$this->prefix}setting"];

          foreach ($new_settings as $setting_name => $setting_value) {
          foreach ($setting_value as $type => $value) {
          if ($setting_name == 'user_meta_data') {
          $this->set_setting($setting_name, array_map(function ($attr) {
          return explode(':', $attr);
          }, array_filter(preg_split('/\r\n|\n|\r|;/', $value))));
          } elseif ($type == "array") {
          $this->set_setting($setting_name, explode(";", $value));
          } else {
          $this->set_setting($setting_name, $value);
          }
          }
          }

          add_action('admin_notices', array($this, 'saved_admin_notice'));
          }
          }

          function saved_admin_notice() {
          echo '<div class="updated">
          <p>Vereinsflieger Login settings have been saved.</p>
          </div>';

          if (!str_true($this->get_setting('enabled'))) {
          echo '<div class="error">
          <p>Vereinsflieger Login is disabled.</p>
          </div>';
          }
          }

          function authenticate($user, $username, $password) {
          // If previous authentication succeeded, respect that
          if (is_a($user, 'WP_User')) {
          return $user;
          }

          // Determine if user a local admin
          $local_admin = false;
          $user_obj = get_user_by('login', $username);
          if (user_can($user_obj, 'update_core')) {
          $local_admin = true;
          }

          //$local_admin = apply_filters('sll_force_ldap', $local_admin);
          $password = stripslashes($password);

          // To force Vereinsflieger authentication, the filter should return boolean false

          if (empty($username) || empty($password)) {
          $error = new WP_Error();

          if (empty($username))
          $error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));

          if (empty($password))
          $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

          return $error;
          }

          // If high security mode is enabled, remove default WP authentication hook
          if (apply_filters('sll_remove_default_authentication_hook', str_true($this->get_setting('high_security')) && !$local_admin)) {
          remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
          }

          // Sweet, let's try to authenticate our user and pass
          $auth_result = $this->vereinsfliegerRest->SignIn($username, $password);

          if ($auth_result) {
          $result = $this->vereinsfliegerRest->GetUser();
          if (!isset($result['uid'])) {
          return $this->auth_error("{$this->prefix}login_error", __('<strong>Vereinsflieger Login Error</strong>: Vereinsflieger credentials are correct, but there is no user id given in response.'));
          }
          $user = get_user_by($this->get_setting('compare_wp'), $result[$this->get_setting('compare_vfl')]);

          //if (!$user || ( strtolower($user->user_login) != $result['uid'] )) {
          if (!$user) {
          if (!str_true($this->get_setting('create_users'))) {
          do_action('wp_login_failed', $username);
          return $this->auth_error('invalid_username', __('<strong>Vereinsflieger Login Error</strong>: Vereinsflieger credentials are correct, but there is no matching WordPress user and user creation is not enabled.'));
          }

          $new_user = wp_insert_user($this->get_user_data($username));

          if (!is_wp_error($new_user)) {
          // Add user meta data
          $user_meta_data = $this->get_user_meta_data($username);
          foreach ($user_meta_data as $meta_key => $meta_value) {
          add_user_meta($new_user, $meta_key, $meta_value);
          }

          // Successful Login
          $new_user = new WP_User($new_user);
          do_action_ref_array($this->prefix . 'auth_success', array($new_user));

          return $new_user;
          } else {
          do_action('wp_login_failed', $username);
          return $this->auth_error("{$this->prefix}login_error", __('<strong>Vereinsflieger Login Error</strong>: Vereinsflieger credentials are correct and user creation is allowed but an error occurred creating the user in WordPress. Actual error: ' . $new_user->get_error_message()));
          }
          } else {
          $search_keys = array_keys($this->get_setting('user_meta_data'));
          $all_keys = array_keys(get_user_meta($user->ID));
          if (count(array_intersect($search_keys, $all_keys)) !== count($search_keys)) {
          // Add missing user meta data
          $user_meta_data = $this->get_user_meta_data($username);
          foreach ($user_meta_data as $meta_key => $meta_value) {
          add_user_meta($user->ID, $meta_key, $meta_value);
          }
          }
          return new WP_User($user->ID);
          }
          } elseif (str_true($this->get_setting('high_security'))) {
          return $this->auth_error('invalid_username', __('<strong>Vereinsflieger Login</strong>: Vereinsflieger Login could not authenticate your credentials. The security settings do not permit trying the WordPress user database as a fallback.'));
          }

          do_action($this->prefix . 'auth_failure');
          return false;
          } */

        /**
         * Prevent modification of the error message by other authenticate hooks
         * before it is shown to the user
         *
         * @param string $code
         * @param string $message
         * @return WP_Error
         */
        function auth_error($code, $message) {
            remove_all_filters('authenticate');
            return new WP_Error($code, $message);
        }

        /* function get_user_data($username) {
          $user_data = array(
          'user_pass' => md5(microtime()),
          'user_login' => $username,
          'user_nicename' => '',
          'user_email' => '',
          'display_name' => '',
          'first_name' => '',
          'last_name' => '',
          'role' => $this->get_setting('role')
          );

          $result = $this->vereinsfliegerRest->GetUser();

          if (is_array($result)) {
          $user_login = $this->get_setting('user_login');
          if (!in_array($user_login, ['uid', 'memberid', 'email'], true)) {
          $user_login = 'uid';
          }
          $user_data['user_login'] = $result[$user_login];
          $user_nicename = $this->get_setting('user_nicename');
          if (in_array($user_nicename, ['firstname', 'lastname', 'email'], true)) {
          $user_data['user_nicename'] = $result[$user_nicename];
          } else if ('first_lastname' === $user_nicename) {
          $user_data['user_nicename'] = $result['firstname'] . '-' . $result['lastname'];
          }
          $user_data['user_email'] = $result['email'];
          $user_display_name = $this->get_setting('user_display_name');
          if (in_array($user_display_name, ['firstname', 'lastname', 'email'], true)) {
          $user_data['display_name'] = $result[$user_display_name];
          } else if ('first_lastname' === $user_display_name) {
          $user_data['display_name'] = $result['firstname'] . ' ' . $result['lastname'];
          }
          $user_data['first_name'] = $result['firstname'];
          $user_data['last_name'] = $result['lastname'];
          }

          return apply_filters($this->prefix . 'user_data', $user_data);
          }

          function get_user_meta_data($username) {
          $userinfo = $this->vereinsfliegerRest->GetUser();
          $user_meta_data = array();
          foreach ($this->get_setting('user_meta_data') as $attr) {
          $user_meta_data[$attr[1]] = $userinfo[$attr[0]];
          }

          return apply_filters($this->prefix . 'user_meta_data', $user_meta_data);
          } */
    }

}
// END if(!class_exists('WP_VfL'))

if (class_exists('WP_VfL')) {
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('WP_VfL', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_VfL', 'deactivate'));

    // instantiate the plugin class
    $wp_vfl = new WP_VfL();
}
