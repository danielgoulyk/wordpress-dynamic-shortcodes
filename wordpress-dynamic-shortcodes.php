<?php
/**
 * Plugin Name: Dynamic Shortcodes (No ACF Required)
 * Description: Dynamically register shortcodes from custom field values on a selected page. Fully standalone—no ACF needed.
 * Version: 3.0
 * Author: Daniel Goulyk (danielgoulyk.com)
 */

function ds_register_settings() {
    register_setting('ds_settings_group', 'ds_page_id');
    register_setting('ds_settings_group', 'ds_shortcode_custom');
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
}
add_action('admin_init', 'ds_save_field_values');

function ds_settings_page() {
    $selected_page = isset($_GET['ds_page']) ? intval($_GET['ds_page']) : get_option('ds_page_id');
    $shortcode_map = get_option('ds_shortcode_custom', []);

    echo '<div class="wrap">';
    echo '<h1>Dynamic Shortcodes</h1>';
    echo '<p>This plugin allows you to dynamically create shortcodes using values from custom fields on a specific page. Select the page, assign shortcode names to each custom field, and display those values anywhere using shortcodes.</p>';

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
    echo '<p class="description">Values from this page will be used to populate shortcodes across your site.</p>';
    echo '</form>';

    if ($selected_page) {
        update_option('ds_page_id', $selected_page);
        $meta_raw = get_post_meta($selected_page);
        $fields = [];

        foreach ($meta_raw as $key => $values) {
            if (strpos($key, '_') === 0) continue; // skip internal WP meta keys
            $fields[$key] = maybe_unserialize($values[0]);
        }

        if (empty($fields)) {
            echo '<p><em>No custom fields found for this page. Add some via the "Custom Fields" box when editing the page.</em></p>';
        } else {
            echo '<form method="post" action="options.php">';
            settings_fields('ds_settings_group');
            echo '<input type="hidden" name="ds_page_id" value="' . esc_attr($selected_page) . '">';

            echo '<h2>Shortcode Mapping</h2>';
            echo '<table class="widefat">';
            echo '<thead>
                <tr>
                    <th>Field Name<br><small>The name of your custom field.</small></th>
                    <th>Shortcode Name<br><small>What users will enter between brackets.</small></th>
                    <th>Copy<br><small>Copy shortcode name to clipboard.</small></th>
                    <th>Value<br><small>Current value (editable).</small></th>
                </tr>
            </thead><tbody>';

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
                </tr>";
            }

            echo '</tbody></table>';
            submit_button('Save Changes');

            echo '<div class="notice notice-warning inline" style="margin-top: 20px;"><p><strong>Note:</strong> If you’re using a caching plugin, clear your cache to see updates on the front end.</p></div>';
            echo '</form>';
        }
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
        });
    </script>
    <?php
}

add_action('admin_menu', function () {
    add_options_page('Dynamic Shortcodes', 'Shortcodes', 'manage_options', 'ds-settings', 'ds_settings_page');
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