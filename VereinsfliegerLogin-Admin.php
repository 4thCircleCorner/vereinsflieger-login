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
            <p>These are the most basic settings you must configure. Without these, you won't be able to use <code>Vereinsflieger.de</code> Login.</p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">Enable Authentication by <code>Vereinsflieger.de</code></th>
                        <td>
                            <input type="hidden" name="<?php echo $this->get_field_name('enabled'); ?>" value="false" />
                            <label><input type="checkbox" name="<?php echo $this->get_field_name('enabled'); ?>" value="true" <?php if (str_true($this->get_setting('enabled'))) echo "checked"; ?> /> Enable <code>Vereinsflieger.de</code> login authentication for WordPress. (this one is kind of important)</label><br/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">Authentication Order</th>
                        <td>
                            <select name="<?php echo $this->get_field_name('order'); ?>">
                                <option value="first"<?php echo $this->get_setting('order') == 'first' ? ' selected=selected' : ''; ?>>First(1)</option>
                                <option value="default"<?php echo $this->get_setting('order') == 'default' ? ' selected=selected' : ''; ?>>Default(10)</option>
                                <option value="last"<?php echo $this->get_setting('order') == 'last' ? ' selected=selected' : ''; ?>>Very Last(100)</option>
                            </select><br/>
                            Select hook priority in Wordpress authentication process.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top"><code>Vereinsflieger.de</code> Integration</th>
                        <td>
                            <label>
                                <input type="text" name="" value="<?php echo site_url( 'index.php?vfl_api', 'https' ); ?>" size="50" readonly="readonly" /><br/> 
                                Use this url in <code>Vereinsflieger.de</code> to integrate under "Vereinsübersicht".
                            </label><br/>
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
                        <th scope="row" valign="top">Comparison</th>
                        <td>
                            Wordpress User Info:
                            <select name="<?php echo $this->get_field_name('compare_wp'); ?>">
                                <option value="id"<?php echo $this->get_setting('compare_wp') == 'id' ? ' selected=selected' : ''; ?>>ID</option>
                                <option value="slug"<?php echo $this->get_setting('compare_wp') == 'slug' ? ' selected=selected' : ''; ?>>Slug</option>
                                <option value="email"<?php echo $this->get_setting('compare_wp') == 'email' ? ' selected=selected' : ''; ?>>E-Mail</option>
                                <option value="login"<?php echo $this->get_setting('compare_wp') == 'login' ? ' selected=selected' : ''; ?>>Login</option>

                            </select>
                            against <code>Vereinsflieger.de</code>:
                            <select name="<?php echo $this->get_field_name('compare_vfl'); ?>">
                                <option value="uid"<?php echo $this->get_setting('compare_vfl') == 'uid' ? ' selected=selected' : ''; ?>>Eindeutige Id im Vereinsflieger [uid]</option>
                                <option value="memberid"<?php echo $this->get_setting('compare_vfl') == 'memberid' ? ' selected=selected' : ''; ?>>Mitgliedsnr [memberid]</option>
                                <option value="email"<?php echo $this->get_setting('compare_vfl') == 'email' ? ' selected=selected' : ''; ?>>E-Mail-Adresse [email]</option>
                            </select><br/>
                            Select which user info should by checked against <code>Vereinsflieger.de</code> authentication. <code>Vereinsflieger.de</code> authenticates with uid or email as username.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">Vereinsflieger Exclusive</th>
                        <td>
                            <input type="hidden" name="<?php echo $this->get_field_name('high_security'); ?>" value="false" />
                            <label><input type="checkbox" name="<?php echo $this->get_field_name('high_security'); ?>" value="true" <?php if (str_true($this->get_setting('high_security'))) echo "checked"; ?> /> Force all logins to authenticate against <code>Vereinsflieger.de</code>. Do NOT fallback to default authentication for existing users.<br/>Formerly known as high security mode.</label><br/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">User Creations</th>
                        <td>
                            <input type="hidden" name="<?php echo $this->get_field_name('create_users'); ?>" value="false" />
                            <label><input type="checkbox" name="<?php echo $this->get_field_name('create_users'); ?>" value="true" <?php if (str_true($this->get_setting('create_users'))) echo "checked"; ?> /> Create WordPress user for authenticated <code>Vereinsflieger.de</code> login with appropriate roles.</label><br/>
                        </td>
                    <tr>
                        <th scope="row" valign="top">New User Role</th>
                        <td>
                            <select name="<?php echo $this->get_field_name('role'); ?>">
                                <?php wp_dropdown_roles(strtolower($this->get_setting('role'))); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">New User Login</th>
                        <td>
                            <select name="<?php echo $this->get_field_name('user_login'); ?>">
                                <option value="uid"<?php echo $this->get_setting('user_login') == 'uid' ? ' selected=selected' : ''; ?>>Eindeutige ID im Vereinsflieger</option>
                                <option value="email"<?php echo $this->get_setting('user_login') == 'email' ? ' selected=selected' : ''; ?>>E-Mail-Adresse</option>
                                <option value="mid"<?php echo $this->get_setting('user_login') == 'mid' ? ' selected=selected' : ''; ?>>Mitgliedsnr</option>
                                <option value="lastname"<?php echo $this->get_setting('user_login') == 'lastname' ? ' selected=selected' : ''; ?>>Nachname</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">New User Nickname</th>
                        <td>
                            <select name="<?php echo $this->get_field_name('user_nicename'); ?>">
                                <option value="firstname"<?php echo $this->get_setting('user_nicename') == 'firstname' ? ' selected=selected' : ''; ?>>[Firstname]</option>
                                <option value="lastname"<?php echo $this->get_setting('user_nicename') == 'lastname' ? ' selected=selected' : ''; ?>>[Lastname]</option>
                                <option value="email"<?php echo $this->get_setting('user_nicename') == 'email' ? ' selected=selected' : ''; ?>>[E-Mail]</option>
                                <option value="first_lastname"<?php echo $this->get_setting('user_nicename') == 'first_lastname' ? ' selected=selected' : ''; ?>>[Firstname]-[Lastname]</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">New User Displayname</th>
                        <td>
                            <select name="<?php echo $this->get_field_name('user_display_name'); ?>">
                                <option value="firstname"<?php echo $this->get_setting('user_display_name') == 'firstname' ? ' selected=selected' : ''; ?>>[Firstname]</option>
                                <option value="lastname"<?php echo $this->get_setting('user_display_name') == 'lastname' ? ' selected=selected' : ''; ?>>[Lastname]</option>
                                <option value="email"<?php echo $this->get_setting('user_display_name') == 'email' ? ' selected=selected' : ''; ?>>[E-Mail]</option>
                                <option value="first_lastname"<?php echo $this->get_setting('user_display_name') == 'first_lastname' ? ' selected=selected' : ''; ?>>[Firstname] [Lastname]</option>
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
        <?php elseif ($active_tab == "user"): ?>
            <h3>Additional user data</h3>
            <p>Additional user data can be stored as user meta data. You can specify the <code>Vereinsflieger.de</code>
                attributes and the associated wordpress meta keys in the format <i>&lt;vfl_attribute_name&gt;:&lt;wordpress_meta_key&gt;</i>. Multiple attributes can be given on separate lines.</p>
            <p> Example:<br/><i>phone:user_phone_number</i><br/><i>adress:user_home_address</i></p>
            <table class="form-table" style="margin-bottom: 20px;">
                <tbody>
                    <tr>
                        <th scope="row" valign="top">Fixed Meta data</th>
                        <td>
                            <textarea disabled="disabled"><?php
                                echo join("\n", array_map(function ($attr) {
                                            return join(':', $attr);
                                        }, $VereinsfliegerLogin->fix_user_meta));
                                ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" valign="top">All/Combined Meta data</th>
                        <td>
                            <textarea name="<?php echo $this->get_field_name('user_meta_data'); ?>"><?php
                                echo join("\n", array_map(function ($attr) {
                                            return join(':', $attr);
                                        }, $VereinsfliegerLogin->get_setting('user_meta_data')));
                                ?></textarea><br/>
                            Fixed meta data will be added automatically.
                        </td>
                    </tr>
                </tbody>
            </table>
            <p><input class="button-primary" type="submit" value="Save Settings" /></p>
            <?php /* else: ?>
              <h3>Help</h3>
              <p>Here's a brief primer on how to effectively use and test Simple LDAP Login.</p>
              <h4>Testing</h4>
              <p>The most effective way to test logins is to use two browsers. In other words, keep the WordPress Dashboard open in Chrome, and use Firefox to try logging in. This will give you real time feedback on your settings and prevent you from inadvertently locking yourself out.</p>
              <h4>Which raises the question, what happens if I get locked out?</h4>
              <p>If you accidentally lock yourself out, the easiest way to get back in is to rename <strong><?php echo plugin_dir_path(__FILE__); ?></strong> to something else and then refresh. WordPress will detect the change and disable Simple LDAP Login. You can then rename the folder back to its previous name.</p>
              <?php */ endif; ?>
    </form>
</div>
