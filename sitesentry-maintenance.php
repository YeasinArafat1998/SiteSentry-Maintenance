<?php
/*
Plugin Name: SiteSentry Maintenance
Description: A sophisticated maintenance mode plugin for WordPress that allows administrators to schedule maintenance windows, customize messages using a WYSIWYG editor, and exclude administrators from the maintenance restrictions.
Version: 1.0
Author: Your Name
*/

// Hook for adding admin menus
add_action('admin_menu', 'sitesentry_maintenance_menu');

// Action function for above hook
function sitesentry_maintenance_menu() {
    add_menu_page('SiteSentry Maintenance', 'SiteSentry Maintenance', 'administrator', 'sitesentry_maintenance', 'sitesentry_maintenance_settings_page');
}

// Function to display the settings page
function sitesentry_maintenance_settings_page() {
    ?>
    <div class="wrap">
        <h2>SiteSentry Maintenance Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('sitesentry_maintenance_options_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Maintenance Mode Enabled:</th>
                    <td><input type="checkbox" name="maintenance_mode_enabled" value="1" <?php checked(1, get_option('maintenance_mode_enabled'), true); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Maintenance Message:</th>
                    <td>
                        <?php
                        $content = get_option('maintenance_message');
                        $editor_id = 'maintenance_message';
                        $settings = array(
                            'textarea_name' => 'maintenance_message'
                        );
                        wp_editor($content, $editor_id, $settings);
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Start Time:</th>
                    <td><input type="datetime-local" name="maintenance_start_time" value="<?php echo get_option('maintenance_start_time'); ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">End Time:</th>
                    <td><input type="datetime-local" name="maintenance_end_time" value="<?php echo get_option('maintenance_end_time'); ?>"></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register and define the settings
add_action('admin_init', 'sitesentry_maintenance_admin_init');
function sitesentry_maintenance_admin_init(){
    register_setting('sitesentry_maintenance_options_group', 'maintenance_mode_enabled');
    register_setting('sitesentry_maintenance_options_group', 'maintenance_message', 'sitesentry_maintenance_message_sanitize');
    register_setting('sitesentry_maintenance_options_group', 'maintenance_start_time');
    register_setting('sitesentry_maintenance_options_group', 'maintenance_end_time');
}

function sitesentry_maintenance_message_sanitize($input) {
    return wp_kses_post($input);
}

// Hook into the WordPress 'init' action to check for maintenance mode
add_action('init', 'sitesentry_check_for_maintenance_mode');
function sitesentry_check_for_maintenance_mode() {
    if (current_user_can('administrator')) {
        return; // Skip maintenance mode for administrators
    }

    $isEnabled = get_option('maintenance_mode_enabled');
    $startTime = strtotime(get_option('maintenance_start_time'));
    $endTime = strtotime(get_option('maintenance_end_time'));
    $currentTime = time();

    if ($isEnabled && $currentTime > $startTime && $currentTime < $endTime) {
        wp_die(wp_kses_post(get_option('maintenance_message')));
    }
}
?>
