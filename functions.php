<?php
/**
 * Astra Child Theme functions and definitions
 */

// Load parent theme styles
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');

function astra_child_enqueue_styles(): void
{
    // Load parent theme styles
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');

    // Load child theme styles (if needed)
    wp_enqueue_style('astra-child-style', get_stylesheet_uri(), array('astra-parent-style'));
}

// Load custom resources only on the order-complete page
add_action('wp_enqueue_scripts', 'load_order_complete_assets');

function load_order_complete_assets(): void
{
    // Load only on the order-complete page
    if (!is_page('order-complete')) {
        return;
    }

    // Load custom CSS
    wp_enqueue_style(
        'order-complete-min-css',
        get_stylesheet_directory_uri() . '/assets/css/order-complete.min.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/order-complete.min.css')
    );

    // Load custom JS
    wp_enqueue_script(
        'order-complete-min-js',
        get_stylesheet_directory_uri() . '/assets/js/order-complete.min.js',
        array(), // Dependencies: add array('jquery') if jQuery is needed
        filemtime(get_stylesheet_directory() . '/assets/js/order-complete.min.js'),
        true // Load in footer
    );

    // Pass transaction_id to JavaScript
    $transaction_id = isset($_GET['transaction_id']) ? sanitize_text_field($_GET['transaction_id']) : '';

    wp_localize_script('order-complete-min-js', 'orderConfig', array(
        'transactionId' => $transaction_id,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('order_complete_nonce')
    ));
}