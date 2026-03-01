<?php
/**
 * 模块加载器 - 按需加载所有功能模块
 */
// 只在需要时加载 UsersWP 相关功能
if (defined('USERSWP_VERSION') || class_exists('UsersWP')) {
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-account-tabs.php';
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-api-client.php';
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-license-handler.php';
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-order-handler.php';
    // 初始化
    new RW_Account_Tabs();
}

// 加载用户中心资源
require_once ASTRA_CHILD_PATH . '/inc/assets/register-assets.php';