<?php
if (!class_exists('WP_VfL_Settings')) {

    /**
     * Options
     */
    class WP_VfL_Options {

        // Default settings
        var $enabled = false;
        var $order = 'first';
        var $high_security = false;
        var $compare_wp = 'email';
        var $compare_vfl = 'email';
        var $create_users = true;
        var $user_role = 'contributor';
        var $user_login = 'uid';
        var $user_nicename = 'first_lastname';
        var $user_display_name = 'first_lastname';
        var $user_meta_data = array();
        var $network_version = null;

        function __construct($options = '') {
            $this->update($options);
        }

        // Options are saved as array because WP settings API is fussy about objects
        static function get() {
            $options = get_option('wp_vfl_options');
            return new WP_VfL_Options($options);
        }

        static function get_defaults() {
            return (object) get_class_vars(__CLASS__);
        }

        function save() {
            return update_option('wp_vfl_options', get_object_vars($this));
        }

        function update($atts = null) {
            if (!$atts) {
                return;
            }

            $obj_atts = get_object_vars($this);

            foreach ($obj_atts as $key => $value) {
                $newvalue = (isset($atts[$key])) ? $atts[$key] : null;

                // Allow attributes to be all lowercase to handle shortcodes
                if ($newvalue === null) {
                    $lkey = strtolower($key);
                    $newvalue = (isset($atts[$lkey])) ? $atts[$lkey] : null;
                }

                if ($newvalue === null) {
                    continue;
                }

                // Convert any string versions of true/false
                if ($newvalue === "true") {
                    $newvalue = true;
                }
                if ($newvalue === "false") {
                    $newvalue = false;
                }

                $this->$key = $newvalue;
            }
        }

    }

    // End class WP_FCS_CAM_Options

    class WP_VfL_Settings {

        var $options;

        /**
         * Construct the plugin object
         */
        public function __construct() {
            $this->options = WP_VfL_Options::get();

            // register actions
            add_action('admin_init', array(&$this, 'admin_init'));
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init() {
            register_setting('wp_vfl', 'wp_vfl_options', array($this, 'set_options'));

            add_settings_section('basic_settings', __('Basic Settings', 'wp_vfl'), null, 'wp_vfl');
            add_settings_field('enabled', __('Enable Authentication by <code>Vereinsflieger.de</code>', 'wp_vfl'), array(&$this, 'set_enable'), 'wp_vfl', 'basic_settings');
            add_settings_field('authorder', __('Authentication Order', 'wp_vfl'), array(&$this, 'set_authorder'), 'wp_vfl', 'basic_settings');
            add_settings_field('integration', __('Integration in <code>Vereinsflieger.de</code>', 'wp_vfl'), array(&$this, 'set_integration'), 'wp_vfl', 'basic_settings');

            add_settings_section('adv_settings', __('Advanced Settings', 'wp_vfl'), null, 'wp_vfl');
            add_settings_field('comparison', __('Comparison', 'wp_vfl'), array(&$this, 'set_comparison'), 'wp_vfl', 'adv_settings');
            add_settings_field('highsecurity', __('Vereinsflieger Login Exclusive', 'wp_vfl'), array(&$this, 'set_highsecurity'), 'wp_vfl', 'adv_settings');
            add_settings_field('usercreation', __('User Creations', 'wp_vfl'), array(&$this, 'set_createusers'), 'wp_vfl', 'adv_settings');
            add_settings_field('userrole', __('New User Role', 'wp_vfl'), array(&$this, 'set_userrole'), 'wp_vfl', 'adv_settings');
            add_settings_field('userlogin', __('New User Login', 'wp_vfl'), array(&$this, 'set_userlogin'), 'wp_vfl', 'adv_settings');
            add_settings_field('usernickname', __('New User Nickname', 'wp_vfl'), array(&$this, 'set_usernickname'), 'wp_vfl', 'adv_settings');
            add_settings_field('userdisplayname', __('New User Displayname', 'wp_vfl'), array(&$this, 'set_userdisplayname'), 'wp_vfl', 'adv_settings');

            add_settings_section('debug_settings', __('Debug Settings', 'wp_vfl'), array(&$this, 'debug_section'), 'wp_vfl');
        }

        /**
         * Called on load action.
         *
         * @return void
         */
        public static function load() {
            global $wpdb;
            die('jo');
            if (isset($_GET['action']) && $_GET['action'] == 'wpfcscamdebug') {

                //check permissions
                check_admin_referer('wpfcscamdebug');

                /* if ( ! current_user_can( 'backwpup_jobs_edit' ) )
                  die(); */

                //doing dump
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/octet-stream; charset=" . get_bloginfo('charset'));
                /* header( "Content-Disposition: attachment; filename=" . DB_NAME . ".sql;" );
                  try {
                  $sql_dump = new BackWPup_MySQLDump();
                  foreach ( $sql_dump->tables_to_dump as $key => $table ) {
                  if ( $wpdb->prefix != substr( $table,0 , strlen( $wpdb->prefix ) ) )
                  unset( $sql_dump->tables_to_dump[ $key ] );
                  }
                  $sql_dump->execute();
                  unset( $sql_dump );
                  } catch ( Exception $e ) {
                  die( $e->getMessage() );
                  } */
                echo 'test debug';
                die();
            }
        }

        function set_options($input) {
            // If reset defaults was clicked
            if (isset($_POST['reset_defaults'])) {
                $options = new WP_VfL_Options();
                return get_object_vars($this);
            }
        }

        function set_enable() {
            echo self::checkbox($this->options->enabled, 'wp_vfl_options[enable]');
            echo __('Enable <code>Vereinsflieger.de</code> login authentication for WordPress. (this one is kind of important)', 'wp_vfl');
        }

        function set_authorder() {
            $units = array(1 => __('First(1)', 'wp_vfl'), 10 => __('Default(10)', 'wp_vfl'), 100 => __('Very Last(100)', 'wp_vfl'));
            echo self::dropdown($units, $this->options->directionsUnits, 'wp_vfl_options[authorder]') . '<br/>';
            echo __('Select hook priority in Wordpress authentication process.', 'wp_vfl');
        }

        function set_integration() {
            ?>
            <input type="text" name="" value="<?php echo site_url('index.php?vfl_api', 'https'); ?>" size="50" readonly="readonly" /><br/>
            <?php
            echo __('Use this url in <code>Vereinsflieger.de</code> to integrate under "Vereinsübersicht".', 'wp_vfl');
            echo '<br/><br/>';

            $labels = array();
            $labels['post'] = array('name' => __('Post', 'wp_vfl'), 'data' => 'tabs-panel-post-search');
            $labels['page'] = array('name' => __('Page', 'wp_vfl'), 'data' => 'tabs-panel-page-search');
            $labels['custom'] = array('name' => __('Custom', 'wp_vfl'), 'data' => 'tabs-panel-custom');

            /* $custom_post_types = get_post_types(array('show_ui' => true, '_builtin' => false), 'objects');
              var_dump($custom_post_types);
              foreach ($custom_post_types as $name => $type) {
              $labels[$name] = $type->label;
              }
              var_dump($labels); */
            echo self::radio($labels, $this->options->postTypes, "wp_vfl_options[integration]");
            ?>
            <div id="tabs-panel-post-search" class="tabs-panel <?php
            echo ( 'post' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
            ?>">
                Currently selected: 
                <p class="quick-search-wrap">
                    <input type="search" class="quick-search input-with-default-title" title="<?php esc_attr_e('Search'); ?>" value="<?php echo $searched; ?>" name="quick-search-post" />
                    <span class="spinner"></span>
                    <?php //submit_button(__('Search'), 'button-small quick-search-submit button-secondary', 'submit', false, array('id' => 'post')); ?>
                </p>
            </div><!-- /.tabs-panel -->

            <div id="tabs-panel-page-search" class="tabs-panel <?php
            echo ( 'page' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
            ?>">
                Currently selected: 
                <p class="quick-search-wrap">
                    <input type="search" class="quick-search input-with-default-title" title="<?php esc_attr_e('Search'); ?>" value="<?php echo $searched; ?>" name="quick-search-page" />
                    <span class="spinner"></span>
                    <?php //submit_button(__('Search'), 'button-small quick-search-submit button-secondary', 'submit', false, array('id' => 'page')); ?>
                </p>
            </div><!-- /.tabs-panel -->

            <div id="tabs-panel-custom" class="tabs-panel <?php
            echo ( 'custom' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
            ?>">
                <label for="wp_vfl_options[integration][custom]"><?php echo __('Set Custom URL:','wp_vfl') ?></label>
                <input type="text" name="wp_vfl_options[integration][custom]" value="" size="50" /><br/>
                <?php //submit_button(__('Search'), 'button-small quick-search-submit button-secondary', 'submit', false, array('id' => 'custom')); ?>
            </div><!-- /.tabs-panel -->
            <?php
        }

        function set_comparison() {
            $units_wp = array('id' => __('ID', 'wp_vfl'), 'slug' => __('Slug', 'wp_vfl'), 'email' => __('E-Mail', 'wp_vfl'), 'login' => __('Login', 'wp_vfl'));
            $units_vfl = array('uid' => __('Unique ID by Vereinsflieger.de', 'wp_vfl'), 'mid' => __('Membership Number', 'wp_vfl'), 'email' => __('E-Mail', 'wp_vfl'));
            echo __('Compare wordpress user info: ', 'wp_vfl');
            echo self::dropdown($units_wp, $this->options->compare_wp, 'wp_vfl_options[compare_wp]');
            echo __('against <code>Vereinsflieger.de</code>: ', 'wp_vfl');
            echo self::dropdown($units_vfl, $this->options->compare_wp, 'wp_vfl_options[compare_vfl]') . '<br/>';
            echo __('Select which user info should by checked against <code>Vereinsflieger.de</code> authentication. <code>Vereinsflieger.de</code> authenticates with uid or email as username.', 'wp_vfl');
        }

        function set_highsecurity() {
            echo self::checkbox($this->options->high_security, 'wp_vfl_options[highsecurity]');
            echo __('Force all logins to authenticate against <code>Vereinsflieger.de</code>. Do NOT fallback to default authentication for existing users.<br/>Formerly known as high security mode.', 'wp_vfl');
        }

        function set_createusers() {
            echo self::checkbox($this->options->create_users, 'wp_vfl_options[createusers]');
            echo __('Enable <code>Vereinsflieger.de</code> login authentication for WordPress. (this one is kind of important)', 'wp_vfl');
        }

        function set_userrole() {
            ?>
            <select name="wp_vfl_options[userrole]">
                <?php wp_dropdown_roles(strtolower($this->options->user_role)); ?>
            </select>
            <?php
            //echo '<br/>...';
        }

        function set_userlogin() {
            $units = array('uid' => __('Unique ID by Vereinsflieger.de', 'wp_vfl'), 'mid' => __('Membership Number', 'wp_vfl'), 'lastname' => __('Lastname', 'wp_vfl'), 'email' => __('E-Mail', 'wp_vfl'));
            echo self::dropdown($units, $this->options->user_login, 'wp_vfl_options[userlogin]');
            //echo '<br/>...';
        }

        function set_usernickname() {
            $units = array('firstname' => __('[Firstname]', 'wp_vfl'), 'lastname' => __('[Lastname]', 'wp_vfl'), 'email' => __('[E-Mail]', 'wp_vfl'), 'first_lastname' => __('[Firstname]-[Lastname]', 'wp_vfl'));
            echo self::dropdown($units, $this->options->user_nicename, 'wp_vfl_options[usernickname]');
            //echo '<br/>...';
        }

        function set_userdisplayname() {
            $units = array('firstname' => __('[Firstname]', 'wp_vfl'), 'lastname' => __('[Lastname]', 'wp_vfl'), 'email' => __('[E-Mail]', 'wp_vfl'), 'first_lastname' => __('[Firstname] [Lastname]', 'wp_vfl'));
            echo self::dropdown($units, $this->options->user_display_name, 'wp_vfl_options[userdisplayname]');
            //echo '<br/>...';
        }

        function debug_section() {
            ?>
            <a href="<?php echo wp_nonce_url(network_admin_url('admin.php') . '?page=wp_vfl&action=wpvfldebug', 'wpvfldebug'); ?>" class="button button-primary button-primary-bwp" title="<?php _e('Debug', 'wp_vfl'); ?>"><?php _e('Debug', 'wp_vfl'); ?></a><br />
            <?php
        }

        /**
         * Output a metabox for a settings section
         *
         * @param mixed $object - required by WP, but ignored, always null
         * @param mixed $metabox - arguments for the metabox
         */
        function metabox_settings($object, $metabox) {
            global $wp_settings_fields;

            $page = $metabox['args']['page'];
            $section = $metabox['args']['section'];

            if ($section['callback']) {
                call_user_func($section['callback'], $section);
            }
            if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]))
                return;

            echo '<table class="form-table">';
            do_settings_fields($page, $section['id']);
            echo '</table>';
        }

        /**
         * Replacement for standard WP do_settings_sections() function.
         * This version creates a metabox for each settings section instead of just outputting the section to the screen
         *
         */
        public function do_settings_sections($page) {
            global $wp_settings_sections, $wp_settings_fields;

            if (!isset($wp_settings_sections) || !isset($wp_settings_sections[$page]))
                return;

            // Add a metabox for each settings section
            foreach ((array) $wp_settings_sections[$page] as $section) {
                add_meta_box('metabox_' . $section['id'], $section['title'], array(&$this, 'metabox_settings'), 'wp_vfl', 'normal', 'high', array('page' => 'wp_vfl', 'section' => $section));
            }

            // Display all the registered metaboxes
            do_meta_boxes('wp_vfl', 'normal', null);
        }

        /**
         * Boolean checkbox
         *
         * @param mixed $value - field value
         * @param mixed $name - field name
         * @param mixed $label - field label
         * @param mixed $checked - value to check against (true will set the checkbox only if the value is true)
         */
        static function checkbox($value, $name, $label = '', $checked = true) {
            return "<input type='hidden' name='$name' value='false' /><label><input type='checkbox' name='$name' value='true' " . checked($value, $checked, false) . " /> $label </label>";
        }

        /**
         * List checkbox
         *
         * @param mixed $value - current field value
         * @param mixed $name - field name
         * @param mixed $labels - array of (key => label) for all possible values
         */
        static function checkbox_list($values, $name, $labels) {

            $html = "";
            if (empty($values))
                $values = array();

            foreach ($labels as $key => $label) {
                $checked = (in_array($key, $values)) ? "checked='checked'" : "";
                $html .= "<div style='display:inline-block;margin-right:10px;'><label><input type='checkbox' name='$name' value='$key' " . $checked . " /> $label</label></div>";
            }

            return $html;
        }

        /**
         * Show a dropdown list
         *
         * $args values:
         *   id ('') - HTML id for the dropdown field
         *   title = HTML title for the field
         *   selected (null) - currently selected key value
         *   ksort (true) - sort the array by keys, ascending
         *   asort (false) - sort the array by values, ascending
         *   none (false) - add a blank entry; set to true to use '' or provide a string (like '-none-')
         *   select_attr - string to apply to the <select> tag, e.g. "DISABLED"
         *
         * @param array  $data  - array of (key => description) to display.  If description is itself an array, only the first column is used
         * @param string $selected - currently selected value
         * @param string $name - HTML field name
         * @param mixed  $args - arguments to modify the display
         *
         */
        static function dropdown($data, $selected, $name = '', $args = null) {
            $defaults = array(
                'id' => $name,
                'asort' => false,
                'ksort' => false,
                'none' => false,
                'class' => null,
                'multiple' => false,
                'select_attr' => ""
            );

            if (!is_array($data))
                return;

            if (empty($data))
                $data = array();

            // Data is in key => value format.  If value is itself an array, use only the 1st column
            foreach ($data as $key => &$value) {
                if (is_array($value))
                    $value = array_shift($value);
            }

            extract(wp_parse_args($args, $defaults));

            if ($asort)
                asort($data);
            if ($ksort)
                ksort($data);

            // If 'none' arg provided, prepend a blank entry
            if ($none) {
                if ($none === true)
                    $none = '&nbsp;';
                $data = array('' => $none) + $data;    // Note that array_merge() won't work because it renumbers indexes!
            }

            if (!$id)
                $id = $name;

            $name = ($name) ? "name='$name'" : "";
            $id = ($id) ? "id='$id'" : "";
            $class = ($class) ? "class='$class'" : "";
            $multiple = ($multiple) ? "multiple='multiple'" : "";

            $html = "<select $name $id $class $multiple $select_attr>";

            foreach ((array) $data as $key => $description) {
                $key = esc_attr($key);
                $description = esc_attr($description);

                $html .= "<option value='$key' " . selected($selected, $key, false) . ">$description</option>";
            }
            $html .= "</select>";
            return $html;
        }

        /**
         * Generate a set of radio buttons
         *
         * @param array $arr
         * @param mixed $checked
         * @param mixed $name
         * @param mixed $data
         * @return mixed
         */
        static function radio(array $arr, $checked, $name) {

            $name = ($name) ? "name='$name'" : "";
            $html = "";

            // If the value is an array, loop through it and print each key => description
            foreach ((array) $arr as $key => $values) {
                $key = esc_attr($key);
                $data = esc_attr($values['data']);
                $html .= "<div style='display:inline-block;margin-right:10px;'><label><input type='radio' $name value='$key' data-target='$data' " . checked($checked, $key, false) . "/> ".$values['name']."</label></div>";
            }
            return $html;
        }

        /**
         * Outputs a table
         *
         * $args values:
         *   class 		- CSS class for table
         * 	col_styles 	- array of column styles
         *  	footer 		- array of footer cols
         * 	id 			- table id
         * 	style 		- CSS styles for table
         *
         * @param mixed array $headers - array of header cols
         * @param mixed array $rows - array of rows; rows are arrays of cols
         * @param mixed array $args
         */
        static function table($headers, $rows, $args = '') {
            $defaults = array(
                'class' => 'mapp-table',
                'id' => '',
                'style' => '',
                'col_styles' => null
            );

            extract(wp_parse_args($args, $defaults));

            $html = "<table id='$id' class='$class' style='$style'><thead><tr>";

            foreach ((array) $headers as $i => $header) {
                $style = ($col_styles) ? "style='$col_styles[$i]'" : '';
                $html .= "<th $style>$header</th>";
            }
            $html .= "</tr></thead>";
            $html .= "<tbody>";

            foreach ((array) $rows as $i => $row) {
                $html .= "<tr>";
                foreach ((array) $row as $col)
                    $html .= "<td>$col</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody>";

            $html .= "</table>";
            return $html;
        }

        /**
         * Menu Callback
         */
        public function plugin_settings_page() {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            // Render the settings template
            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        }

    }

    // END class WP_FCS_CAM_Settings
} // END if(!class_exists('WP_FCS_CAM_Settings'))
