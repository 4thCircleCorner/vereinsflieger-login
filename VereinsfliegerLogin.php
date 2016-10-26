<?php
/*
  Plugin Name: Vereinsflieger Login
  Plugin URI:
  Description:  Authenticate WordPress against Vereinsflieger.de.
  Version: 0.1.2
  Author:
  Author URI:
 */

class VereinsfliegerLogin {

    static $instance = false;
    var $prefix = 'vfl_';
    var $settings = array();
    var $vereinsfliegerRest;
    var $network_version = null;
    var $version = "012";
    var $fix_user_meta = array();

    public function __construct() {

        $this->settings = $this->get_settings_obj($this->prefix);

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
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    function activate() {
        // Default settings
        $this->add_setting('role', "contributor");
        $this->add_setting('high_security', "false");
        $this->add_setting('use_tls', "false");
        $this->add_setting('create_users', "true");
        $this->add_setting('enabled', "false");
        $this->add_setting('order', 'first');
        $this->add_setting('compare_wp', "email");
        $this->add_setting('compare_vfl', "email");
        $this->add_setting('user_login', "uid");
        $this->add_setting('user_nicename', "first_lastname");
        $this->add_setting('user_display_name', "first_lastname");

        $this->add_setting('user_meta_data', array());
    }

    function upgrade_settings() {
        
    }

    public static function vfl_init_internal() {
        add_rewrite_rule('VfL-API.php$', 'index.php?vfl_api', 'top');
    }

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

    function menu() {
        if ($this->is_network_version()) {
            add_submenu_page(
                    "settings.php", "Vereinsflieger Login", "Vereinsflieger Login", 'manage_network_plugins', "vereinsflieger-login", array($this, 'admin_page')
            );
        } else {
            add_options_page("Vereinsflieger Login", "Vereinsflieger Login", 'manage_options', "vereinsflieger-login", array($this, 'admin_page'));
        }
    }

    function admin_page() {
        include 'VereinsfliegerLogin-Admin.php';
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
            <?php /*if (!is_null($this->vereinsfliegerRest)) : ?>
                <tr>
                    <th><label for="vfl_authtoken">Current AuthToken</label></th>
                    <td>
                        <input type="text" name="vfl_authtoken" id="vfl_authtoken" value="<?php echo esc_attr(); ?>" class="regular-text" disabled="disabled"/><br />
                        <span class="description"><code>Vereinsflieger.de</code> AccessToken</span>
                    </td>
                </tr>
            <?php endif;*/ ?>
        </table>
        <?php
    }

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
    }

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

    function get_user_data($username) {
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

}

if (!function_exists('str_true')) {

    /**
     * Evaluates natural language strings to boolean equivalent
     *
     * Used primarily for handling boolean text provided in shopp() tag options.
     * All values defined as true will return true, anything else is false.
     *
     * Boolean values will be passed through.
     *
     * Replaces the 1.0-1.1 value_is_true()
     *
     * @author Jonathan Davis
     * @since 1.2
     *
     * @param string $string The natural language value
     * @param array $istrue A list strings that are true
     * @return boolean The boolean value of the provided text
     * */
    function str_true($string, $istrue = array('yes', 'y', 'true', '1', 'on', 'open')) {
        if (is_array($string))
            return false;
        if (is_bool($string))
            return $string;
        return in_array(strtolower($string), $istrue);
    }

}

$VereinsfliegerLogin = VereinsfliegerLogin::getInstance();
