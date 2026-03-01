<?php
/**
 * 注册静态资源
 */
// 只在账户页面加载资源
add_action('wp_enqueue_scripts', 'rw_enqueue_account_assets');

function rw_enqueue_account_assets(): void
{
    // 只在账户页面加载
    // 通过 URL 判断是否在 account 页面
    if (str_contains($_SERVER['REQUEST_URI'], '/account/')) {
        // 引入 CSS
        wp_enqueue_style(
            'rw-account-tabs',
            ASTRA_CHILD_URL . '/inc/assets/css/rw-account-tabs.css',
            array(),
            '1.0.0.0'
        );

        // 引入 JS（放在页脚）
        wp_enqueue_script(
            'rw-account-tabs',
            ASTRA_CHILD_URL . '/inc/assets/js/rw-account-tabs.js',
            array('jquery'),
            '1.0.0.0',
            true
        );

        // 本地化脚本，传递 AJAX 参数 - 添加 REST Nonce
        wp_localize_script('rw-account-tabs', 'rw_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url(), // 添加 REST URL
            'rest_nonce' => wp_create_nonce('wp_rest'), // 添加 REST Nonce
            'user_id' => get_current_user_id(),
            'nonce' => wp_create_nonce('rw_account_nonce'),
            'is_logged_in' => is_user_logged_in()
        ));

    }
}