<?php
/**
 * Plugin Name: Dynamic Shortcodes (No ACF Required)
 * Description: This plugin allows you to dynamically create shortcodes using values from custom fields on a specific page. Create easy WordPress shortcodes without code — just set a value once, and it updates across your whole site automatically.
 * Version: 2.6
 * Author: Daniel Goulyk (danielgoulyk.com)
 * Requires at least: 5.5
 * Tested up to: 6.8.1
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Register settings
function ds_register_settings() {
    register_setting('ds_settings_group', 'ds_page_id');
    register_setting('ds_settings_group', 'ds_shortcode_custom');
    register_setting('ds_settings_group', 'ds_enable_prefix');
}
add_action('admin_init', 'ds_register_settings');

// Save field values
function ds_save_field_values() {
    if (!current_user_can('manage_options')) return;

    if (
        isset($_POST['ds_field_values']) &&
        isset($_POST['ds_page_id']) &&
        is_array($_POST['ds_field_values'])
    ) {
        $page_id = intval($_POST['ds_page_id']);
        foreach ($_POST['ds_field_values'] as $field_key => $field_value) {
            update_post_meta($page_id, $field_key, sanitize_text_field($field_value));
        }
    }

    if (isset($_POST['ds_shortcode_custom'])) {
        update_option('ds_shortcode_custom', array_map('sanitize_text_field', $_POST['ds_shortcode_custom']));
    }
}
add_action('admin_init', 'ds_save_field_values');

// Create admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'Dynamic Values',
        'Dynamic Values',
        'manage_options',
        'ds-settings',
        'ds_settings_page',
        'dashicons-editor-table',
        60
    );
});

// Admin UI
function ds_settings_page() {
    $selected_page = isset($_GET['ds_page']) ? intval($_GET['ds_page']) : get_option('ds_page_id');
    $shortcode_map = get_option('ds_shortcode_custom', []);
    $prefix_enabled = get_option('ds_enable_prefix', true);

    echo '<div class="wrap">';
    echo '<h1>Dynamic Values</h1>';
    echo '<p>This plugin allows you to dynamically create shortcodes using values from custom fields on a specific page. Create easy WordPress shortcodes without code — just set a value once, and it updates across your whole site automatically.</p>';

    if (isset($_GET['ds_message'])) {
        $message = sanitize_text_field($_GET['ds_message']);
        if ($message === 'added') {
            echo '<div class="notice notice-success is-dismissible"><p>New field and shortcode added successfully.</p></div>';
        } elseif ($message === 'duplicate') {
            echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> Field name or shortcode already exists.</p></div>';
        } elseif ($message === 'deleted') {
            echo '<div class="notice notice-warning is-dismissible"><p>Field and shortcode deleted.</p></div>';
        }
    }

    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="ds-settings" />';
    echo '<label for="ds_page">Select Source Page:</label><br>';
    echo '<select name="ds_page" id="ds_page" onchange="this.form.submit()">';
    echo '<option value="">-- Select a Page --</option>';
    foreach (get_pages() as $page) {
        $selected = ($selected_page == $page->ID) ? 'selected' : '';
        echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
    }
    echo '</select>';
    echo '</form>';

    echo '<form method="post" action="options.php">';
    settings_fields('ds_settings_group');
    $checked = $prefix_enabled ? 'checked' : '';
    echo '<h2>Plugin Settings</h2>';
    echo '<label><input type="checkbox" name="ds_enable_prefix" value="1" ' . $checked . '> Enable <code>ds_</code> prefix for shortcodes</label>';
    submit_button('Save Settings');
    echo '</form>';

    if ($selected_page) {
        update_option('ds_page_id', $selected_page);
        $meta_raw = get_post_meta($selected_page);
        $fields = [];
        foreach ($meta_raw as $key => $values) {
            if (strpos($key, '_') === 0) continue;
            $fields[$key] = maybe_unserialize($values[0]);
        }

        echo '<form method="post">';
        settings_fields('ds_settings_group');
        echo '<input type="hidden" name="ds_page_id" value="' . esc_attr($selected_page) . '">';
        echo '<h2>Shortcode Mapping</h2>';
        echo '<p>This section displays all custom fields detected on the selected page. You can assign a shortcode to any of these fields and use it anywhere across your website.</p>';
        echo '<table class="widefat">';
        echo '<thead><tr>
                <th>Field Name<br><small>This is the field WordPress uses to define custom fields.</small></th>
                <th>Shortcode<br><small>This is the shortcode which defines the “variable”.</small></th>
                <th>Copy<br><small>Copy shortcode name to clipboard.</small></th>
                <th>Value<br><small>The value of the custom field.</small></th>
                <th>Delete<br><small>Remove field and mapping.</small></th>
              </tr></thead><tbody>';

        foreach ($fields as $field_name => $value) {
            $shortcode = $shortcode_map[$field_name] ?? '';
            $copy_text = $shortcode ? "[{$shortcode}]" : '';
            $copy_disabled = $shortcode ? '' : 'disabled style="opacity:0.5;"';
            $value_escaped = esc_attr($value);
            $shortcode_escaped = esc_attr($shortcode);

            echo "<tr>
                <td><code>{$field_name}</code></td>
                <td><input type='text' name='ds_shortcode_custom[{$field_name}]' value='{$shortcode_escaped}' /></td>
                <td><button type='button' class='button copy-button' data-copy='{$copy_text}' {$copy_disabled}>Copy</button></td>
                <td><input type='text' name='ds_field_values[{$field_name}]' value='{$value_escaped}' /></td>
                <td>
                    <form method='post' action='" . esc_url(admin_url('admin-post.php')) . "' onsubmit=\"return confirm('Are you sure?')\">
                        <input type='hidden' name='action' value='ds_delete_field'>
                        <input type='hidden' name='field_name' value='{$field_name}'>
                        <input type='hidden' name='page_id' value='{$selected_page}'>
                        <input type='submit' class='button button-secondary' value='Delete'>
                    </form>
                </td>
            </tr>";
        }

        echo '</tbody></table>';
        submit_button('Save Changes');
        echo '<div style="border-left: 4px solid #dba617; padding: 10px; margin-top: 20px; background: #fffbe5;">
                <strong>Note:</strong> If you