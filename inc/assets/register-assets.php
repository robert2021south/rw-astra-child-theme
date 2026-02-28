<?php
/**
 * 注册静态资源
 */
error_log('register-assets.php loaded.');

// 只在账户页面加载资源
add_action('wp_enqueue_scripts', 'rw_enqueue_account_assets');

function rw_enqueue_account_assets(): void
{
    // 只在账户页面加载
    if (function_exists('uwp_get_current_page_type') && uwp_get_current_page_type() == 'account') {

        // 引入 CSS
        wp_enqueue_style(
            'rw-account-tabs',
            ASTRA_CHILD_URL . '/inc/assets/css/rw-account-tabs.css',
            array(),
            '1.0.0'
        );

        // 引入 JS（放在页脚）
        wp_enqueue_script(
            'rw-account-tabs',
            ASTRA_CHILD_URL . '/inc/assets/js/rw-account-tabs.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // 本地化脚本，传递 AJAX 参数
        wp_localize_script('rw-account-tabs', 'rw_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'user_id' => get_current_user_id(),
            'nonce' => wp_create_nonce('rw_account_nonce')
        ));
    }
}