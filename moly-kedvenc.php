<?php
/*
  Plugin Name: Moly Kedvencelő
  Plugin URI: http://wordpress.org/extend/plugins/moly-kedvencelo/
  Description: A Moly kedvenc plugin segítségével könnyedén megjelenítheted a Moly.hu kedvencelő funkcióját blogbejegyzéseidnél
  Version: 1.3.1
  Author: bolint
  Author URI: http://bolint.hu
 */

add_action('admin_menu', 'molyKedvenc_options');

function molyKedvenc_options() {
    add_options_page(__('Moly kedvencelő'), __('Moly kedvencelő'), 'manage_options', basename(__FILE__), 'molyKedvenc_options_page');
}

function molyKedvenc_build_options() {
    global $post;

    $button = urlencode(get_permalink());
    
    // options
    /*
    if (get_option('molyKedvenc_show') == 'true') {
        $button .= '&amp;show=true';
    } else {
        $button .= '&amp;show=false';
    }
    */

    return $button;
}

function molyKedvenc_generate_button() {
    if (get_post_status($post->ID) == 'publish') {
        $button = '<div class="molyKedvencButton" style="' . get_option('molyKedvenc_style') . '">';
        $button .= '<iframe src="http://moly.hu/external/entries?url=' . molyKedvenc_build_options() . '" frameborder="0" scrolling="no" style="border:medium none;overflow:hidden;width:550px;height:26px;" allowtransparency="true"></iframe>';
        $button .= '</div>';
    return $button;
    } else {
        return '';
    }
}

function molyKedvenc_update($content) {
    global $post;

    // add the manual option
    if (get_option('molyKedvenc_location') == 'manual') {
        return $content;
    }
    // is it a page or feed
    if (is_page() || is_feed()) {
        return $content;
    }
    // are we on the front page
    if (get_option('molyKedvenc_display_front') == null && is_home()) {
        return $content;
    }
    $button = molyKedvenc_generate_button();
    $where = 'molyKedvenc_location';

    // are we just using the shortcode
    if (get_option($where) == 'shortcode') {
        return str_replace('[molyKedvencButton]', $button, $content);
    } else {
        // if we have switched the button off
        if (get_post_meta($post->ID, 'molyKedvencButton') == null) {
            if (get_option($where) == 'beforeandafter') {
                // adding it before and after
                return $button . $content . $button;
            } else if (get_option($where) == 'before') {
                // just before
                return $button . $content;
            } else {
                // just after
                return $content . $button;
            }
        } else {
            return $content;
        }
    }
}

function molyKedvencButton() {
    if (get_option('molyKedvenc_location') == 'manual') {
        return molyKedvenc_generate_button();
    } else {
        return false;
    }
}

function molyKedvenc_remove_filter($content) {
    if (!is_feed()) {
        remove_action('the_content', 'molyKedvenc_update');
    }
    return $content;
}

function molyKedvenc_options_page() {
    ?>
    <div class="wrap">
        <div class="icon32" id="icon-options-general"><br/></div><h2><?php _e('Moly.hu kedvencelő beállítások') ?></h2>
        <p>
            <?php _e('A Moly kedvenc plugin segítségével könnyedén megjelenítheted a Moly.hu kedvencelő funkcióját blogbejegyzéseidnél.<br />
            A megjelenés az alsó stílus mező kitöltésével formázható (a molyKedvencButton CSS osztályt is használhatpd CSS-ben)') ?>
        </p>
        <form method="post" action="options.php">
    <?php
    if (function_exists('settings_fields')) {
        settings_fields('tm-options');
    } else {
        wp_nonce_field('update-options');
        ?>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="molyKedvenc_location,molyKedvenc_style,molyKedvenc_version,molyKedvenc_display_front" />
                <?php
            }
            ?>
            <table class="form-table">
                <tr>
                <tr>
                    <th scope="row" valign="top">
                        <?php _e('Megjelenítés') ?>
                    </th>
                    <td>
                        <input type="checkbox" value="1" <?php if (get_option('molyKedvenc_display_front') == '1') echo 'checked="checked"'; ?> name="molyKedvenc_display_front" id="molyKedvenc_display_front" group="molyKedvenc_display" />
                        <label for="molyKedvenc_display_front"><?php _e('A gomb megjelenítése a nyitó oldalon') ?></label>
                    </td>
                </tr>
                <th scope="row" valign="top">
                    <?php _e('Pozíció a bejegyzésben') ?>
                </th>
                <td>
                    <select name="molyKedvenc_location">
                        <option <?php if (get_option('molyKedvenc_location') == 'before') echo 'selected="selected"'; ?> value="before"><?php _e('Előtte') ?></option>
                        <option <?php if (get_option('molyKedvenc_location') == 'after') echo 'selected="selected"'; ?> value="after"><?php _e('Utána') ?></option>
                        <option <?php if (get_option('molyKedvenc_location') == 'beforeandafter') echo 'selected="selected"'; ?> value="beforeandafter"><?php _e('Előtte és utána') ?></option>
                        <option <?php if (get_option('molyKedvenc_location') == 'shortcode') echo 'selected="selected"'; ?> value="shortcode"><?php _e('Egyedi kód [molyKedvencbutton]') ?></option>
                        <option <?php if (get_option('molyKedvenc_location') == 'manual') echo 'selected="selected"'; ?> value="manual"><?php _e('Kézi') ?></option>
                    </select>
                </td>
                </tr>
                <tr>
                    <th scope="row" valign="top"><label for="molyKedvenc_style"><?php _e('Stílus') ?></label></th>
                    <td>
                        <input type="text" value="<?php echo htmlspecialchars(get_option('molyKedvenc_style')); ?>" name="molyKedvenc_style" id="molyKedvenc_style" />
                        <span class="description"><?php _e('Itt adhatsz egyedi CSS szabályokat a kód megjelenítéséhez pl.') ?>: <code>margin-top: 10px; width: 500px;</code></span>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
            </p>
            <h3><?php _e('Kapcsolódó oldalak') ?>:</h3>
            <p>
                <a href="http://bolint.hu" target="_blank"><img src="http://bolint.hu/wp-content/uploads/2013/10/bolint100-e1380744960357.png" title="bolint.hu" alt="bolint.hu" style="border: 0px; opacity: 0.3;" onmouseover="this.style.opacity=1;if(this.filters)this.filters.alpha.opacity=100" onmouseout="this.style.opacity=0.3;if(this.filters)this.filters.alpha.opacity=30" /></a>
                <a href="http://moly.hu" target="_blank"><img src="http://bolint.hu/wp-content/uploads/2013/10/moly100-e1380744919433.png" title="moly.hu" alt="moly.hu" style="border: 0px; opacity: 0.3;margin-left: 20px;" onmouseover="this.style.opacity=1;if(this.filters)this.filters.alpha.opacity=100" onmouseout="this.style.opacity=0.3;if(this.filters)this.filters.alpha.opacity=30" /></a>
            </p>
        </form>
    </div>
    <?php
}

// register these variables for admin page
function molyKedvenc_init() {
    if (function_exists('register_setting')) {
        register_setting('tm-options', 'molyKedvenc_display_front');
        register_setting('tm-options', 'molyKedvenc_style');
        register_setting('tm-options', 'molyKedvenc_version');
        register_setting('tm-options', 'molyKedvenc_location');
    }
}

// Only all the admin options if the user is an admin
if (is_admin()) {
    add_action('admin_menu', 'molyKedvenc_options');
    add_action('admin_init', 'molyKedvenc_init');
}

// Set the default options when the plugin is activated
function molyKedvenc_activate() {
    add_option('molyKedvenc_location', 'after');
    add_option('molyKedvenc_style', '');
    add_option('molyKedvenc_display_front', '1');
}

add_filter('the_content', 'molyKedvenc_update', 8);
add_filter('get_the_excerpt', 'molyKedvenc_remove_filter', 9);

register_activation_hook(__FILE__, 'molyKedvenc_activate');
