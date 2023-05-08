<?php
/**
 * Plugin Name: Revalidate
 * Description: This plugin provides on-demand revalidation of a saved post on the Next 12 front-end
 * Version: 1.1
 * Author: Ákos Blázsik
 * Text Domain: revalidate-text-domain
 */
require_once plugin_dir_path(__FILE__) . 'settings.php';

function revalidate_meta_box()
{
    add_meta_box('revalidate', __('Revalidate', 'revalidate-text-domain'), 'revalidate_meta_box_callback', 'post', 'side', 'high');
}
add_action('add_meta_boxes', 'revalidate_meta_box');

function revalidate_meta_box_callback($post)
{
    // Nonce field for security
    wp_nonce_field('revalidate_nonce', 'revalidate_nonce_field');
    echo '<div id="revalidate_info">...</div>';
}

function revalidate()
{
    // Verify the nonce
    check_ajax_referer('revalidate_nonce', 'security');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('You do not have sufficient permissions', 'revalidate-text-domain')]);
    }

    // Check if the 'slug' is set
    if (!isset($_POST['slug'])) {
        wp_send_json_error(['message' => __('Slug is missing', 'revalidate-text-domain')]);
    }

    // Sanitize the slug
    $slug = sanitize_title($_POST['slug']);

    $config = require plugin_dir_path(__FILE__) . 'config.php';

    // Get the values from the settings or use the default values from the external file
    $api_url = get_option('revalidate_api_url', $config['api_url']);
    $secret = get_option('revalidate_secret', $config['secret']);

    $url = add_query_arg(['secret' => $secret, 'slug' => $slug], $api_url);
    $response = wp_remote_get($url);

    $error_message = __("Failed to revalidate post on the front-end.", 'revalidate-text-domain');
    $success_message = __("Post revalidated on the front-end.", 'revalidate-text-domain');

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => __('An error occurred during the request', 'revalidate-text-domain'), $response]);
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['revalidated']) && $body['revalidated'] === true) {
            if (empty($body['items'])) {
                // The 'items' property is an empty array
                wp_send_json_error(['message' => $error_message]);
            } else {
                // The 'items' property is not an empty array
                wp_send_json_success(['message' => $success_message]);
            }
        } else {
            wp_send_json_error(['message' => $error_message]);
        }
    }
}
add_action('wp_ajax_revalidate', 'revalidate');

register_activation_hook(__FILE__, 'revalidate_activate');
function revalidate_activate()
{

}

register_deactivation_hook(__FILE__, 'revalidate_deactivate');
function revalidate_deactivate()
{

}

function revalidate_load_textdomain()
{
    load_plugin_textdomain('revalidate-text-domain', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'revalidate_load_textdomain');

function revalidate_enqueue_scripts($hook)
{

    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }

    // Get the current post ID
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : null;

    // Retrieve the post slug if the post ID is available
    $post_slug = $post_id ? get_post_field('post_name', $post_id) : '';

    wp_enqueue_script('revalidate-script', plugin_dir_url(__FILE__) . 'revalidate.js', array('jquery'), '1.0', true);

    wp_localize_script('revalidate-script', 'revalidate_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('revalidate_nonce'),
        'hook' => $hook,
        'slug' => $post_slug,
    ));
}
add_action('admin_enqueue_scripts', 'revalidate_enqueue_scripts');
