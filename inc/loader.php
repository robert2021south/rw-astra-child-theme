<?php
/**
 * 模块加载器 - 按需加载所有功能模块
 */
error_log('loader.php loaded.');
// 只在需要时加载 UsersWP 相关功能
if (defined('USERSWP_VERSION') || class_exists('UsersWP')) {
    error_log('UsersWP 已激活，版本: ' . (defined('USERSWP_VERSION') ? USERSWP_VERSION : 'unknown'));

    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-account-tabs.php';
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-api-client.php';
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-license-handler.php';
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-order-handler.php';
    error_log(__LINE__);
    // 初始化
    new RW_Account_Tabs();
}else{
    error_log('function UsersWP does not exist.');
}

// 加载用户中心资源
require_once ASTRA_CHILD_PATH . '/inc/assets/register-assets.php';