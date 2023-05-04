<?php
// settings.php

function revalidate_settings_menu()
{
    add_options_page(
        __('Revalidate Settings', 'revalidate'),
        __('Revalidate Settings', 'revalidate'),
        'manage_options',
        'revalidate-settings',
        'revalidate_settings_page'
    );
}
add_action('admin_menu', 'revalidate_settings_menu');

function revalidate_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php _e('Revalidate Settings', 'revalidate');?></h1>
        <form method="post" action="options.php">
            <?php
settings_fields('revalidate-settings');
    do_settings_sections('revalidate-settings');
    submit_button();
    ?>
        </form>
    </div>
    <?php
}

function revalidate_settings_init()
{
    $config = require plugin_dir_path(__FILE__) . 'config.php';

    register_setting('revalidate-settings', 'revalidate_api_url');
    register_setting('revalidate-settings', 'revalidate_secret');

    add_settings_section(
        'revalidate_settings_section',
        __('API Settings', 'revalidate'),
        null,
        'revalidate-settings'
    );

    add_settings_field(
        'revalidate_api_url',
        __('API URL', 'revalidate'),
        'revalidate_api_url_callback',
        'revalidate-settings',
        'revalidate_settings_section'
    );

    add_settings_field(
        'revalidate_secret',
        __('Secret', 'revalidate'),
        'revalidate_secret_callback',
        'revalidate-settings',
        'revalidate_settings_section'
    );

    function revalidate_api_url_callback()
    {
        global $config;
        $api_url = get_option('revalidate_api_url', $config['api_url']);
        echo '<input type="text" name="revalidate_api_url" value="' . esc_attr($api_url) . '" size="50">';
    }

    function revalidate_secret_callback()
    {
        global $config;
        $secret = get_option('revalidate_secret', $config['secret']);
        echo '<input type="text" name="revalidate_secret" value="' . esc_attr($secret) . '" size="50">';
    }
}
add_action('admin_init', 'revalidate_settings_init');
