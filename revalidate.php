<?php
/**
 * Plugin Name: Revalidate
 * Description: This plugin provides on-demand revalidation of a saved post on the Next 12 front-end
 * Version: 1.0
 * Author: Ákos Blázsik
 * Text Domain: revalidate
 */
require_once plugin_dir_path(__FILE__) . 'settings.php';

function revalidate($post_id)
{
    $config = require plugin_dir_path(__FILE__) . 'config.php';

    $post = get_post($post_id);
    $slug = $post->post_name;

    // Get the values from the settings or use the default values from the external file
    $api_url = get_option('revalidate_api_url', $config['api_url']);
    $secret = get_option('revalidate_secret', $config['secret']);

    $url = add_query_arg(array('secret' => $secret, 'slug' => $slug), $api_url);
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        set_transient('revalidate_error', true, 10);
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($body['revalidated']) && $body['revalidated'] === true) {
            set_transient('revalidate_success', true, 10);
        } else {
            set_transient('revalidate_error', true, 10);
        }
    }
}
add_action('save_post', 'revalidate');

function revalidation_notice()
{
    if (get_transient('revalidate_success')) {
        delete_transient('revalidate_success');
        $class = 'notice notice-success';
        $message = __('Post successfully revalidated!', 'revalidate');
    } elseif (get_transient('revalidate_error')) {
        delete_transient('revalidate_error');
        $class = 'notice notice-error';
        $message = __('Error revalidating post. Please try again.', 'revalidate');
    } else {
        return;
    }

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}
add_action('admin_notices', 'revalidation_notice');

register_activation_hook(__FILE__, 'revalidate_activate');
function revalidate_activate()
{

}

register_deactivation_hook(__FILE__, 'revalidate_deactivate');
function revalidate_deactivate()
{

}

function revalidate_load_plugin_textdomain()
{
    load_plugin_textdomain('revalidate', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'revalidate_load_plugin_textdomain');
