<?php
global $VereinsfliegerLogin;

if (isset($_GET['tab'])) {
    $active_tab = $_GET['tab'];
} else {
    $active_tab = 'simple';
}
?>
<div class="wrap">

    <div id="icon-themes" class="icon32"></div>
    <h2>Vereinsflieger Login Settings</h2>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo add_query_arg(array('tab' => 'simple'), $_SERVER['REQUEST_URI']); ?>" class="nav-tab <?php echo $active_tab == 'simple' ? 'nav-tab-active' : ''; ?>">Simple</a>
        <a href="<?php echo add_query_arg(array('tab' => 'advanced'), $_SERVER['REQUEST_URI']); ?>" class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>">Advanced</a>
        <a href="<?php echo add_query_arg(array('tab' => 'user'), $_SERVER['REQUEST_URI']); ?>" class="nav-tab <?php echo $active_tab == 'user' ? 'nav-tab-active' : ''; ?>">User</a>
        <a href="<?php echo add_query_arg(array('tab' => 'help'), $_SERVER['REQUEST_URI']); ?>" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">Help</a>
    </h2>

    <form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <?php wp_nonce_field('save_sll_settings', 'save_the_sll'); ?>

        <?php if ($active_tab == "simple"): ?>
            <h3>Required</h3>
            <p>These are the most basic settings you must configure. Without these, you won't be able to use Simple LDAP Login.</p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">Enable LDAP Authentication</th>
                        <td>
                            <input type="hidden" name="<?php echo $this->get_field_name('enabled'); ?>" value="false" />
                            <label><input type="checkbox" name="<?php echo $this->get_field_name('enabled'); ?>" value="true" <?php if (str_true($this->get_setting('enabled'))) echo "checked"; ?> /> Enable Vereinsflieger login authentication for WordPress. (this one is kind of important)</label><br/>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p><input class="button-primary" type="submit" value="Save Settings" /></p>
        <?php elseif ($active_tab == "advanced"): ?>
            <h3>Typical</h3>
            <p>These settings give you finer control over how logins work.</p>
            <table class="form-table" style="margin-bottom: 20px;">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">Vereinsflieger Exclusive</th>
                        <td>
                            <input type="hidden" name="<?php echo $this->get_field_name('high_security'); ?>" value="false" />
                            <label><input type="checkbox" name="<?php echo $this->get_field_name('high_security'); ?>" value="true" <?php if (str_true($this->get_setting('high_security'))) echo "checked"; ?> /> Force all logins to authenticate against Vereinsflieger. Do NOT fallback to default authentication for existing users.<br/>Formerly known as high security mode.</label><br/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">User Creations</th>
                        <td>
                            <input type="hidden" name="<?php echo $this->get_field_name('create_users'); ?>" value="false" />
                            <label><input type="checkbox" name="<?php echo $this->get_field_name('create_users'); ?>" value="true" <?php if (str_true($this->get_setting('create_users'))) echo "checked"; ?> /> Create WordPress user for authenticated Vereinsflieger login with appropriate roles.</label><br/>
                        </td>
                    <tr>
                        <th scope="row" valign="top">New User Role</th>
                        <td>
                            <select name="<?php echo $this->get_field_name('role'); ?>">
                                <?php wp_dropdown_roles(strtolower($this->get_setting('role'))); ?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr />
            <h3>Extraordinary</h3>
            <p>Most users should leave these alone.</p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">Use TLS</th>
                        <td>
                            <input type="hidden" name="<?php echo $this->get_field_name('use_tls'); ?>" value="false" />
                            <label><input type="checkbox" name="<?php echo $this->get_field_name('use_tls'); ?>" value="true" <?php if (str_true($this->get_setting('use_tls'))) echo "checked"; ?> /> Transport Layer Security. This feature is beta, very beta.</label><br/>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p><input class="button-primary" type="submit" value="Save Settings" /></p>
        <?php elseif ($active_tab == "user"): /*?>
            <h3>User Data</h3>
            <p>These settings give you control over which LDAP attributes are used for user creation.</p>
            <table class="form-table" style="margin-bottom: 20px;">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">First name</th>
                        <td>
                            <input type="text" name="<?php echo $this->get_field_name('user_first_name_attribute'); ?>" value="<?php echo $SimpleLDAPLogin->get_setting('user_first_name_attribute'); ?>" />
                            <br/>
                            The LDAP attribute for the first name.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">Last name</th>
                        <td>
                            <input type="text" name="<?php echo $this->get_field_name('user_last_name_attribute'); ?>" value="<?php echo $SimpleLDAPLogin->get_setting('user_last_name_attribute'); ?>" />
                            <br/>
                            The LDAP attribute for the last name.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">Email</th>
                        <td>
                            <input type="text" name="<?php echo $this->get_field_name('user_email_attribute'); ?>" value="<?php echo $SimpleLDAPLogin->get_setting('user_email_attribute'); ?>" />
                            <br/>
                            The LDAP attribute for the email.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">Website</th>
                        <td>
                            <input type="text" name="<?php echo $this->get_field_name('user_url_attribute'); ?>" value="<?php echo $SimpleLDAPLogin->get_setting('user_url_attribute'); ?>" />
                            <br/>
                            The LDAP attribute for the website.
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr />
            <h3>Additional user data</h3>
            <p>Additional user data can be stored as user meta data. You can specify the LDAP
                attributes and the associated wordpress meta keys in the format <i>&lt;ldap_attribute_name&gt;:&lt;wordpress_meta_key&gt;</i>. Multiple attributes can be given on separate lines.</p>
            <p> Example:<br/><i>phone:user_phone_number</i><br/><i>adress:user_home_address</i></p>
            <table class="form-table" style="margin-bottom: 20px;">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">Meta data</th>
                        <td>
                            <textarea name="<?php echo $this->get_field_name('user_meta_data'); ?>">
                                <?php echo join("\n", array_map(function ($attr) {
                                            return join(':', $attr);
                                        }, $SimpleLDAPLogin->get_setting('user_meta_data'))); ?>
                            </textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p><input class="button-primary" type="submit" value="Save Settings" /></p>
<?php else: ?>
            <h3>Help</h3>
            <p>Here's a brief primer on how to effectively use and test Simple LDAP Login.</p>
            <h4>Testing</h4>
            <p>The most effective way to test logins is to use two browsers. In other words, keep the WordPress Dashboard open in Chrome, and use Firefox to try logging in. This will give you real time feedback on your settings and prevent you from inadvertently locking yourself out.</p>
            <h4>Which raises the question, what happens if I get locked out?</h4>
            <p>If you accidentally lock yourself out, the easiest way to get back in is to rename <strong><?php echo plugin_dir_path(__FILE__); ?></strong> to something else and then refresh. WordPress will detect the change and disable Simple LDAP Login. You can then rename the folder back to its previous name.</p>
<?php */ endif; ?>
    </form>
</div>
