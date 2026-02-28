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

    // Pass transaction_id, slug and version to JavaScript
    $transaction_id = isset($_GET['transaction_id']) ? sanitize_text_field($_GET['transaction_id']) : '';
    $plugin_slug = isset($_GET['slug']) ? sanitize_text_field($_GET['slug']) : '';
    $plugin_version = isset($_GET['version']) ? sanitize_text_field($_GET['version']) : '';

    // Pass PHP variables to JavaScript
    wp_localize_script('order-complete-min-js', 'orderConfig', array(
        'transactionId' => $transaction_id,
        'pluginSlug' => $plugin_slug,
        'pluginVersion' => $plugin_version,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('order_complete_nonce')
    ));
}

// Add custom page template
add_filter('theme_page_templates', 'add_order_complete_template');

function add_order_complete_template($templates) {
    $templates['page-templates/order-complete.php'] = 'Order Complete';
    return $templates;
}

// Load custom template
add_filter('template_include', 'load_order_complete_template');

function load_order_complete_template($template) {
    if (is_page() && get_page_template_slug() === 'page-templates/order-complete.php') {
        $new_template = get_stylesheet_directory() . '/page-templates/order-complete.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}

/**
 * Astra Child Theme Functions
 */

// 定义主题常量
define('ASTRA_CHILD_PATH', get_stylesheet_directory());
define('ASTRA_CHILD_URL', get_stylesheet_directory_uri());

// 加载模块加载器
require_once ASTRA_CHILD_PATH . '/inc/loader.php';