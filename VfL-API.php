<?php

header("Content-type: text/html");

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === 'https://vereinsflieger.de') {
    //coming from vereinsflieger.de
    if (isset($_REQUEST['accesstoken'])) {
        //accesstoken is served

        if (is_user_logged_in()) {
            wp_logout();
        }

        $ret = $this->vereinsfliegerRest->SetAccessToken($_REQUEST['accesstoken']);
        if ($ret === 0) {   //same accesstoken
            //do something?
        } else if (!$ret) { //error setting accesstoken
            die('error setting accesstoken');
        }

        $result = $this->vereinsfliegerRest->GetUser();
        if (!isset($result['uid'])) {
            return $this->auth_error("{$this->prefix}login_error", __('<strong>Vereinsflieger Login Error</strong>: Vereinsflieger credentials are correct, but there is no user id given in response.'));
        }

        $user = get_user_by($this->get_setting('compare_wp'), $result[$this->get_setting('compare_vfl')]);

        //if (!$user || ( strtolower($user->user_login) != $result['uid'] )) {
        if (!$user) {
            do_action('wp_login_failed', $username);
            return $this->auth_error('invalid_username', __('<strong>Vereinsflieger Login Error</strong>: Vereinsflieger credentials are correct, but there is no matching WordPress user and user creation is not enabled.'));
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
            //login user
            wp_set_current_user($user->ID, $user->user_login);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login);
        }
    } else {
        die('no access token');
    }
} else {
    die('wrong origin');
}
?>
<?php if (is_user_logged_in) : ?>
    
<?php endif; ?>
