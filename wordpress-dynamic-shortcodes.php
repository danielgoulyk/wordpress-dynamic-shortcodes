<?php
/**
 * Plugin Name: Dynamic Values
 * Description: This plugin allows you to dynamically create shortcodes using values from custom fields on a specific page. Create easy WordPress shortcodes without code — just set a value once, and it updates across your whole site automatically.
 * Version: 2.6.2
 * Author: Daniel Goulyk (danielgoulyk.com)
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

function ds_register_settings() {
    register_setting('ds_settings_group', 'ds_page_id');
    register_setting('ds_settings_group', 'ds_shortcode_custom');
    register_setting('ds_settings_group', 'ds_enable_prefix');
}
add_action('admin_init', 'ds_register_settings');

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
    echo '<label><input type="checkbox" name="ds_enable_prefix" value="1" ' . $checked . '> Enable <code>ds_</code> prefix for shortcodes (warning: updating this will erase all existing shortcodes and mappings).</label>';
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

        $filter = isset($_GET['ds_filter']) ? strtolower($_GET['ds_filter']) : '';
        if ($filter) {
            $fields = array_filter($fields, function ($key) use ($filter) {
                return strpos(strtolower($key), $filter) !== false;
            }, ARRAY_FILTER_USE_KEY);
        }

        echo '<form method="post">';
        settings_fields('ds_settings_group');
        echo '<input type="hidden" name="ds_page_id" value="' . esc_attr($selected_page) . '">';
        echo '<h2>Shortcode Mapping</h2>';
        echo '<p>This section displays all custom fields detected on the selected page. You can assign a shortcode to any of these fields and use it anywhere across your website.</p>';
        echo '<input type="text" id="ds-search" placeholder="Search fields..." style="margin-bottom: 10px; padding: 5px; width: 300px;">';

        echo '<table class="widefat" id="ds-table">';
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

        echo '<div style="border-left: 4px solid #dba500; background: #fffbe5; padding: 10px 15px; margin-top: 20px;">
                <strong>Note:</strong> If you\'re using a caching plugin (e.g. LiteSpeed, WP Rocket, W3 Total Cache, etc.), make sure to <strong>clear the cache</strong> after saving changes to see the updates reflected on the front end.
              </div>';
        echo '</form>';

        echo '<hr><h2>Add a New Custom Field</h2>';
        echo '<form method="post">';
        echo '<input type="hidden" name="ds_add_field_page_id" value="' . esc_attr($selected_page) . '">';
        echo '<table class="form-table">';
        echo '<tr><th>Field Name</th><td><input name="ds_new_field_key" type="text" required /></td></tr>';
        echo '<tr><th>Field Value</th><td><input name="ds_new_field_value" type="text" required /></td></tr>';
        echo '<tr><th>Shortcode</th><td><input name="ds_new_field_shortcode" type="text" required /></td></tr>';
        echo '</table>';
        echo '<p><input type="submit" name="ds_add_new_field" class="button button-primary" value="Add Field & Shortcode"></p>';
        echo '</form>';
    }

    echo '</div>';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.copy-button');
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    const shortcode = button.dataset.copy;
                    if (!shortcode || shortcode === '[]') return;
                    navigator.clipboard.writeText(shortcode).then(() => {
                        button.innerText = 'Copied!';
                        setTimeout(() => button.innerText = 'Copy', 1500);
                    });
                });
            });

            const search = document.getElementById('ds-search');
            const table = document.getElementById('ds-table');
            search.addEventListener('input', function () {
                const rows = table.querySelectorAll('tbody tr');
                const query = this.value.toLowerCase();
                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
                });
            });
        });
    </script>
    <?php
}

add_action('admin_menu', function () {
    add_menu_page(
        'Dynamic Values',
        'Dynamic Values',
        'manage_options',
        'ds-settings',
        'ds_settings_page',
        'dashicons-editor-code',
        60
    );
});

function ds_handle_new_field_submission() {
    if (!current_user_can('manage_options')) return;
    if (!isset($_POST['ds_add_new_field'])) return;

    $page_id   = intval($_POST['ds_add_field_page_id']);
    $key       = sanitize_key($_POST['ds_new_field_key']);
    $value     = sanitize_text_field($_POST['ds_new_field_value']);
    $shortcode = sanitize_key($_POST['ds_new_field_shortcode']);

    if (!$page_id || !$key || !$shortcode) return;

    if (get_option('ds_enable_prefix', true)) {
        $shortcode = 'ds_' . $shortcode;
    }

    $existing_fields = get_post_meta($page_id);
    $existing_map = get_option('ds_shortcode_custom', []);

    if (array_key_exists($key, $existing_fields) || in_array($shortcode, $existing_map)) {
        wp_redirect(add_query_arg(['page' => 'ds-settings', 'ds_page' => $page_id, 'ds_message' => 'duplicate'], admin_url('admin.php')));
        exit;
    }

    update_post_meta($page_id, $key, $value);
    $existing_map[$key] = $shortcode;
    update_option('ds_shortcode_custom', $existing_map);

    wp_redirect(add_query_arg(['page' => 'ds-settings', 'ds_page' => $page_id, 'ds_message' => 'added'], admin_url('admin.php')));
    exit;
}
add_action('admin_init', 'ds_handle_new_field_submission');

add_action('admin_post_ds_delete_field', function () {
    if (!current_user_can('manage_options')) return;

    $field = sanitize_key($_POST['field_name']);
    $page_id = intval($_POST['page_id']);
    $map = get_option('ds_shortcode_custom', []);

    delete_post_meta($page_id, $field);
    unset($map[$field]);
    update_option('ds_shortcode_custom', $map);

    wp_redirect(add_query_arg(['page' => 'ds-settings', 'ds_page' => $page_id, 'ds_message' => 'deleted'], admin_url('admin.php')));
    exit;
});

function ds_register_dynamic_shortcodes() {
    $page_id = get_option('ds_page_id');
    $custom_map = get_option('ds_shortcode_custom');

    if (!$page_id || !is_array($custom_map)) return;

    foreach ($custom_map as $field_name => $shortcode) {
        $shortcode = trim($shortcode, '[] ');
        if (!$shortcode) continue;

        add_shortcode($shortcode, function () use ($field_name, $page_id) {
            $value = get_post_meta($page_id, $field_name, true);
            return $value ?: '';
        });
    }
}
add_action('init', 'ds_register_dynamic_shortcodes');